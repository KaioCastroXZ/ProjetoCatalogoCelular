/**
 * KALF STORE — catálogo: busca, filtros, ordenação, paginação. Tudo
 * refletido na URL e sem recarregar a página.
 */
(function () {
	'use strict';

	if ( typeof kalfCatalogo === 'undefined' ) return;

	var grade = document.querySelector( '[data-kalf-grade]' );
	var estadoVazio = document.querySelector( '[data-kalf-estado-vazio]' );
	var totalEl = document.querySelector( '[data-kalf-total]' );
	var mostrandoEl = document.querySelector( '[data-kalf-mostrando]' );
	var botaoMais = document.querySelector( '[data-kalf-carregar-mais]' );
	var inputBusca = document.querySelector( '[data-kalf-busca]' );
	var selectOrdenar = document.querySelector( '[data-kalf-ordenar]' );
	var listaAtivos = document.querySelector( '[data-kalf-lista-ativos]' );
	var blocoAtivos = document.querySelector( '[data-kalf-filtros-ativos]' );

	if ( ! grade ) return;

	var POR_PAGINA = 12;

	var estado = lerEstadoDaUrl();

	function lerEstadoDaUrl() {
		var params = new URLSearchParams( window.location.search );
		return {
			busca: params.get( 'busca' ) || '',
			marca: ( params.get( 'marca' ) || '' ).split( ',' ).filter( Boolean ),
			condicao: ( params.get( 'condicao' ) || '' ).split( ',' ).filter( Boolean ),
			armazenamento: ( params.get( 'armazenamento' ) || '' ).split( ',' ).filter( Boolean ),
			ordenar: params.get( 'ordenar' ) || 'recentes',
			pagina: parseInt( params.get( 'pagina' ) || '1', 10 ),
		};
	}

	function escreverEstadoNaUrl( substituir ) {
		var params = new URLSearchParams();
		if ( estado.busca ) params.set( 'busca', estado.busca );
		if ( estado.marca.length ) params.set( 'marca', estado.marca.join( ',' ) );
		if ( estado.condicao.length ) params.set( 'condicao', estado.condicao.join( ',' ) );
		if ( estado.armazenamento.length ) params.set( 'armazenamento', estado.armazenamento.join( ',' ) );
		if ( estado.ordenar !== 'recentes' ) params.set( 'ordenar', estado.ordenar );
		if ( estado.pagina > 1 ) params.set( 'pagina', String( estado.pagina ) );

		var query = params.toString();
		var url = window.location.pathname + ( query ? '?' + query : '' );
		var metodo = substituir ? 'replaceState' : 'pushState';
		window.history[ metodo ]( {}, '', url );
	}

	var timerBusca = null;
	var requisicaoAtual = null;

	function buscar( acrescentar ) {
		if ( requisicaoAtual ) requisicaoAtual.abort();

		grade.setAttribute( 'aria-busy', 'true' );

		var dados = new FormData();
		dados.append( 'action', 'kalf_catalogo' );
		dados.append( 'nonce', kalfCatalogo.nonce );
		dados.append( 'busca', estado.busca );
		dados.append( 'marca', estado.marca.join( ',' ) );
		dados.append( 'condicao', estado.condicao.join( ',' ) );
		dados.append( 'armazenamento', estado.armazenamento.join( ',' ) );
		dados.append( 'ordenar', estado.ordenar );
		dados.append( 'pagina', String( estado.pagina ) );

		var controlador = new AbortController();
		requisicaoAtual = controlador;

		fetch( kalfCatalogo.ajaxUrl, { method: 'POST', body: dados, signal: controlador.signal } )
			.then( function ( resposta ) { return resposta.json(); } )
			.then( function ( resultado ) {
				if ( ! resultado.success ) return;
				var d = resultado.data;

				if ( acrescentar ) {
					grade.insertAdjacentHTML( 'beforeend', envolverItens( d.html ) );
				} else {
					grade.innerHTML = envolverItens( d.html );
				}

				if ( estadoVazio ) {
					if ( d.vazio && d.vazioHtml ) estadoVazio.innerHTML = d.vazioHtml;
					estadoVazio.hidden = ! d.vazio;
				}
				if ( totalEl ) {
					totalEl.textContent = d.total + ( d.total === 1 ? ' aparelho' : ' aparelhos' );
				}
				if ( mostrandoEl ) {
					mostrandoEl.textContent = 'Mostrando ' + d.mostrando + ' de ' + d.total + ' aparelhos';
				}
				if ( botaoMais ) botaoMais.hidden = ! d.temMais;

				grade.setAttribute( 'aria-busy', 'false' );
				requisicaoAtual = null;
			} )
			.catch( function ( erro ) {
				if ( erro.name !== 'AbortError' ) {
					grade.setAttribute( 'aria-busy', 'false' );
				}
			} );
	}

	function envolverItens( html ) {
		// O endpoint devolve <article>...; a grade espera <li><article>...</li>.
		var modelo = document.createElement( 'template' );
		modelo.innerHTML = html;
		var itens = Array.prototype.slice.call( modelo.content.children );
		return itens.map( function ( item ) { return '<li>' + item.outerHTML + '</li>'; } ).join( '' );
	}

	function aplicarMudanca( substituirNaHistoria ) {
		estado.pagina = 1;
		escreverEstadoNaUrl( substituirNaHistoria );
		atualizarUi();
		buscar( false );
	}

	function atualizarUi() {
		if ( inputBusca ) inputBusca.value = estado.busca;
		if ( selectOrdenar ) selectOrdenar.value = estado.ordenar;

		document.querySelectorAll( '[data-kalf-painel] input[type="checkbox"]' ).forEach( function ( input ) {
			var grupo = input.name;
			input.checked = estado[ grupo ] && estado[ grupo ].indexOf( input.value ) !== -1;
		} );

		document.querySelectorAll( '[data-kalf-filtro]' ).forEach( function ( botao ) {
			var grupo = botao.dataset.kalfFiltro;
			var ativo = estado[ grupo ] && estado[ grupo ].indexOf( botao.dataset.valor ) !== -1;
			botao.setAttribute( 'aria-pressed', ativo ? 'true' : 'false' );
		} );

		renderizarFiltrosAtivos();
	}

	function renderizarFiltrosAtivos() {
		if ( ! listaAtivos || ! blocoAtivos ) return;
		var ativos = [];

		estado.marca.forEach( function ( slug ) {
			ativos.push( { grupo: 'marca', valor: slug, rotulo: rotuloAmigavel( 'marca', slug ) } );
		} );
		estado.condicao.forEach( function ( slug ) {
			ativos.push( { grupo: 'condicao', valor: slug, rotulo: rotuloAmigavel( 'condicao', slug ) } );
		} );
		estado.armazenamento.forEach( function ( slug ) {
			ativos.push( { grupo: 'armazenamento', valor: slug, rotulo: rotuloAmigavel( 'armazenamento', slug ) } );
		} );

		blocoAtivos.hidden = ativos.length === 0;
		listaAtivos.innerHTML = ativos
			.map( function ( item ) {
				return (
					'<button type="button" class="kalf-chip-remover" data-kalf-remover data-grupo="' +
					escaparHtml( item.grupo ) +
					'" data-valor="' +
					escaparHtml( item.valor ) +
					'" aria-label="Remover filtro ' +
					escaparHtml( item.rotulo ) +
					'">' +
					escaparHtml( item.rotulo ) +
					' ✕</button>'
				);
			} )
			.join( '' );
	}

	var MAPA_ESCAPE = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
	function escaparHtml( texto ) {
		return String( texto ).replace( /[&<>"']/g, function ( c ) { return MAPA_ESCAPE[ c ]; } );
	}

	function rotuloAmigavel( grupo, slug ) {
		var mapa = kalfCatalogo.rotulos && kalfCatalogo.rotulos[ grupo ];
		return ( mapa && mapa[ slug ] ) || slug;
	}

	/* ---------------- Busca ---------------- */
	if ( inputBusca ) {
		inputBusca.addEventListener( 'input', function () {
			clearTimeout( timerBusca );
			var valor = inputBusca.value;
			timerBusca = setTimeout( function () {
				estado.busca = valor;
				aplicarMudanca( false );
			}, 250 );
		} );
	}

	/* ---------------- Ordenar ---------------- */
	if ( selectOrdenar ) {
		selectOrdenar.addEventListener( 'change', function () {
			estado.ordenar = selectOrdenar.value;
			aplicarMudanca( false );
		} );
	}

	/* ---------------- Checkboxes do popover desktop ---------------- */
	document.querySelectorAll( '[data-kalf-painel] input[type="checkbox"]' ).forEach( function ( input ) {
		input.addEventListener( 'change', function () {
			var grupo = input.name;
			var lista = estado[ grupo ];
			if ( input.checked ) {
				if ( lista.indexOf( input.value ) === -1 ) lista.push( input.value );
			} else {
				estado[ grupo ] = lista.filter( function ( v ) { return v !== input.value; } );
			}
			aplicarMudanca( false );
		} );
	} );

	/* ---------------- Popovers desktop (Marca / Mais filtros) ---------------- */
	document.querySelectorAll( '[data-kalf-abrir-marca], [data-kalf-abrir-mais]' ).forEach( function ( botao ) {
		botao.addEventListener( 'click', function () {
			var alvo = botao.hasAttribute( 'data-kalf-abrir-marca' ) ? 'marca' : 'mais';
			var painel = document.querySelector( '[data-kalf-painel="' + alvo + '"]' );
			if ( ! painel ) return;
			var vaiAbrir = painel.hidden;

			document.querySelectorAll( '[data-kalf-painel]' ).forEach( function ( p ) { p.hidden = true; } );
			document.querySelectorAll( '[data-kalf-abrir-marca], [data-kalf-abrir-mais]' ).forEach( function ( b ) {
				b.setAttribute( 'aria-expanded', 'false' );
			} );

			painel.hidden = ! vaiAbrir;
			botao.setAttribute( 'aria-expanded', vaiAbrir ? 'true' : 'false' );
		} );
	} );

	document.addEventListener( 'click', function ( evento ) {
		var dentroDePainel = evento.target.closest( '[data-kalf-painel], [data-kalf-abrir-marca], [data-kalf-abrir-mais]' );
		if ( dentroDePainel ) return;
		document.querySelectorAll( '[data-kalf-painel]' ).forEach( function ( p ) { p.hidden = true; } );
		document.querySelectorAll( '[data-kalf-abrir-marca], [data-kalf-abrir-mais]' ).forEach( function ( b ) {
			b.setAttribute( 'aria-expanded', 'false' );
		} );
	} );

	/* ---------------- Painel mobile (chips) ---------------- */
	var rascunhoMobile = null;

	function abrirPainelMobile() {
		rascunhoMobile = {
			marca: estado.marca.slice(),
			condicao: estado.condicao.slice(),
			armazenamento: estado.armazenamento.slice(),
		};
		var fundo = document.querySelector( '[data-kalf-painel-mobile]' );
		if ( ! fundo ) return;
		fundo.hidden = false;
		document.body.style.overflow = 'hidden';
	}

	function fecharPainelMobile() {
		var fundo = document.querySelector( '[data-kalf-painel-mobile]' );
		if ( fundo ) fundo.hidden = true;
		document.body.style.overflow = '';
	}

	document.querySelectorAll( '[data-kalf-abrir-painel-mobile]' ).forEach( function ( botao ) {
		botao.addEventListener( 'click', abrirPainelMobile );
	} );
	document.querySelectorAll( '[data-kalf-fechar-painel-mobile]' ).forEach( function ( botao ) {
		botao.addEventListener( 'click', fecharPainelMobile );
	} );

	document.querySelectorAll( '[data-kalf-filtro]' ).forEach( function ( botao ) {
		botao.addEventListener( 'click', function () {
			var grupo = botao.dataset.kalfFiltro;
			var valor = botao.dataset.valor;
			var ativo = botao.getAttribute( 'aria-pressed' ) === 'true';
			botao.setAttribute( 'aria-pressed', ativo ? 'false' : 'true' );

			if ( ! rascunhoMobile ) return;
			var lista = rascunhoMobile[ grupo ];
			if ( ativo ) {
				rascunhoMobile[ grupo ] = lista.filter( function ( v ) { return v !== valor; } );
			} else {
				lista.push( valor );
			}
		} );
	} );

	var botaoLimparMobile = document.querySelector( '[data-kalf-limpar-painel-mobile]' );
	if ( botaoLimparMobile ) {
		botaoLimparMobile.addEventListener( 'click', function () {
			rascunhoMobile = { marca: [], condicao: [], armazenamento: [] };
			document.querySelectorAll( '[data-kalf-filtro]' ).forEach( function ( b ) {
				b.setAttribute( 'aria-pressed', 'false' );
			} );
		} );
	}

	var botaoAplicarMobile = document.querySelector( '[data-kalf-aplicar-painel-mobile]' );
	if ( botaoAplicarMobile ) {
		botaoAplicarMobile.addEventListener( 'click', function () {
			if ( rascunhoMobile ) {
				estado.marca = rascunhoMobile.marca;
				estado.condicao = rascunhoMobile.condicao;
				estado.armazenamento = rascunhoMobile.armazenamento;
			}
			fecharPainelMobile();
			aplicarMudanca( false );
		} );
	}

	/* ---------------- Remover filtro / limpar tudo ---------------- */
	document.addEventListener( 'click', function ( evento ) {
		var botaoRemover = evento.target.closest( '[data-kalf-remover]' );
		if ( botaoRemover ) {
			var grupo = botaoRemover.dataset.grupo;
			var valor = botaoRemover.dataset.valor;
			estado[ grupo ] = estado[ grupo ].filter( function ( v ) { return v !== valor; } );
			aplicarMudanca( false );
			return;
		}

		var limparTudo = evento.target.closest( '[data-kalf-limpar-tudo]' );
		if ( limparTudo ) {
			evento.preventDefault();
			estado = { busca: estado.busca, marca: [], condicao: [], armazenamento: [], ordenar: estado.ordenar, pagina: 1 };
			aplicarMudanca( false );
			return;
		}

		var limparBusca = evento.target.closest( '[data-kalf-limpar-busca]' );
		if ( limparBusca ) {
			estado.busca = '';
			aplicarMudanca( false );
		}
	} );

	/* ---------------- Carregar mais ---------------- */
	if ( botaoMais ) {
		botaoMais.addEventListener( 'click', function () {
			estado.pagina += 1;
			escreverEstadoNaUrl( false );
			buscar( true );
		} );
	}

	/* ---------------- Voltar/avançar do navegador ---------------- */
	window.addEventListener( 'popstate', function () {
		estado = lerEstadoDaUrl();
		atualizarUi();
		buscar( false );
	} );

	atualizarUi();
} )();
