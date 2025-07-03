<?php
 include "navbar.php";

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM competidor WHERE id = $id;";
    $resultadoGuardarBD = guardarDadosBaseDados($sql);

    // buscar o id do tema para mandar no link que redireciona
    $sql2 = "SELECT TemaId From competidor Where id=$id;";
    $resultado2 = guardarDadosBaseDados($sql2);
    $linhaTabela = mysqli_fetch_assoc($resultado2);
    $temaId = $linhaTabela['TemaId'];

    header("Location: criarTema.php?temaId=$temaId");
} 

 else {
    echo "<div class='alert alert-danger'><br>Erro a eliminar imagem</div>";
}

?>