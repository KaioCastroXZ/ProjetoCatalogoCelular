<?php
/**
 * Catálogo: busca, filtros, ordenação e grade. Filtros refletidos na URL
 * e funcionando sem recarregar a página (AJAX em catalogo.js).
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$filtros    = kalf_ler_filtros( $_GET );
$por_pagina = 12;
$resultado  = kalf_consultar_catalogo( $filtros, $por_pagina );
$contagem   = kalf_contagem_por_marca();
$marcas     = get_terms( array( 'taxonomy' => 'marca', 'hide_empty' => false ) );

$rotulos_ordenar = array(
	'recentes'    => __( 'Mais recentes', 'kalf-store' ),
	'menor-preco' => __( 'Menor preço', 'kalf-store' ),
	'maior-preco' => __( 'Maior preço', 'kalf-store' ),
);
?>

<main id="conteudo">

	<section class="kalf-container kalf-catalogo-topo">
		<nav class="kalf-trilha" aria-label="<?php esc_attr_e( 'Trilha', 'kalf-store' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Início', 'kalf-store' ); ?></a>
			<span aria-hidden="true">/</span>
			<span aria-current="page"><?php esc_html_e( 'Celulares', 'kalf-store' ); ?></span>
		</nav>
		<div class="kalf-catalogo-titulo">
			<h1><?php esc_html_e( 'Celulares', 'kalf-store' ); ?></h1>
			<span class="kalf-catalogo-contagem" data-kalf-total>
				<?php
				printf(
					/* translators: %d: quantidade de aparelhos */
					esc_html( _n( '%d aparelho', '%d aparelhos', $resultado['total'], 'kalf-store' ) ),
					(int) $resultado['total']
				);
				?>
			</span>
		</div>
	</section>

	<section class="kalf-container kalf-catalogo-filtros" data-kalf-filtros>
		<div class="kalf-filtros-linha">
			<label class="kalf-busca">
				<?php kalf_icone( 'busca', 20 ); ?>
				<span class="kalf-sr"><?php esc_html_e( 'Buscar por nome', 'kalf-store' ); ?></span>
				<input type="search" name="busca" data-kalf-busca value="<?php echo esc_attr( $filtros['busca'] ); ?>" placeholder="<?php esc_attr_e( 'Buscar por nome, ex.: iPhone 13', 'kalf-store' ); ?>">
			</label>

			<button type="button" class="kalf-chip-filtro" data-kalf-abrir-marca aria-expanded="false" aria-controls="kalf-painel-marca">
				<?php esc_html_e( 'Marca', 'kalf-store' ); ?><span data-kalf-contador-marca><?php echo $filtros['marca'] ? ' · ' . count( $filtros['marca'] ) : ''; ?></span>
				<?php kalf_icone( 'baixo', 18 ); ?>
			</button>

			<button type="button" class="kalf-chip-filtro" data-kalf-abrir-mais aria-expanded="false" aria-controls="kalf-painel-mais">
				<?php esc_html_e( 'Mais filtros', 'kalf-store' ); ?><span data-kalf-contador-mais><?php echo ( $filtros['condicao'] || $filtros['armazenamento'] ) ? ' · ' . ( count( $filtros['condicao'] ) + count( $filtros['armazenamento'] ) ) : ''; ?></span>
				<?php kalf_icone( 'baixo', 18 ); ?>
			</button>

			<button type="button" class="kalf-btn kalf-btn--contorno kalf-catalogo-filtros-mobile" data-kalf-abrir-painel-mobile>
				<?php kalf_icone( 'filtros', 18 ); ?><?php esc_html_e( 'Filtros', 'kalf-store' ); ?>
			</button>

			<div class="kalf-catalogo-flex-espaco"></div>

			<label class="kalf-ordenar">
				<?php esc_html_e( 'Ordenar:', 'kalf-store' ); ?>
				<select data-kalf-ordenar>
					<?php foreach ( $rotulos_ordenar as $valor => $rotulo ) : ?>
						<option value="<?php echo esc_attr( $valor ); ?>" <?php selected( $filtros['ordenar'], $valor ); ?>><?php echo esc_html( $rotulo ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>

		<div class="kalf-filtros-ativos" data-kalf-filtros-ativos hidden>
			<span class="kalf-filtros-ativos__rotulo"><?php esc_html_e( 'Filtros ativos:', 'kalf-store' ); ?></span>
			<div class="kalf-filtros-ativos__lista" data-kalf-lista-ativos></div>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'aparelho' ) ); ?>" class="kalf-btn--link" data-kalf-limpar-tudo><?php esc_html_e( 'Limpar tudo', 'kalf-store' ); ?></a>
		</div>

		<div id="kalf-painel-marca" class="kalf-painel-filtro" data-kalf-painel="marca" hidden>
			<?php if ( ! is_wp_error( $marcas ) ) : ?>
				<?php foreach ( $marcas as $marca ) : ?>
					<label class="kalf-painel-filtro__linha">
						<input type="checkbox" name="marca" value="<?php echo esc_attr( $marca->slug ); ?>" <?php checked( in_array( $marca->slug, $filtros['marca'], true ) ); ?>>
						<?php echo esc_html( $marca->name ); ?>
						<span class="kalf-painel-filtro__contador"><?php echo esc_html( isset( $contagem[ $marca->slug ] ) ? $contagem[ $marca->slug ] : 0 ); ?></span>
					</label>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div id="kalf-painel-mais" class="kalf-painel-filtro kalf-painel-filtro--largo" data-kalf-painel="mais" hidden>
			<fieldset>
				<legend><?php esc_html_e( 'Condição', 'kalf-store' ); ?></legend>
				<?php foreach ( kalf_opcoes_condicao() as $valor => $rotulo ) : ?>
					<label class="kalf-painel-filtro__pilula">
						<input type="checkbox" name="condicao" value="<?php echo esc_attr( $valor ); ?>" <?php checked( in_array( $valor, $filtros['condicao'], true ) ); ?>>
						<?php echo esc_html( $rotulo ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
			<fieldset>
				<legend><?php esc_html_e( 'Armazenamento', 'kalf-store' ); ?></legend>
				<?php foreach ( kalf_opcoes_armazenamento() as $valor => $rotulo ) : ?>
					<label class="kalf-painel-filtro__pilula">
						<input type="checkbox" name="armazenamento" value="<?php echo esc_attr( $valor ); ?>" <?php checked( in_array( $valor, $filtros['armazenamento'], true ) ); ?>>
						<?php echo esc_html( $rotulo ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		</div>
	</section>

	<section class="kalf-container">
		<h2 class="kalf-sr"><?php esc_html_e( 'Resultados', 'kalf-store' ); ?></h2>
		<ul class="kalf-grade" data-kalf-grade aria-live="polite" aria-busy="false">
			<?php if ( $resultado['itens'] ) : ?>
				<?php foreach ( $resultado['itens'] as $post ) : ?>
					<li><?php get_template_part( 'template-parts/card-produto', null, array( 'aparelho_id' => $post->ID ) ); ?></li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>

		<div data-kalf-estado-vazio <?php echo $resultado['itens'] ? 'hidden' : ''; ?>>
			<?php get_template_part( 'template-parts/estado-vazio', null, array( 'contexto' => $filtros['busca'] ? 'busca' : 'catalogo', 'termo' => $filtros['busca'] ) ); ?>
		</div>
	</section>

	<section class="kalf-container kalf-catalogo-rodape">
		<span data-kalf-mostrando>
			<?php
			printf(
				/* translators: 1: quantidade mostrada, 2: total */
				esc_html__( 'Mostrando %1$d de %2$d aparelhos', 'kalf-store' ),
				count( $resultado['itens'] ),
				(int) $resultado['total']
			);
			?>
		</span>
		<button type="button" class="kalf-btn kalf-btn--contorno" data-kalf-carregar-mais <?php echo ( $resultado['total'] > $por_pagina && count( $resultado['itens'] ) < $resultado['total'] ) ? '' : 'hidden'; ?>>
			<?php esc_html_e( 'Carregar mais', 'kalf-store' ); ?>
		</button>
	</section>

</main>

<?php get_template_part( 'template-parts/painel-filtros-mobile', null, array( 'filtros' => $filtros, 'marcas' => $marcas, 'contagem' => $contagem, 'total' => $resultado['total'] ) ); ?>

<?php get_footer(); ?>
