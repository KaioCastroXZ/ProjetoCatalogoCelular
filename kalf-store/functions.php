<?php
/**
 * KALF STORE — bootstrap do tema.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KALF_VERSAO', '0.1.0' );
define( 'KALF_DIR', get_template_directory() );
define( 'KALF_URI', get_template_directory_uri() );

require_once KALF_DIR . '/inc/helpers.php';
require_once KALF_DIR . '/inc/cpt.php';
require_once KALF_DIR . '/inc/meta-fields.php';
require_once KALF_DIR . '/inc/settings.php';
require_once KALF_DIR . '/inc/admin.php';
require_once KALF_DIR . '/inc/query.php';
require_once KALF_DIR . '/inc/seo.php';

// Ferramenta de desenvolvimento — remover antes de publicar (ver o topo do arquivo).
if ( file_exists( KALF_DIR . '/inc/dev-seed.php' ) ) {
	require_once KALF_DIR . '/inc/dev-seed.php';
}

/**
 * Suportes e tamanhos de imagem.
 */
function kalf_setup() {
	load_theme_textdomain( 'kalf-store', KALF_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );

	// A foto do aparelho nunca e cortada: o recorte fica na moldura 4:5 via CSS.
	// Os tamanhos abaixo so limitam o lado maior, preservando a proporcao original.
	add_image_size( 'kalf-card', 600, 750, false );
	add_image_size( 'kalf-galeria', 1200, 1500, false );
	add_image_size( 'kalf-mini', 180, 225, false );

	register_nav_menus(
		array(
			'principal' => __( 'Menu principal', 'kalf-store' ),
			'rodape'    => __( 'Menu do rodapé', 'kalf-store' ),
		)
	);
}
add_action( 'after_setup_theme', 'kalf_setup' );

/**
 * O dono fotografa com o celular, entao os arquivos chegam grandes.
 * Reduz o lado maior para 1200px no upload.
 */
add_filter( 'big_image_size_threshold', function () {
	return 1200;
} );

/**
 * Converte as fotos de aparelho para WebP quando o servidor suporta,
 * mantendo o arquivo original (o WordPress guarda os dois).
 */
add_filter( 'image_editor_output_format', function ( $formatos ) {
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		return $formatos;
	}
	$formatos['image/jpeg'] = 'image/webp';
	$formatos['image/png']  = 'image/webp';
	return $formatos;
} );

/**
 * CSS e JS do front.
 */
