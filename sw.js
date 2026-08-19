/**
 * Service worker do Tournament.
 *
 * Existe por duas razões: sem um, o Chrome não considera o site instalável e
 * não há TWA nenhuma; e as fotografias dos competidores são o grosso do que
 * a app transfere, por isso vale a pena guardá-las.
 *
 * O que NÃO faz: jogar offline. A bracket vem da base de dados, e essa está
 * no servidor — sem rede há o offline.html e mais nada. Fingir o contrário
 * daria um torneio vazio em vez de uma mensagem honesta.
 */

'use strict';

/* Sobe quando o conteúdo de um ficheiro muda sem o nome mudar.
 *
 * A cache de imagens é lida antes da rede e a chave é o URL, por isso quem
 * jogou o tema Dinosaurs antes de as imagens passarem de esqueletos a
 * reconstituições continuava a ver esqueletos: o Allosaurus.jpg guardado no
 * telemóvel nunca mais era pedido ao servidor. Mudar a versão deita fora as
 * caches antigas no activate.
 *
 * O mesmo vale para o CSS e o JavaScript, que também são lidos da cache antes
 * da rede: publicar um style.css novo sem subir isto dá a quem já cá esteve o
 * HTML novo com a folha de estilos antiga. */
var VERSAO = 'v4';
var CACHE_ESTATICO = 'tournament-estatico-' + VERSAO;
var CACHE_IMAGENS = 'tournament-imagens-' + VERSAO;

/* Limite da cache de imagens. Cada tema são 16 ficheiros; isto chegam para
   uns quantos temas jogados sem encher o telemóvel de quem o instalou. */
var MAX_IMAGENS = 180;

var OFFLINE = 'offline.html';

/* Pastas que só mudam quando se publica. */
var PASTAS_ESTATICAS = ['/CSS/', '/JavaScript/', '/icons/'];

/* O app.php não entra aqui: é PHP e a lista de temas muda. */
var ESTATICOS = [
  OFFLINE,
  'CSS/style.css',
  'JavaScript/ui.js',
  'JavaScript/bracket.js',
  'JavaScript/torneio.js',
  'icons/icon-192.png',
  'Imagens/placeholder.jpg'
];

self.addEventListener('install', function (evento) {
  evento.waitUntil(
    caches.open(CACHE_ESTATICO)
      .then(function (cache) {
        // addAll é atómico: um 404 num ficheiro qualquer faz falhar a
        // instalação inteira. Vai um a um para que um ficheiro renomeado não
        // deixe o service worker por instalar e o site por instalável.
        return Promise.all(ESTATICOS.map(function (caminho) {
          return cache.add(caminho).catch(function () {
            /* Este fica de fora; a rede trata dele. */
          });
        }));
      })
      .then(function () {
        return self.skipWaiting();
      })
  );
});

self.addEventListener('activate', function (evento) {
  evento.waitUntil(
    caches.keys()
      .then(function (nomes) {
        return Promise.all(nomes.map(function (nome) {
          var meu = nome.indexOf('tournament-') === 0;
          var actual = nome === CACHE_ESTATICO || nome === CACHE_IMAGENS;
          return (meu && !actual) ? caches.delete(nome) : null;
        }));
      })
      .then(function () {
        return self.clients.claim();
      })
  );
});

/** Deita fora as entradas mais antigas quando a cache passa do limite. */
function aparar(nomeCache, maximo) {
  return caches.open(nomeCache).then(function (cache) {
    return cache.keys().then(function (chaves) {
      if (chaves.length <= maximo) {
        return null;
      }
      // As chaves vêm por ordem de inserção, por isso as primeiras são as
      // mais antigas.
      return Promise.all(chaves.slice(0, chaves.length - maximo).map(function (chave) {
        return cache.delete(chave);
      }));
    });
  });
}

/** True para os caminhos de PASTAS_ESTATICAS. */
function ehEstatico(caminho) {
  for (var i = 0; i < PASTAS_ESTATICAS.length; i++) {
    if (caminho.indexOf(PASTAS_ESTATICAS[i]) === 0) {
      return true;
    }
  }
  return false;
}

/** Rede primeiro, cache só quando a rede falha. Para as páginas. */
function paginaComRedeAntes(pedido) {
  return fetch(pedido).catch(function () {
    return caches.match(OFFLINE);
  });
}

/** Cache primeiro, rede em segundo. Para o que nunca muda sem mudar de nome. */
function cacheAntes(pedido, nomeCache, maximo) {
  return caches.match(pedido).then(function (guardado) {
    if (guardado) {
      return guardado;
    }
    return fetch(pedido).then(function (resposta) {
      // Só respostas completas e nossas. Uma opaca (cross-origin sem CORS)
      // ocupa espaço sem se poder inspeccionar, e um 404 guardado fica
      // guardado.
      if (resposta && resposta.ok && resposta.type === 'basic') {
        var copia = resposta.clone();
        caches.open(nomeCache).then(function (cache) {
          return cache.put(pedido, copia);
        }).then(function () {
          if (maximo) {
            return aparar(nomeCache, maximo);
          }
        });
      }
      return resposta;
    });
  });
}

self.addEventListener('fetch', function (evento) {
  var pedido = evento.request;

  // Só GET. As duas APIs do torneio são POST, por isso ficam de fora deste
  // if sozinhas — guardar um resultado de batalha em cache seria um erro.
  if (pedido.method !== 'GET') {
    return;
  }

  var url;
  try {
    url = new URL(pedido.url);
  } catch (erro) {
    return;
  }

  // O Bootstrap vem de um CDN. Deixa-se ao cache do browser: guardar
  // respostas opacas aqui não deixa sequer verificar se correram bem.
  if (url.origin !== self.location.origin) {
    return;
  }

  // As páginas são PHP e dependem da base de dados: nunca se servem de uma
  // cópia guardada, só se cai no offline.html quando não há rede.
  if (pedido.mode === 'navigate') {
    evento.respondWith(paginaComRedeAntes(pedido));
    return;
  }

  if (url.pathname.indexOf('/Imagens/') === 0) {
    evento.respondWith(cacheAntes(pedido, CACHE_IMAGENS, MAX_IMAGENS));
    return;
  }

  if (ehEstatico(url.pathname)) {
    evento.respondWith(cacheAntes(pedido, CACHE_ESTATICO, 0));
    return;
  }

  /* O resto (api/, entre outros) vai directo à rede. */
});
