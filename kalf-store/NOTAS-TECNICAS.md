# KALF STORE — notas técnicas

Para quem for dar manutenção no código. Não é o guia do dono da loja
(esse é o `GUIA-DO-CLIENTE.md`, ao lado deste arquivo).

## Stack

WordPress clássico (sem page builder). PHP + template hierarchy, CSS com
custom properties, JavaScript puro (sem jQuery no front, sem framework).
Zero plugins — CPT, taxonomia, meta boxes e configurações são todos
registrados no próprio tema.

## Estrutura

```
kalf-store/
  style.css              cabeçalho do tema (metadados)
  functions.php          bootstrap: assets, image sizes, fontes, hooks gerais
  header.php / footer.php
  front-page.php         Home
  archive-aparelho.php   Catálogo (/celulares/)
  taxonomy-marca.php     /marca/<slug>/ — reaproveita o archive
  single-aparelho.php    Página do produto
  page-contato.php       Template da página /contato/ (slug "contato")
  404.php / search.php   search.php redireciona a busca pro catálogo

  inc/
    cpt.php              CPT "aparelho", taxonomia "marca", opções fixas
    meta-fields.php       Campos do cadastro (meta boxes) + salvar
    settings.php          Tela "Configurações da loja"
    admin.php              Colunas da lista, edição rápida, duplicar, limpar menu
    query.php              Filtros/busca/ordenação + endpoint AJAX do catálogo
    seo.php                 Meta tags, Open Graph, schema.org, sitemap, GTM
    helpers.php             Funções de leitura/formatação usadas nos templates
    dev-seed.php            SÓ DESENVOLVIMENTO — ver seção própria abaixo

  template-parts/
    card-produto.php        Card de produto — usado em Home/Catálogo/Relacionados/AJAX
    menu-mobile.php
    contato-bloco.php        Bloco "Venha nos visitar" (Home + Contato)
    painel-filtros-mobile.php
    estado-vazio.php         "Busca sem resultado" / "catálogo vazio"

  assets/
    css/  tokens.css → base.css → componentes.css → layout.css
          + catalogo.css, produto.css, contato.css (carregados só na página certa)
    js/   app.js (menu, scroll-reveal, evento whatsapp_click)
          catalogo.js (filtros/busca/ordenação via AJAX + URL)
          galeria.js (carrossel + lightbox + compartilhar)
          mapa.js (carrega o iframe do Maps só quando entra na tela)
          admin-galeria.js (media library da galeria, só no admin)
```

## Onde mudar cada coisa

| Quero mudar... | Vou em... |
|---|---|
| Cor da marca, tipografia, espaçamento, raio | `assets/css/tokens.css` — é a única fonte, tudo puxa dali |
| Mensagem do WhatsApp, endereço, horário etc. | Painel → Configurações da loja (não tem nada disso fixo no código) |
| Campos do cadastro de aparelho | `inc/meta-fields.php` (tela) + `inc/helpers.php` (leitura) |
| Layout de um card de produto | `template-parts/card-produto.php` + `assets/css/componentes.css` |
| Regras de filtro/ordenação/paginação | `inc/query.php` |
| Textos de SEO / schema.org | `inc/seo.php` |

## Como o WhatsApp funciona

`kalf_whatsapp_link( $mensagem )` em `inc/helpers.php` monta
`https://wa.me/55DDDNUMERO?text=...`. O número vem só de
**Configurações da loja**; se estiver vazio, o link cai num fallback
`#whatsapp-nao-configurado` em vez de gerar um link quebrado — configure
o WhatsApp antes de publicar.

A mensagem do produto (`kalf_mensagem_produto()`) monta o texto sem
deixar parênteses vazios quando algum campo (cor, armazenamento) não
está preenchido.

Todo clique num botão de WhatsApp dispara `whatsapp_click` no
`dataLayer`, com a origem do clique e (quando é de um produto) nome e
preço — é só plugar o Google Tag Manager em Configurações → ID do GTM.

## Catálogo: filtros e AJAX

`inc/query.php` faz duas consultas (disponíveis + vendidos) e concatena,
porque não dá pra misturar ordenação por preço com "vendido sempre no
fim" numa query só. O endpoint AJAX (`kalf_ajax_catalogo`) devolve o
HTML pronto dos cards — o JS (`catalogo.js`) só troca o conteúdo da
grade e atualiza a URL via `history.pushState`, sem recarregar a
página. Isso inclui o HTML do estado "sem resultado", pra mostrar o
termo de busca certo mesmo depois de uma busca via AJAX.

## Imagens

