<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/uploads.php';

/**
 * Recebe os uploads de CriarTema.php e volta a redireccionar para lá.
 *
 * Não imprime HTML: guarda as mensagens na sessão e redirecciona, para que
 * um refresh não reenvie os ficheiros.
 */

exigirLogin();

if (!ehPost()) {
    redirecionar('CriarTema.php');
}

exigirCsrf();

$utilizador = (int) utilizadorId();

/* -------------------------------------------------------------------------- */
/*  Descobrir o tema de destino                                               */
/* -------------------------------------------------------------------------- */

$tema = null;

if (isset($_POST['criar_tema'])) {
    $nome = trim((string) ($_POST['nomeTema'] ?? ''));
    $erro = validarNomeTema($nome);

    if ($erro !== null) {
        guardarMensagem('erro', $erro);
        redirecionar('CriarTema.php');
    }

    if (obterTemaDoUtilizadorPorNome($utilizador, $nome) !== null) {
        guardarMensagem('erro', 'You already have a theme with that name.');
        redirecionar('CriarTema.php');
    }

    query('INSERT INTO tema (nome, utilizadorId, publico) VALUES (?, ?, 0)', [$nome, $utilizador]);
    $tema = obterTema((int) db()->lastInsertId());
} elseif (isset($_POST['tema_existente'])) {
    $tema = obterTema(inteiro($_POST, 'temaId'));

    if (!podeEditarTema($tema)) {
        guardarMensagem('erro', 'That theme does not exist, or it is not yours.');
        redirecionar('CriarTema.php');
    }
} else {
    redirecionar('CriarTema.php');
}

/* -------------------------------------------------------------------------- */
/*  Guardar as imagens                                                        */
/* -------------------------------------------------------------------------- */

$temaId = (int) $tema['id'];

try {
    $resultado = guardarImagens($temaId, $_FILES['imagens'] ?? []);
} catch (Throwable $e) {
    error_log('Falha no upload: ' . $e->getMessage());
    guardarMensagem('erro', 'Something went wrong while storing the images.');
    redirecionar('CriarTema.php?temaId=' . $temaId);
}

if ($resultado['guardados'] > 0) {
    guardarMensagem('success', sprintf(
        '%d image%s added to "%s".',
        $resultado['guardados'],
        $resultado['guardados'] === 1 ? '' : 's',
        $tema['nome']
    ));
}

foreach ($resultado['erros'] as $erro) {
    guardarMensagem('erro', $erro);
}

redirecionar('CriarTema.php?temaId=' . $temaId);
