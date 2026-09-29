<?php
/**
 * Painel de filtros mobile: sobe de baixo pra cima, foco preso, fecha com Esc.
 *
 * @package kalf-store
 * @var array $args { filtros: array, marcas: WP_Term[], contagem: array, total: int }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filtros  = $args['filtros'];
$marcas   = $args['marcas'];
$contagem = $args['contagem'];
?>
<div id="kalf-painel-mobile" class="kalf-painel-mobile-fundo" data-kalf-painel-mobile hidden>
	<div class="kalf-painel-mobile" role="dialog" aria-modal="true" aria-labelledby="kalf-titulo-filtros">
		<div class="kalf-painel-mobile__alca" aria-hidden="true"></div>
		<div class="kalf-painel-mobile__topo">
			<h2 id="kalf-titulo-filtros"><?php esc_html_e( 'Filtros', 'kalf-store' ); ?></h2>
			<button type="button" class="kalf-btn-icone" data-kalf-fechar-painel-mobile aria-label="<?php esc_attr_e( 'Fechar filtros', 'kalf-store' ); ?>">
				<?php kalf_icone( 'fechar', 20 ); ?>
			</button>
		</div>

		<div class="kalf-painel-mobile__corpo">
			<fieldset>
				<legend><?php esc_html_e( 'Marca', 'kalf-store' ); ?></legend>
				<div class="kalf-painel-mobile__pilulas">
					<?php if ( ! is_wp_error( $marcas ) ) : ?>
						<?php foreach ( $marcas as $marca ) : ?>
							<button type="button" class="kalf-chip-filtro" data-kalf-filtro="marca" data-valor="<?php echo esc_attr( $marca->slug ); ?>" aria-pressed="<?php echo in_array( $marca->slug, $filtros['marca'], true ) ? 'true' : 'false'; ?>">
								<span class="kalf-chip-filtro__check"><?php kalf_icone( 'check', 16 ); ?></span>
								<?php echo esc_html( $marca->name ); ?>
							</button>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php esc_html_e( 'Condição', 'kalf-store' ); ?></legend>
				<div class="kalf-painel-mobile__grade2">
					<?php foreach ( kalf_opcoes_condicao() as $valor => $rotulo ) : ?>
						<button type="button" class="kalf-chip-filtro" data-kalf-filtro="condicao" data-valor="<?php echo esc_attr( $valor ); ?>" aria-pressed="<?php echo in_array( $valor, $filtros['condicao'], true ) ? 'true' : 'false'; ?>">
							<?php echo esc_html( $rotulo ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php esc_html_e( 'Armazenamento', 'kalf-store' ); ?></legend>
				<div class="kalf-painel-mobile__grade4">
					<?php foreach ( kalf_opcoes_armazenamento() as $valor => $rotulo ) : ?>
						<button type="button" class="kalf-chip-filtro" data-kalf-filtro="armazenamento" data-valor="<?php echo esc_attr( $valor ); ?>" aria-pressed="<?php echo in_array( $valor, $filtros['armazenamento'], true ) ? 'true' : 'false'; ?>">
							<?php echo esc_html( $rotulo ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</fieldset>
		</div>

		<div class="kalf-painel-mobile__rodape">
			<button type="button" class="kalf-btn kalf-btn--contorno" data-kalf-limpar-painel-mobile><?php esc_html_e( 'Limpar', 'kalf-store' ); ?></button>
			<button type="button" class="kalf-btn kalf-btn--primario" data-kalf-aplicar-painel-mobile>
				<?php esc_html_e( 'Ver', 'kalf-store' ); ?> <span data-kalf-total-painel><?php echo (int) $args['total']; ?></span> <?php esc_html_e( 'aparelhos', 'kalf-store' ); ?>
			</button>
		</div>
	</div>
</div>
