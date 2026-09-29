<?php
/**
 * Bloco "Venha nos visitar": endereço, horário, WhatsApp, Maps e Waze.
 * Reaproveitado na home e na página de contato. O mapa só carrega o
 * iframe quando entra na tela.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$endereco    = kalf_config( 'endereco', '[ENDEREÇO COMPLETO]' );
$horario     = kalf_config( 'horario', '[DIAS E HORÁRIO DE FUNCIONAMENTO]' );
$maps_link   = kalf_config( 'maps_link' );
$waze_link   = kalf_config( 'waze_link' );
$maps_embed  = kalf_config( 'maps_embed' );
$whatsapp    = kalf_config( 'whatsapp', '[DDD] [NÚMERO]' );
?>
<section class="kalf-container kalf-secao">
	<div class="kalf-contato">
		<div class="kalf-revelar">
			<h2 style="margin-bottom: var(--s-6);"><?php esc_html_e( 'Venha nos visitar', 'kalf-store' ); ?></h2>
			<div class="kalf-contato__info">
				<div class="kalf-info-linha">
					<span class="kalf-info-linha__icone"><?php kalf_icone( 'pino', 22 ); ?></span>
					<div class="kalf-info-linha__texto">
						<span class="kalf-info-linha__legenda"><?php esc_html_e( 'Endereço', 'kalf-store' ); ?></span>
						<strong class="kalf-info-linha__valor"><?php echo esc_html( $endereco ); ?>, Goiânia – GO</strong>
					</div>
				</div>
				<div class="kalf-info-linha">
					<span class="kalf-info-linha__icone"><?php kalf_icone( 'relogio', 22 ); ?></span>
					<div class="kalf-info-linha__texto">
						<span class="kalf-info-linha__legenda"><?php esc_html_e( 'Horário', 'kalf-store' ); ?></span>
						<strong class="kalf-info-linha__valor"><?php echo esc_html( $horario ); ?></strong>
					</div>
				</div>
				<div class="kalf-info-linha">
					<span class="kalf-info-linha__icone"><?php kalf_icone( 'whatsapp', 22 ); ?></span>
					<div class="kalf-info-linha__texto">
						<span class="kalf-info-linha__legenda">WhatsApp</span>
						<strong class="kalf-info-linha__valor"><?php echo esc_html( $whatsapp ); ?></strong>
					</div>
				</div>
			</div>
			<div class="kalf-contato__botoes" style="margin-top: var(--s-6);">
				<a href="<?php echo esc_url( $maps_link ? $maps_link : '#maps' ); ?>" class="kalf-btn kalf-btn--secundario" target="_blank" rel="noopener">
					<?php kalf_icone( 'pino', 18 ); ?><?php esc_html_e( 'Abrir no Google Maps', 'kalf-store' ); ?>
				</a>
				<a href="<?php echo esc_url( $waze_link ? $waze_link : '#waze' ); ?>" class="kalf-btn kalf-btn--contorno" target="_blank" rel="noopener">
					<?php kalf_icone( 'waze', 18 ); ?><?php esc_html_e( 'Abrir no Waze', 'kalf-store' ); ?>
				</a>
			</div>
		</div>

		<div class="kalf-mapa kalf-revelar" <?php echo $maps_embed ? 'data-kalf-mapa="' . esc_url( $maps_embed ) . '"' : ''; ?>>
			<span class="kalf-mapa__pino"><?php kalf_icone( 'pino', 28 ); ?></span>
			<span class="kalf-mapa__legenda">
				<?php echo $maps_embed
					? esc_html__( 'Mapa carregando…', 'kalf-store' )
					: esc_html__( '[MAPA — Google Maps incorporado]', 'kalf-store' ); ?>
			</span>
		</div>
	</div>
</section>
