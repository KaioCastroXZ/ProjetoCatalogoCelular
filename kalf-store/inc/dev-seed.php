<?php
/**
 * Ferramenta de desenvolvimento: cadastra 8 aparelhos de exemplo cobrindo
 * os casos dificeis pedidos no prompt (sem preco, sem foto, titulo longo,
 * 1 foto, galeria cheia, vendido, seminovo com bateria).
 *
 * Só existe neste arquivo enquanto o tema está em construção — remova
 * inc/dev-seed.php (e a linha que o inclui em functions.php) antes de
 * publicar o site.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visite /wp-admin/?kalf_seed=1 logado como administrador para rodar.
 * Idempotente: apaga os aparelhos [EXEMPLO] criados antes de recriar.
 */
function kalf_dev_seed() {
	if ( ! isset( $_GET['kalf_seed'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Limpa exemplos anteriores.
	$antigos = get_posts( array( 'post_type' => 'aparelho', 'posts_per_page' => -1, 'fields' => 'ids' ) );
	foreach ( $antigos as $id ) {
		wp_delete_post( $id, true );
	}

	$pasta = KALF_DIR . '/_seed-images/';

	$anexar = function ( $arquivo, $titulo_pai_id ) use ( $pasta ) {
		$caminho = $pasta . $arquivo;
		if ( ! file_exists( $caminho ) ) {
			return 0;
		}
		$tipo      = wp_check_filetype( $arquivo, null );
		$upload    = wp_upload_bits( $arquivo, null, file_get_contents( $caminho ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( $upload['error'] ) {
			return 0;
		}
		$anexo_id = wp_insert_attachment(
			array(
				'post_mime_type' => $tipo['type'],
				'post_title'     => sanitize_file_name( $arquivo ),
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$titulo_pai_id
		);
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $anexo_id, wp_generate_attachment_metadata( $anexo_id, $upload['file'] ) );
		return $anexo_id;
	};

	$criar = function ( $dados ) use ( $anexar ) {
		$id = wp_insert_post(
			array(
				'post_title'  => $dados['titulo'],
				'post_type'   => 'aparelho',
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $id ) ) {
			return;
		}

		if ( ! empty( $dados['marca'] ) ) {
			wp_set_object_terms( $id, $dados['marca'], 'marca' );
		}

		$meta = array(
			'_kalf_condicao'      => $dados['condicao'] ?? 'novo',
			'_kalf_preco'         => $dados['preco'] ?? '',
			'_kalf_preco_antigo'  => $dados['preco_antigo'] ?? '',
			'_kalf_parcelas'      => $dados['parcelas'] ?? '',
			'_kalf_parcela_valor' => $dados['parcela_valor'] ?? '',
			'_kalf_armazenamento' => $dados['armazenamento'] ?? '',
			'_kalf_cor'           => $dados['cor'] ?? '',
			'_kalf_cor_hex'       => $dados['cor_hex'] ?? '',
			'_kalf_bateria'       => $dados['bateria'] ?? '',
			'_kalf_observacoes'   => $dados['observacoes'] ?? '',
			'_kalf_destaque'      => ! empty( $dados['destaque'] ) ? 1 : 0,
			'_kalf_vendido'       => ! empty( $dados['vendido'] ) ? 1 : 0,
		);
		foreach ( $meta as $chave => $valor ) {
			update_post_meta( $id, $chave, $valor );
		}

		$fotos = $dados['fotos'] ?? array();
		if ( $fotos ) {
			$primeira = $anexar( $fotos[0], $id );
			if ( $primeira ) {
				set_post_thumbnail( $id, $primeira );
			}
			$resto = array();
			foreach ( array_slice( $fotos, 1 ) as $arquivo ) {
				$anexo = $anexar( $arquivo, $id );
				if ( $anexo ) {
					$resto[] = $anexo;
				}
			}
			if ( $resto ) {
				update_post_meta( $id, '_kalf_galeria', implode( ',', $resto ) );
			}
		}
	};

	$criar(
		array(
			'titulo'        => 'iPhone 15 128GB Preto [EXEMPLO]',
			'marca'         => 'Apple',
			'condicao'      => 'novo',
			'preco'         => '5299.00',
			'parcelas'      => 10,
			'parcela_valor' => '529.90',
			'armazenamento' => '128',
			'cor'           => 'Preto',
			'cor_hex'       => '#1a1a1a',
			'destaque'      => true,
			'fotos'         => array( 'iphone15-preto.png' ),
		)
	);

	$criar(
		array(
			'titulo'        => 'iPhone 13 128GB Meia-noite [EXEMPLO]',
			'marca'         => 'Apple',
			'condicao'      => 'seminovo',
			'preco'         => '3199.00',
			'preco_antigo'  => '3599.00',
			'parcelas'      => 12,
			'parcela_valor' => '299.90',
			'armazenamento' => '128',
			'cor'           => 'Meia-noite',
			'cor_hex'       => '#1f2430',
			'bateria'       => '92',
			'destaque'      => true,
			'observacoes'   => 'Acompanha carregador original.',
			'fotos'         => array( 'iphone13-laranja.png', 'galeria-extra-1.png', 'galeria-extra-2.png' ),
		)
	);

	$criar(
		array(
			// Caso dificil: sem preco.
			'titulo'        => 'Galaxy S23 256GB Creme [EXEMPLO]',
			'marca'         => 'Samsung',
			'condicao'      => 'novo',
			'armazenamento' => '256',
			'cor'           => 'Creme',
			'cor_hex'       => '#f0e6d2',
			'destaque'      => true,
			'fotos'         => array( 'galaxy-creme.png' ),
		)
	);

	$criar(
		array(
			// Caso dificil: titulo muito longo.
			'titulo'        => 'iPhone 15 Pro Max 256GB Titânio Natural Seminovo Bateria 92% [EXEMPLO]',
			'marca'         => 'Apple',
			'condicao'      => 'seminovo',
			'preco'         => '6499.00',
			'parcelas'      => 12,
			'parcela_valor' => '599.90',
			'armazenamento' => '256',
			'cor'           => 'Titânio natural',
			'cor_hex'       => '#8a3700',
			'bateria'       => '92',
			'fotos'         => array( 'iphone14-roxo.png' ),
		)
	);

	$criar(
		array(
			// Caso dificil: sem foto nenhuma.
			'titulo'        => 'iPhone SE 64GB Estelar [EXEMPLO]',
			'marca'         => 'Apple',
			'condicao'      => 'seminovo',
			'preco'         => '1899.00',
			'armazenamento' => '64',
			'cor'           => 'Estelar',
			'cor_hex'       => '#e8e0d0',
			'bateria'       => '87',
			'fotos'         => array(),
		)
	);

	$criar(
		array(
			// Caso dificil: vendido.
			'titulo'        => 'iPhone XR 64GB Vermelho [EXEMPLO]',
			'marca'         => 'Apple',
			'condicao'      => 'seminovo',
			'preco'         => '1599.00',
			'armazenamento' => '64',
			'cor'           => 'Vermelho',
			'cor_hex'       => '#b22d2d',
			'bateria'       => '84',
			'vendido'       => true,
			'fotos'         => array( 'iphonexr-vermelho.png' ),
		)
	);

	$criar(
		array(
			// Caso dificil: galeria cheia (8 fotos, o maximo permitido).
			'titulo'        => 'Moto G84 256GB Grafite [EXEMPLO]',
			'marca'         => 'Motorola',
			'condicao'      => 'novo',
			'preco'         => '1799.00',
			'parcelas'      => 10,
			'parcela_valor' => '179.90',
			'armazenamento' => '256',
			'cor'           => 'Grafite',
			'cor_hex'       => '#4a4a4a',
			'destaque'      => true,
			'fotos'         => array(
				'moto-grafite.png',
				'iphone15-preto.png',
				'iphone13-laranja.png',
				'galaxy-creme.png',
				'iphone14-roxo.png',
				'iphone11-verde.png',
				'redmi-azul.png',
				'iphonexr-vermelho.png',
			),
		)
	);

	$criar(
		array(
			'titulo'        => 'Redmi Note 13 128GB Azul [EXEMPLO]',
			'marca'         => 'Xiaomi',
			'condicao'      => 'novo',
			'armazenamento' => '128',
			'cor'           => 'Azul',
			'cor_hex'       => '#254482',
			'fotos'         => array( 'redmi-azul.png' ),
		)
	);

	wp_safe_redirect( admin_url( 'edit.php?post_type=aparelho' ) );
	exit;
}
add_action( 'admin_init', 'kalf_dev_seed' );
