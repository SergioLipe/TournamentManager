/**
 * Comportamento partilhado por todas as páginas: barra de navegação
 * retráctil e o contador de competidores.
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
    aplicarEstadoNav(localStorage.getItem(CHAVE_NAV) === '1');

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

  /* ---------------------------------------------------------------------- */
  /*  Confirmação antes de acções destrutivas                               */
  /* ---------------------------------------------------------------------- */

  // O texto vem de um data-attribute e não de um onsubmit inline: assim
  // nomes com plicas ou aspas não conseguem escapar para dentro do código.
  document.addEventListener('submit', function (evento) {
    var formulario = evento.target;
    if (!formulario.dataset || !formulario.dataset.confirmar) {
      return;
    }
    if (!window.confirm(formulario.dataset.confirmar)) {
      evento.preventDefault();
    }
  });

  /* ---------------------------------------------------------------------- */
  /*  Contador de competidores                                              */
  /* ---------------------------------------------------------------------- */

  var input = document.getElementById('numImagens');
  var menos = document.getElementById('qtyMenos');
  var mais = document.getElementById('qtyMais');

  if (!input) {
    return;
  }

  var minimo = parseInt(input.min, 10) || 2;
  var maximo = parseInt(input.max, 10) || 16;

  function limitar(valor) {
    if (isNaN(valor)) {
      return minimo;
    }
    return Math.min(maximo, Math.max(minimo, valor));
  }

  function definir(valor) {
    var novo = limitar(valor);
    if (String(novo) === input.value) {
      return;
    }
    input.value = String(novo);
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }

  if (menos) {
    menos.addEventListener('click', function () {
      definir(parseInt(input.value, 10) - 1);
    });
  }

  if (mais) {
    mais.addEventListener('click', function () {
      definir(parseInt(input.value, 10) + 1);
    });
  }

  // Só normaliza quando o campo perde o foco, para não corrigir o valor a
  // meio de o utilizador o estar a escrever.
  input.addEventListener('blur', function () {
    definir(parseInt(input.value, 10));
  });

  input.addEventListener('change', function () {
    var limitado = limitar(parseInt(input.value, 10));
    if (String(limitado) !== input.value) {
      input.value = String(limitado);
    }
  });
})();
