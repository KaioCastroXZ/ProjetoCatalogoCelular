<?php
/**
 * 404: reaproveita o mesmo bloco de "estado vazio" do catálogo, com um
 * link de volta e para o catálogo.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="conteudo">
	<section class="kalf-container kalf-secao">
		<div class="kalf-estado">
			<span class="kalf-estado__icone"><?php kalf_icone( 'busca', 32 ); ?></span>
			<h1><?php esc_html_e( 'Página não encontrada', 'kalf-store' ); ?></h1>
			<p><?php esc_html_e( 'O link pode ter mudado ou o aparelho já não está mais disponível. Confira o catálogo atual ou fale com a gente.', 'kalf-store' ); ?></p>
			<div class="kalf-estado__acoes">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-btn kalf-btn--primario kalf-btn--bloco"><?php esc_html_e( 'Ver catálogo', 'kalf-store' ); ?></a>
				<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--contorno kalf-btn--bloco" data-whatsapp-evento="404" target="_blank" rel="noopener"><?php esc_html_e( 'Falar no WhatsApp', 'kalf-store' ); ?></a>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
