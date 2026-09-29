<?php
/**
 * Home: hero, diferenciais, destaques, marcas, como comprar, contato.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hero_selo      = kalf_config( 'hero_selo', __( 'Novos e seminovos · Goiânia – GO', 'kalf-store' ) );
$hero_titulo    = kalf_config( 'hero_titulo', __( 'Seu próximo celular está aqui.', 'kalf-store' ) );
$hero_subtitulo = kalf_config( 'hero_subtitulo', __( 'iPhones e Androids novos e seminovos, com assistência técnica. Escolheu? É só chamar no WhatsApp.', 'kalf-store' ) );

$destaques = get_posts(
	array(
		'post_type'      => 'aparelho',
		'posts_per_page' => 8,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_kalf_destaque', 'value' => '1' ),
			array(
				'relation' => 'OR',
				array( 'key' => '_kalf_vendido', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_kalf_vendido', 'value' => '1', 'compare' => '!=' ),
			),
		),
	)
);

$marcas = get_terms( array( 'taxonomy' => 'marca', 'hide_empty' => false ) );
?>

<main id="conteudo">

	<section class="kalf-container kalf-hero">
		<div class="kalf-hero__conteudo kalf-revelar">
			<span class="kalf-hero__selo"><?php echo esc_html( $hero_selo ); ?></span>
			<h1 class="kalf-hero__titulo"><?php echo esc_html( $hero_titulo ); ?></h1>
			<p class="kalf-hero__subtitulo"><?php echo esc_html( $hero_subtitulo ); ?></p>
			<div class="kalf-hero__acoes">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-btn kalf-btn--g kalf-btn--primario">
					<?php esc_html_e( 'Ver celulares', 'kalf-store' ); ?>
					<?php kalf_icone( 'seta', 20 ); ?>
				</a>
				<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--g kalf-btn--contorno" data-whatsapp-evento="hero" target="_blank" rel="noopener">
					<?php kalf_icone( 'whatsapp', 20 ); ?>
					<?php esc_html_e( 'Falar no WhatsApp', 'kalf-store' ); ?>
				</a>
			</div>
		</div>
		<div class="kalf-hero__imagem kalf-revelar">
			<span class="kalf-hero__imagem-decoracao" aria-hidden="true"></span>
			<div class="kalf-hero__imagem-placeholder">
				<span><?php esc_html_e( '[FOTO DO APARELHO EM DESTAQUE]', 'kalf-store' ); ?></span>
			</div>
		</div>
	</section>

	<?php
	$tem_diferencial = kalf_config( 'diferencial_1' ) || kalf_config( 'diferencial_2' ) || kalf_config( 'diferencial_3' );
	$diferenciais     = array(
		array(
			'icone'   => 'escudo',
			'titulo'  => kalf_config( 'diferencial_1', __( 'Garantia de [X] meses', 'kalf-store' ) ),
			'sub'     => kalf_config( 'diferencial_1_sub', __( '[EXEMPLO] Em todos os aparelhos', 'kalf-store' ) ),
		),
		array(
			'icone'  => 'cartao',
			'titulo' => kalf_config( 'diferencial_2', __( 'Até [X]x no cartão', 'kalf-store' ) ),
			'sub'    => kalf_config( 'diferencial_2_sub', __( '[EXEMPLO] Parcelamento', 'kalf-store' ) ),
		),
		array(
			'icone'  => 'troca',
			'titulo' => kalf_config( 'diferencial_3', __( 'Aceitamos seu usado', 'kalf-store' ) ),
			'sub'    => kalf_config( 'diferencial_3_sub', __( '[EXEMPLO] Na troca por outro aparelho', 'kalf-store' ) ),
		),
		array(
			'icone'  => 'chave',
			'titulo' => __( 'Assistência técnica', 'kalf-store' ),
			'sub'    => __( 'Celulares e tablets', 'kalf-store' ),
		),
	);
	?>
	<section class="kalf-container">
		<div class="kalf-diferenciais kalf-revelar">
			<?php foreach ( $diferenciais as $item ) : ?>
				<div class="kalf-diferencial">
					<span class="kalf-diferencial__icone"><?php kalf_icone( $item['icone'], 24 ); ?></span>
					<div class="kalf-diferencial__texto">
						<strong><?php echo esc_html( $item['titulo'] ); ?></strong>
						<span><?php echo esc_html( $item['sub'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="kalf-container kalf-secao">
		<div class="kalf-secao__topo kalf-revelar">
			<h2><?php esc_html_e( 'Destaques', 'kalf-store' ); ?></h2>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-ver-todos">
				<?php esc_html_e( 'Ver todos os celulares', 'kalf-store' ); ?>
				<?php kalf_icone( 'seta', 18 ); ?>
			</a>
		</div>

		<?php if ( $destaques ) : ?>
			<div class="kalf-destaques">
				<?php foreach ( $destaques as $post ) : ?>
					<div class="kalf-revelar">
						<?php get_template_part( 'template-parts/card-produto', null, array( 'aparelho_id' => $post->ID ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="kalf-estado kalf-revelar">
				<span class="kalf-estado__icone"><?php kalf_icone( 'celular', 32 ); ?></span>
				<h3><?php esc_html_e( 'Estamos atualizando o catálogo', 'kalf-store' ); ?></h3>
				<p><?php esc_html_e( 'Fale com a gente no WhatsApp para saber quais aparelhos estão disponíveis na loja agora.', 'kalf-store' ); ?></p>
				<div class="kalf-estado__acoes">
					<a href="<?php echo esc_url( kalf_whatsapp_link() ); ?>" class="kalf-btn kalf-btn--primario kalf-btn--bloco" data-whatsapp-evento="home-vazio" target="_blank" rel="noopener"><?php esc_html_e( 'Falar no WhatsApp', 'kalf-store' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</section>

	<section class="kalf-secao--claro">
		<div class="kalf-container kalf-secao">
			<h2 class="kalf-revelar" style="margin-bottom: var(--s-7);"><?php esc_html_e( 'Procure pela marca', 'kalf-store' ); ?></h2>
			<div class="kalf-marcas kalf-revelar">
				<?php if ( ! is_wp_error( $marcas ) ) : ?>
					<?php foreach ( $marcas as $marca ) : ?>
						<a href="<?php echo esc_url( get_term_link( $marca ) ); ?>" class="kalf-marcas__item">
							<span class="kalf-marcas__nome"><?php echo esc_html( $marca->name ); ?></span>
							<span class="kalf-marcas__link"><?php esc_html_e( 'Ver aparelhos →', 'kalf-store' ); ?></span>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-marcas__item kalf-marcas__item--todas">
					<span class="kalf-marcas__nome"><?php esc_html_e( 'Todas', 'kalf-store' ); ?></span>
					<span class="kalf-marcas__link"><?php esc_html_e( 'Ver catálogo →', 'kalf-store' ); ?></span>
				</a>
			</div>
		</div>
	</section>

	<section class="kalf-secao--laranja">
		<div class="kalf-container kalf-secao kalf-como-comprar">
			<div class="kalf-como-comprar__topo kalf-revelar">
				<h2><?php esc_html_e( 'Como comprar', 'kalf-store' ); ?></h2>
				<p><?php esc_html_e( 'Sem cadastro e sem carrinho. Você escolhe, a gente conversa.', 'kalf-store' ); ?></p>
			</div>
			<div class="kalf-passos kalf-revelar">
				<div class="kalf-passo">
					<span class="kalf-passo__numero">1</span>
					<h3><?php esc_html_e( 'Escolha o aparelho', 'kalf-store' ); ?></h3>
					<p><?php esc_html_e( 'Navegue pelo catálogo e filtre por marca, condição e armazenamento.', 'kalf-store' ); ?></p>
				</div>
				<div class="kalf-passo">
					<span class="kalf-passo__numero">2</span>
					<h3><?php esc_html_e( 'Chame no WhatsApp', 'kalf-store' ); ?></h3>
					<p><?php esc_html_e( 'Toque em "Quero este no WhatsApp". A mensagem já vai pronta com o modelo.', 'kalf-store' ); ?></p>
				</div>
				<div class="kalf-passo">
					<span class="kalf-passo__numero">3</span>
					<h3><?php esc_html_e( 'Retire ou receba', 'kalf-store' ); ?></h3>
					<p><?php echo esc_html( kalf_config( 'entrega_texto', __( 'Retire na loja em Goiânia ou combine a entrega. [CONFIRMAR ENTREGA COM O CLIENTE]', 'kalf-store' ) ) ); ?></p>
				</div>
			</div>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-btn kalf-btn--g kalf-btn--secundario kalf-revelar">
				<?php esc_html_e( 'Ver celulares', 'kalf-store' ); ?>
				<?php kalf_icone( 'seta', 20 ); ?>
			</a>
		</div>
	</section>

	<?php get_template_part( 'template-parts/contato-bloco' ); ?>

</main>

<?php get_footer(); ?>
