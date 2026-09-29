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
