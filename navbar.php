<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="CSS/style2.css" rel="stylesheet">
    
    <style>
        /* CSS DA NAVBAR */
        .navbar {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
            min-height: 60px;
            transition: margin-top 0.4s ease-in-out;
            z-index: 1000;
            position: relative;
            /* Sombra na barra para separar do conteúdo */
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
        }

        .navbar-hidden {
            margin-top: -100px !important; /* Esconde a barra puxando para cima */
        }

        /* --- BOTÃO PREMIUM --- */
        #toggleNavBtn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 2000; /* Fica SEMPRE acima de tudo */
            
            /* Tamanho e Forma */
            width: 45px;
            height: 45px;
            border-radius: 50%; /* Redondo */
            border: none;
            
            /* Estilo Visual (Gradiente Escuro Moderno) */
            background: linear-gradient(145deg, #212529, #495057);
            color: white;
            
            /* Sombra para dar efeito 3D */
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            
            /* Alinhamento da Seta */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Animação "elástica" */
        }

        /* Efeito ao passar o rato */
        #toggleNavBtn:hover {
            transform: scale(1.1) rotate(0deg);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
            background: linear-gradient(145deg, #000000, #343a40);
        }

        /* Efeito ao clicar */
        #toggleNavBtn:active {
            transform: scale(0.95);
        }

        /* Ajustes internos */
        .nav-gap { gap: 10px; }
        #numImagens { height: 38px; width: 70px; }
        .img-label { font-weight: bold; font-size: 14px; white-space: nowrap; }

        /* --- AJUSTES MOBILE --- */
        @media (max-width: 992px) {
            .navbar-container { justify-content: center !important; gap: 15px; }
            .section-block { justify-content: center !important; margin-bottom: 5px; }
            
            /* No telemóvel, o botão fica um pouco mais pequeno */
            #toggleNavBtn { 
                width: 40px; 
                height: 40px; 
                top: 10px; 
                left: 10px; 
                font-size: 18px;
            }
            
            /* Garante que o conteúdo da navbar não fica debaixo do botão */
            .navbar-container {
                padding-top: 10px; 
            }
        }
    </style>
</head>

<body>

    <?php
    session_start();
    ?>
    
    <button id="toggleNavBtn" onclick="toggleNavbar()" title="Mostrar/Esconder Menu">
        &#8593; </button>

    <nav id="mainNav" class="navbar navbar-light bg-light" style="margin-bottom: 0;">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap navbar-container" style="padding-left: 70px;">
            
            <div class="d-flex align-items-center flex-wrap nav-gap section-block">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                       Choose Theme
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php
                        include "funcoesBD.php";
                        if(function_exists('obterDadosBaseDados')){
                            $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = 2" ;
                            $resultado = obterDadosBaseDados($sql_temas);
                            if ($resultado && mysqli_num_rows($resultado) > 0) {
                                while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                                    echo '<li><button class="dropdown-item escolheTema" type="button" value="' . $linhaTabela['nome'] . '">' . $linhaTabela['nome'] . '</button></li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>

                <?php if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) { ?>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButtonU" data-bs-toggle="dropdown" aria-expanded="false">
                        Your Themes
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonU">
                        <?php
                        $userId = $_SESSION['userId']; 
                        $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = $userId"; 
                        $resultado = obterDadosBaseDados($sql_temas);
                        if ($resultado && mysqli_num_rows($resultado) > 0) {
                            while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                                echo '<li><button class="dropdown-item escolheTemaU" type="button" value="' . $linhaTabela['nome'] . '">' . $linhaTabela['nome'] . '</button></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php } ?>

                <a class="btn btn-outline-success" href="CriarTema.php">Create</a>
                
                <div class="d-flex align-items-center">
                    <span class="img-label me-2">Images:</span>
                    <input type="number" class="form-control" placeholder="8" id="numImagens" value="8" min="1" max="16">
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center section-block">
                <a href="index.php" class="btn btn-outline-dark px-4 fw-bold">TOURNAMENT</a>
            </div>

            <div class="d-flex align-items-center flex-wrap nav-gap justify-content-end section-block">
                <a href="Estatisticas.php" class="btn btn-outline-secondary">Statistics</a>
                <a href="sobre.php" class="btn btn-outline-secondary">About</a>
                <?php
                if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] == true) {
                    echo '<a href="Logout.php" class="btn btn-outline-secondary">Logout</a>';
                } else {
                    echo '<a href="Login.php" class="btn btn-outline-secondary">Login</a>';
                    echo '<a href="Registar.php" class="btn btn-outline-secondary">Register</a>';
                }
                ?>
            </div>
        </div>
    </nav>

    <script>
        function toggleNavbar() {
            var nav = document.getElementById('mainNav');
            var btn = document.getElementById('toggleNavBtn');
            
            nav.classList.toggle('navbar-hidden');
            
            if (nav.classList.contains('navbar-hidden')) {
                btn.innerHTML = "&#8595;"; // Seta Baixo
                // No mobile, adicionamos um pequeno estilo para o botão não tapar o torneio
                btn.style.opacity = "0.7"; 
            } else {
                btn.innerHTML = "&#8593;"; // Seta Cima
                btn.style.opacity = "1";
            }
        }
    </script>