  
  <?php
   
    include "navbar.php";?>
    <a style="margin-left:10px;" href='CriarTema.php' class='btn btn-primary'>Voltar</a> <br><br>
<?php
    if (isset($_POST["criar_tema"])) {

        $nomeTema = $_POST['nomePasta'];
        $utilizadorId =  $_SESSION['userId']; // vai buscar o id do utilizador logado
        // diretorio das imagens
        $base = "Imagens/";
        $diretorio = $base . $nomeTema . "/";
        // cria a pasta do tema se nao existir
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
            // cria um tema na base de dados
            $sql_tema = "INSERT INTO tema (nome,utilizadorId) VALUES ('$nomeTema','$utilizadorId')";
            $resultado_tema = guardarDadosBaseDados($sql_tema);
        }
        uploadImagens($nomeTema, $diretorio);


    }
    if (isset($_POST["tema_existente"])) {

        $nomeTema = $_POST['nomeTema'];
        // diretorio das imagens
        $base = "Imagens/";
        $diretorio = $base . $nomeTema . "/";

        uploadImagens($nomeTema, $diretorio);

    }

    function uploadImagens($nomeTema, $diretorio)
    {
        // Encontrar o id do tema para depois usar na chave estrangeira do competidor
        $sql_find_tema = "SELECT id FROM tema WHERE nome = '$nomeTema' ";
        $resultado_idTema = obterDadosBaseDados($sql_find_tema);
        $linhaTabela = mysqli_fetch_assoc($resultado_idTema);
        $idTema = $linhaTabela['id'];      // id do tema
    

        // conta o numero de ficheiros
        $totalFiles = count($_FILES["imagens"]["name"]);
        for ($i = 0; $i < $totalFiles; $i++) {    // percorrer cada imagem
    
            $nome_imagem = basename($_FILES["imagens"]["name"][$i]);  // nome da imagem
            $imagem = $diretorio . $nome_imagem;  // diretorio da imagem
            $imageFileType = strtolower(pathinfo($imagem, PATHINFO_EXTENSION));   // verifica o tipo de imagem
            $confirmacao = 1;   // variavel para confirmar que o upload foi sucesso
    
            //nome da imagem sem extensao
            $fileInfo = pathinfo($nome_imagem);
            $nome_imagem_apenas = $fileInfo['filename'];
            
            if (strpos($nome_imagem_apenas, "'") !== false) {
                      echo "<div class='alert alert-danger'><br>O nome do ficheiro não pode conter ' </div>";
                      $confirmacao= 0;
             }

            // verifica se imagem existe
            if (file_exists($imagem)) {
                echo "<div class='alert alert-danger'><br> A imagem"  . htmlspecialchars(basename($_FILES["imagens"]["name"][$i])) . " já existe</div>";
                $confirmacao = 0;
            }
            // limita o tipo de imagem para imagens
            if (
                $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" && $imageFileType != "jfif" && $imageFileType != "webp" && $imageFileType != "avif"
            ) {
                echo "<div class='alert alert-danger'><br>Apenas JPG, JPEG, PNG, JFIF, WEBP, AVIF & GIF permitido.</div>";
               
                $confirmacao = 0;
            }
            // verifica se  houve um problema
            if ($confirmacao == 0) {
              
                // se nao ouver erro faz upload
            } else {
                if (move_uploaded_file($_FILES["imagens"]["tmp_name"][$i], $imagem)) {
                    echo "<br><div class='alert alert-success'><br>O ficheiro " . htmlspecialchars(basename($_FILES["imagens"]["name"][$i])) . " foi uploaded. </div>";
                    
                    //inserir a imagem na base de dados do competidor
                    $sql_competidor = "INSERT INTO competidor(imagem,nome,TemaId) VALUES('$imagem','$nome_imagem_apenas','$idTema');";
                     guardarDadosBaseDados($sql_competidor);

                } else {
                    echo "<div class='alert alert-danger'><br>Houve algum Erro</div>";
                   
                }
            }

        }
    }
   
    fecharLigacaoBaseDados();
   

    ?>
 
</body>

</html>