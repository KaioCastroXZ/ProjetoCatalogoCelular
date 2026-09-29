<?php
/**
 * Card de produto. Reaproveitado na home, catálogo, relacionados e nas
 * respostas AJAX do filtro — então toda a lógica de exibição mora aqui,
 * uma única vez.
 *
 * @package kalf-store
 * @var array $args { aparelho_id?: int }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aparelho_id = isset( $args['aparelho_id'] ) ? (int) $args['aparelho_id'] : get_the_ID();
$d           = kalf_dados_aparelho( $aparelho_id );
$selo        = kalf_selo( $d );
$specs       = kalf_linha_specs( $d );
$tem_foto    = has_post_thumbnail( $aparelho_id );
?>
<article class="kalf-card<?php echo $d['vendido'] ? ' kalf-card--vendido' : ''; ?>">
	<div class="kalf-card__foto-wrap">
		<?php
		/**
		 * Sem aria-label aqui de proposito: o nome acessivel do link precisa
		 * nascer do que esta visivel dentro dele (alt da foto, ou o texto do
		 * placeholder), nunca de um rotulo por fora que diga algo diferente —
		 * WCAG 2.5.3, "Label in Name".
		 */
		?>
		<a href="<?php echo esc_url( $d['url'] ); ?>" class="kalf-card__foto">
			<?php if ( $tem_foto ) : ?>
				<?php echo get_the_post_thumbnail( $aparelho_id, 'kalf-card', array( 'loading' => 'lazy' ) ); ?>
			<?php else : ?>
				<div class="kalf-sem-foto">
					<span class="kalf-sem-foto__marca">KALF STORE</span>
					<span class="kalf-sem-foto__aviso"><?php esc_html_e( 'Foto em breve', 'kalf-store' ); ?></span>
				</div>
			<?php endif; ?>
		</a>
		<span class="kalf-selo kalf-selo--<?php echo esc_attr( $selo['slug'] ); ?> kalf-card__selo"><?php echo esc_html( $selo['texto'] ); ?></span>
	</div>

	<div class="kalf-card__corpo">
		<h3 class="kalf-card__titulo" title="<?php echo esc_attr( $d['titulo'] ); ?>">
			<a href="<?php echo esc_url( $d['url'] ); ?>"><?php echo esc_html( $d['titulo'] ); ?></a>
		</h3>
		<?php if ( $specs ) : ?>
			<span class="kalf-card__specs"><?php echo esc_html( $specs ); ?></span>
		<?php endif; ?>
	</div>

	<div class="kalf-card__preco">
		<?php if ( $d['preco_antigo'] ) : ?>
			<span class="kalf-preco-antigo"><?php echo esc_html( $d['preco_antigo'] ); ?></span>
		<?php endif; ?>
		<span class="kalf-preco<?php echo $d['preco_txt'] ? '' : ' kalf-preco--consulte'; ?>">
			<?php echo esc_html( $d['preco_txt'] ? $d['preco_txt'] : __( 'Consulte o preço', 'kalf-store' ) ); ?>
		</span>
		<?php if ( $d['parcelamento'] ) : ?>
			<span class="kalf-parcelamento"><?php echo esc_html( $d['parcelamento'] ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( $d['vendido'] ) : ?>
		<button type="button" class="kalf-btn kalf-btn--secundario" disabled>
			<?php esc_html_e( 'Vendido', 'kalf-store' ); ?>
		</button>
	<?php else : ?>
		<a href="<?php echo esc_url( kalf_whatsapp_link( kalf_mensagem_produto( $d ) ) ); ?>" class="kalf-btn kalf-btn--secundario" aria-label="<?php echo esc_attr( sprintf( __( 'Quero %s no WhatsApp', 'kalf-store' ), $d['titulo'] ) ); ?>" data-whatsapp-evento="card" data-whatsapp-nome="<?php echo esc_attr( $d['titulo'] ); ?>" data-whatsapp-preco="<?php echo esc_attr( $d['preco'] ); ?>" target="_blank" rel="noopener">
			<?php kalf_icone( 'whatsapp', 18 ); ?>
			<span class="kalf-card__cta-texto-longo" aria-hidden="true"><?php esc_html_e( 'Quero no WhatsApp', 'kalf-store' ); ?></span>
			<span class="kalf-card__cta-texto-curto" aria-hidden="true"><?php esc_html_e( 'WhatsApp', 'kalf-store' ); ?></span>
		</a>
	<?php endif; ?>
</article>
