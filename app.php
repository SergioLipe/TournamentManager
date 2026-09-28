<?php
declare(strict_types=1);

/**
 * Ponto de entrada da aplicação Android.
 *
 * É o index.php sem as ligações para as estatísticas e para a página sobre —
 * só escolher um tema e jogar a bracket. É este o URL que o
 * manifest.webmanifest aponta como start_url, e é por isso que a app abre
 * sempre já no torneio.
 *
 * Os resultados jogados aqui continuam a contar para as estatísticas.
 *
 * Continua a ser uma página normal do site — abrir /app.php num browser
 * qualquer funciona. O que a torna "a app" é o manifest e o Digital Asset
 * Links, não código próprio.
 */

require_once __DIR__ . '/includes/temas.php';

$tituloPagina     = 'Tournament';
$controlosTorneio = true;
$modoApp          = true;
$scriptsExtra     = ['JavaScript/bracket.js', 'JavaScript/torneio.js'];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/torneio-view.php';
require __DIR__ . '/includes/footer.php';
