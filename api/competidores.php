<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/temas.php';

/**
 * Devolve os competidores de um tema, por ordem aleatória, para preencher
 * uma bracket.
 *
 * POST: temaId (int), quantos (int, opcional)
 */

if (!ehPost()) {
    erroJson('Método não permitido.', 405);
}

$temaId  = inteiro($_POST, 'temaId');
$quantos = inteiro($_POST, 'quantos', MAX_COMPETITORS, MIN_COMPETITORS, MAX_COMPETITORS);

if ($temaId <= 0) {
    erroJson('Tema inválido.');
}

// Um tema privado de outro utilizador é indistinguível de um que não existe.
$tema = temaVisivelOuNull($temaId);
if ($tema === null) {
    erroJson('Tema não encontrado.', 404);
}

// O LIMIT não aceita placeholder com prepares nativos ligados, por isso o
// valor é interpolado — mas só depois de inteiro() o ter forçado a um int
// entre MIN_COMPETITORS e MAX_COMPETITORS, logo nunca chega aqui texto livre.
$competidores = obterTodas(
    'SELECT id, nome, imagem FROM competidor WHERE TemaId = ? ORDER BY RAND() LIMIT ' . (int) $quantos,
    [$temaId]
);

responderJson([
    'tema'         => ['id' => (int) $tema['id'], 'nome' => $tema['nome']],
    'competidores' => array_map(static function (array $linha): array {
        return [
            'id'     => (int) $linha['id'],
            'nome'   => $linha['nome'],
            'imagem' => $linha['imagem'],
        ];
    }, $competidores),
]);
