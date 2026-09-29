<?php
/**
 * Funcoes de leitura e formatacao usadas nos templates.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Le uma configuracao da loja. Devolve o placeholder visivel quando vazia,
 * porque o design foi feito para mostrar [ENDERECO] ate o dono preencher.
 *
 * @param string $chave      Chave sem o prefixo kalf_.
 * @param string $fallback   Texto mostrado quando a opcao esta vazia.
 * @return string
 */
function kalf_config( $chave, $fallback = '' ) {
	$valor = get_option( 'kalf_' . $chave, '' );

	if ( is_string( $valor ) ) {
		$valor = trim( $valor );
	}

	if ( '' === $valor || null === $valor ) {
		return $fallback;
	}

	return $valor;
}

/**
 * Formata valor em real brasileiro: R$ 1.299,00.
 *
 * @param mixed $valor Numero cru vindo do meta.
 * @return string String vazia se nao houver preco valido.
 */
function kalf_formata_preco( $valor ) {
	if ( '' === $valor || null === $valor ) {
		return '';
	}

	$numero = (float) str_replace( ',', '.', (string) $valor );

	if ( $numero <= 0 ) {
		return '';
	}

	return 'R$ ' . number_format( $numero, 2, ',', '.' );
}

/**
 * Preco cru como float, para ordenacao e schema.
 *
 * @param int $id ID do aparelho.
 * @return float 0 quando nao ha preco.
 */
function kalf_preco_numero( $id = null ) {
	$id  = $id ? $id : get_the_ID();
	$raw = get_post_meta( $id, '_kalf_preco', true );

	if ( '' === $raw ) {
		return 0.0;
	}

	return (float) str_replace( ',', '.', (string) $raw );
}

/**
 * Junta todos os dados de um aparelho num array, para os templates nao
 * ficarem chamando get_post_meta a cada linha.
 *
 * @param int $id ID do aparelho.
 * @return array
 */
function kalf_dados_aparelho( $id = null ) {
	$id = $id ? $id : get_the_ID();

	$condicao    = get_post_meta( $id, '_kalf_condicao', true );
	$condicao    = array_key_exists( $condicao, kalf_opcoes_condicao() ) ? $condicao : 'novo';
	$vendido     = (bool) get_post_meta( $id, '_kalf_vendido', true );
	$destaque    = (bool) get_post_meta( $id, '_kalf_destaque', true );
	$preco       = kalf_preco_numero( $id );
	$preco_velho = (float) str_replace( ',', '.', (string) get_post_meta( $id, '_kalf_preco_antigo', true ) );

	// O preco antigo so aparece se for realmente maior que o atual.
	$mostra_velho = ( $preco > 0 && $preco_velho > $preco );

	$parcelas      = (int) get_post_meta( $id, '_kalf_parcelas', true );
	$parcela_valor = (float) str_replace( ',', '.', (string) get_post_meta( $id, '_kalf_parcela_valor', true ) );

	$armazenamento = get_post_meta( $id, '_kalf_armazenamento', true );
	$opcoes_arm    = kalf_opcoes_armazenamento();

	$marcas = get_the_terms( $id, 'marca' );
	$marca  = ( $marcas && ! is_wp_error( $marcas ) ) ? $marcas[0] : null;

	return array(
		'id'            => $id,
		'titulo'        => get_the_title( $id ),
		'url'           => get_permalink( $id ),
		'condicao'      => $condicao,
		'condicao_txt'  => kalf_opcoes_condicao()[ $condicao ],
		'vendido'       => $vendido,
		'destaque'      => $destaque,
		'preco'         => $preco,
		'preco_txt'     => kalf_formata_preco( $preco ),
		'preco_antigo'  => $mostra_velho ? kalf_formata_preco( $preco_velho ) : '',
		'parcelas'      => $parcelas,
		'parcela_valor' => $parcela_valor,
		'parcelamento'  => ( $parcelas > 1 && $parcela_valor > 0 )
			? sprintf(
				/* translators: 1: numero de parcelas, 2: valor da parcela */
				__( 'ou %1$dx de %2$s', 'kalf-store' ),
				$parcelas,
				kalf_formata_preco( $parcela_valor )
			)
			: '',
		'armazenamento' => $armazenamento,
		'armaz_txt'     => isset( $opcoes_arm[ $armazenamento ] ) ? $opcoes_arm[ $armazenamento ] : '',
		'cor'           => get_post_meta( $id, '_kalf_cor', true ),
		'cor_hex'       => get_post_meta( $id, '_kalf_cor_hex', true ),
		'bateria'       => get_post_meta( $id, '_kalf_bateria', true ),
		'observacoes'   => get_post_meta( $id, '_kalf_observacoes', true ),
		'marca'         => $marca ? $marca->name : '',
		'marca_url'     => $marca ? get_term_link( $marca ) : '',
		'galeria'       => kalf_galeria_ids( $id ),
	);
}

/**
 * IDs das fotos: a destacada primeiro, depois a galeria, sem repetir.
 * Limite de 8 imagens.
 *
 * @param int $id ID do aparelho.
 * @return int[]
 */
function kalf_galeria_ids( $id = null ) {
	$id  = $id ? $id : get_the_ID();
	$ids = array();

	$destacada = get_post_thumbnail_id( $id );
	if ( $destacada ) {
		$ids[] = (int) $destacada;
	}

	$galeria = get_post_meta( $id, '_kalf_galeria', true );
	if ( $galeria ) {
		foreach ( explode( ',', $galeria ) as $item ) {
			$item = (int) trim( $item );
			if ( $item && ! in_array( $item, $ids, true ) ) {
				$ids[] = $item;
			}
		}
	}

	return array_slice( $ids, 0, 8 );
}

