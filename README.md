# KALF STORE — site-catálogo

Site-catálogo em WordPress para a **KALF STORE**, loja de celulares em
Goiânia – GO. Vitrine sem carrinho e sem checkout: em cada aparelho, um
botão abre o WhatsApp da loja com a mensagem já pronta.

> Feito a partir de um design no Claude Design (guia de estilo, telas e
> componentes preservados em [`_design-referencia/`](./_design-referencia)),
> implementado como tema WordPress clássico — sem page builder, sem
> plugins.

## O que tem aqui

```
CatalogoCelular/
├── kalf-store/              ← O TEMA. É isto que vai para a hospedagem.
│   ├── GUIA-DO-CLIENTE.md    Passo a passo para o dono da loja usar o painel
│   ├── NOTAS-TECNICAS.md     Referência para quem mexer no código
│   └── ...
├── _design-referencia/      Telas e guia de estilo originais do design
├── prompt-claude-*.md       Os prompts que originaram o design e o tema
└── package.json             Ferramentas de teste local (não vai pra hospedagem)
```

## Cadastrando produtos — o fluxo, do zero

Este é o caminho completo: da instalação até o primeiro aparelho no ar.

### 1. Suba o tema

Pegue a pasta `kalf-store/` (só ela, não o repositório inteiro), zipe, e
em qualquer WordPress: **Aparência → Temas → Adicionar novo → Enviar
tema**. Ative.

Ao ativar, o tema já cria sozinho:
- as marcas padrão (Apple, Samsung, Motorola, Xiaomi, Outras);
- a página **Contato**;
- as URLs amigáveis (`/celulares/`, `/celulares/<nome-do-aparelho>/`).

### 2. Configure os dados da loja

Menu **Aparelhos → Configurações da loja**. Preencha WhatsApp, endereço,
horário, Instagram e o resto — **isso vem antes de cadastrar qualquer
aparelho**, porque o botão de WhatsApp só funciona depois que o número
está configurado aqui (sem ele, o site nem mostra o botão como link
ativo, de propósito, pra não gerar um link quebrado).

### 3. Cadastre o primeiro aparelho

Menu **Aparelhos → Cadastrar aparelho**:

1. **Título** = nome completo do jeito que aparece no site
   (`iPhone 13 128GB Meia-noite`).
2. Escolha a **Foto principal** (painel do lado direito) — é a foto do
   card no catálogo.
3. Adicione fotos extras em **Galeria de fotos** (até 8 no total,
   clicando em "Escolher fotos da galeria" — abre a biblioteca de mídia
   normal do WordPress, dá pra arrastar pra reordenar).
4. Preencha condição, preço, armazenamento, cor — cada campo já tem uma
   dica de preenchimento embaixo.
5. **Publicar**.

O passo a passo completo, escrito para quem não é técnico (com como
trocar foto, marcar como vendido, duplicar produto), está em
[`kalf-store/GUIA-DO-CLIENTE.md`](./kalf-store/GUIA-DO-CLIENTE.md) —
é esse arquivo que deve ir para o dono da loja.

### Sobre as fotos

O site nunca corta nem distorce foto. Qualquer proporção (retrato,
paisagem, quadrada) é centralizada numa moldura 4:5. O ideal é
fotografar em fundo claro e liso, com boa luz — mas o layout não quebra
mesmo com fotos fora do padrão, porque foi testado assim de propósito
(ver seção de testes abaixo).

## Rodando local para desenvolver

Este repositório inclui as ferramentas de teste (fora da pasta do
tema, não vão para a hospedagem):

```bash
npm install
node_modules/.bin/wp-playground-cli server --auto-mount="./kalf-store" --login --port=8881 --php=8.3 --intl=false
```

Abre um WordPress real em `http://127.0.0.1:8881`, já logado, servindo
o tema direto desta pasta — qualquer alteração em `kalf-store/` aparece
ao dar F5.

Para popular com 8 aparelhos de exemplo (cobrindo os casos difíceis:
sem preço, sem foto, título longo, vendido, seminovo com bateria,
galeria cheia), visite `/wp-admin/?kalf_seed=1` logado. Isso usa
`kalf-store/inc/dev-seed.php`, que **não deve ir para produção** — ver
o aviso no topo desse arquivo e em `NOTAS-TECNICAS.md`.

## Testado

- **Lighthouse mobile** (Chrome real): Home 93/100/100/100, Catálogo
  93/97/100/100, Produto 92/100/100/100 — Performance / Acessibilidade
  / Boas Práticas / SEO, todos ≥ 90.
- Zero erros/avisos PHP com `WP_DEBUG` ligado, em 17 URLs.
- Sem rolagem horizontal em 390 / 768 / 1440px.
- Menu mobile, filtros do catálogo (refletidos na URL), busca, galeria
  com lightbox e zoom, CTA fixo do produto no mobile — todos testados
  por interação real (clique, teclado), não só visualmente.
- Links de WhatsApp corretos nos 8 produtos de exemplo.

Detalhes técnicos completos, decisões de acessibilidade e o que mudar
onde: [`kalf-store/NOTAS-TECNICAS.md`](./kalf-store/NOTAS-TECNICAS.md).

## Stack

WordPress clássico · PHP + template hierarchy · CSS com custom
properties (zero framework) · JavaScript puro (zero jQuery, zero
dependências) · zero plugins.
