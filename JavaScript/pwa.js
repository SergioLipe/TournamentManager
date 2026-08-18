/**
 * Regista o service worker.
 *
 * Sem isto não há instalação: o Chrome só oferece "instalar" a um site com
 * manifest E service worker activo, e a TWA que vai para a Play Store assenta
 * exactamente nesses dois.
 *
 * Corre em todas as páginas, e não só no app.php, para que o site também seja
 * instalável a partir do browser normal.
 */
(function () {
  'use strict';

  if (!('serviceWorker' in navigator)) {
    return;
  }

  // Depois do load: o registo compete com os pedidos da própria página, e a
  // primeira coisa que a bracket faz é ir buscar imagens.
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('sw.js').catch(function () {
      /* Sem service worker o site funciona à mesma — só não se instala. */
    });
  });
})();
