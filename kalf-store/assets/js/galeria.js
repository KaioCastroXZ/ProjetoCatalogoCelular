/**
 * KALF STORE — galeria do produto: carrossel com swipe no mobile,
 * miniaturas no desktop, e lightbox em tela cheia com zoom por pinça.
 */
(function () {
	'use strict';

	var raiz = document.querySelector( '[data-kalf-galeria]' );
	if ( ! raiz ) return;

	var trilho = raiz.querySelector( '[data-kalf-trilho]' );
	var slides = Array.prototype.slice.call( raiz.querySelectorAll( '.kalf-galeria__slide' ) );
	var contador = raiz.querySelector( '[data-kalf-contador]' );
	var pontos = Array.prototype.slice.call( raiz.querySelectorAll( '.kalf-galeria__ponto' ) );
	var miniaturas = Array.prototype.slice.call( raiz.querySelectorAll( '.kalf-galeria__miniatura' ) );
	var indiceAtual = 0;

	function irPara( indice ) {
		if ( indice < 0 || indice >= slides.length ) return;
		indiceAtual = indice;
		trilho.scrollTo( { left: slides[ indice ].offsetLeft - trilho.offsetLeft, behavior: 'smooth' } );
		atualizarIndicadores();
	}

	function atualizarIndicadores() {
		if ( contador ) contador.textContent = ( indiceAtual + 1 ) + ' / ' + slides.length;
		pontos.forEach( function ( ponto, i ) {
			ponto.classList.toggle( 'kalf-galeria__ponto--ativo', i === indiceAtual );
		} );
		miniaturas.forEach( function ( mini, i ) {
			if ( i === indiceAtual ) {
				mini.setAttribute( 'aria-current', 'true' );
			} else {
				mini.removeAttribute( 'aria-current' );
			}
		} );
	}

	// Detecta o slide visível ao arrastar/deslizar.
	var timerScroll = null;
	if ( trilho ) {
		trilho.addEventListener( 'scroll', function () {
			clearTimeout( timerScroll );
			timerScroll = setTimeout( function () {
				var centro = trilho.scrollLeft + trilho.clientWidth / 2;
				var maisProximo = 0;
				var menorDistancia = Infinity;
				slides.forEach( function ( slide, i ) {
					var distancia = Math.abs( slide.offsetLeft + slide.offsetWidth / 2 - centro );
					if ( distancia < menorDistancia ) {
						menorDistancia = distancia;
						maisProximo = i;
					}
				} );
				indiceAtual = maisProximo;
				atualizarIndicadores();
			}, 100 );
		} );
	}

	var botaoAnterior = raiz.querySelector( '[data-kalf-anterior]' );
	var botaoProxima = raiz.querySelector( '[data-kalf-proxima]' );
	if ( botaoAnterior ) botaoAnterior.addEventListener( 'click', function () { irPara( indiceAtual - 1 ); } );
	if ( botaoProxima ) botaoProxima.addEventListener( 'click', function () { irPara( indiceAtual + 1 ); } );

	miniaturas.forEach( function ( mini, i ) {
		mini.addEventListener( 'click', function () { irPara( i ); } );
	} );

	/* ------------------------------------------------------------------
	 * Lightbox em tela cheia
	 * ------------------------------------------------------------------ */
	var urlsGrandes = slides.map( function ( slide ) {
		var img = slide.querySelector( 'img' );
		return img ? ( img.currentSrc || img.src ) : '';
	} );

	var lightbox = criarLightbox();
	document.body.appendChild( lightbox.elemento );

	function criarLightbox() {
		var el = document.createElement( 'div' );
		el.className = 'kalf-lightbox';
		el.setAttribute( 'role', 'dialog' );
		el.setAttribute( 'aria-modal', 'true' );
		el.setAttribute( 'aria-label', 'Fotos do produto' );
		el.hidden = true;

		el.innerHTML =
			'<div class="kalf-lightbox__topo">' +
				'<span data-lb-contador></span>' +
				'<button type="button" class="kalf-btn-icone kalf-lightbox__fechar" data-lb-fechar aria-label="Fechar galeria"></button>' +
			'</div>' +
			'<div class="kalf-lightbox__palco" data-lb-palco>' +
				'<img class="kalf-lightbox__imagem" data-lb-imagem alt="">' +
				'<button type="button" class="kalf-lightbox__seta kalf-lightbox__seta--esq" data-lb-anterior aria-label="Foto anterior"></button>' +
				'<button type="button" class="kalf-lightbox__seta kalf-lightbox__seta--dir" data-lb-proxima aria-label="Próxima foto"></button>' +
			'</div>' +
			'<div class="kalf-lightbox__rodape">' +
				'<span class="kalf-lightbox__dica">Pinça ou toque duplo para ampliar · deslize para trocar</span>' +
				'<div class="kalf-lightbox__miniaturas" data-lb-miniaturas></div>' +
			'</div>';

		// Icones via kalf_icone equivalente em JS simples (reaproveita os mesmos paths essenciais).
		el.querySelector( '[data-lb-fechar]' ).innerHTML = svg( 'M6 6l12 12M18 6L6 18', 22 );
		el.querySelector( '[data-lb-anterior]' ).innerHTML = svg( 'M15 6l-6 6 6 6', 22 );
		el.querySelector( '[data-lb-proxima]' ).innerHTML = svg( 'M9 6l6 6-6 6', 22 );

		var miniaturasWrap = el.querySelector( '[data-lb-miniaturas]' );
		slides.forEach( function ( slide, i ) {
			var botao = document.createElement( 'button' );
			botao.type = 'button';
			botao.className = 'kalf-lightbox__miniatura';
			botao.setAttribute( 'aria-label', 'Foto ' + ( i + 1 ) );
			var img = slide.querySelector( 'img' );
			if ( img ) {
				var mini = document.createElement( 'img' );
				mini.src = img.currentSrc || img.src;
				mini.alt = '';
				botao.appendChild( mini );
			}
			botao.addEventListener( 'click', function () { mostrar( i ); } );
			miniaturasWrap.appendChild( botao );
		} );

		var indiceLb = 0;
		var focoAnterior = null;

		function mostrar( indice ) {
			if ( indice < 0 || indice >= urlsGrandes.length ) return;
			indiceLb = indice;
			var imgEl = el.querySelector( '[data-lb-imagem]' );
			imgEl.src = urlsGrandes[ indice ];
			imgEl.style.transform = 'scale(1)';
			el.querySelector( '[data-lb-contador]' ).textContent = ( indice + 1 ) + ' / ' + urlsGrandes.length;
			Array.prototype.forEach.call( miniaturasWrap.children, function ( botao, i ) {
				if ( i === indice ) {
					botao.setAttribute( 'aria-current', 'true' );
				} else {
					botao.removeAttribute( 'aria-current' );
				}
			} );
		}

		function abrir( indice ) {
			focoAnterior = document.activeElement;
			mostrar( indice );
			el.hidden = false;
			document.body.style.overflow = 'hidden';
			el.querySelector( '[data-lb-fechar]' ).focus();
			document.addEventListener( 'keydown', aoTeclar );
		}

		function fechar() {
			el.hidden = true;
			document.body.style.overflow = '';
			document.removeEventListener( 'keydown', aoTeclar );
			if ( focoAnterior ) focoAnterior.focus();
		}

		function aoTeclar( evento ) {
			if ( evento.key === 'Escape' ) fechar();
			if ( evento.key === 'ArrowLeft' ) mostrar( indiceLb - 1 );
			if ( evento.key === 'ArrowRight' ) mostrar( indiceLb + 1 );
		}

		el.querySelector( '[data-lb-fechar]' ).addEventListener( 'click', fechar );
		el.querySelector( '[data-lb-anterior]' ).addEventListener( 'click', function () { mostrar( indiceLb - 1 ); } );
		el.querySelector( '[data-lb-proxima]' ).addEventListener( 'click', function () { mostrar( indiceLb + 1 ); } );
		el.addEventListener( 'click', function ( evento ) {
			if ( evento.target === el ) fechar();
		} );

		/* Zoom por pinca / duplo toque, so na imagem em foco */
		var palco = el.querySelector( '[data-lb-palco]' );
		var imagemZoom = el.querySelector( '[data-lb-imagem]' );
		configurarZoom( palco, imagemZoom );

		return { elemento: el, abrir: abrir, mostrar: mostrar };
	}

	function svg( path, tamanho ) {
		return (
			'<svg aria-hidden="true" width="' + tamanho + '" height="' + tamanho +
			'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="' +
			path + '"/></svg>'
		);
	}

	function configurarZoom( palco, imagem ) {
		var escala = 1;
		var distanciaInicial = 0;
		var escalaInicial = 1;
		var ultimoToque = 0;

		palco.addEventListener( 'touchstart', function ( evento ) {
			if ( evento.touches.length === 2 ) {
				distanciaInicial = distanciaEntreToques( evento.touches );
				escalaInicial = escala;
			} else if ( evento.touches.length === 1 ) {
				var agora = Date.now();
				if ( agora - ultimoToque < 300 ) {
					escala = escala > 1 ? 1 : 2;
					imagem.style.transform = 'scale(' + escala + ')';
				}
				ultimoToque = agora;
			}
		} );

		palco.addEventListener(
			'touchmove',
			function ( evento ) {
				if ( evento.touches.length === 2 && distanciaInicial ) {
					evento.preventDefault();
					var nova = distanciaEntreToques( evento.touches );
					escala = Math.min( 3, Math.max( 1, escalaInicial * ( nova / distanciaInicial ) ) );
					imagem.style.transform = 'scale(' + escala + ')';
				}
			},
			{ passive: false }
		);

		palco.addEventListener( 'touchend', function ( evento ) {
			if ( evento.touches.length === 0 ) distanciaInicial = 0;
		} );
	}

	function distanciaEntreToques( toques ) {
		var dx = toques[ 0 ].clientX - toques[ 1 ].clientX;
		var dy = toques[ 0 ].clientY - toques[ 1 ].clientY;
		return Math.sqrt( dx * dx + dy * dy );
	}

	raiz.querySelectorAll( '[data-kalf-abrir-lightbox]' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( evento ) {
			evento.preventDefault();
			lightbox.abrir( parseInt( link.dataset.indice, 10 ) || 0 );
		} );
	} );

	var botaoAmpliar = raiz.querySelector( '[data-kalf-ampliar]' );
	if ( botaoAmpliar ) {
		botaoAmpliar.addEventListener( 'click', function () {
			lightbox.abrir( indiceAtual );
		} );
	}

	/* ------------------------------------------------------------------
	 * Compartilhar: Web Share API com fallback de copiar o link.
	 * ------------------------------------------------------------------ */
	var botaoCompartilhar = document.querySelector( '[data-kalf-compartilhar]' );
	if ( botaoCompartilhar ) {
		botaoCompartilhar.addEventListener( 'click', function () {
			var titulo = botaoCompartilhar.dataset.titulo || document.title;
			var url = botaoCompartilhar.dataset.url || window.location.href;

			if ( navigator.share ) {
				navigator.share( { title: titulo, url: url } ).catch( function () {} );
				return;
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( url ).then( function () {
					var textoOriginal = botaoCompartilhar.innerHTML;
					botaoCompartilhar.textContent = 'Link copiado!';
					setTimeout( function () {
						botaoCompartilhar.innerHTML = textoOriginal;
					}, 2000 );
				} );
			}
		} );
	}
} )();
