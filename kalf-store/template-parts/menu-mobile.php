<?php
/**
 * Menu mobile em tela cheia, fundo laranja, links grandes.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="kalf-menu-mobile" class="kalf-menu-mobile" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'kalf-store' ); ?>" hidden>
	<div class="kalf-menu-mobile__topo">
		<span class="kalf-logo">
			<span class="kalf-logo__marca kalf-logo__marca--preto"></span>
			<span class="kalf-logo__texto">KALF STORE</span>
		</span>
		<button type="button" class="kalf-btn-icone kalf-menu-mobile__fechar" data-kalf-menu-fechar aria-label="<?php esc_attr_e( 'Fechar menu', 'kalf-store' ); ?>">
			<?php kalf_icone( 'fechar', 22 ); ?>
		</button>
	</div>

	<nav class="kalf-menu-mobile__nav" aria-label="<?php esc_attr_e( 'Principal', 'kalf-store' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Início', 'kalf-store' ); ?></a>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>"><?php esc_html_e( 'Celulares', 'kalf-store' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Contato', 'kalf-store' ); ?></a>
	</nav>

	<div class="kalf-menu-mobile__rodape">
		<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--secundario kalf-btn--bloco" data-whatsapp-evento="menu-mobile" target="_blank" rel="noopener">
			<?php kalf_icone( 'whatsapp', 20 ); ?>
			<?php esc_html_e( 'Falar no WhatsApp', 'kalf-store' ); ?>
		</a>
		<?php $instagram = kalf_config( 'instagram', '[INSTAGRAM]' ); ?>
		<a href="<?php echo esc_url( 'https://instagram.com/' . ltrim( $instagram, '@' ) ); ?>" class="kalf-btn kalf-btn--contorno kalf-btn--bloco" target="_blank" rel="noopener">
			<?php kalf_icone( 'instagram', 20 ); ?>
			@<?php echo esc_html( ltrim( $instagram, '@' ) ); ?>
		</a>
		<span class="kalf-menu-mobile__endereco">
			<?php echo esc_html( kalf_config( 'endereco', '[ENDEREÇO COMPLETO]' ) ); ?>, Goiânia – GO · <?php echo esc_html( kalf_config( 'horario', '[HORÁRIO]' ) ); ?>
		</span>
	</div>
</div>
