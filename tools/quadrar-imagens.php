<?php
declare(strict_types=1);

require_once __DIR__ . '/comum.php';

/**
 * Põe as imagens de um tema num quadrado, com fundo branco.
 *
 * Uso:
 *   php tools/quadrar-imagens.php Dinosaurs
 *   php tools/quadrar-imagens.php Dinosaurs --dry-run
 *
 * Duas razões, ambas visíveis no site e nenhuma resolúvel no CSS:
 *
 * 1. O cartão do duelo mostra a imagem num quadrado com `object-fit: cover`,
 *    que corta o que não couber. Uma reconstituição de um saurópode tem 960
 *    por 320 — do quadrado do meio sobra o tronco, sem cabeça nem cauda.
 *    Deixar a imagem já quadrada faz o `cover` não ter nada que cortar.
 *
 * 2. Muitas das reconstituições do Commons são PNG com fundo transparente.
 *    No tema escuro do site, um bicho de traço escuro sobre transparente
 *    desaparece contra o fundo. O branco é o que a ilustração pressupõe.
 *
 * Antes de quadrar, corta-se a margem vazia à volta do bicho. Estas
 * ilustrações vêm quase todas com muito branco de lado, e sem este corte um
 * saurópode de 960 por 320 ficava num quadrado com o animal a ocupar um terço
 * da altura e o resto branco.
 *
 * A cor com que se preenche não é sempre branco: sai dos quatro cantos da
 * imagem, quando concordam entre si. Serve o tema TV Shows, que são cartões
 * de título — metade deles brancos sobre preto, e emoldurar 'THE WIRE' a
 * branco dava uma tarja branca à volta de um rectângulo preto.
 *
 * O resultado é sempre JPEG: depois de achatado não há transparência para
 * guardar, e o PNG de uma ilustração destas ocupa três a quatro vezes mais.
 * Quem já for JPEG e quadrado fica como está, para o comando poder correr
 * outra vez sem voltar a comprimir o que já lá está; com --refazer passa por
 * cima disso e trata tudo.
 */

const LADO_MAX  = 900;
const QUALIDADE = 88;

/** Quanto do quadrado fica de margem, de cada lado. */
const MARGEM = 0.04;

/**
 * Quão longe da cor de fundo uma cor tem de estar para contar como conteúdo.
 *
 * Baixo de mais e a sombra esbatida por baixo do animal conta como margem;
 * alto de mais e come as patas claras. 0.2 apanha as sombras destas
 * ilustrações e deixa o fundo de fora.
 */
const LIMIAR_CORTE = 0.2;

/** Diferença máxima entre cantos, por canal, para se aceitar a cor deles. */
const TOLERANCIA_CANTOS = 12;

/**
 * A cor de fundo da imagem: a dos cantos, se os quatro concordarem.
 *
 * Um cartão de título preto tem os quatro cantos pretos e é isso que se quer
 * à volta dele. Uma fotografia tem cantos diferentes uns dos outros, e aí
 * volta-se ao branco, que é o que uma ilustração recortada pressupõe.
 */
function corDeFundo($imagem): array
{
    $largura = imagesx($imagem);
    $altura  = imagesy($imagem);

    $cantos = [];
    foreach ([[0, 0], [$largura - 1, 0], [0, $altura - 1], [$largura - 1, $altura - 1]] as [$x, $y]) {
        $cor      = imagecolorat($imagem, $x, $y);
        $cantos[] = [($cor >> 16) & 0xFF, ($cor >> 8) & 0xFF, $cor & 0xFF];
    }

    foreach ([0, 1, 2] as $canal) {
        $valores = array_column($cantos, $canal);

        if (max($valores) - min($valores) > TOLERANCIA_CANTOS) {
            return [255, 255, 255];
        }
    }

    return [
        (int) round(array_sum(array_column($cantos, 0)) / 4),
        (int) round(array_sum(array_column($cantos, 1)) / 4),
        (int) round(array_sum(array_column($cantos, 2)) / 4),
    ];
}

$argumentos = argumentos($argv);
$seco       = temFlag($argv, 'dry-run');
$refazer    = temFlag($argv, 'refazer');

if ($argumentos === []) {
    erro('Falta o tema. Ex.: php tools/quadrar-imagens.php Dinosaurs');
    exit(2);
}

$totalFeitos  = 0;
$totalSaltos  = 0;
$totalFalhas  = 0;

