<?php
/**
 * Ajustes do painel: lista de aparelhos, edição rápida, duplicar e
 * esconder o que o cliente não usa.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media uploader + script da galeria, só na tela de edição de aparelho.
 */
function kalf_admin_assets_aparelho( $hook ) {
	global $post_type;
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'aparelho' !== $post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'kalf-admin-galeria',
		KALF_URI . '/assets/js/admin-galeria.js',
		array( 'jquery' ),
		file_exists( KALF_DIR . '/assets/js/admin-galeria.js' ) ? filemtime( KALF_DIR . '/assets/js/admin-galeria.js' ) : KALF_VERSAO,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'kalf_admin_assets_aparelho' );

/**
 * Colunas da lista: miniatura, título, preço, condição, vendido.
 */
function kalf_colunas_lista( $colunas ) {
	$novas = array();
	foreach ( $colunas as $chave => $rotulo ) {
		if ( 'title' === $chave ) {
			$novas['kalf_foto'] = __( 'Foto', 'kalf-store' );
		}
		$novas[ $chave ] = $rotulo;
	}
	$novas['kalf_preco']    = __( 'Preço', 'kalf-store' );
	$novas['kalf_condicao'] = __( 'Condição', 'kalf-store' );
	$novas['kalf_vendido']  = __( 'Vendido', 'kalf-store' );
	unset( $novas['date'] );
	$novas['date'] = __( 'Data', 'kalf-store' );

	return $novas;
}
add_filter( 'manage_aparelho_posts_columns', 'kalf_colunas_lista' );

function kalf_conteudo_coluna( $coluna, $post_id ) {
	switch ( $coluna ) {
		case 'kalf_foto':
			echo get_the_post_thumbnail( $post_id, array( 40, 50 ), array( 'style' => 'object-fit:cover;border-radius:6px;' ) );
			break;

		case 'kalf_preco':
			$preco = kalf_formata_preco( get_post_meta( $post_id, '_kalf_preco', true ) );
			echo esc_html( $preco ? $preco : '—' );
			break;

		case 'kalf_condicao':
			$condicao = get_post_meta( $post_id, '_kalf_condicao', true );
			$opcoes   = kalf_opcoes_condicao();
			echo esc_html( isset( $opcoes[ $condicao ] ) ? $opcoes[ $condicao ] : '—' );
			break;

		case 'kalf_vendido':
			$vendido = (bool) get_post_meta( $post_id, '_kalf_vendido', true );
			echo $vendido
				? '<span style="color:#8A3700;font-weight:600;">' . esc_html__( 'Vendido', 'kalf-store' ) . '</span>'
				: '<span style="color:#6B625B;">' . esc_html__( 'Disponível', 'kalf-store' ) . '</span>';
			break;
	}
}
add_action( 'manage_aparelho_posts_custom_column', 'kalf_conteudo_coluna', 10, 2 );

/**
 * Campos disponíveis na edição rápida (Quick Edit): preço e vendido,
 * que é o que o dono mexe no balcão sem abrir o produto inteiro.
 */
