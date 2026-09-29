<?php
/**
 * Busca nativa do WP (ex.: alguem digita direto na URL /?s=...).
 * Como so existe um CPT publico, redireciona para o catalogo com o
 * mesmo termo, que e onde a busca de verdade acontece via AJAX.
 *
 * @package kalf-store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$termo = get_search_query();
wp_safe_redirect( add_query_arg( 'busca', rawurlencode( $termo ), get_post_type_archive_link( 'aparelho' ) ) );
exit;
