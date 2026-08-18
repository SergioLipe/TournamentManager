<?php
declare(strict_types=1);

/**
 * Gera os ícones da aplicação Android/PWA para icons/.
 *
 * Uso:  php tools/gerar-icones.php
 *
 * O desenho é a própria bracket: quatro entradas que se juntam duas a duas
 * até restar uma, com o vencedor a dourado. Está definido numa grelha de
 * 512x512 e é redimensionado a partir daí, por isso acrescentar tamanhos
 * novos é só juntar uma linha ao $ALVOS.
 *
 * Tudo é desenhado a 4x e reduzido no fim — o GD não suavia o que desenha,
 * e a circunferência do vencedor sem isto fica visivelmente serrilhada.
 *
 * Os ícones NÃO ficam em Imagens/: o tools/gerar-seed.php trata cada subpasta
 * de Imagens/ como um tema público, e uma pasta de ícones viraria um torneio
 * de quatro linhas azuis.
 */

const SUPER = 4;          // factor de supersampling
const GRELHA = 512.0;     // espaço de coordenadas do desenho

/**
 * Ficheiros a gerar: tamanho final, escala do desenho e se leva fundo.
 *
 * O ícone "maskable" é recortado pelo Android com a forma que o fabricante
 * escolher (círculo, quadrado redondo, gota). Só a zona segura — o círculo
 * central com 80% do lado — é garantidamente visível, por isso o desenho
 * encolhe para lá caber inteiro.
 */
$ALVOS = [
    ['icons/icon-192.png',           192, 0.82],
    ['icons/icon-512.png',           512, 0.82],
    ['icons/icon-maskable-512.png',  512, 0.55],
];

/** Uma barra horizontal, em coordenadas da grelha. */
function barraH($img, float $x1, float $x2, float $y, float $espessura, int $cor, float $k, float $dx, float $dy): void
{
    $meia = $espessura / 2;
    imagefilledrectangle(
        $img,
        (int) round($dx + $x1 * $k),
        (int) round($dy + ($y - $meia) * $k),
        (int) round($dx + $x2 * $k),
        (int) round($dy + ($y + $meia) * $k),
        $cor
    );
}

/** Uma barra vertical, em coordenadas da grelha. */
function barraV($img, float $x, float $y1, float $y2, float $espessura, int $cor, float $k, float $dx, float $dy): void
{
    $meia = $espessura / 2;
    imagefilledrectangle(
        $img,
        (int) round($dx + ($x - $meia) * $k),
        (int) round($dy + $y1 * $k),
        (int) round($dx + ($x + $meia) * $k),
        (int) round($dy + $y2 * $k),
        $cor
    );
}

/**
 * Desenha a bracket centrada em $img.
 *
 * $escala é a fracção do lado que o desenho ocupa; o resto é margem.
 */
function desenharBracket($img, int $lado, float $escala, int $branco, int $ouro): void
{
    $t = 20.0;    // espessura das linhas
    $r = 38.0;    // raio do vencedor

    // Caixa que o desenho ocupa de facto: da ponta esquerda das entradas ao
    // bordo direito do vencedor. Centra-se esta caixa, e não a grelha inteira,
    // senão o conjunto fica encostado a um canto.
    $cx = (96.0 + (384.0 + $r)) / 2;
    $cy = 256.0;

    // Empurrão para a direita. O lado esquerdo é um bloco de linhas e o
    // direito é só um ponto, por isso o centro geométrico não é o centro que
    // o olho vê — sem isto o desenho parece encostado à esquerda.
    $optico = 26.0;

    $k  = ($lado * $escala) / GRELHA;
    $dx = $lado / 2 - ($cx - $optico) * $k;
    $dy = $lado / 2 - $cy * $k;

    // As barras sobrepõem-se meia espessura nos encontros. A tocarem-se
    // apenas, a redução final deixa uma costura visível em cada canto.
    $m = $t / 2;

    // Quatro entradas, que se juntam duas a duas.
    foreach ([128.0, 208.0, 304.0, 384.0] as $y) {
        barraH($img, 96, 176 + $m, $y, $t, $branco, $k, $dx, $dy);
    }
    barraV($img, 176, 128 - $m, 208 + $m, $t, $branco, $k, $dx, $dy);
    barraV($img, 176, 304 - $m, 384 + $m, $t, $branco, $k, $dx, $dy);

    // As duas meias-finais.
    barraH($img, 176 - $m, 264 + $m, 168, $t, $branco, $k, $dx, $dy);
    barraH($img, 176 - $m, 264 + $m, 344, $t, $branco, $k, $dx, $dy);

    // A final.
    barraV($img, 264, 168 - $m, 344 + $m, $t, $branco, $k, $dx, $dy);
    barraH($img, 264 - $m, 384, 256, $t, $branco, $k, $dx, $dy);

    // O vencedor.
    imagefilledellipse(
        $img,
        (int) round($dx + 384 * $k),
        (int) round($dy + 256 * $k),
        (int) round(2 * $r * $k),
        (int) round(2 * $r * $k),
        $ouro
    );
}

if (!function_exists('imagecreatetruecolor')) {
    fwrite(STDERR, "Falta a extensão gd.\n");
    exit(1);
}

$raiz = dirname(__DIR__);

foreach ($ALVOS as [$destino, $lado, $escala]) {
    $grande = $lado * SUPER;

    $tela = imagecreatetruecolor($grande, $grande);
    $azul   = imagecolorallocate($tela, 0x2f, 0x6f, 0xed);   // --acento
    $branco = imagecolorallocate($tela, 0xff, 0xff, 0xff);
    $ouro   = imagecolorallocate($tela, 0xe0, 0xa8, 0x00);   // --ouro

    imagefilledrectangle($tela, 0, 0, $grande, $grande, $azul);
    desenharBracket($tela, $grande, $escala, $branco, $ouro);

    $final = imagecreatetruecolor($lado, $lado);
    imagecopyresampled($final, $tela, 0, 0, 0, 0, $lado, $lado, $grande, $grande);

    $caminho = $raiz . '/' . $destino;
    if (!imagepng($final, $caminho, 9)) {
        fwrite(STDERR, "Não consegui escrever $destino\n");
        exit(1);
    }

    imagedestroy($tela);
    imagedestroy($final);

    printf("  %-32s %dx%d  %s\n", $destino, $lado, $lado, number_format((float) filesize($caminho)) . ' bytes');
}

echo "\nFeito. Não te esqueças do git add icons/.\n";