function kalf_quick_edit_campos( $nome_coluna, $tipo_post ) {
	if ( 'aparelho' !== $tipo_post || 'kalf_preco' !== $nome_coluna ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label>
				<span class="title"><?php esc_html_e( 'Preço (R$)', 'kalf-store' ); ?></span>
				<span class="input-text-wrap"><input type="number" step="0.01" min="0" name="kalf_preco_quick" class="widefat"></span>
			</label>
			<label class="alignleft">
				<input type="checkbox" name="kalf_vendido_quick" value="1">
				<span class="checkbox-title"><?php esc_html_e( 'Marcar como vendido', 'kalf-store' ); ?></span>
			</label>
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'kalf_quick_edit_campos', 10, 2 );

/**
 * Preenche os campos da edição rápida via JS com os valores atuais,
 * lendo os data-attributes que a coluna de preço carrega.
 */
function kalf_quick_edit_js( $hook ) {
	global $post_type;
	if ( 'edit.php' !== $hook || 'aparelho' !== $post_type ) {
		return;
	}
	wp_add_inline_script(
		'inline-edit-post',
		"
		(function($){
			var original = inlineEditPost.edit;
			inlineEditPost.edit = function(id) {
				original.apply(this, arguments);
				var postId = (typeof id === 'object') ? inlineEditPost.getId(id) : id;
				if (!postId) return;
				var linha = document.getElementById('post-' + postId);
				if (!linha) return;
				var preco = linha.querySelector('.column-kalf_preco')?.dataset.preco || '';
				var vendido = linha.querySelector('.column-kalf_vendido')?.dataset.vendido === '1';
				var editRow = document.getElementById('edit-' + postId);
				if (!editRow) return;
				var campoPreco = editRow.querySelector('[name=\"kalf_preco_quick\"]');
				var campoVendido = editRow.querySelector('[name=\"kalf_vendido_quick\"]');
				if (campoPreco) campoPreco.value = preco;
				if (campoVendido) campoVendido.checked = vendido;
			};
		})(jQuery);
		"
	);
}
add_action( 'admin_enqueue_scripts', 'kalf_quick_edit_js' );

/**
 * Acrescenta data-preco e data-vendido na celula, para o JS acima ler.
 */
function kalf_quick_edit_dados( $coluna, $post_id ) {
	if ( 'kalf_preco' === $coluna ) {
		echo '<script>document.currentScript.closest("td").dataset.preco="' . esc_js( get_post_meta( $post_id, '_kalf_preco', true ) ) . '";</script>';
	}
	if ( 'kalf_vendido' === $coluna ) {
		echo '<script>document.currentScript.closest("td").dataset.vendido="' . esc_js( (int) get_post_meta( $post_id, '_kalf_vendido', true ) ) . '";</script>';
	}
}
add_action( 'manage_aparelho_posts_custom_column', 'kalf_quick_edit_dados', 20, 2 );

/**
 * Salva o que veio da edição rápida.
 */
function kalf_salvar_quick_edit( $post_id ) {
	if ( ! isset( $_POST['kalf_preco_quick'] ) && ! isset( $_POST['_inline_edit'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['kalf_preco_quick'] ) ) {
		update_post_meta( $post_id, '_kalf_preco', sanitize_text_field( wp_unslash( $_POST['kalf_preco_quick'] ) ) );
	}
	if ( isset( $_POST['_inline_edit'] ) ) {
		update_post_meta( $post_id, '_kalf_vendido', isset( $_POST['kalf_vendido_quick'] ) ? 1 : 0 );
	}
}
add_action( 'save_post_aparelho', 'kalf_salvar_quick_edit' );

/**
 * Botão "Duplicar" na lista e no editor — cadastrar aparelho parecido
 * sem começar do zero.
 */
function kalf_link_duplicar( $links, $post ) {
	if ( 'aparelho' !== $post->post_type || ! current_user_can( 'edit_posts' ) ) {
		return $links;
	}
	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'kalf_duplicar',
				'post'   => $post->ID,
			),
			admin_url( 'admin.php' )
		),
		'kalf_duplicar_' . $post->ID
	);
	$links['duplicar'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplicar', 'kalf-store' ) . '</a>';
	return $links;
}
add_filter( 'post_row_actions', 'kalf_link_duplicar', 10, 2 );

