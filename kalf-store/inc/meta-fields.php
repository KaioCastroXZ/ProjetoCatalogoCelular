<?php
/**
 * Campos do cadastro de aparelho. Ordem, rótulos em PT-BR simples e ajuda
 * curta embaixo de cada campo, para o dono da loja cadastrar sozinho.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uma meta box só, com todos os campos na ordem do prompt.
 * Mais simples para quem não é técnico do que várias caixas separadas.
 */
function kalf_registrar_meta_box() {
	add_meta_box(
		'kalf_dados_aparelho',
		__( 'Dados do aparelho', 'kalf-store' ),
		'kalf_renderizar_meta_box',
		'aparelho',
		'normal',
		'high'
	);

	add_meta_box(
		'kalf_galeria_aparelho',
		__( 'Galeria de fotos (até 8, além da foto principal)', 'kalf-store' ),
		'kalf_renderizar_galeria',
		'aparelho',
		'normal',
		'high'
	);

	add_meta_box(
		'kalf_status_aparelho',
		__( 'Status', 'kalf-store' ),
		'kalf_renderizar_status',
		'aparelho',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'kalf_registrar_meta_box' );

/**
 * Campo de texto/numero simples, com ajuda abaixo.
 */
function kalf_campo_texto( $id, $label, $valor, $ajuda = '', $tipo = 'text', $extra = '' ) {
	?>
	<p class="kalf-campo">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br>
		<input
			type="<?php echo esc_attr( $tipo ); ?>"
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $id ); ?>"
			value="<?php echo esc_attr( $valor ); ?>"
			class="widefat"
			<?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- atributos fixos vindos do proprio tema. ?>
		>
		<?php if ( $ajuda ) : ?>
			<span class="description"><?php echo esc_html( $ajuda ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Meta box principal, na ordem: condição, preço, parcelamento,
 * armazenamento, cor, bateria, observações.
 */
function kalf_renderizar_meta_box( $post ) {
	wp_nonce_field( 'kalf_salvar_aparelho', 'kalf_nonce' );

	$condicao      = get_post_meta( $post->ID, '_kalf_condicao', true );
	$condicao      = $condicao ? $condicao : 'novo';
	$preco         = get_post_meta( $post->ID, '_kalf_preco', true );
	$preco_antigo  = get_post_meta( $post->ID, '_kalf_preco_antigo', true );
	$parcelas      = get_post_meta( $post->ID, '_kalf_parcelas', true );
	$parcela_valor = get_post_meta( $post->ID, '_kalf_parcela_valor', true );
	$armazenamento = get_post_meta( $post->ID, '_kalf_armazenamento', true );
	$cor           = get_post_meta( $post->ID, '_kalf_cor', true );
	$cor_hex       = get_post_meta( $post->ID, '_kalf_cor_hex', true );
	$cor_hex       = $cor_hex ? $cor_hex : '#8F857E';
	$bateria       = get_post_meta( $post->ID, '_kalf_bateria', true );
	$observacoes   = get_post_meta( $post->ID, '_kalf_observacoes', true );
	?>
	<style>
		.kalf-campo { margin-bottom: 16px; }
		.kalf-campo .description { display: block; margin-top: 4px; color: #666; }
		.kalf-linha { display: flex; gap: 16px; flex-wrap: wrap; }
		.kalf-linha > div { flex: 1; min-width: 180px; }
		.kalf-radio-grande label { display: inline-flex; align-items: center; gap: 6px; margin-right: 20px; font-size: 14px; }
		.kalf-radio-grande input { width: 18px; height: 18px; }
	</style>

	<p class="kalf-campo kalf-radio-grande">
		<strong><?php esc_html_e( 'Condição', 'kalf-store' ); ?></strong><br>
		<?php foreach ( kalf_opcoes_condicao() as $valor => $rotulo ) : ?>
			<label>
				<input type="radio" name="kalf_condicao" value="<?php echo esc_attr( $valor ); ?>" <?php checked( $condicao, $valor ); ?>>
				<?php echo esc_html( $rotulo ); ?>
			</label>
		<?php endforeach; ?>
	</p>

	<div id="kalf-campo-bateria" style="<?php echo 'seminovo' === $condicao ? '' : 'display:none;'; ?>">
		<?php kalf_campo_texto( 'kalf_bateria', __( 'Saúde da bateria (%)', 'kalf-store' ), $bateria, __( 'Só aparece no site quando a condição é Seminovo.', 'kalf-store' ), 'number', 'min="0" max="100"' ); ?>
	</div>

	<div class="kalf-linha">
		<div><?php kalf_campo_texto( 'kalf_preco', __( 'Preço (R$)', 'kalf-store' ), $preco, __( 'Deixe em branco para mostrar "Consulte o preço".', 'kalf-store' ), 'number', 'step="0.01" min="0"' ); ?></div>
		<div><?php kalf_campo_texto( 'kalf_preco_antigo', __( 'Preço antigo (R$)', 'kalf-store' ), $preco_antigo, __( 'Opcional. Só aparece riscado se for maior que o preço atual.', 'kalf-store' ), 'number', 'step="0.01" min="0"' ); ?></div>
	</div>

	<div class="kalf-linha">
		<div><?php kalf_campo_texto( 'kalf_parcelas', __( 'Número de parcelas', 'kalf-store' ), $parcelas, __( 'Ex.: 12', 'kalf-store' ), 'number', 'min="0" max="24"' ); ?></div>
		<div><?php kalf_campo_texto( 'kalf_parcela_valor', __( 'Valor de cada parcela (R$)', 'kalf-store' ), $parcela_valor, __( 'Ex.: 199,00. Aparece como "ou 12x de R$ 199,00".', 'kalf-store' ), 'number', 'step="0.01" min="0"' ); ?></div>
	</div>

	<p class="kalf-campo">
		<label for="kalf_armazenamento"><strong><?php esc_html_e( 'Armazenamento', 'kalf-store' ); ?></strong></label><br>
		<select id="kalf_armazenamento" name="kalf_armazenamento" class="widefat">
			<option value=""><?php esc_html_e( '— Não informado —', 'kalf-store' ); ?></option>
			<?php foreach ( kalf_opcoes_armazenamento() as $valor => $rotulo ) : ?>
				<option value="<?php echo esc_attr( $valor ); ?>" <?php selected( $armazenamento, $valor ); ?>><?php echo esc_html( $rotulo ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>

	<div class="kalf-linha">
		<div><?php kalf_campo_texto( 'kalf_cor', __( 'Cor', 'kalf-store' ), $cor, __( 'Ex.: Meia-noite', 'kalf-store' ) ); ?></div>
		<div>
			<p class="kalf-campo">
				<label for="kalf_cor_hex"><strong><?php esc_html_e( 'Amostra da cor', 'kalf-store' ); ?></strong></label><br>
				<input type="color" id="kalf_cor_hex" name="kalf_cor_hex" value="<?php echo esc_attr( $cor_hex ); ?>">
				<span class="description"><?php esc_html_e( 'Só decorativo, aparece como uma bolinha ao lado do nome da cor.', 'kalf-store' ); ?></span>
			</p>
		</div>
	</div>

	<p class="kalf-campo">
		<label for="kalf_observacoes"><strong><?php esc_html_e( 'Observações', 'kalf-store' ); ?></strong></label><br>
		<input type="text" id="kalf_observacoes" name="kalf_observacoes" value="<?php echo esc_attr( $observacoes ); ?>" class="widefat">
		<span class="description"><?php esc_html_e( 'Texto curto, opcional. Ex.: "Acompanha carregador original".', 'kalf-store' ); ?></span>
	</p>

	<script>
	(function () {
		var radios = document.querySelectorAll('input[name="kalf_condicao"]');
		var caixaBateria = document.getElementById('kalf-campo-bateria');
		radios.forEach(function (r) {
			r.addEventListener('change', function () {
				caixaBateria.style.display = (this.value === 'seminovo') ? '' : 'none';
			});
		});
	})();
	</script>
	<?php
}

/**
 * Galeria: guarda uma lista de IDs de anexo separados por virgula.
 * Usa a media library nativa, que ja permite reordenar arrastando.
 */
function kalf_renderizar_galeria( $post ) {
	$galeria = get_post_meta( $post->ID, '_kalf_galeria', true );
	?>
	<p class="description">
		<?php esc_html_e( 'A primeira foto do produto é sempre a "Foto principal", no bloco ao lado. Aqui você adiciona fotos extras (até 8 no total). Para reordenar, abra a galeria e arraste as fotos.', 'kalf-store' ); ?>
	</p>
	<div id="kalf-galeria-preview" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;"></div>
	<input type="hidden" id="kalf_galeria" name="kalf_galeria" value="<?php echo esc_attr( $galeria ); ?>">
	<button type="button" class="button button-secondary" id="kalf-galeria-botao"><?php esc_html_e( 'Escolher fotos da galeria', 'kalf-store' ); ?></button>
	<?php
}

/**
 * Destaque e vendido, na barra lateral — sao os dois campos que o dono
 * mais mexe no dia a dia.
 */
function kalf_renderizar_status( $post ) {
	$destaque = (bool) get_post_meta( $post->ID, '_kalf_destaque', true );
	$vendido  = (bool) get_post_meta( $post->ID, '_kalf_vendido', true );
	?>
	<p>
		<label style="display:flex; align-items:center; gap:8px;">
			<input type="checkbox" name="kalf_destaque" value="1" <?php checked( $destaque ); ?>>
			<?php esc_html_e( 'Mostrar na Home, em Destaques', 'kalf-store' ); ?>
		</label>
	</p>
	<p>
		<label style="display:flex; align-items:center; gap:8px;">
			<input type="checkbox" name="kalf_vendido" value="1" <?php checked( $vendido ); ?>>
			<?php esc_html_e( 'Vendido', 'kalf-store' ); ?>
		</label>
		<span class="description"><?php esc_html_e( 'Sai da vitrine principal e vai para o fim do catálogo. O link continua funcionando.', 'kalf-store' ); ?></span>
	</p>
	<?php
}

/**
 * Salva os campos. Sanitiza tudo que entra, confere o nonce e a permissão.
 */
function kalf_salvar_aparelho( $post_id ) {
	if ( ! isset( $_POST['kalf_nonce'] ) || ! wp_verify_nonce( $_POST['kalf_nonce'], 'kalf_salvar_aparelho' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$condicao = isset( $_POST['kalf_condicao'] ) ? sanitize_key( $_POST['kalf_condicao'] ) : 'novo';
	if ( ! array_key_exists( $condicao, kalf_opcoes_condicao() ) ) {
		$condicao = 'novo';
	}
	update_post_meta( $post_id, '_kalf_condicao', $condicao );

	$campos_numero = array(
		'kalf_bateria'       => '_kalf_bateria',
		'kalf_preco'         => '_kalf_preco',
		'kalf_preco_antigo'  => '_kalf_preco_antigo',
		'kalf_parcelas'      => '_kalf_parcelas',
		'kalf_parcela_valor' => '_kalf_parcela_valor',
	);
	foreach ( $campos_numero as $post_key => $meta_key ) {
		$valor = isset( $_POST[ $post_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) : '';
		update_post_meta( $post_id, $meta_key, $valor );
	}

	$armazenamento = isset( $_POST['kalf_armazenamento'] ) ? sanitize_text_field( wp_unslash( $_POST['kalf_armazenamento'] ) ) : '';
	if ( '' !== $armazenamento && ! array_key_exists( $armazenamento, kalf_opcoes_armazenamento() ) ) {
		$armazenamento = '';
	}
	update_post_meta( $post_id, '_kalf_armazenamento', $armazenamento );

	update_post_meta( $post_id, '_kalf_cor', isset( $_POST['kalf_cor'] ) ? sanitize_text_field( wp_unslash( $_POST['kalf_cor'] ) ) : '' );
	update_post_meta( $post_id, '_kalf_cor_hex', isset( $_POST['kalf_cor_hex'] ) ? sanitize_hex_color( wp_unslash( $_POST['kalf_cor_hex'] ) ) : '' );
	update_post_meta( $post_id, '_kalf_observacoes', isset( $_POST['kalf_observacoes'] ) ? sanitize_text_field( wp_unslash( $_POST['kalf_observacoes'] ) ) : '' );

	// Galeria: so aceita IDs numericos, no maximo 8.
	$galeria_bruta = isset( $_POST['kalf_galeria'] ) ? sanitize_text_field( wp_unslash( $_POST['kalf_galeria'] ) ) : '';
	$ids           = array_filter( array_map( 'intval', explode( ',', $galeria_bruta ) ) );
	$ids           = array_slice( array_unique( $ids ), 0, 8 );
	update_post_meta( $post_id, '_kalf_galeria', implode( ',', $ids ) );

	update_post_meta( $post_id, '_kalf_destaque', isset( $_POST['kalf_destaque'] ) ? 1 : 0 );
	update_post_meta( $post_id, '_kalf_vendido', isset( $_POST['kalf_vendido'] ) ? 1 : 0 );
}
add_action( 'save_post_aparelho', 'kalf_salvar_aparelho' );
