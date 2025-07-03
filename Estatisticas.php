<?php include "navbar.php" ?>

 <!-- dropdown para selecionar um tema predifinido para ver as imagens e suas estatisticas -->
 <div class="row">
  <div class="col-md-6">
    <form method="post" class="d-flex align-items-center">
      <div class="dropdown">
        <button class="btn btn-lg btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1"
          data-bs-toggle="dropdown" aria-expanded="false">
          Choose a Theme
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
          <?php
            $sql_temas = "SELECT id, nome FROM tema WHERE utilizadorId = '2'";
            $resultado = obterDadosBaseDados($sql_temas);
            if (mysqli_num_rows($resultado) > 0) {
              while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                echo '<li><button class="dropdown-item" type="submit" name="temaId" value="' . $linhaTabela['id'] . '">' . $linhaTabela['nome'] . '</button></li>';
              }
            }
          ?>
        </ul>
      </div>
    </form>
  </div>
 <!-- dropdown para selecionar um tema do utilizador-->
 <?php if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {?>
  <div class="col-md-6">
    <form method="post" class="d-flex align-items-center">
      <div class="dropdown">
        <button class="btn btn-lg btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton2"
          data-bs-toggle="dropdown" aria-expanded="false">
          Choose One of Your Themes
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
          <?php
          
            $sql_temas = "SELECT id, nome FROM tema WHERE utilizadorId = $userId";
            $resultado = obterDadosBaseDados($sql_temas);
            if ($resultado && mysqli_num_rows($resultado) > 0) {
              while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                echo '<li><button class="dropdown-item" type="submit" name="temaId" value="' . $linhaTabela['id'] . '">' . $linhaTabela['nome'] . '</button></li>';
              }
            }
          }
          ?>
        </ul>
      </div>
    </form>
  </div>
</div>
<br><br>
<?php  

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['temaId'])) {
  $temaId = $_POST['temaId'];  
  
 
  $sql = "SELECT id, nome, imagem, nBatalhasVencidas, nBatalhasPerdidas, nTorneiosVencidos FROM competidor WHERE TemaId = $temaId";
  $resultado = obterDadosBaseDados($sql);
  
  
  if (mysqli_num_rows($resultado) > 0) {
    echo "<div class='row'>";
    while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
      echo "<div class='col-md-3 mb-3'>";
      echo "<div class='card h-100'>";
      echo "<img src='" . $linhaTabela['imagem'] . "' class='card-img-top img-fluid' style='height: 150px; object-fit: cover;'>";
      echo "<div class='card-body'>";
      echo "<h5 class='card-title'>" . $linhaTabela['nome'] . "</h5>";
      echo "<p>Batalhas Vencidas: " . $linhaTabela['nBatalhasVencidas'] . "</p>";
      echo "<p>Batalhas Perdidas: " . $linhaTabela['nBatalhasPerdidas'] . "</p>";
      echo "<p>Torneios Vencidos: " . $linhaTabela['nTorneiosVencidos'] . "</p>";
      echo "</div></div></div>";
    }
    echo "</div>";
  }
}
