<?php
/**
 * Página do produto: galeria, título, chips, preço, WhatsApp fixo no
 * mobile, compartilhar, relacionados.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$d       = kalf_dados_aparelho( get_the_ID() );
	$selo    = kalf_selo( $d );
	$galeria = $d['galeria'];

	// "Voce tambem pode gostar": mesma marca, excluindo o proprio, limitado a 4.
	$termos_marca = get_the_terms( get_the_ID(), 'marca' );
	$marca_slug   = ( $termos_marca && ! is_wp_error( $termos_marca ) ) ? $termos_marca[0]->slug : '';

	$relacionados = get_posts(
		array(
			'post_type'      => 'aparelho',
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
			'tax_query'      => $marca_slug ? array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'marca',
					'field'    => 'slug',
					'terms'    => $marca_slug,
				),
			) : array(),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'relation' => 'OR',
					array( 'key' => '_kalf_vendido', 'compare' => 'NOT EXISTS' ),
					array( 'key' => '_kalf_vendido', 'value' => '1', 'compare' => '!=' ),
				),
			),
		)
	);
	?>

	<main id="conteudo">

		<nav class="kalf-trilha kalf-container" aria-label="<?php esc_attr_e( 'Trilha', 'kalf-store' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Início', 'kalf-store' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>"><?php esc_html_e( 'Celulares', 'kalf-store' ); ?></a>
			<?php if ( $d['marca'] ) : ?>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $d['marca_url'] ); ?>"><?php echo esc_html( $d['marca'] ); ?></a>
			<?php endif; ?>
			<span aria-hidden="true">/</span>
			<span aria-current="page"><?php echo esc_html( $d['titulo'] ); ?></span>
		</nav>

		<section class="kalf-container kalf-produto">
			<div class="kalf-galeria" data-kalf-galeria>
				<div class="kalf-galeria__principal">
					<div class="kalf-galeria__trilho" data-kalf-trilho>
						<?php if ( $galeria ) : ?>
							<?php foreach ( $galeria as $indice => $foto_id ) : ?>
								<a href="<?php echo esc_url( add_query_arg( 'foto', $indice + 1, get_permalink() . 'galeria/' ) ); ?>" class="kalf-galeria__slide" data-kalf-abrir-lightbox data-indice="<?php echo esc_attr( $indice ); ?>" aria-label="<?php esc_attr_e( 'Abrir foto em tela cheia', 'kalf-store' ); ?>">
									<?php echo wp_get_attachment_image( $foto_id, 'kalf-galeria', false, array( 'loading' => 0 === $indice ? 'eager' : 'lazy', 'fetchpriority' => 0 === $indice ? 'high' : 'auto' ) ); ?>
								</a>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="kalf-galeria__slide kalf-sem-foto-grande">
								<div class="kalf-sem-foto">
									<span class="kalf-sem-foto__marca">KALF STORE</span>
									<span class="kalf-sem-foto__aviso"><?php esc_html_e( 'Foto em breve', 'kalf-store' ); ?></span>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<span class="kalf-selo kalf-selo--<?php echo esc_attr( $selo['slug'] ); ?> kalf-galeria__selo"><?php echo esc_html( $selo['texto'] ); ?></span>

					<?php if ( count( $galeria ) > 1 ) : ?>
						<span class="kalf-galeria__contador" data-kalf-contador>1 / <?php echo count( $galeria ); ?></span>
						<button type="button" class="kalf-galeria__seta kalf-galeria__seta--esq" data-kalf-anterior aria-label="<?php esc_attr_e( 'Foto anterior', 'kalf-store' ); ?>"><?php kalf_icone( 'voltar', 22 ); ?></button>
						<button type="button" class="kalf-galeria__seta kalf-galeria__seta--dir" data-kalf-proxima aria-label="<?php esc_attr_e( 'Próxima foto', 'kalf-store' ); ?>"><?php kalf_icone( 'avancar', 22 ); ?></button>
					<?php endif; ?>

					<button type="button" class="kalf-galeria__ampliar" data-kalf-ampliar aria-label="<?php esc_attr_e( 'Ampliar foto', 'kalf-store' ); ?>">
						<?php kalf_icone( 'lupa-mais', 20 ); ?>
					</button>
				</div>

				<?php if ( count( $galeria ) > 1 ) : ?>
					<div class="kalf-galeria__miniaturas" data-kalf-miniaturas>
						<?php foreach ( $galeria as $indice => $foto_id ) : ?>
							<button type="button" class="kalf-galeria__miniatura" data-kalf-ir-para="<?php echo esc_attr( $indice ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Foto %d', 'kalf-store' ), $indice + 1 ) ); ?>" <?php echo 0 === $indice ? 'aria-current="true"' : ''; ?>>
								<?php echo wp_get_attachment_image( $foto_id, 'kalf-mini', false, array( 'loading' => 'lazy' ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="kalf-galeria__pontos" data-kalf-pontos aria-hidden="true">
						<?php foreach ( $galeria as $indice => $foto_id ) : ?>
							<span class="kalf-galeria__ponto<?php echo 0 === $indice ? ' kalf-galeria__ponto--ativo' : ''; ?>"></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="kalf-produto__info">
				<div class="kalf-produto__cabecalho">
					<?php if ( $d['marca'] ) : ?>
						<span class="kalf-produto__marca"><?php echo esc_html( $d['marca'] ); ?></span>
					<?php endif; ?>
					<h1 class="kalf-produto__titulo"><?php echo esc_html( $d['titulo'] ); ?></h1>
				</div>

				<ul class="kalf-produto__specs" aria-label="<?php esc_attr_e( 'Especificações', 'kalf-store' ); ?>">
					<?php if ( $d['armaz_txt'] ) : ?>
						<li class="kalf-chip"><?php echo esc_html( $d['armaz_txt'] ); ?></li>
					<?php endif; ?>
					<?php if ( $d['cor'] ) : ?>
						<li class="kalf-chip">
							<?php if ( $d['cor_hex'] ) : ?>
								<span class="kalf-chip__cor" style="background:<?php echo esc_attr( $d['cor_hex'] ); ?>"></span>
							<?php endif; ?>
							<?php echo esc_html( $d['cor'] ); ?>
						</li>
					<?php endif; ?>
					<?php if ( 'seminovo' === $d['condicao'] && '' !== $d['bateria'] ) : ?>
						<li class="kalf-chip"><?php kalf_icone( 'bateria', 18 ); ?><?php echo esc_html( sprintf( __( 'Bateria %s%%', 'kalf-store' ), $d['bateria'] ) ); ?></li>
					<?php endif; ?>
				</ul>

				<div class="kalf-produto__preco-bloco">
					<?php if ( $d['preco_antigo'] ) : ?>
						<span class="kalf-preco-antigo"><?php echo esc_html( $d['preco_antigo'] ); ?></span>
					<?php endif; ?>
					<span class="kalf-produto__preco<?php echo $d['preco_txt'] ? '' : ' kalf-produto__preco--consulte'; ?>">
						<?php echo esc_html( $d['preco_txt'] ? $d['preco_txt'] : __( 'Consulte o preço', 'kalf-store' ) ); ?>
					</span>
					<?php if ( $d['parcelamento'] ) : ?>
						<span class="kalf-produto__parcelamento"><?php echo esc_html( $d['parcelamento'] ); ?> <?php esc_html_e( 'no cartão', 'kalf-store' ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $d['vendido'] ) : ?>
					<button type="button" class="kalf-btn kalf-btn--secundario kalf-btn--cta kalf-btn--bloco" disabled><?php esc_html_e( 'Vendido', 'kalf-store' ); ?></button>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-btn kalf-btn--contorno kalf-btn--bloco"><?php esc_html_e( 'Ver aparelhos parecidos', 'kalf-store' ); ?></a>
				<?php else : ?>
					<a href="<?php echo esc_url( kalf_whatsapp_link( kalf_mensagem_produto( $d ) ) ); ?>" class="kalf-btn kalf-btn--primario kalf-btn--cta kalf-btn--bloco kalf-produto__cta-inline" data-whatsapp-evento="produto" data-whatsapp-nome="<?php echo esc_attr( $d['titulo'] ); ?>" data-whatsapp-preco="<?php echo esc_attr( $d['preco'] ); ?>" target="_blank" rel="noopener">
						<?php kalf_icone( 'whatsapp', 24 ); ?><?php esc_html_e( 'Quero este no WhatsApp', 'kalf-store' ); ?>
					</a>
				<?php endif; ?>

				<button type="button" class="kalf-btn kalf-btn--contorno kalf-btn--bloco" data-kalf-compartilhar data-titulo="<?php echo esc_attr( $d['titulo'] ); ?>" data-url="<?php echo esc_url( $d['url'] ); ?>">
					<?php kalf_icone( 'compartir', 20 ); ?><?php esc_html_e( 'Compartilhar', 'kalf-store' ); ?>
				</button>

				<div class="kalf-produto__extras">
					<?php if ( kalf_config( 'garantia_meses' ) ) : ?>
						<div class="kalf-produto__extra"><span><?php kalf_icone( 'escudo', 20 ); ?></span><?php echo esc_html( sprintf( __( 'Garantia de %s meses', 'kalf-store' ), kalf_config( 'garantia_meses' ) ) ); ?></div>
					<?php endif; ?>
					<div class="kalf-produto__extra"><span><?php kalf_icone( 'pino', 20 ); ?></span><?php echo esc_html( kalf_config( 'entrega_texto', __( 'Retire na loja em Goiânia ou combine a entrega.', 'kalf-store' ) ) ); ?></div>
					<?php if ( $d['observacoes'] ) : ?>
						<div class="kalf-produto__extra"><?php echo esc_html( $d['observacoes'] ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $relacionados ) : ?>
			<section class="kalf-secao--branco">
				<div class="kalf-container kalf-secao">
					<h2 style="margin-bottom: var(--s-7);"><?php esc_html_e( 'Você também pode gostar', 'kalf-store' ); ?></h2>
					<div class="kalf-grade">
						<?php foreach ( $relacionados as $post_relacionado ) : ?>
							<?php get_template_part( 'template-parts/card-produto', null, array( 'aparelho_id' => $post_relacionado->ID ) ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</main>

	<?php if ( ! $d['vendido'] ) : ?>
		<div class="kalf-cta-fixo" data-kalf-cta-fixo>
			<div class="kalf-cta-fixo__linha">
				<span class="kalf-cta-fixo__nome"><?php echo esc_html( $d['titulo'] . ( $d['condicao_txt'] ? ' · ' . $d['condicao_txt'] : '' ) ); ?></span>
				<span class="kalf-cta-fixo__preco"><?php echo esc_html( $d['preco_txt'] ? $d['preco_txt'] : __( 'Consulte', 'kalf-store' ) ); ?></span>
			</div>
			<a href="<?php echo esc_url( kalf_whatsapp_link( kalf_mensagem_produto( $d ) ) ); ?>" class="kalf-btn kalf-btn--primario kalf-btn--cta kalf-btn--bloco" data-whatsapp-evento="cta-fixo" data-whatsapp-nome="<?php echo esc_attr( $d['titulo'] ); ?>" data-whatsapp-preco="<?php echo esc_attr( $d['preco'] ); ?>" target="_blank" rel="noopener">
				<?php kalf_icone( 'whatsapp', 22 ); ?><?php esc_html_e( 'Quero este no WhatsApp', 'kalf-store' ); ?>
			</a>
		</div>
	<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
