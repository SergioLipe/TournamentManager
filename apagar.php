<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/uploads.php';

/**
 * Apaga um competidor ou um tema inteiro.
 *
 * Antes bastava um GET com um id qualquer — sem sessão e sem verificar o
 * dono — para apagar o competidor de outra pessoa. Agora exige POST, token
 * CSRF e propriedade do tema.
 */

exigirLogin();

if (!ehPost()) {
    redirecionar('CriarTema.php');
}

exigirCsrf();

/* -------------------------------------------------------------------------- */
/*  Apagar um competidor                                                      */
/* -------------------------------------------------------------------------- */

if (isset($_POST['competidorId'])) {
    $competidorId = inteiro($_POST, 'competidorId');
    $competidor   = obterLinha('SELECT id, imagem, TemaId FROM competidor WHERE id = ?', [$competidorId]);

    if ($competidor === null) {
        guardarMensagem('erro', 'That competitor no longer exists.');
        redirecionar('CriarTema.php');
    }

    // O id do tema é lido antes de apagar. A versão antiga lia-o depois, por
    // isso o redireccionamento perdia sempre o tema.
    $temaId = (int) $competidor['TemaId'];

    if (!podeEditarTema(obterTema($temaId))) {
        guardarMensagem('erro', 'That competitor is not yours to delete.');
        redirecionar('CriarTema.php');
    }

    apagarCompetidor($competidor);
    guardarMensagem('success', 'Competitor deleted.');
    redirecionar('CriarTema.php?temaId=' . $temaId);
}

/* -------------------------------------------------------------------------- */
/*  Apagar um tema inteiro                                                    */
/* -------------------------------------------------------------------------- */

if (isset($_POST['temaId'])) {
    $tema = obterTema(inteiro($_POST, 'temaId'));

    if (!podeEditarTema($tema)) {
        guardarMensagem('erro', 'That theme does not exist, or it is not yours.');
        redirecionar('CriarTema.php');
    }

    $temaId = (int) $tema['id'];

    foreach (obterTodas('SELECT id, imagem FROM competidor WHERE TemaId = ?', [$temaId]) as $competidor) {
        apagarCompetidor($competidor);
    }

    query('DELETE FROM tema WHERE id = ?', [$temaId]);

    // A pasta só desaparece se ficou mesmo vazia.
    @rmdir(UPLOAD_DIR . '/temas/' . $temaId);

    guardarMensagem('success', sprintf('Theme "%s" deleted.', $tema['nome']));
    redirecionar('CriarTema.php');
}

redirecionar('CriarTema.php');
