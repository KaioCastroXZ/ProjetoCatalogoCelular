<?php
/**
 * Estado de "busca sem resultado" ou "catálogo vazio".
 *
 * @package kalf-store
 * @var array $args { contexto: 'busca'|'catalogo', termo?: string }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contexto = isset( $args['contexto'] ) ? $args['contexto'] : 'catalogo';
$termo    = isset( $args['termo'] ) ? $args['termo'] : '';
?>
<div class="kalf-estado">
	<span class="kalf-estado__icone">
		<?php echo 'busca' === $contexto ? kalf_icone( 'busca', 32 ) : kalf_icone( 'celular', 32 ); ?>
	</span>

	<?php if ( 'busca' === $contexto ) : ?>
		<h2><?php echo esc_html( sprintf( __( 'Nada encontrado para "%s"', 'kalf-store' ), $termo ) ); ?></h2>
		<p><?php esc_html_e( 'Confira a grafia ou tente um termo mais simples, como "iPhone 13". Se não achar, pergunte pra gente.', 'kalf-store' ); ?></p>
		<div class="kalf-estado__acoes">
			<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--primario kalf-btn--bloco" data-whatsapp-evento="busca-vazia" target="_blank" rel="noopener"><?php esc_html_e( 'Perguntar no WhatsApp', 'kalf-store' ); ?></a>
			<button type="button" class="kalf-btn kalf-btn--contorno kalf-btn--bloco" data-kalf-limpar-busca><?php esc_html_e( 'Limpar busca', 'kalf-store' ); ?></button>
		</div>
	<?php else : ?>
		<h2><?php esc_html_e( 'Estamos atualizando o catálogo', 'kalf-store' ); ?></h2>
		<p><?php esc_html_e( 'Fale com a gente no WhatsApp para saber quais aparelhos estão disponíveis na loja agora.', 'kalf-store' ); ?></p>
		<div class="kalf-estado__acoes">
			<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--primario kalf-btn--bloco" data-whatsapp-evento="catalogo-vazio" target="_blank" rel="noopener"><?php esc_html_e( 'Falar no WhatsApp', 'kalf-store' ); ?></a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-btn kalf-btn--contorno kalf-btn--bloco"><?php esc_html_e( 'Limpar filtros', 'kalf-store' ); ?></a>
		</div>
	<?php endif; ?>
</div>
