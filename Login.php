<?php
include "navbar.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['utilizador']) ? $_POST['utilizador'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $sql = "SELECT id FROM utilizador WHERE username = '$username' AND password = '$password'";
    $tabelaResultado = obterDadosBaseDados($sql);

    if ($tabelaResultado) {
        if (mysqli_num_rows($tabelaResultado) > 0) {
            $linhaTabela = mysqli_fetch_assoc($tabelaResultado);

            $_SESSION['userId'] = $linhaTabela['id'];
            $_SESSION['autenticado'] = true;

            echo "<div class='alert alert-success'><br>Login foi feito com sucesso</div>";
            header("Location: index.php");
        } else {
            echo "<div class='alert alert-danger'><br>Invalido username ou password</div>";
        }
    } else {
        echo "<div class='alert alert-danger'><br>Erro a aceder a base de dados</div>";
    }
}
?>

<div class="container mt-4">
<div class="text-center mb-4">
        <h2 class="display-4">Login</h2>
    </div>
    <form id="loginForm" method="post">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="utilizador" class="form-control" required><br>
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required><br>
        </div>
        <input type="submit" value="Login" class="btn btn-primary">
    </form>
</div>