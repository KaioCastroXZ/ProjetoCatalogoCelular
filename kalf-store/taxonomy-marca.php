<?php
/**
 * Arquivo de marca (ex.: /marca/apple/). Reaproveita o catálogo inteiro,
 * só pré-selecionando o filtro de marca a partir do termo da URL.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $_GET['marca'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- leitura de filtro, nao de acao.
	$termo = get_queried_object();
	if ( $termo instanceof WP_Term ) {
		$_GET['marca'] = $termo->slug; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}
}

require locate_template( 'archive-aparelho.php' );
