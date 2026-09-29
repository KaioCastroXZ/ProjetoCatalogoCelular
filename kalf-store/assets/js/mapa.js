/**
 * Carrega o iframe do Google Maps somente quando o bloco entra na tela.
 */
(function () {
	'use strict';

	var mapa = document.querySelector( '[data-kalf-mapa]' );
	if ( ! mapa ) return;

	function carregar() {
		var url = mapa.getAttribute( 'data-kalf-mapa' );
		if ( ! url ) return;
		var iframe = document.createElement( 'iframe' );
		iframe.src = url;
		iframe.loading = 'lazy';
		iframe.title = 'Mapa — KALF STORE';
		iframe.setAttribute( 'referrerpolicy', 'no-referrer-when-downgrade' );
		mapa.prepend( iframe );
	}

	if ( 'IntersectionObserver' in window ) {
		var observador = new IntersectionObserver( function ( entradas ) {
			entradas.forEach( function ( entrada ) {
				if ( entrada.isIntersecting ) {
					carregar();
					observador.disconnect();
				}
			} );
		} );
		observador.observe( mapa );
	} else {
		carregar();
	}
} )();
