<?php
declare(strict_types=1);

/**
 * Ponto de entrada da aplicação Android.
 *
 * É o index.php sem as contas: nem login, nem registo, nem criação de temas,
 * nem estatísticas — só escolher um tema público e jogar a bracket. É este o
 * URL que o manifest.webmanifest aponta como start_url, e é por isso que a
 * app abre sempre já no torneio.
 *
 * O torneio nunca precisou de sessão iniciada (ver api/resultado.php), por
 * isso não falta aqui nada de funcional: os temas públicos são visíveis a
 * toda a gente e os resultados continuam a contar para as estatísticas.
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
