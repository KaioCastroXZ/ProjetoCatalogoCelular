<?php
/**
 * SEO e compartilhamento: título/description automáticos, Open Graph com
 * a foto do produto e schema.org (Product na página do aparelho,
 * LocalBusiness na home). Usa o sitemap nativo do WordPress.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta description automática por tipo de página.
 */
function kalf_meta_description() {
	$descricao = '';

	if ( is_singular( 'aparelho' ) ) {
		$d         = kalf_dados_aparelho( get_the_ID() );
		$preco     = $d['preco_txt'] ? $d['preco_txt'] : __( 'consulte o preço', 'kalf-store' );
		$descricao = sprintf(
			/* translators: 1: nome do aparelho, 2: condicao, 3: preco */
			__( '%1$s — %2$s. %3$s na KALF STORE, Goiânia. Fale no WhatsApp.', 'kalf-store' ),
			$d['titulo'],
			$d['condicao_txt'],
			$preco
		);
	} elseif ( is_post_type_archive( 'aparelho' ) || is_tax( 'marca' ) ) {
		$descricao = __( 'Celulares novos e seminovos em Goiânia – GO. Filtre por marca, condição e armazenamento e chame no WhatsApp.', 'kalf-store' );
	} elseif ( is_front_page() ) {
		$descricao = kalf_config(
			'hero_subtitulo',
			__( 'iPhones e Androids novos e seminovos, com assistência técnica, em Goiânia – GO.', 'kalf-store' )
		);
	}

	if ( $descricao ) {
		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $descricao ) ) . '">' . "\n";
	}

	return $descricao;
}

/**
 * Open Graph + Twitter Card. Na página do produto, usa a foto principal —
 * é o que faz o link aparecer bonito quando colado no WhatsApp/Instagram.
 */
function kalf_open_graph() {
	$titulo = wp_get_document_title();
	$url    = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$imagem = '';

	if ( is_singular( 'aparelho' ) ) {
		$imagem_id = get_post_thumbnail_id();
		if ( $imagem_id ) {
			$imagem = wp_get_attachment_image_url( $imagem_id, 'kalf-galeria' );
		}
		$url = get_permalink();
	} else {
		$url = home_url( '/' === substr( $_SERVER['REQUEST_URI'] ?? '/', 0, 1 ) ? $_SERVER['REQUEST_URI'] : '/' );
	}

	$tipo = is_singular( 'aparelho' ) ? 'product' : 'website';

	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $tipo ) );
	printf( '<meta property="og:site_name" content="KALF STORE">' . "\n" );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $titulo ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="pt_BR">' . "\n" );

	$descricao = kalf_meta_description_valor();
	if ( $descricao ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $descricao ) ) );
	}

	if ( $imagem ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $imagem ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	} else {
		echo '<meta name="twitter:card" content="summary">' . "\n";
	}
}

/**
 * Mesma lógica de kalf_meta_description(), mas devolvendo o texto em vez
 * de ecoar a tag — para o Open Graph reaproveitar sem duplicar a regra.
 */
function kalf_meta_description_valor() {
	if ( is_singular( 'aparelho' ) ) {
		$d     = kalf_dados_aparelho( get_the_ID() );
		$preco = $d['preco_txt'] ? $d['preco_txt'] : __( 'consulte o preço', 'kalf-store' );
		return sprintf(
			/* translators: 1: nome do aparelho, 2: condicao, 3: preco */
			__( '%1$s — %2$s. %3$s na KALF STORE, Goiânia. Fale no WhatsApp.', 'kalf-store' ),
			$d['titulo'],
			$d['condicao_txt'],
			$preco
		);
	}
	if ( is_post_type_archive( 'aparelho' ) || is_tax( 'marca' ) ) {
		return __( 'Celulares novos e seminovos em Goiânia – GO. Filtre por marca, condição e armazenamento e chame no WhatsApp.', 'kalf-store' );
	}
	if ( is_front_page() ) {
		return kalf_config( 'hero_subtitulo', __( 'iPhones e Androids novos e seminovos, com assistência técnica, em Goiânia – GO.', 'kalf-store' ) );
	}
	return '';
}

add_action( 'wp_head', 'kalf_meta_description', 1 );
add_action( 'wp_head', 'kalf_open_graph', 1 );

/**
 * schema.org Product, com Offer só quando há preço (o design pede
 * explicitamente para não inventar "R$ 0,00").
 */
function kalf_schema_produto() {
	if ( ! is_singular( 'aparelho' ) ) {
		return;
	}

	$d = kalf_dados_aparelho( get_the_ID() );

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $d['titulo'],
		'url'         => $d['url'],
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => $d['marca'] ? $d['marca'] : 'KALF STORE',
		),
		'itemCondition' => 'seminovo' === $d['condicao']
			? 'https://schema.org/UsedCondition'
			: 'https://schema.org/NewCondition',
	);

	$imagem_id = get_post_thumbnail_id();
	if ( $imagem_id ) {
		$schema['image'] = wp_get_attachment_image_url( $imagem_id, 'kalf-galeria' );
	}

	if ( $d['preco'] > 0 ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'priceCurrency' => 'BRL',
			'price'         => number_format( $d['preco'], 2, '.', '' ),
			'availability'  => $d['vendido']
				? 'https://schema.org/SoldOut'
				: 'https://schema.org/InStock',
			'url'           => $d['url'],
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'kalf_schema_produto' );

/**
 * schema.org LocalBusiness na home, com os dados vindos das configurações.
 */
function kalf_schema_loja() {
	if ( ! is_front_page() ) {
		return;
	}

	$endereco = kalf_config( 'endereco' );
	if ( '' === $endereco ) {
		return;
	}

	$schema = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'ElectronicsStore',
		'name'      => 'KALF STORE',
		'url'       => home_url( '/' ),
		'address'   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $endereco,
			'addressLocality' => 'Goiânia',
			'addressRegion'   => 'GO',
			'addressCountry'  => 'BR',
			'postalCode'      => kalf_config( 'cep' ),
		),
	);

	$whatsapp = kalf_whatsapp_numero();
	if ( $whatsapp ) {
		$schema['telephone'] = '+' . $whatsapp;
	}

	$instagram = kalf_config( 'instagram' );
	if ( $instagram ) {
		$schema['sameAs'] = array( 'https://instagram.com/' . ltrim( $instagram, '@' ) );
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'kalf_schema_loja' );

/**
 * Google Tag Manager: só entra se o ID foi preenchido nas configurações.
 */
function kalf_gtm_head() {
	$id = kalf_config( 'gtm_id' );
	if ( '' === $id ) {
		return;
	}
	?>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $id ); ?>');</script>
	<?php
}
add_action( 'wp_head', 'kalf_gtm_head', 1 );

function kalf_gtm_body() {
	$id = kalf_config( 'gtm_id' );
	if ( '' === $id ) {
		return;
	}
	?>
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $id ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<?php
}
add_action( 'wp_body_open', 'kalf_gtm_body' );

/**
 * Garante que o sitemap nativo do WP inclua o CPT — ele ja inclui por
 * padrao quando public=true e show_in_rest nao bloqueia, mas deixamos
 * explicito para nao depender do comportamento default mudar.
 */
function kalf_sitemap_aparelhos( $tipos ) {
	$tipos['aparelho'] = get_post_type_object( 'aparelho' );
	return $tipos;
}
add_filter( 'wp_sitemaps_post_types', 'kalf_sitemap_aparelhos' );
