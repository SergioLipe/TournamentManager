<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneio</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/navbars/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <link href="CSS/style2.css" rel="stylesheet">
</head>

<body>

    <?php
    session_start();
    ?>
    <!--nav bar-->
    <nav class="navbar navbar-expand-sm navbar-light bg-light" aria-label="Third navbar example"
        style="margin-bottom: 40px;">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="navbarsExample03">
                <form class="d-flex align-items-center">
                    <div class="dropdown">    <!--Os temas do admin-->
                        <button class="btn btn-lg btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                           Choose a Theme
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <?php
                            include "funcoesBD.php";
                            $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = 2" ;
                            $resultado = obterDadosBaseDados($sql_temas);
                            if (mysqli_num_rows($resultado) > 0) {
                                while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                                    echo '<li><button class="dropdown-item escolheTema"  value="' . $linhaTabela['nome'] . '">' . $linhaTabela['nome'] . '</button></li>';
                                }
                            }

                            ?>
                        </ul>
                    </div>
                 
                     <!--Os temas do utilizador-->
                  <?php   if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {?>
                     <div class="dropdown" style="margin-left: 10px">
                        <button class="btn btn-lg btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButtonU" data-bs-toggle="dropdown" aria-expanded="false">
                            Choose your Own Theme
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <?php
                            
                            $userId = $_SESSION['userId']; // vai buscar o id do utilizador logado
                            $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = $userId";     // vai buscar os temas do utilizador
                            $resultado = obterDadosBaseDados($sql_temas);
                            if (mysqli_num_rows($resultado) > 0) {
                                while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                                    echo '<li><button class="dropdown-item escolheTemaU"  value="' . $linhaTabela['nome'] . '">' . $linhaTabela['nome'] . '</button></li>';
                                }
                            }

                            ?>
                        </ul>
                    </div>
                    <?php } ?>

                    
                    <a class="btn btn-outline-success btn-lg ms-5" href="CriarTema.php" type="submit">Create Your Theme</a>
                    <div class="ms-5">Nº of Images:
                        <input type="number" class="form-control " placeholder="Número de imagens" id="numImagens"
                            value="8" min="1" max="16">
                    </div>
                </form>
                <div class="d-flex justify-content-center ms-sm-auto">
                    <a href="index.php" class="btn btn-lg btn-outline-secondary">Tournament</a>
                </div>
                <div class="d-flex justify-content-start ms-sm-auto">
                    <a href="Estatisticas.php" class="btn btn-lg btn-outline-secondary">Statistics</a>
                </div>
                <div class="ms-auto">
                    <a href="sobre.php" class="btn btn-lg btn-outline-secondary ms-2">About</a>
                  <?php
                
                    if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] == true) {
                        echo '<a href="Logout.php" class="btn btn-lg btn-outline-secondary ms-2">Logout</a>';
                    }
                    else{
                        echo '<a href="Login.php" class="btn btn-lg btn-outline-secondary ms-2">Login</a>';
                        echo '<a href="Registar.php" class="btn btn-lg btn-outline-secondary ms-2">Register</a>';
                    }
                    ?>

                </div>

            </div>
        </div>
    </nav>