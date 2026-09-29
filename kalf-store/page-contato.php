<?php
/**
 * Template: Página de contato.
 * Crie uma página no WP chamada "Contato" (slug contato) e selecione
 * este template, ou deixe o slug /contato/ que ele é pego automaticamente.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$instagram = ltrim( kalf_config( 'instagram', '[INSTAGRAM]' ), '@' );
?>

<main id="conteudo">

	<section class="kalf-container kalf-contato-topo">
		<h1><?php esc_html_e( 'Fale com a gente', 'kalf-store' ); ?></h1>
		<p><?php esc_html_e( 'O jeito mais rápido é o WhatsApp. Se preferir, venha até a loja em Goiânia.', 'kalf-store' ); ?></p>
	</section>

	<section class="kalf-container kalf-secao">
		<div class="kalf-contato">
			<div class="kalf-contato__coluna-esq">
				<div class="kalf-whatsapp-destaque">
					<span class="kalf-whatsapp-destaque__rotulo">WhatsApp</span>
					<strong class="kalf-whatsapp-destaque__numero"><?php echo esc_html( kalf_config( 'whatsapp', '([DDD]) [NÚMERO]' ) ); ?></strong>
					<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--secundario kalf-btn--bloco" data-whatsapp-evento="contato-destaque" target="_blank" rel="noopener">
						<?php kalf_icone( 'whatsapp', 20 ); ?><?php esc_html_e( 'Chamar no WhatsApp', 'kalf-store' ); ?>
					</a>
				</div>

				<div class="kalf-contato-card">
					<div class="kalf-info-linha">
						<span class="kalf-info-linha__icone"><?php kalf_icone( 'pino', 22 ); ?></span>
						<div class="kalf-info-linha__texto">
							<span class="kalf-info-linha__legenda"><?php esc_html_e( 'Endereço', 'kalf-store' ); ?></span>
							<strong class="kalf-info-linha__valor"><?php echo esc_html( kalf_config( 'endereco', '[ENDEREÇO COMPLETO]' ) ); ?><br>Goiânia – GO, <?php echo esc_html( kalf_config( 'cep', '[CEP]' ) ); ?></strong>
						</div>
					</div>
					<div class="kalf-contato__botoes">
						<a href="<?php echo esc_url( kalf_config( 'maps_link', '#maps' ) ); ?>" class="kalf-btn kalf-btn--secundario" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir no Maps', 'kalf-store' ); ?></a>
						<a href="<?php echo esc_url( kalf_config( 'waze_link', '#waze' ) ); ?>" class="kalf-btn kalf-btn--contorno" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir no Waze', 'kalf-store' ); ?></a>
					</div>
				</div>

				<div class="kalf-contato-grade2">
					<div class="kalf-contato-card">
						<span class="kalf-contato-card__icone"><?php kalf_icone( 'relogio', 20 ); ?></span>
						<span class="kalf-info-linha__legenda"><?php esc_html_e( 'Horário', 'kalf-store' ); ?></span>
						<strong class="kalf-produto__extra"><?php echo nl2br( esc_html( kalf_config( 'horario', '[SEG–SEX: HH–HH] / [SÁB: HH–HH]' ) ) ); ?></strong>
					</div>
					<a href="<?php echo esc_url( 'https://instagram.com/' . $instagram ); ?>" class="kalf-contato-card" target="_blank" rel="noopener">
						<span class="kalf-contato-card__icone"><?php kalf_icone( 'instagram', 20 ); ?></span>
						<span class="kalf-info-linha__legenda"><?php esc_html_e( 'Instagram', 'kalf-store' ); ?></span>
						<strong>@<?php echo esc_html( $instagram ); ?></strong>
					</a>
				</div>
			</div>

			<div class="kalf-mapa" <?php $embed = kalf_config( 'maps_embed' ); echo $embed ? 'data-kalf-mapa="' . esc_url( $embed ) . '"' : ''; ?>>
				<span class="kalf-mapa__pino"><?php kalf_icone( 'pino', 28 ); ?></span>
				<span class="kalf-mapa__legenda">
					<?php echo $embed ? esc_html__( 'Mapa carregando…', 'kalf-store' ) : esc_html__( '[MAPA — Google Maps incorporado]', 'kalf-store' ); ?>
				</span>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
