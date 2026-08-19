# tools/ — temas públicos em lote

Ferramentas de linha de comandos para acrescentar temas públicos sem ter de
carregar imagem a imagem no site. Correm neste computador; a pasta `tools/`
está excluída do `deploy.sh` e nunca vai para o servidor.

Tudo o que está aqui é em inglês, como o resto do site. Os temas sobre
Portugal existem, mas têm nome inglês (`Portuguese Landmarks`,
`Portuguese Desserts`) e ficam ao lado dos outros.

## Os três comandos

O PHP do XAMPP não está no PATH, por isso o caminho vai completo:

```sh
PHP="C:/xampp/php/php.exe"

# 1. Descarregar as imagens de um tema (ou de todos, sem argumentos)
$PHP tools/buscar-imagens.php Animals
$PHP tools/buscar-imagens.php                    # todas as listas
$PHP tools/buscar-imagens.php Animals --dry-run  # ver sem descarregar

# 2. (opcional) Cortar a margem e pôr num quadrado de fundo branco
$PHP tools/quadrar-imagens.php Dinosaurs

# 3. Reescrever o SQL a partir do que está em Imagens/
$PHP tools/gerar-seed.php
```

O passo 2 só é preciso em temas de ilustrações — ver
"Ilustrações em vez de fotografias", mais abaixo.

Depois é correr o SQL na base de dados e publicar:

```sh
mysql -u ... nome_da_bd < database/seed-temas-publicos.sql   # ou pelo phpMyAdmin
git add Imagens tools database
./deploy.sh
```

## Acrescentar um tema novo

Criar um ficheiro em `tools/temas/`. **O nome do ficheiro é o nome do tema**
(máximo 25 caracteres, que é o que a coluna aceita):

```
tools/temas/Guitars.txt
```

Uma linha por competidor. À esquerda o nome a mostrar; à direita, depois de
uma barra, o artigo da Wikipédia — só é preciso quando os dois não coincidem:

```
# Guitars — comentários começam por cardinal

Fender Stratocaster
Gibson Les Paul
Flying V | Gibson Flying V
```

E depois:

```sh
$PHP tools/buscar-imagens.php Guitars
$PHP tools/gerar-seed.php
```

Se o nome do tema tiver acentos ou não der um nome de ficheiro jeitoso, põe-se
uma linha `#tema=` no topo da lista, que manda sobre o nome do ficheiro:

```
#tema=Rock Bands
```

## Escolher a imagem à mão

A imagem que vem por omissão é a principal do artigo, e nem sempre é a que se
quer. O caso claro é o dos dinossauros: a imagem principal é quase sempre o
esqueleto montado num museu, e vinte e quatro esqueletos castanhos não se
distinguem uns dos outros num jogo em que se escolhe pela imagem.

Nesses casos põe-se, depois da barra, um ficheiro do Wikimedia Commons em vez
de um artigo:

```
T. rex | File:202007 Tyrannosaurus rex.svg
Velociraptor | File:Fred Wierum Velociraptor.png
```

O nome tem de ser o do ficheiro no Commons, com a extensão certa (há muito
`.svg` onde se esperaria `.png`). Se estiver errado, a entrada sai como
SEM IMAGEM — não se procura o nome na Wikipédia, que devolveria outra coisa
qualquer. Para encontrar o nome exacto, a categoria
`Category:<Género> life restorations` costuma ser o melhor sítio.

## Ilustrações em vez de fotografias

Uma ilustração não se comporta como uma fotografia nos cartões do site, e é
por isso que existe o `quadrar-imagens.php`:

- O cartão do duelo é quadrado e usa `object-fit: cover`. Uma reconstituição
  de 960×320 fica reduzida ao tronco, sem cabeça nem cauda.
- Muitas ilustrações do Commons são PNG de fundo transparente, e no tema
  escuro do site um bicho escuro sobre transparente desaparece.

O comando corta a margem vazia à volta, achata sobre branco e grava um JPEG
quadrado — o que o `cover` já não tem como cortar:

