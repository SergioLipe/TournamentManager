<?php include "navbar.php"; ?>

<?php if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) { ?>
  <div class="container mt-4">
    <div class="row">
      <!-- form para criar um novo tema -->
      <div class="col-md-6">
        <h3>Create a new Theme</h3>
        <form action="AdicionarCompetidor.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>Theme's Name:</label>
            <input type="text" name="nomePasta" class="form-control" required maxlength="25">
          </div>
          <div class="form-group">
            <label>Select the Images:</label>
            <input type="file" name="imagens[]" class="form-control-file" multiple>
          </div>
          <button type="submit" class="btn btn-primary" name="criar_tema">Upload Images</button>
        </form>
      </div>

      <!-- form para adicionar imagens a um tema existente-->
      <div class="col-md-6">
        <h3>Select a Existing Theme</h3>
        <form action="AdicionarCompetidor.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>Your Themes:</label>
            <select name="nomeTema" class="form-control">
              <?php
              $userId = $_SESSION['userId']; // vai buscar o id do utilizador logado
              $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = $userId";     // vai buscar os temas do utilizador
              $resultado_temas = obterDadosBaseDados($sql_temas);
              if (mysqli_num_rows($resultado_temas) > 0) {
                while ($linhaTabelaTemas = mysqli_fetch_assoc($resultado_temas)) {
                  echo '<option value="' . $linhaTabelaTemas['nome'] . '">' . $linhaTabelaTemas['nome'] . '</option>';
                }
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Select the Images:</label>
            <input type="file" name="imagens[]" class="form-control-file" multiple>
          </div>
          <button type="submit" class="btn btn-primary" name="tema_existente">Upload Images</button>
        </form>
      </div>
    </div>
    <br><br><br>

   <!-- dropdown para selecionar um tema para ver as imagens e apagar -->
    <form method ="post" class="d-flex align-items-center">
      <div class="dropdown">
        <button class="btn btn-lg btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
          data-bs-toggle="dropdown" aria-expanded="false">
          Choose a Theme
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <?php
       
         
          $sql_temas = "SELECT id,nome FROM tema where utilizadorId = $userId ";
          $resultado = obterDadosBaseDados($sql_temas);
          if (mysqli_num_rows($resultado) > 0) {
            while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
             
             echo '<li><button class="dropdown-item" type="submit" name="temaId"  value="' . $linhaTabela['id'] . '">' . $linhaTabela['nome'] . '</button></li>';
         


            }
          }

          ?>
        </ul>
      </div>
    </form><br>
<?php  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['temaId'])) {
  $temaId = $_POST['temaId'];  // id do tema que vem do formulário
  $sql = "SELECT id, nome, imagem FROM competidor WHERE TemaId = $temaId";
  $resultado = obterDadosBaseDados($sql);
  
  if (mysqli_num_rows($resultado) > 0) {
    echo "<div class='row'>";
    while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
      echo "<div class='col-md-3 mb-3'>";
      echo "<div class='card h-100'>";
      echo "<img src='" . $linhaTabela['imagem'] . "' class='card-img-top img-fluid' style='height: 150px; object-fit: cover;'>";
      echo "<div class='card-body'>";
      echo "<h5 class='card-title'>" . $linhaTabela['nome'] . "</h5>";
      echo "<a href='apagar.php?id=" . $linhaTabela['id'] . "' class='btn btn-danger'>Eliminate</a>";
      echo "</div></div></div>";
    }
    echo "</div>";
  }
}

?>


  </div>
<?php } else { ?> <!-- se nao tiver login-->

  <div class="container mt-4">
    <p class="font-weight-bold mt-4 display-6 ">Para começar a criar um tema, é preciso fazer o login.</p>
    <a href='Login.php' class='btn btn-primary'>Login</a>
    <a href='Registar.php' class='btn btn-primary'>Registar</a>
  </div>

<?php } ?>

</body>

</html>