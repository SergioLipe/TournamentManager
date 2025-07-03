
    <?php
    include "funcoesBD.php";
     header("Access-Control-Allow-Origin: *");
  
    $imagens = array();    // array que vai conter as imagens
    $tema = $_POST['tema'];  // buscar o tema pela funcao carregarImagens
  
    // Encontrar o id do tema 
    $sql_find_tema = "SELECT id FROM tema WHERE nome = '$tema' ";
    $resultado_idTema = obterDadosBaseDados($sql_find_tema);
    $linhaTabela = mysqli_fetch_assoc($resultado_idTema);
    $idTema = $linhaTabela['id'];      // id do tema
    
    // selecionar as imagens que tem o id escolhido e aleatoriamente seleciona-las e pegar no diretorio e no nome
    $sql_imagens = "SELECT imagem, nome FROM competidor WHERE TemaId = $idTema ORDER BY RAND()";
    $resultado = obterDadosBaseDados($sql_imagens);

    // colocar o diretorio e o nome no array "imagens"
    if (mysqli_num_rows($resultado) > 0) {
        while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
            array_push($imagens, ["imagem" => $linhaTabela["imagem"], "nome" => $linhaTabela["nome"]]);
        }
    }


   // output com o json
    echo json_encode($imagens);

    ?>
