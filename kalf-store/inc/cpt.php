<?php
/**
 * Custom Post Type "aparelho" e taxonomia "marca".
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT aparelho. As URLs ficam em /celulares/<slug>/ e o arquivo em /celulares/.
 */
function kalf_registrar_cpt() {
	register_post_type(
		'aparelho',
		array(
			'labels'              => array(
				'name'                  => __( 'Aparelhos', 'kalf-store' ),
				'singular_name'         => __( 'Aparelho', 'kalf-store' ),
				'menu_name'             => __( 'Aparelhos', 'kalf-store' ),
				'add_new'               => __( 'Cadastrar aparelho', 'kalf-store' ),
				'add_new_item'          => __( 'Cadastrar aparelho', 'kalf-store' ),
				'edit_item'             => __( 'Editar aparelho', 'kalf-store' ),
				'new_item'              => __( 'Novo aparelho', 'kalf-store' ),
				'view_item'             => __( 'Ver aparelho', 'kalf-store' ),
				'view_items'            => __( 'Ver aparelhos', 'kalf-store' ),
				'search_items'          => __( 'Buscar aparelhos', 'kalf-store' ),
				'not_found'             => __( 'Nenhum aparelho cadastrado ainda.', 'kalf-store' ),
				'not_found_in_trash'    => __( 'Nenhum aparelho na lixeira.', 'kalf-store' ),
				'featured_image'        => __( 'Foto principal', 'kalf-store' ),
				'set_featured_image'    => __( 'Escolher a foto principal', 'kalf-store' ),
				'remove_featured_image' => __( 'Remover a foto principal', 'kalf-store' ),
				'use_featured_image'    => __( 'Usar como foto principal', 'kalf-store' ),
				'item_published'        => __( 'Aparelho publicado. Já está no site.', 'kalf-store' ),
				'item_updated'          => __( 'Aparelho atualizado.', 'kalf-store' ),
			),
			'public'              => true,
			'has_archive'         => 'celulares',
			'rewrite'             => array(
				'slug'       => 'celulares',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-smartphone',
			'menu_position'       => 5,
			'supports'            => array( 'title', 'thumbnail' ),
			'taxonomies'          => array( 'marca' ),
			'show_in_rest'        => false,
			'exclude_from_search' => false,
			'hierarchical'        => false,
		)
	);
}
add_action( 'init', 'kalf_registrar_cpt' );

/**
 * Taxonomia marca. Nao hierarquica no comportamento, mas com a caixa de
 * checkbox do admin, que e mais simples para quem nao e tecnico do que a
 * caixa de tags de texto livre.
 */
function kalf_registrar_taxonomia() {
	register_taxonomy(
		'marca',
		'aparelho',
		array(
			'labels'            => array(
				'name'          => __( 'Marcas', 'kalf-store' ),
				'singular_name' => __( 'Marca', 'kalf-store' ),
				'menu_name'     => __( 'Marcas', 'kalf-store' ),
				'add_new_item'  => __( 'Adicionar marca', 'kalf-store' ),
				'edit_item'     => __( 'Editar marca', 'kalf-store' ),
				'all_items'     => __( 'Todas as marcas', 'kalf-store' ),
				'not_found'     => __( 'Nenhuma marca cadastrada.', 'kalf-store' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => array(
				'slug'       => 'marca',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'kalf_registrar_taxonomia' );

/**
 * Cria as marcas iniciais na primeira ativacao e forca o flush das rewrites.
 */
function kalf_ativar_tema() {
	$marcas = array( 'Apple', 'Samsung', 'Motorola', 'Xiaomi', 'Outras' );

	foreach ( $marcas as $marca ) {
		if ( ! term_exists( $marca, 'marca' ) ) {
			wp_insert_term( $marca, 'marca' );
		}
	}

	// A pagina /contato/ precisa existir como Page para o WordPress
	// encontrar o template page-contato.php pela hierarquia de slug.
	if ( ! get_page_by_path( 'contato' ) ) {
		wp_insert_post(
			array(
				'post_title'   => __( 'Contato', 'kalf-store' ),
				'post_name'    => 'contato',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kalf_ativar_tema' );

/**
 * Opcoes fixas de armazenamento e condicao, num lugar so.
 */
function kalf_opcoes_armazenamento() {
	return array(
		'64'  => '64 GB',
		'128' => '128 GB',
		'256' => '256 GB',
		'512' => '512 GB+',
	);
}

function kalf_opcoes_condicao() {
	return array(
		'novo'     => __( 'Novo', 'kalf-store' ),
		'seminovo' => __( 'Seminovo', 'kalf-store' ),
	);
}
