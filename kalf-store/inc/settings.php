<?php
/**
 * Tela "Configurações da loja". Tudo que se repete no site vem daqui —
 * nada de WhatsApp, endereço ou horário fixo no código.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Página de opções, dentro do menu Aparelhos.
 */
function kalf_menu_configuracoes() {
	add_submenu_page(
		'edit.php?post_type=aparelho',
		__( 'Configurações da loja', 'kalf-store' ),
		__( 'Configurações da loja', 'kalf-store' ),
		'manage_options',
		'kalf-configuracoes',
		'kalf_tela_configuracoes'
	);
}
add_action( 'admin_menu', 'kalf_menu_configuracoes' );

/**
 * Todos os campos de configuração, num lugar só.
 * O 'help' aparece como texto de ajuda abaixo do campo no admin.
 *
 * @return array
 */
function kalf_campos_configuracao() {
	return array(
		'dados' => array(
			'titulo' => __( 'Dados da loja', 'kalf-store' ),
			'campos' => array(
				'whatsapp'        => array(
					'label' => __( 'WhatsApp (DDD + número)', 'kalf-store' ),
					'tipo'  => 'text',
					'help'  => __( 'Só números, com DDD. Ex.: 62999998888', 'kalf-store' ),
				),
				'mensagem_padrao' => array(
					'label' => __( 'Mensagem padrão do WhatsApp', 'kalf-store' ),
					'tipo'  => 'textarea',
					'help'  => __( 'Usada no botão do menu, do topo e da página de contato.', 'kalf-store' ),
				),
				'endereco'        => array(
					'label' => __( 'Endereço completo', 'kalf-store' ),
					'tipo'  => 'text',
					'help'  => __( 'Rua, número e bairro.', 'kalf-store' ),
				),
				'cep'             => array(
					'label' => __( 'CEP', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'horario'         => array(
					'label' => __( 'Horário de funcionamento', 'kalf-store' ),
					'tipo'  => 'textarea',
					'help'  => __( 'Ex.: Seg a sex, 9h às 18h. Sáb, 9h às 13h.', 'kalf-store' ),
				),
				'instagram'       => array(
					'label' => __( 'Instagram (sem @)', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'cnpj'            => array(
					'label' => __( 'CNPJ', 'kalf-store' ),
					'tipo'  => 'text',
					'help'  => __( 'Deixe em branco para não mostrar.', 'kalf-store' ),
				),
				'maps_link'       => array(
					'label' => __( 'Link do Google Maps', 'kalf-store' ),
					'tipo'  => 'url',
				),
				'maps_embed'      => array(
					'label' => __( 'Link do mapa incorporado (embed)', 'kalf-store' ),
					'tipo'  => 'url',
					'help'  => __( 'No Google Maps: Compartilhar → Incorporar um mapa → copie só o link do src.', 'kalf-store' ),
				),
				'waze_link'       => array(
					'label' => __( 'Link do Waze', 'kalf-store' ),
					'tipo'  => 'url',
				),
			),
		),
		'home'  => array(
			'titulo' => __( 'Textos da home', 'kalf-store' ),
			'campos' => array(
				'hero_selo'        => array(
					'label' => __( 'Selo do hero', 'kalf-store' ),
					'tipo'  => 'text',
					'help'  => __( 'Ex.: Novos e seminovos · Goiânia – GO', 'kalf-store' ),
				),
				'hero_titulo'      => array(
					'label' => __( 'Título do hero', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'hero_subtitulo'   => array(
					'label' => __( 'Subtítulo do hero', 'kalf-store' ),
					'tipo'  => 'textarea',
				),
				'diferencial_1'    => array(
					'label' => __( 'Diferencial 1 — título', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'diferencial_1_sub' => array(
					'label' => __( 'Diferencial 1 — descrição', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'diferencial_2'    => array(
					'label' => __( 'Diferencial 2 — título', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'diferencial_2_sub' => array(
					'label' => __( 'Diferencial 2 — descrição', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'diferencial_3'    => array(
					'label' => __( 'Diferencial 3 — título', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'diferencial_3_sub' => array(
					'label' => __( 'Diferencial 3 — descrição', 'kalf-store' ),
					'tipo'  => 'text',
				),
				'entrega_texto'    => array(
					'label' => __( 'Como funciona a entrega/retirada', 'kalf-store' ),
					'tipo'  => 'textarea',
				),
			),
		),
		'venda' => array(
			'titulo' => __( 'Regras de venda', 'kalf-store' ),
			'campos' => array(
				'parcelamento_padrao_max' => array(
					'label' => __( 'Máximo de parcelas no cartão', 'kalf-store' ),
					'tipo'  => 'number',
					'help'  => __( 'Usado como sugestão ao cadastrar um aparelho.', 'kalf-store' ),
				),
				'garantia_meses'          => array(
					'label' => __( 'Garantia (em meses)', 'kalf-store' ),
					'tipo'  => 'number',
				),
			),
		),
		'tecnico' => array(
			'titulo' => __( 'Avançado', 'kalf-store' ),
			'campos' => array(
				'gtm_id' => array(
					'label' => __( 'ID do Google Tag Manager', 'kalf-store' ),
					'tipo'  => 'text',
					'help'  => __( 'Formato GTM-XXXXXXX. Deixe em branco para não ativar.', 'kalf-store' ),
				),
			),
		),
	);
}

/**
 * Registra cada campo como uma option separada (kalf_whatsapp, kalf_endereco...).
 */
function kalf_registrar_configuracoes() {
	foreach ( kalf_campos_configuracao() as $grupo ) {
		foreach ( $grupo['campos'] as $chave => $def ) {
			register_setting(
				'kalf_configuracoes',
				'kalf_' . $chave,
				array(
					'type'              => 'string',
					'sanitize_callback' => 'url' === $def['tipo'] ? 'esc_url_raw' : 'sanitize_textarea_field',
					'default'           => '',
				)
			);
		}
	}
}
add_action( 'admin_init', 'kalf_registrar_configuracoes' );

/**
 * Renderiza a tela de configurações.
 */
function kalf_tela_configuracoes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap kalf-admin">
		<h1><?php esc_html_e( 'Configurações da loja', 'kalf-store' ); ?></h1>
		<p><?php esc_html_e( 'Tudo que aparece repetido pelo site (WhatsApp, endereço, horário...) vem daqui.', 'kalf-store' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'kalf_configuracoes' ); ?>
			<?php foreach ( kalf_campos_configuracao() as $grupo ) : ?>
				<h2><?php echo esc_html( $grupo['titulo'] ); ?></h2>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( $grupo['campos'] as $chave => $def ) : ?>
						<?php $nome = 'kalf_' . $chave; ?>
						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $nome ); ?>"><?php echo esc_html( $def['label'] ); ?></label>
							</th>
							<td>
								<?php if ( 'textarea' === $def['tipo'] ) : ?>
									<textarea
										name="<?php echo esc_attr( $nome ); ?>"
										id="<?php echo esc_attr( $nome ); ?>"
										rows="3"
										class="large-text"
									><?php echo esc_textarea( kalf_config( $chave ) ); ?></textarea>
								<?php else : ?>
									<input
										type="<?php echo 'number' === $def['tipo'] ? 'number' : ( 'url' === $def['tipo'] ? 'url' : 'text' ); ?>"
										name="<?php echo esc_attr( $nome ); ?>"
										id="<?php echo esc_attr( $nome ); ?>"
										value="<?php echo esc_attr( kalf_config( $chave ) ); ?>"
										class="regular-text"
									>
								<?php endif; ?>
								<?php if ( ! empty( $def['help'] ) ) : ?>
									<p class="description"><?php echo esc_html( $def['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>
			<?php submit_button( __( 'Salvar configurações', 'kalf-store' ) ); ?>
		</form>
	</div>
	<?php
}