/**
 * Linha de especificacoes do card: "128 GB · Meia-noite · Bateria 92%".
 * Campos vazios simplesmente nao entram.
 *
 * @param array $d Dados de kalf_dados_aparelho().
 * @return string
 */
function kalf_linha_specs( $d ) {
	$partes = array();

	if ( $d['armaz_txt'] ) {
		$partes[] = $d['armaz_txt'];
	}
	if ( $d['cor'] ) {
		$partes[] = $d['cor'];
	}
	if ( 'seminovo' === $d['condicao'] && '' !== $d['bateria'] ) {
		$partes[] = sprintf(
			/* translators: %s: porcentagem da bateria */
			__( 'Bateria %s%%', 'kalf-store' ),
			$d['bateria']
		);
	}

	return implode( ' · ', $partes );
}

/**
 * Qual selo o card mostra. Prioridade: Vendido > Destaque > Novo/Seminovo.
 *
 * @param array $d Dados de kalf_dados_aparelho().
 * @return array{slug:string,texto:string}
 */
function kalf_selo( $d ) {
	if ( $d['vendido'] ) {
		return array(
			'slug'  => 'vendido',
			'texto' => __( 'Vendido', 'kalf-store' ),
		);
	}

	if ( $d['destaque'] ) {
		return array(
			'slug'  => 'destaque',
			'texto' => __( 'Destaque', 'kalf-store' ),
		);
	}

	return array(
		'slug'  => $d['condicao'],
		'texto' => $d['condicao_txt'],
	);
}

/**
 * Numero do WhatsApp so com digitos, no formato 55DDDNUMERO.
 *
 * @return string Vazio quando nao configurado.
 */
function kalf_whatsapp_numero() {
	$bruto = preg_replace( '/\D/', '', (string) kalf_config( 'whatsapp' ) );

	if ( '' === $bruto ) {
		return '';
	}

	// Aceita o numero com ou sem o 55 na frente.
	if ( 0 !== strpos( $bruto, '55' ) ) {
		$bruto = '55' . $bruto;
	}

	return $bruto;
}

/**
 * Monta o link do WhatsApp com a mensagem codificada.
 *
 * @param string $mensagem Texto da mensagem.
 * @return string Link wa.me, ou '#' quando o numero nao esta configurado.
 */
function kalf_whatsapp_link( $mensagem = '' ) {
	$numero = kalf_whatsapp_numero();

	if ( '' === $numero ) {
		return '#whatsapp-nao-configurado';
	}

	$mensagem = '' !== $mensagem ? $mensagem : kalf_mensagem_geral();

	return 'https://wa.me/' . $numero . '?text=' . rawurlencode( $mensagem );
}

/**
 * Mensagem padrao do header, hero e contato.
 *
 * @return string
 */
function kalf_mensagem_geral() {
	return kalf_config(
		'mensagem_padrao',
		__( 'Olá! Vim pelo site da KALF STORE e quero ajuda para escolher um celular.', 'kalf-store' )
	);
}

/**
 * Mensagem do produto. Campos vazios nao deixam parenteses soltos.
 *
 * @param array $d Dados de kalf_dados_aparelho().
 * @return string
 */
function kalf_mensagem_produto( $d ) {
	$detalhes = array_filter( array( $d['condicao_txt'], $d['armaz_txt'], $d['cor'] ) );

	$nome = $d['titulo'];
	if ( $detalhes ) {
		$nome .= ' (' . implode( ', ', $detalhes ) . ')';
	}

	return sprintf(
		/* translators: 1: nome e detalhes do aparelho, 2: link do produto */
		__( 'Olá! Tenho interesse no %1$s. Ainda está disponível? %2$s', 'kalf-store' ),
		$nome,
		$d['url']
	);
}

/**
 * Imprime um icone do conjunto (traco 2px, pontas arredondadas — estilo Lucide).
 *
 * @param string $nome    Nome do icone.
 * @param int    $tamanho Lado em px.
 * @return void
 */
function kalf_icone( $nome, $tamanho = 20 ) {
	$paths = array(
		'whatsapp'  => '<path d="M21 11.5a8.4 8.4 0 0 1-12.3 7.5L3 21l2-5.6A8.4 8.4 0 1 1 21 11.5z"/>',
		'seta'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'voltar'    => '<path d="M15 6l-6 6 6 6"/>',
		'avancar'   => '<path d="M9 6l6 6-6 6"/>',
		'busca'     => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'lupa-mais' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5M11 8v6M8 11h6"/>',
		'fechar'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'filtros'   => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
		'escudo'    => '<path d="M12 3l8 3v6c0 4.5-3.4 8-8 9-4.6-1-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
		'cartao'    => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18M7 15h4"/>',
		'troca'     => '<path d="M7 4L3 8l4 4M3 8h14M17 20l4-4-4-4M21 16H7"/>',
		'chave'     => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
		'pino'      => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'relogio'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.8"/>',
		'waze'      => '<path d="M3 11l18-8-8 18-2-8-8-2z"/>',
		'bateria'   => '<rect x="2" y="7" width="18" height="10" rx="2"/><path d="M22 11v2M6 10v4M9 10v4"/>',
		'celular'   => '<rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/>',
		'compartir' => '<path d="M12 3v12M7 8l5-5 5 5M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/>',
		'check'     => '<path d="M5 12l5 5 9-10"/>',
		'cima'      => '<path d="M6 15l6-6 6 6"/>',
		'baixo'     => '<path d="M6 9l6 6 6-6"/>',
	);

	if ( ! isset( $paths[ $nome ] ) ) {
		return;
	}

	printf(
		'<svg aria-hidden="true" focusable="false" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%2$s</svg>',
		(int) $tamanho,
		$paths[ $nome ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interno fixo.
	);
}
