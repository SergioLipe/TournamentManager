<?php
declare(strict_types=1);

require_once __DIR__ . '/temas.php';

/**
 * Upload de imagens de competidores.
 *
 * Regras que a versão antiga não tinha:
 *  - o tipo é decidido pelo conteúdo real do ficheiro, não pela extensão;
 *  - o nome do ficheiro é gerado aqui, nunca vem do cliente;
 *  - a pasta de destino deriva do id do tema, por isso o nome do tema não
 *    consegue escapar da pasta Imagens/.
 */

/** Valida o nome de um tema. Devolve null se for aceitável, ou o erro. */
function validarNomeTema(string $nome): ?string
{
    $nome = trim($nome);

    if ($nome === '') {
        return 'The theme needs a name.';
    }
    if (mb_strlen($nome) > 25) {
        return 'The theme name can be at most 25 characters.';
    }
    if (!preg_match('/^[\p{L}\p{N} _\'-]+$/u', $nome)) {
        return 'The theme name can only contain letters, digits, spaces, apostrophes, dashes and underscores.';
    }

    return null;
}

/** Pasta onde ficam as imagens de um tema, criada se ainda não existir. */
function pastaDoTema(int $temaId): string
{
    // Só o id inteiro entra no caminho — nada vindo do utilizador.
    $pasta = UPLOAD_DIR . '/temas/' . $temaId;

    if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
        throw new RuntimeException('Não foi possível criar a pasta do tema.');
    }

    return $pasta;
}

/** Caminho relativo guardado na base de dados e usado no src das imagens. */
function caminhoRelativo(int $temaId, string $ficheiro): string
{
    return 'Imagens/temas/' . $temaId . '/' . $ficheiro;
}

/**
 * Reorganiza $_FILES['imagens'] numa lista de ficheiros individuais.
 * O PHP entrega os uploads múltiplos como colunas paralelas, o que é
 * incómodo de percorrer.
 */
function normalizarFicheiros(array $campo): array
{
    if (!isset($campo['name']) || !is_array($campo['name'])) {
        return [];
    }

    $ficheiros = [];
    foreach (array_keys($campo['name']) as $i) {
        $ficheiros[] = [
            'name'     => (string) $campo['name'][$i],
            'tmp_name' => (string) $campo['tmp_name'][$i],
            'size'     => (int) $campo['size'][$i],
            'error'    => (int) $campo['error'][$i],
        ];
    }

    return $ficheiros;
}

/** Descreve um código de erro de upload do PHP. */
function descreverErroUpload(int $codigo): string
{
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'the file is too large';
        case UPLOAD_ERR_PARTIAL:
            return 'the upload was interrupted';
        case UPLOAD_ERR_NO_FILE:
            return 'no file was sent';
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
            return 'the server could not store the file';
        default:
            return 'the upload failed';
    }
}

/**
 * Deriva o nome do competidor a partir do nome original do ficheiro.
 * É só uma sugestão inicial — o utilizador pode renomear depois.
 */
function nomeCompetidorDeFicheiro(string $nomeFicheiro): string
{
    $nome = pathinfo($nomeFicheiro, PATHINFO_FILENAME);
    $nome = str_replace(['_', '-'], ' ', $nome);
    $nome = preg_replace('/\s+/u', ' ', $nome);
    $nome = trim((string) $nome);

    if ($nome === '') {
        $nome = 'Competitor';
    }

    // A coluna aceita 50 caracteres.
    return mb_substr($nome, 0, 50);
}

/**
 * Guarda os ficheiros enviados como competidores do tema.
 *
 * Devolve ['guardados' => int, 'erros' => string[]].
 */