function kalf_assets() {
	$css = array( 'tokens', 'base', 'componentes', 'layout' );
	$dep = array();

	foreach ( $css as $arquivo ) {
		$caminho = KALF_DIR . "/assets/css/{$arquivo}.css";
		if ( ! file_exists( $caminho ) ) {
			continue;
		}
		$handle = "kalf-{$arquivo}";
		wp_enqueue_style( $handle, KALF_URI . "/assets/css/{$arquivo}.css", $dep, filemtime( $caminho ) );
		$dep[] = $handle;
	}

	if ( is_post_type_archive( 'aparelho' ) || is_tax( 'marca' ) ) {
		$caminho = KALF_DIR . '/assets/css/catalogo.css';
		wp_enqueue_style( 'kalf-catalogo', KALF_URI . '/assets/css/catalogo.css', $dep, filemtime( $caminho ) );
		$dep[] = 'kalf-catalogo';
	}

	if ( is_singular( 'aparelho' ) ) {
		$caminho = KALF_DIR . '/assets/css/produto.css';
		wp_enqueue_style( 'kalf-produto', KALF_URI . '/assets/css/produto.css', $dep, filemtime( $caminho ) );
		$dep[] = 'kalf-produto';
	}

	if ( is_page( 'contato' ) ) {
		$caminho = KALF_DIR . '/assets/css/contato.css';
		wp_enqueue_style( 'kalf-contato', KALF_URI . '/assets/css/contato.css', $dep, filemtime( $caminho ) );
		$dep[] = 'kalf-contato';
	}

	// Handle esperado pelo WordPress para o style.css do tema.
	wp_enqueue_style( 'kalf-store', KALF_URI . '/style.css', $dep, KALF_VERSAO );

	wp_enqueue_script(
		'kalf-app',
		KALF_URI . '/assets/js/app.js',
		array(),
		file_exists( KALF_DIR . '/assets/js/app.js' ) ? filemtime( KALF_DIR . '/assets/js/app.js' ) : KALF_VERSAO,
		true
	);

	if ( is_post_type_archive( 'aparelho' ) || is_tax( 'marca' ) || is_search() ) {
		wp_enqueue_script(
			'kalf-catalogo',
			KALF_URI . '/assets/js/catalogo.js',
			array( 'kalf-app' ),
			file_exists( KALF_DIR . '/assets/js/catalogo.js' ) ? filemtime( KALF_DIR . '/assets/js/catalogo.js' ) : KALF_VERSAO,
			true
		);
		$marcas_termos = get_terms( array( 'taxonomy' => 'marca', 'hide_empty' => false ) );
		$rotulos_marca = array();
		if ( ! is_wp_error( $marcas_termos ) ) {
			foreach ( $marcas_termos as $termo ) {
				$rotulos_marca[ $termo->slug ] = $termo->name;
			}
		}

		wp_localize_script(
			'kalf-catalogo',
			'kalfCatalogo',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'kalf_catalogo' ),
				'base'    => get_post_type_archive_link( 'aparelho' ),
				'rotulos' => array(
					'marca'         => $rotulos_marca,
					'condicao'      => kalf_opcoes_condicao(),
					'armazenamento' => kalf_opcoes_armazenamento(),
				),
			)
		);
	}

	if ( is_singular( 'aparelho' ) ) {
		wp_enqueue_script(
			'kalf-galeria',
			KALF_URI . '/assets/js/galeria.js',
			array( 'kalf-app' ),
			file_exists( KALF_DIR . '/assets/js/galeria.js' ) ? filemtime( KALF_DIR . '/assets/js/galeria.js' ) : KALF_VERSAO,
			true
		);
	}

	if ( is_front_page() || is_page( 'contato' ) ) {
		wp_enqueue_script(
			'kalf-mapa',
			KALF_URI . '/assets/js/mapa.js',
			array( 'kalf-app' ),
			file_exists( KALF_DIR . '/assets/js/mapa.js' ) ? filemtime( KALF_DIR . '/assets/js/mapa.js' ) : KALF_VERSAO,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'kalf_assets' );

/**
 * Fontes do Google com preconnect e display=swap.
 */
function kalf_fontes() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="preload" as="style" href="' . esc_url( kalf_url_fontes() ) . '">' . "\n";
	echo '<link rel="stylesheet" href="' . esc_url( kalf_url_fontes() ) . '" media="print" onload="this.media=\'all\'">' . "\n";
	echo '<noscript><link rel="stylesheet" href="' . esc_url( kalf_url_fontes() ) . '"></noscript>' . "\n";
}
add_action( 'wp_head', 'kalf_fontes', 2 );

/**
 * URL das duas familias do design.
 */
function kalf_url_fontes() {
	return add_query_arg(
		array(
			'family'  => 'Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800|Figtree:wght@400;500;600;700',
			'display' => 'swap',
		),
		'https://fonts.googleapis.com/css2'
	);
}

/**
 * Remove a classe no-js assim que o JS roda.
 */
function kalf_classe_no_js() {
	echo '<script>document.documentElement.classList.remove("no-js");</script>' . "\n";
}
add_action( 'wp_body_open', 'kalf_classe_no_js', 1 );

/**
 * Barra de admin desligada no site publico. Ela fica com z-index mais
 * alto que qualquer coisa do tema e, no mobile, sobrepoe controles como
 * o botao de fechar o menu — sem ela, quem navega logado (o proprio
 * dono da loja) ve o site exatamente como o cliente ve.
 */
add_filter( 'show_admin_bar', '__return_false' );

/**
 * Alt automatico: se a imagem nao tem alt, usa o titulo do aparelho.
 */
function kalf_alt_automatico( $attr, $attachment ) {
	if ( empty( $attr['alt'] ) ) {
		$pai = wp_get_post_parent_id( $attachment->ID );
		$attr['alt'] = $pai ? get_the_title( $pai ) : get_the_title( $attachment->ID );
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'kalf_alt_automatico', 10, 2 );
