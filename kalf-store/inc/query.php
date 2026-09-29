<?php
/**
 * Consulta do catálogo: filtros por marca/condição/armazenamento, busca,
 * ordenação e vendidos sempre no fim da lista.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Le os parametros de filtro da URL (funciona tanto no ?marca=apple&condicao=...
 * quanto na chamada AJAX, que manda os mesmos nomes por POST).
 *
 * @param array $origem $_GET ou o payload do AJAX.
 * @return array
 */
function kalf_ler_filtros( $origem ) {
	$marcas = array();
	if ( ! empty( $origem['marca'] ) ) {
		$marcas = array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $origem['marca'] ) ) ) );
	}

	$condicoes = array();
	if ( ! empty( $origem['condicao'] ) ) {
		$permitidas = array_keys( kalf_opcoes_condicao() );
		$condicoes  = array_intersect( array_map( 'sanitize_key', explode( ',', wp_unslash( $origem['condicao'] ) ) ), $permitidas );
	}

	$armazenamentos = array();
	if ( ! empty( $origem['armazenamento'] ) ) {
		$permitidos     = array_keys( kalf_opcoes_armazenamento() );
		$armazenamentos = array_intersect( array_map( 'sanitize_key', explode( ',', wp_unslash( $origem['armazenamento'] ) ) ), $permitidos );
	}

	$ordenacoes_validas = array( 'recentes', 'menor-preco', 'maior-preco' );
	$ordenar            = ! empty( $origem['ordenar'] ) && in_array( $origem['ordenar'], $ordenacoes_validas, true )
		? sanitize_key( $origem['ordenar'] )
		: 'recentes';

	return array(
		'busca'          => ! empty( $origem['busca'] ) ? sanitize_text_field( wp_unslash( $origem['busca'] ) ) : '',
		'marca'          => $marcas,
		'condicao'       => $condicoes,
		'armazenamento'  => $armazenamentos,
		'ordenar'        => $ordenar,
		'pagina'         => ! empty( $origem['pagina'] ) ? max( 1, (int) $origem['pagina'] ) : 1,
	);
}

/**
 * Monta e executa a WP_Query do catálogo.
 *
 * Vendidos sempre no fim: como o WP não ordena por meta booleano de forma
 * nativa junto com outros criterios, buscamos disponiveis e vendidos em
 * duas consultas e concatenamos.
 *
 * @param array $filtros   Saída de kalf_ler_filtros().
 * @param int   $por_pagina Quantos itens por página (12, conforme o design).
 * @return array{itens:WP_Post[],total:int,paginas:int}
 */
function kalf_consultar_catalogo( $filtros, $por_pagina = 12 ) {
	$args_base = array(
		'post_type'      => 'aparelho',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => false,
	);

	if ( $filtros['busca'] ) {
		$args_base['s'] = $filtros['busca'];
	}

	$tax_query = array();
	if ( $filtros['marca'] ) {
		$tax_query[] = array(
			'taxonomy' => 'marca',
			'field'    => 'slug',
			'terms'    => $filtros['marca'],
		);
	}
	if ( $tax_query ) {
		$args_base['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$meta_query = array( 'relation' => 'AND' );
	if ( $filtros['condicao'] ) {
		$meta_query[] = array(
			'key'     => '_kalf_condicao',
			'value'   => $filtros['condicao'],
			'compare' => 'IN',
		);
	}
	if ( $filtros['armazenamento'] ) {
		$meta_query[] = array(
			'key'     => '_kalf_armazenamento',
			'value'   => $filtros['armazenamento'],
			'compare' => 'IN',
		);
	}

	switch ( $filtros['ordenar'] ) {
		case 'menor-preco':
			$orderby = array( 'meta_value_num' => 'ASC', 'date' => 'DESC' );
			$args_base['meta_key'] = '_kalf_preco'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			break;
		case 'maior-preco':
			$orderby = array( 'meta_value_num' => 'DESC', 'date' => 'DESC' );
			$args_base['meta_key'] = '_kalf_preco'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			break;
		default:
			$orderby = 'date';
	}
	$args_base['orderby'] = $orderby;

	// Disponiveis primeiro.
	$args_disponiveis                = $args_base;
	$args_disponiveis['meta_query']  = array_merge( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$meta_query,
		array(
			array(
				'relation' => 'OR',
				array( 'key' => '_kalf_vendido', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_kalf_vendido', 'value' => '1', 'compare' => '!=' ),
			),
		)
	);
	$disponiveis = get_posts( $args_disponiveis );

	// Vendidos por ultimo, sempre por data (a ordenacao de preco nao importa mais aqui).
	$args_vendidos               = $args_base;
	$args_vendidos['orderby']    = 'date';
	unset( $args_vendidos['meta_key'] );
	$args_vendidos['meta_query'] = array_merge( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$meta_query,
		array( array( 'key' => '_kalf_vendido', 'value' => '1' ) )
	);
	$vendidos = get_posts( $args_vendidos );

	$todos  = array_merge( $disponiveis, $vendidos );
	$total  = count( $todos );
	$fatia  = array_slice( $todos, 0, $filtros['pagina'] * $por_pagina );

	return array(
		'itens'   => $fatia,
		'total'   => $total,
		'paginas' => (int) ceil( $total / $por_pagina ),
	);
}

/**
 * Quantos aparelhos existem por marca, para o contador dos filtros.
 *
 * @return array<string,int> slug => quantidade.
 */
function kalf_contagem_por_marca() {
	$termos  = get_terms( array( 'taxonomy' => 'marca', 'hide_empty' => false ) );
	$contagem = array();
	if ( ! is_wp_error( $termos ) ) {
		foreach ( $termos as $termo ) {
			$contagem[ $termo->slug ] = (int) $termo->count;
		}
	}
	return $contagem;
}

/**
 * Endpoint AJAX: devolve o HTML da grade de cards + metadados de paginação,
 * para o catálogo filtrar sem recarregar a página.
 */
function kalf_ajax_catalogo() {
	check_ajax_referer( 'kalf_catalogo', 'nonce' );

	$filtros    = kalf_ler_filtros( $_POST );
	$por_pagina = 12;
	$resultado  = kalf_consultar_catalogo( $filtros, $por_pagina );

	ob_start();
	if ( $resultado['itens'] ) {
		foreach ( $resultado['itens'] as $post ) {
			get_template_part( 'template-parts/card-produto', null, array( 'aparelho_id' => $post->ID ) );
		}
	}
	$html = ob_get_clean();
	$vazio = 0 === $resultado['total'];

	$vazio_html = '';
	if ( $vazio ) {
		ob_start();
		get_template_part(
			'template-parts/estado-vazio',
			null,
			array(
				'contexto' => $filtros['busca'] ? 'busca' : 'catalogo',
				'termo'    => $filtros['busca'],
			)
		);
		$vazio_html = ob_get_clean();
	}

	wp_send_json_success(
		array(
			'html'        => $html,
			'total'       => $resultado['total'],
			'mostrando'   => count( $resultado['itens'] ),
			'temMais'     => count( $resultado['itens'] ) < $resultado['total'],
			'vazio'       => $vazio,
			'vazioHtml'   => $vazio_html,
		)
	);
}
add_action( 'wp_ajax_kalf_catalogo', 'kalf_ajax_catalogo' );
add_action( 'wp_ajax_nopriv_kalf_catalogo', 'kalf_ajax_catalogo' );
