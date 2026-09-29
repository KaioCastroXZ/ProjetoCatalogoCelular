/**
 * Admin: escolher, reordenar (arrastar) e remover fotos da galeria do
 * aparelho, usando a media library nativa do WordPress.
 */
(function ($) {
	'use strict';

	var campoOculto = document.getElementById('kalf_galeria');
	var preview = document.getElementById('kalf-galeria-preview');
	var botao = document.getElementById('kalf-galeria-botao');
	if (!campoOculto || !preview || !botao || typeof wp === 'undefined' || !wp.media) return;

	var LIMITE = 8;
	var anexos = {}; // id -> { url, alt }

	function idsAtuais() {
		return campoOculto.value ? campoOculto.value.split(',').filter(Boolean).map(Number) : [];
	}

	function salvarIds(ids) {
		campoOculto.value = ids.join(',');
	}

	function renderizar() {
		var ids = idsAtuais();
		preview.innerHTML = '';

		ids.forEach(function (id) {
			var info = anexos[id];
			var item = document.createElement('div');
			item.className = 'kalf-galeria-item';
			item.dataset.id = id;
			item.style.cssText = 'position:relative;width:90px;height:90px;border-radius:8px;overflow:hidden;border:1px solid #dcdcde;cursor:grab;';

			var img = document.createElement('img');
			img.src = info ? info.url : '';
			img.alt = '';
			img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
			item.appendChild(img);

			var remover = document.createElement('button');
			remover.type = 'button';
			remover.setAttribute('aria-label', 'Remover foto');
			remover.textContent = '×';
			remover.style.cssText = 'position:absolute;top:2px;right:2px;width:22px;height:22px;line-height:20px;border-radius:50%;border:0;background:rgba(20,17,16,.8);color:#fff;cursor:pointer;font-size:16px;';
			remover.addEventListener('click', function () {
				var novaLista = idsAtuais().filter(function (v) { return v !== id; });
				salvarIds(novaLista);
				renderizar();
			});
			item.appendChild(remover);

			preview.appendChild(item);
		});

		var contagem = document.getElementById('kalf-galeria-contagem');
		if (!contagem) {
			contagem = document.createElement('p');
			contagem.id = 'kalf-galeria-contagem';
			contagem.className = 'description';
			preview.insertAdjacentElement('afterend', contagem);
		}
		contagem.textContent = ids.length + ' de ' + LIMITE + ' fotos extras.';
		botao.disabled = ids.length >= LIMITE;
	}

	// Arrastar para reordenar, sem depender de jQuery UI Sortable
	// (nem sempre carregado fora da tela de mídia).
	var arrastando = null;
	preview.addEventListener('dragstart', function (evento) {
		var item = evento.target.closest('.kalf-galeria-item');
		if (!item) return;
		arrastando = item;
		evento.dataTransfer.effectAllowed = 'move';
	});
	preview.addEventListener('dragover', function (evento) {
		evento.preventDefault();
		var alvo = evento.target.closest('.kalf-galeria-item');
		if (!alvo || alvo === arrastando || !arrastando) return;
		var depois = [].indexOf.call(preview.children, alvo) > [].indexOf.call(preview.children, arrastando);
		preview.insertBefore(arrastando, depois ? alvo.nextSibling : alvo);
	});
	preview.addEventListener('dragend', function () {
		if (!arrastando) return;
		var novaOrdem = [].map.call(preview.children, function (el) { return Number(el.dataset.id); });
		salvarIds(novaOrdem);
		arrastando = null;
	});

	function tornarArrastaveis() {
		[].forEach.call(preview.children, function (el) { el.setAttribute('draggable', 'true'); });
	}

	var frame = null;
	botao.addEventListener('click', function (evento) {
		evento.preventDefault();

		if (!frame) {
			frame = wp.media({
				title: 'Escolher fotos da galeria',
				button: { text: 'Adicionar à galeria' },
				multiple: true,
				library: { type: 'image' },
			});

			frame.on('select', function () {
				var selecionados = frame.state().get('selection').toJSON();
				var ids = idsAtuais();

				selecionados.forEach(function (item) {
					anexos[item.id] = { url: (item.sizes && item.sizes.thumbnail) ? item.sizes.thumbnail.url : item.url };
					if (ids.indexOf(item.id) === -1 && ids.length < LIMITE) {
						ids.push(item.id);
					}
				});

				salvarIds(ids.slice(0, LIMITE));
				renderizar();
				tornarArrastaveis();
			});
		}

		frame.open();
	});

	// Carrega os dados (url) das fotos ja salvas, para desenhar o preview inicial.
	var idsIniciais = idsAtuais();
	if (idsIniciais.length && wp.media.attachment) {
		idsIniciais.forEach(function (id) {
			var attachment = wp.media.attachment(id);
			attachment.fetch().then(function () {
				anexos[id] = { url: (attachment.get('sizes') && attachment.get('sizes').thumbnail) ? attachment.get('sizes').thumbnail.url : attachment.get('url') };
				renderizar();
				tornarArrastaveis();
			});
		});
	} else {
		renderizar();
	}
})(jQuery);