function guardarImagens(int $temaId, array $campoFicheiros): array
{
    $ficheiros = normalizarFicheiros($campoFicheiros);
    $erros     = [];
    $guardados = 0;

    // Ignora as entradas vazias que o browser envia quando não se escolhe nada.
    $ficheiros = array_values(array_filter($ficheiros, static function (array $f): bool {
        return $f['error'] !== UPLOAD_ERR_NO_FILE;
    }));

    if ($ficheiros === []) {
        return ['guardados' => 0, 'erros' => ['Pick at least one image.']];
    }

    if (count($ficheiros) > MAX_UPLOADS_PER_REQUEST) {
        return [
            'guardados' => 0,
            'erros'     => ['You can upload at most ' . MAX_UPLOADS_PER_REQUEST . ' images at a time.'],
        ];
    }

    $pasta = pastaDoTema($temaId);
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($ficheiros as $ficheiro) {
        $etiqueta = $ficheiro['name'] !== '' ? $ficheiro['name'] : 'file';

        if ($ficheiro['error'] !== UPLOAD_ERR_OK) {
            $erros[] = sprintf('"%s": %s.', $etiqueta, descreverErroUpload($ficheiro['error']));
            continue;
        }

        // Confirma que o ficheiro veio mesmo de um upload HTTP e não é um
        // caminho arbitrário do servidor.
        if (!is_uploaded_file($ficheiro['tmp_name'])) {
            $erros[] = sprintf('"%s": rejected.', $etiqueta);
            continue;
        }

        if ($ficheiro['size'] <= 0 || $ficheiro['size'] > MAX_UPLOAD_BYTES) {
            $erros[] = sprintf(
                '"%s": must be between 1 byte and %d MB.',
                $etiqueta,
                (int) (MAX_UPLOAD_BYTES / 1024 / 1024)
            );
            continue;
        }

        // O tipo vem do conteúdo. Um .jpg que na verdade seja PHP é apanhado aqui.
        $mime = (string) $finfo->file($ficheiro['tmp_name']);
        if (!isset(ALLOWED_IMAGE_TYPES[$mime])) {
            $erros[] = sprintf('"%s": not a supported image (JPEG, PNG, GIF, WEBP or AVIF).', $etiqueta);
            continue;
        }

        // Segunda verificação: tem mesmo dimensões de imagem, e o tipo que o
        // getimagesize deduz bate certo com o que o finfo disse.
        // O AVIF só é reconhecido pelo getimagesize em PHP 8.1+, por isso
        // nesse formato fica-se pelo MIME.
        if ($mime !== 'image/avif') {
            $dimensoes = @getimagesize($ficheiro['tmp_name']);

            if ($dimensoes === false || $dimensoes[0] < 1 || $dimensoes[1] < 1) {
                $erros[] = sprintf('"%s": the file is not a readable image.', $etiqueta);
                continue;
            }

            if (isset($dimensoes['mime']) && $dimensoes['mime'] !== $mime) {
                $erros[] = sprintf('"%s": the file contents do not match its type.', $etiqueta);
                continue;
            }

            if ($dimensoes[0] * $dimensoes[1] > MAX_IMAGE_PIXELS) {
                $erros[] = sprintf('"%s": the image dimensions are too large.', $etiqueta);
                continue;
            }

            // Terceira verificação: descodificar mesmo o ficheiro.
            //
            // Sem isto, basta pôr os magic bytes certos ("GIF89a...") à frente
            // de qualquer conteúdo para passar nas duas verificações
            // anteriores — o finfo lê a assinatura e o getimagesize aceita os
            // bytes seguintes como largura e altura. Só a descodificação
            // completa distingue uma imagem verdadeira de uma forjada.
            if (function_exists('imagecreatefromstring')) {
                $conteudo = file_get_contents($ficheiro['tmp_name']);
                $imagem   = $conteudo === false ? false : @imagecreatefromstring($conteudo);
                unset($conteudo);

                if ($imagem === false) {
                    $erros[] = sprintf('"%s": the file is not a valid image.', $etiqueta);
                    continue;
                }

                imagedestroy($imagem);
            }
        }

        $extensao = ALLOWED_IMAGE_TYPES[$mime];
        $destino  = bin2hex(random_bytes(8)) . '.' . $extensao;

        if (!move_uploaded_file($ficheiro['tmp_name'], $pasta . '/' . $destino)) {
            $erros[] = sprintf('"%s": could not be stored.', $etiqueta);
            continue;
        }

        @chmod($pasta . '/' . $destino, 0644);

        query(
            'INSERT INTO competidor (imagem, nome, TemaId) VALUES (?, ?, ?)',
            [caminhoRelativo($temaId, $destino), nomeCompetidorDeFicheiro($ficheiro['name']), $temaId]
        );

        $guardados++;
    }

    return ['guardados' => $guardados, 'erros' => $erros];
}

/**
 * Apaga um competidor e o respectivo ficheiro.
 * Só remove o ficheiro se ele estiver dentro de Imagens/temas/ — as imagens
 * originais do projecto estão noutras pastas e são partilhadas entre temas.
 */
function apagarCompetidor(array $competidor): void
{
    query('DELETE FROM competidor WHERE id = ?', [(int) $competidor['id']]);

    $relativo = (string) $competidor['imagem'];
    if (strpos($relativo, 'Imagens/temas/') !== 0) {
        return;
    }

    $absoluto = APP_ROOT . '/' . $relativo;
    $real     = realpath($absoluto);
    $base     = realpath(UPLOAD_DIR . '/temas');

    // Confirma que o caminho resolvido continua debaixo de Imagens/temas/.
    if ($real !== false && $base !== false && strpos($real, $base) === 0 && is_file($real)) {
        @unlink($real);
    }
}