function kalf_processar_duplicar() {
	if ( ! isset( $_GET['post'] ) || ! isset( $_GET['_wpnonce'] ) ) {
		wp_die( esc_html__( 'Pedido inválido.', 'kalf-store' ) );
	}

	$post_id = (int) $_GET['post'];

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'kalf_duplicar_' . $post_id ) ) {
		wp_die( esc_html__( 'Pedido inválido.', 'kalf-store' ) );
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Você não tem permissão para isso.', 'kalf-store' ) );
	}

	$original = get_post( $post_id );
	if ( ! $original || 'aparelho' !== $original->post_type ) {
		wp_die( esc_html__( 'Aparelho não encontrado.', 'kalf-store' ) );
	}

	$novo_id = wp_insert_post(
		array(
			'post_title'  => $original->post_title . ' ' . __( '(cópia)', 'kalf-store' ),
			'post_type'   => 'aparelho',
			'post_status' => 'draft',
			'post_author' => get_current_user_id(),
		)
	);

	if ( is_wp_error( $novo_id ) ) {
		wp_die( esc_html__( 'Não foi possível duplicar.', 'kalf-store' ) );
	}

	// Copia todos os campos _kalf_*.
	foreach ( get_post_meta( $post_id ) as $chave => $valores ) {
		if ( 0 === strpos( $chave, '_kalf_' ) ) {
			update_post_meta( $novo_id, $chave, maybe_unserialize( $valores[0] ) );
		}
	}

	// Foto principal e marca.
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		set_post_thumbnail( $novo_id, $thumb );
	}
	$marcas = wp_get_object_terms( $post_id, 'marca', array( 'fields' => 'ids' ) );
	if ( $marcas ) {
		wp_set_object_terms( $novo_id, $marcas, 'marca' );
	}

	wp_safe_redirect( get_edit_post_link( $novo_id, 'raw' ) );
	exit;
}
add_action( 'admin_action_kalf_duplicar', 'kalf_processar_duplicar' );

/**
 * Esconde do menu o que o cliente não usa: Posts, Comentários, e nos
 * blocos padrao que nao fazem sentido num catalogo.
 */
function kalf_limpar_menu_admin() {
	remove_menu_page( 'edit.php' );          // Posts.
	remove_menu_page( 'edit-comments.php' ); // Comentários.

	if ( ! current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'edit.php?post_type=page' );
		remove_menu_page( 'themes.php' );
		remove_menu_page( 'plugins.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'users.php' );
	}
}
add_action( 'admin_menu', 'kalf_limpar_menu_admin', 999 );

/**
 * Desliga os comentários por completo no catálogo.
 */
function kalf_desligar_comentarios() {
	remove_post_type_support( 'aparelho', 'comments' );
}
add_action( 'init', 'kalf_desligar_comentarios', 100 );

/**
 * Nomeia o campo "Título" como "Nome do aparelho" no editor, e explica
 * o padrão esperado.
 */
function kalf_placeholder_titulo( $titulo, $post ) {
	if ( 'aparelho' === $post->post_type ) {
		return __( 'Ex.: iPhone 13 128GB Meia-noite', 'kalf-store' );
	}
	return $titulo;
}
add_filter( 'enter_title_here', 'kalf_placeholder_titulo', 10, 2 );

/**
 * Painel de admin com o resumo do catálogo: quantos aparelhos, quantos
 * vendidos e um aviso quando o WhatsApp ainda não foi configurado.
 */
function kalf_widget_resumo() {
	wp_add_dashboard_widget( 'kalf_resumo', __( 'KALF STORE', 'kalf-store' ), 'kalf_renderizar_widget_resumo' );
}
add_action( 'wp_dashboard_setup', 'kalf_widget_resumo' );

function kalf_renderizar_widget_resumo() {
	$total   = wp_count_posts( 'aparelho' )->publish;
	$vendido = new WP_Query(
		array(
			'post_type'      => 'aparelho',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- tela de admin, baixo trafego.
				array(
					'key'   => '_kalf_vendido',
					'value' => '1',
				),
			),
		)
	);

	echo '<p>' . sprintf(
		/* translators: 1: total de aparelhos publicados, 2: quantos estao vendidos */
		esc_html__( '%1$d aparelhos publicados, %2$d marcados como vendidos.', 'kalf-store' ),
		(int) $total,
		(int) $vendido->post_count
	) . '</p>';

	if ( '' === kalf_config( 'whatsapp' ) ) {
		echo '<p style="color:#b32d2e;"><strong>' . esc_html__( 'Atenção:', 'kalf-store' ) . '</strong> ' .
			esc_html__( 'o número de WhatsApp ainda não foi configurado.', 'kalf-store' ) . ' ' .
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=aparelho&page=kalf-configuracoes' ) ) . '">' .
			esc_html__( 'Configurar agora', 'kalf-store' ) . '</a></p>';
	}
}