foreach ($argumentos as $pedido) {
    // Aceita tanto o nome do tema ('Video Games') como o da pasta ('VideoGames').
    $pasta = is_dir(UPLOAD_DIR . '/' . $pedido) ? $pedido : pastaDoTemaPublico($pedido);
    $dir   = UPLOAD_DIR . '/' . $pasta;

    if (!is_dir($dir)) {
        erro('Não há pasta Imagens/' . $pasta . '.');
        $totalFalhas++;
        continue;
    }

    $existentes = lerNomes($pasta);
    $nomes      = $existentes['nomes'];

    linha($pasta . '  (' . count(imagensDaPasta($dir)) . ' imagens)');

    foreach (imagensDaPasta($dir) as $ficheiro) {
        $caminho = $dir . '/' . $ficheiro;
        $medidas = @getimagesize($caminho);

        if ($medidas === false) {
            erro('  FALHOU     ' . $ficheiro . '  (não é uma imagem legível)');
            $totalFalhas++;
            continue;
        }

        [$largura, $altura] = $medidas;
        $eJpeg = $medidas['mime'] === 'image/jpeg';

        if ($eJpeg && $largura === $altura && !$refazer) {
            $totalSaltos++;
            continue;
        }

        $destino = pathinfo($ficheiro, PATHINFO_FILENAME) . '.jpg';

        if ($seco) {
            linha('  quadraria  ' . $ficheiro . '  ' . $largura . 'x' . $altura . ' -> ' . $destino);
            $totalFeitos++;
            continue;
        }

        $origem = @imagecreatefromstring((string) file_get_contents($caminho));

        if ($origem === false) {
            erro('  FALHOU     ' . $ficheiro . '  (não consegui descodificar)');
            $totalFalhas++;
            continue;
        }

        $original = $largura . 'x' . $altura;

        // 1. Achatar sobre branco. O corte automático compara cores, e uma
        //    zona transparente por cima de preto compara como preto.
        $achatado = imagecreatetruecolor($largura, $altura);
        $branco   = imagecolorallocate($achatado, 255, 255, 255);
        imagefill($achatado, 0, 0, $branco);
        imagecopy($achatado, $origem, 0, 0, 0, 0, $largura, $altura);
        imagedestroy($origem);

        // 2. Cortar a margem à volta, na cor que a imagem tiver nos cantos.
        //    Devolve false quando não há nada para cortar — ou quando a imagem
        //    é toda da mesma cor, e aí fica como está.
        [$r, $g, $b] = corDeFundo($achatado);
        $fundo       = imagecolorallocate($achatado, $r, $g, $b);
        $cortado     = @imagecropauto($achatado, IMG_CROP_THRESHOLD, LIMIAR_CORTE, $fundo);

        if ($cortado !== false) {
            imagedestroy($achatado);
            $achatado = $cortado;
        }

        $largura = imagesx($achatado);
        $altura  = imagesy($achatado);

        // 3. Quadrado, com o bicho centrado e a margem de folga.
        $lado    = min(max($largura, $altura), LADO_MAX);
        $interior = (int) round($lado * (1 - 2 * MARGEM));
        $esc     = min($interior / $largura, $interior / $altura);
        $novaLargura = max(1, (int) round($largura * $esc));
        $novaAltura  = max(1, (int) round($altura * $esc));

        $quadro = imagecreatetruecolor($lado, $lado);
        imagefill($quadro, 0, 0, imagecolorallocate($quadro, $r, $g, $b));
        imagecopyresampled(
            $quadro,
            $achatado,
            (int) (($lado - $novaLargura) / 2),
            (int) (($lado - $novaAltura) / 2),
            0,
            0,
            $novaLargura,
            $novaAltura,
            $largura,
            $altura
        );
        imagedestroy($achatado);

        $ok = imagejpeg($quadro, $dir . '/' . $destino, QUALIDADE);
        imagedestroy($quadro);

        if (!$ok) {
            erro('  FALHOU     ' . $ficheiro . '  (escrita)');
            $totalFalhas++;
            continue;
        }

        @chmod($dir . '/' . $destino, 0644);

        // O nome a mostrar segue o ficheiro novo. Só depois se apaga o antigo,
        // para uma falha a meio não deixar a pasta sem a imagem.
        if ($destino !== $ficheiro) {
            if (isset($nomes[$ficheiro])) {
                $nomes[$destino] = $nomes[$ficheiro];
                unset($nomes[$ficheiro]);
            }
            @unlink($caminho);
        }

        linha('  ok         ' . $ficheiro . '  ' . $original . ' -> ' . $destino . '  ' . $lado . 'x' . $lado);
        $totalFeitos++;
    }

    if (!$seco) {
        escreverNomes($pasta, $existentes['tema'], $nomes);
    }

    linha();
}

linha('Quadradas: ' . $totalFeitos . '   Já estavam: ' . $totalSaltos . '   Falhadas: ' . $totalFalhas);

exit($totalFalhas > 0 ? 1 : 0);
