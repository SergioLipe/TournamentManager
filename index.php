<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/temas.php';

$tituloPagina     = 'Tournament';
$controlosTorneio = true;
$scriptsExtra     = ['JavaScript/bracket.js', 'JavaScript/torneio.js'];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/torneio-view.php';
require __DIR__ . '/includes/footer.php';
