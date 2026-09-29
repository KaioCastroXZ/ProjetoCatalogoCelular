<?php
/**
 * Cabeçalho: logo, menu, botão WhatsApp. No mobile, hambúrguer que abre
 * em tela cheia.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#FE6B01">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'no-js' ); ?>>
<?php wp_body_open(); ?>

<a class="kalf-pular" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'kalf-store' ); ?></a>

<header class="kalf-header">
	<div class="kalf-header__linha kalf-container">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kalf-logo" aria-label="<?php esc_attr_e( 'KALF STORE, página inicial', 'kalf-store' ); ?>">
			<span class="kalf-logo__marca"></span>
			<span class="kalf-logo__texto">KALF STORE</span>
		</a>

		<nav class="kalf-nav-principal" aria-label="<?php esc_attr_e( 'Principal', 'kalf-store' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kalf-nav-principal__link" <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'Início', 'kalf-store' ); ?>
			</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-nav-principal__link" <?php echo ( is_post_type_archive( 'aparelho' ) || is_tax( 'marca' ) ) ? 'aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'Celulares', 'kalf-store' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/contato/' ) ); ?>" class="kalf-nav-principal__link" <?php echo is_page( 'contato' ) ? 'aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'Contato', 'kalf-store' ); ?>
			</a>
		</nav>

		<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--primario kalf-header__whatsapp" data-whatsapp-evento="header" target="_blank" rel="noopener">
			<?php kalf_icone( 'whatsapp', 20 ); ?>
			<?php esc_html_e( 'Falar no WhatsApp', 'kalf-store' ); ?>
		</a>

		<div class="kalf-header__mobile-acoes">
			<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn-icone kalf-btn-icone--laranja" aria-label="<?php esc_attr_e( 'Falar no WhatsApp', 'kalf-store' ); ?>" data-whatsapp-evento="header-mobile" target="_blank" rel="noopener">
				<?php kalf_icone( 'whatsapp', 20 ); ?>
			</a>
			<button type="button" class="kalf-btn-icone" data-kalf-menu-abrir aria-haspopup="dialog" aria-controls="kalf-menu-mobile" aria-label="<?php esc_attr_e( 'Abrir menu', 'kalf-store' ); ?>">
				<?php kalf_icone( 'menu', 24 ); ?>
			</button>
		</div>
	</div>
</header>

<?php get_template_part( 'template-parts/menu-mobile' ); ?>
