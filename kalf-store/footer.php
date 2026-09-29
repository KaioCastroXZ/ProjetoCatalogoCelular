<?php
/**
 * Rodapé: dados da loja, navegação, redes, copyright.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$endereco  = kalf_config( 'endereco', '[ENDEREÇO COMPLETO]' );
$horario   = kalf_config( 'horario', '[HORÁRIO]' );
$instagram = ltrim( kalf_config( 'instagram', '[INSTAGRAM]' ), '@' );
$cnpj      = kalf_config( 'cnpj' );
$whatsapp  = kalf_config( 'whatsapp', '[DDD] [NÚMERO]' );
?>
	<footer class="kalf-rodape">
		<div class="kalf-container kalf-rodape__grade">
			<div class="kalf-rodape__sobre">
				<span class="kalf-logo">
					<span class="kalf-logo__marca"></span>
					<span class="kalf-logo__texto">KALF STORE</span>
				</span>
				<p><?php esc_html_e( 'Celulares novos e seminovos e assistência técnica em Goiânia – GO.', 'kalf-store' ); ?></p>
			</div>

			<div class="kalf-rodape__coluna">
				<span class="kalf-rodape__titulo-coluna"><?php esc_html_e( 'Loja', 'kalf-store' ); ?></span>
				<span class="kalf-rodape__texto"><?php echo esc_html( $endereco ); ?><br>Goiânia – GO<br><?php echo esc_html( $horario ); ?></span>
			</div>

			<nav class="kalf-rodape__coluna" aria-label="<?php esc_attr_e( 'Rodapé', 'kalf-store' ); ?>">
				<span class="kalf-rodape__titulo-coluna"><?php esc_html_e( 'Navegação', 'kalf-store' ); ?></span>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Início', 'kalf-store' ); ?></a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>"><?php esc_html_e( 'Celulares', 'kalf-store' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Contato', 'kalf-store' ); ?></a>
			</nav>

			<div class="kalf-rodape__coluna">
				<span class="kalf-rodape__titulo-coluna"><?php esc_html_e( 'Redes', 'kalf-store' ); ?></span>
				<a href="<?php echo esc_url( 'https://instagram.com/' . $instagram ); ?>" target="_blank" rel="noopener">
					<?php kalf_icone( 'instagram', 18 ); ?>@<?php echo esc_html( $instagram ); ?>
				</a>
				<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" data-whatsapp-evento="rodape" target="_blank" rel="noopener">
					<?php kalf_icone( 'whatsapp', 18 ); ?><?php echo esc_html( $whatsapp ); ?>
				</a>
			</div>
		</div>

		<div class="kalf-container kalf-rodape__base">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> KALF STORE<?php echo $cnpj ? ' · CNPJ ' . esc_html( $cnpj ) : ''; ?></span>
			<span>Goiânia – GO</span>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