```sh
$PHP tools/quadrar-imagens.php Dinosaurs
$PHP tools/quadrar-imagens.php Dinosaurs --dry-run   # ver sem mexer
$PHP tools/quadrar-imagens.php Dinosaurs --refazer   # tratar também os já quadrados
```

Corre-se **depois** do `buscar-imagens.php` e **antes** do `gerar-seed.php`,
porque muda a extensão dos ficheiros (`.png` -> `.jpg`) e acerta o CSV. Correr
outra vez sem `--refazer` não toca no que já está quadrado.

## Onde fica cada coisa

| Sítio | O que é |
| --- | --- |
| `tools/temas/<Tema>.txt` | a lista de nomes a procurar — é isto que se edita |
| `Imagens/<Tema>/` | as imagens descarregadas, com nomes só ASCII |
| `tools/nomes/<Tema>.csv` | `ficheiro,nome a mostrar` — guarda os acentos |
| `database/seed-temas-publicos.sql` | **gerado**, não editar à mão |

O CSV existe porque os nomes na base de dados têm acentos e maiúsculas
(`Bacalhau à Lagareiro`) mas os ficheiros em disco não podem ter
(`Bacalhau-a-Lagareiro.jpg`): o FTP deste alojamento estraga nomes com espaços
e acentos. Para corrigir um nome que ficou mal, edita-se o CSV e volta-se a
correr o `gerar-seed.php` — não é preciso descarregar nada outra vez.

## Coisas que valem a pena saber

**Não é preciso passar pelas ferramentas.** O `gerar-seed.php` apanha
*qualquer* ficheiro de imagem que esteja numa subpasta de `Imagens/`. Uma
imagem largada lá à mão entra no tema na mesma; o nome sai do nome do
ficheiro, e afina-se depois no CSV.

**Correr outra vez é seguro.** O `buscar-imagens.php` salta o que já está em
disco — só busca o que falta, o que o torna a maneira normal de recuperar de
falhas. Para forçar tudo de novo, `--refazer`.

**A Wikimedia limita os pedidos.** Os títulos vão em lotes de 40 num só
pedido, e há uma pausa entre downloads com espera crescente quando a resposta
é 429. Mesmo assim, em temas grandes falha uma ou outra imagem — é só correr
o comando outra vez.

**Língua.** Por omissão procura na Wikipédia inglesa. `--lang=pt` procura na
portuguesa, o que só é útil para assuntos sem artigo em inglês; o nome a
mostrar continua a ser o que estiver na lista.

**Direitos de autor.** A imagem principal de um artigo costuma ser do Wikimedia
Commons, com licença livre. Nos filmes e videojogos é quase sempre o cartaz ou
a capa, que a Wikipédia usa ao abrigo de *fair use* — não é livre. Os temas
`Movies` e `Video Games` já estavam nessa situação antes destas ferramentas;
vale a pena ter isso em conta antes de acrescentar mais do género.

## As três pastas com nome antigo

`Imagens/Filmes/`, `Imagens/Bandas-de-Rock/` e `Imagens/VideoGames/` continuam
com o nome português; o que mudou foi o nome do **tema**, através da linha
`#tema=` nos CSVs respectivos (`Movies`, `Rock Bands`, `Video Games`). O nome da
pasta não aparece em lado nenhum no site, e mudá-lo obrigava a reenviar as 68
imagens por FTP e a apagar as antigas do servidor à mão — não compensa.

O que **não** se deve fazer é criar depois uma lista `tools/temas/Movies.txt`:
isso criava uma pasta `Imagens/Movies/` nova a par da `Filmes/`, e ficavam dois
temas com o mesmo nome (o `gerar-seed.php` pára com um erro se isso acontecer).
Para acrescentar filmes, largam-se os ficheiros em `Imagens/Filmes/`.

## Renomear um tema que já está na base de dados

O tema é identificado pelo par `(utilizadorId, nome)`. Mudar o nome no CSV e
correr o seed **cria um tema novo** em vez de renomear o antigo, e as
estatísticas ficam para trás. Primeiro renomeia-se na base de dados
(`database/renomear-temas.sql` é o exemplo), e só depois se corre o seed.