Tamanhos registrados em `functions.php`: `kalf-card` (600×750),
`kalf-galeria` (1200×1500), `kalf-mini` (180×225) — todos com `crop:
false`, porque a moldura 4:5 quem faz é o CSS (`object-fit: contain`),
nunca o recorte do WordPress. Convertidas para WebP automaticamente
quando o servidor suporta (`image_editor_output_format`).

## Acessibilidade — decisões que valem registrar

- A barra de admin do WordPress (`show_admin_bar`) está **desligada no
  front**. Ela usa um z-index maior que qualquer coisa do tema e, no
  mobile, chegou a ficar por cima do botão de fechar o menu — um clique
  ali abria o perfil do admin em vez de fechar o menu. Sem ela, quem
  navega logado vê o site exatamente como o cliente vê.
- O card marcado como **Vendido** usa 45% de opacidade no texto (é o
  que o guia de estilo pede). Isso reprova o teste automático de
  contraste do Lighthouse *só nesse card* — é intencional: o status
  "Vendido" já é anunciado com contraste total pelo selo e pelo botão
  desabilitado; o texto por baixo (título/preço) é só decorativo àquele
  ponto, o aparelho não está mais disponível.
- O link da foto do card **não tem `aria-label`** de propósito — o nome
  acessível nasce do que está visível dentro dele (o `alt` da foto, ou
  o texto do placeholder "Foto em breve"). Um rótulo por fora dizendo
  algo diferente do conteúdo visível reprova o WCAG 2.5.3 e confunde
  quem usa controle por voz.

## Ambiente local usado para testar (não faz parte do tema)

Este projeto tem uma pasta `node_modules/` (fora de `kalf-store/`) com
`@wp-playground/cli`, `puppeteer` e `lighthouse` — servem só para eu
rodar um WordPress local e testar sem precisar instalar PHP/MySQL/Docker
na máquina. Nada disso vai para a hospedagem do cliente.

Para subir de novo:
```
node_modules/.bin/wp-playground-cli server --auto-mount="./kalf-store" --login --port=8881 --php=8.3 --intl=false
```
(`--intl=false` contorna um bug da extensão intl nessa versão do
Playground no Windows.)

### `inc/dev-seed.php` — remover antes de publicar

Cria 8 aparelhos de exemplo cobrindo os casos difíceis (sem preço, sem
foto, título longo, vendido, seminovo com bateria, galeria cheia).
Roda ao visitar `/wp-admin/?kalf_seed=1` logado como administrador, e é
idempotente (apaga os exemplos antigos antes de recriar). **Antes de
publicar o site de verdade**: apague `inc/dev-seed.php`, a pasta
`_seed-images/` e a linha que os inclui em `functions.php` (tem um
comentário marcando o lugar).

## Testado e comprovado nesta sessão

- Lighthouse mobile (Chrome real, não é estimativa): Home
  Performance 93 / Acessibilidade 100 / Boas práticas 100 / SEO 100.
  Catálogo 93 / 97 / 100 / 100. Produto 92 / 100 / 100 / 100.
- Zero erros, avisos, notices ou deprecated do PHP com `WP_DEBUG` e
  `WP_DEBUG_DISPLAY` ligados, testado em 17 URLs (home, catálogo com
  filtro/busca combinados, arquivo de marca, os 8 produtos de exemplo,
  contato, 404, sitemap.xml, robots.txt).
- Links de WhatsApp corretos nos 8 produtos de exemplo (mensagem sem
  parênteses vazios, número e texto certos); o vendido corretamente não
  gera link, mostra botão desabilitado.
- Sem rolagem horizontal em 390/768/1440px, testado com Chrome
  headless de verdade (não é estimativa visual).
- Menu mobile abre, fecha, tranca o foco e fecha com Esc. Filtros do
  catálogo (mobile e desktop) refletem na URL, atualizam a contagem e a
  grade via AJAX. Busca com debounce mostra o estado "sem resultado"
  certo. Galeria: seta, contador, lightbox abre/fecha com Esc.
- Cadastro de aparelho testado em 390px: sem rolagem horizontal, todos
  os campos e ajudas em português simples.

## Publicar na hospedagem

1. Apague `inc/dev-seed.php` + `_seed-images/` (ver acima).
2. Zip da pasta `kalf-store/` inteira.
3. WordPress normal → Aparência → Temas → Adicionar novo → Enviar tema.
4. Ative o tema. Em **Aparelhos → Configurações da loja**, preencha
   WhatsApp, endereço, horário, Instagram e o resto.
5. Crie os aparelhos reais (ou peça pro dono cadastrar pelo
   `GUIA-DO-CLIENTE.md`).
6. Em Configurações → Leitura, confirme a página inicial e os
   links permanentes ("Nome do post") pra `/celulares/<slug>/` funcionar.
