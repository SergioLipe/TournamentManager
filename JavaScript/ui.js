/**
 * Comportamento partilhado por todas as páginas: a barra de navegação
 * retráctil.
 */
(function () {
  'use strict';

  /* ---------------------------------------------------------------------- */
  /*  Barra de navegação retráctil                                          */
  /* ---------------------------------------------------------------------- */

  var CHAVE_NAV = 'torneio:navEscondida';
  var botaoNav = document.getElementById('toggleNavBtn');
  var nav = document.getElementById('mainNav');

  function aplicarEstadoNav(escondida) {
    if (!nav || !botaoNav) {
      return;
    }
    nav.classList.toggle('app-nav--hidden', escondida);
    document.body.classList.toggle('nav-escondida', escondida);
    botaoNav.setAttribute('aria-expanded', escondida ? 'false' : 'true');
    botaoNav.firstElementChild.innerHTML = escondida ? '&#9660;' : '&#9650;';
  }

  if (botaoNav && nav) {
    // Mantém a escolha entre páginas — quem esconde o menu para ver a bracket
    // não o quer de volta a cada navegação.
    var guardada = null;
    try {
      guardada = localStorage.getItem(CHAVE_NAV);
    } catch (erro) {
      /* Sem localStorage a barra começa sempre aberta. */
    }
    aplicarEstadoNav(guardada === '1');

    // Para o torneio esconder a barra ao carregar um tema: a partir daí o que
    // interessa é a bracket, e num telemóvel deitado a barra comia um terço
    // da altura. Não se guarda — é o torneio a pedir, não uma escolha de quem
    // joga — e o botão redondo continua lá para a trazer de volta.
    window.TorneioNav = {
      esconder: function () {
        aplicarEstadoNav(true);
      }
    };

    botaoNav.addEventListener('click', function () {
      var escondida = !nav.classList.contains('app-nav--hidden');
      aplicarEstadoNav(escondida);
      try {
        localStorage.setItem(CHAVE_NAV, escondida ? '1' : '0');
      } catch (erro) {
        /* localStorage indisponível (modo privado): o estado não persiste. */
      }
    });
  }
})();

/**
 * Travar a app na horizontal (só no app.php).
 *
 * Na app da Play Store isto já está resolvido pelo Android, a partir do
 * "orientation" do manifest — e funciona mesmo com a rotação automática
 * bloqueada, sem pedir a ninguém que a desbloqueie.
 *
 * No Chrome, uma página só pode travar a orientação em ecrã inteiro, e só
 * pode pôr-se em ecrã inteiro depois de um toque. Daí o botão "Tap to play":
 * ecrã inteiro, depois screen.orientation.lock. Instalada a partir do Chrome
 * (display-mode standalone) já não precisa do toque e trava logo.
 *
 * Onde não há screen.orientation.lock — o Safari do iPhone — não há maneira
 * de forçar, e fica o pedido para rodar.
 */
(function () {
  'use strict';

  var rodar = document.getElementById('rodar');
  if (!rodar) {
    return;
  }

  var botao = document.getElementById('rodarBotao');
  var alternativa = document.getElementById('rodarAlternativa');
  var raiz = document.documentElement;

  var podeTravar = !!(window.screen && screen.orientation && typeof screen.orientation.lock === 'function');
  var podeEcraInteiro = typeof raiz.requestFullscreen === 'function';

  function travar() {
    return podeTravar ? screen.orientation.lock('landscape') : Promise.reject(new Error('sem lock'));
  }

  function semForcar() {
    botao.hidden = true;
    alternativa.hidden = false;
  }

  if (!podeTravar || !podeEcraInteiro) {
    semForcar();
  }

  var instalada = window.matchMedia('(display-mode: standalone)').matches
    || window.matchMedia('(display-mode: fullscreen)').matches;

  if (instalada) {
    travar().catch(function () {
      /* Fica o botão, que tenta outra vez com ecrã inteiro. */
    });
  }

  botao.addEventListener('click', function () {
    raiz.requestFullscreen({ navigationUI: 'hide' })
      .then(travar)
      .catch(semForcar);
  });
})();
