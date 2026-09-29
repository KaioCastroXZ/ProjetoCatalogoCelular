/**
 * KALF STORE — comportamento geral: menu mobile, entrada ao rolar e
 * evento de clique no WhatsApp para o dataLayer.
 */
(function () {
	'use strict';

	/* ------------------------------------------------------------------
	 * Menu mobile: abre em tela cheia, foco preso, fecha com Esc.
	 * ------------------------------------------------------------------ */
	var menu = document.getElementById( 'kalf-menu-mobile' );
	var botaoAbrir = document.querySelector( '[data-kalf-menu-abrir]' );
	var botaoFechar = document.querySelector( '[data-kalf-menu-fechar]' );
	var focoAnterior = null;

	function focaveisDentroDe( container ) {
		return Array.prototype.slice.call(
			container.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
			)
		);
	}

	function abrirMenu() {
		if ( ! menu ) return;
		focoAnterior = document.activeElement;
		menu.hidden = false;
		document.body.style.overflow = 'hidden';
		var focaveis = focaveisDentroDe( menu );
		if ( focaveis.length ) focaveis[ 0 ].focus();
		document.addEventListener( 'keydown', aoTeclarNoMenu );
	}

	function fecharMenu() {
		if ( ! menu ) return;
		menu.hidden = true;
		document.body.style.overflow = '';
		document.removeEventListener( 'keydown', aoTeclarNoMenu );
		if ( focoAnterior ) focoAnterior.focus();
	}

	function aoTeclarNoMenu( evento ) {
		if ( evento.key === 'Escape' ) {
			fecharMenu();
			return;
		}
		if ( evento.key !== 'Tab' ) return;

		var focaveis = focaveisDentroDe( menu );
		if ( ! focaveis.length ) return;
		var primeiro = focaveis[ 0 ];
		var ultimo = focaveis[ focaveis.length - 1 ];

		if ( evento.shiftKey && document.activeElement === primeiro ) {
			evento.preventDefault();
			ultimo.focus();
		} else if ( ! evento.shiftKey && document.activeElement === ultimo ) {
			evento.preventDefault();
			primeiro.focus();
		}
	}

	if ( botaoAbrir ) botaoAbrir.addEventListener( 'click', abrirMenu );
	if ( botaoFechar ) botaoFechar.addEventListener( 'click', fecharMenu );
	if ( menu ) {
		menu.addEventListener( 'click', function ( evento ) {
			if ( evento.target === menu ) fecharMenu();
		} );
	}

	/* ------------------------------------------------------------------
	 * Entrada ao rolar: fade + translateY, uma vez so, com atraso entre
	 * itens de uma mesma grade (data-kalf-atraso opcional).
	 * ------------------------------------------------------------------ */
	var elementos = document.querySelectorAll( '.kalf-revelar' );
	if ( 'IntersectionObserver' in window && elementos.length ) {
		var observador = new IntersectionObserver(
			function ( entradas ) {
				entradas.forEach( function ( entrada ) {
					if ( ! entrada.isIntersecting ) return;
					var alvo = entrada.target;
					var atraso = alvo.dataset.kalfAtraso ? parseInt( alvo.dataset.kalfAtraso, 10 ) : 0;
					setTimeout( function () {
						alvo.classList.add( 'kalf-visivel' );
					}, atraso );
					observador.unobserve( alvo );
				} );
			},
			{ threshold: 0.15 }
		);
		elementos.forEach( function ( elemento, indice ) {
			if ( elemento.closest( '.kalf-grade, .kalf-destaques, .kalf-diferenciais, .kalf-passos, .kalf-marcas' ) ) {
				elemento.dataset.kalfAtraso = String( ( indice % 4 ) * 60 );
			}
			observador.observe( elemento );
		} );
	} else {
		elementos.forEach( function ( elemento ) {
			elemento.classList.add( 'kalf-visivel' );
		} );
	}

	/* ------------------------------------------------------------------
	 * dataLayer: um evento whatsapp_click a cada clique num link de
	 * WhatsApp, com o nome e preco do produto quando existir.
	 * ------------------------------------------------------------------ */
	window.dataLayer = window.dataLayer || [];

	document.addEventListener( 'click', function ( evento ) {
		var link = evento.target.closest( '[data-whatsapp-evento]' );
		if ( ! link ) return;

		window.dataLayer.push( {
			event: 'whatsapp_click',
			whatsapp_origem: link.dataset.whatsappEvento,
			whatsapp_produto_nome: link.dataset.whatsappNome || '',
			whatsapp_produto_preco: link.dataset.whatsappPreco || '',
		} );
	} );
} )();
