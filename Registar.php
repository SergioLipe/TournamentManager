<?php
include "navbar.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['utilizador']) ? $_POST['utilizador'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $sql = "INSERT INTO utilizador (username, password) VALUES ('$username', '$password')";
    try {
        $resultadoGuardarBD = guardarDadosBaseDados($sql);

        if ($resultadoGuardarBD === true) {
            echo "<div class='alert alert-success' role='alert'><br>Conta criada com sucesso</div>";

        } else {
            echo "<div class='alert alert-danger' role='alert'><br>Erro ao criar a conta</div>";
        }
    } catch (Exception $e) {

        echo "<div class='alert alert-danger' role='alert'><br>Erro ao criar a conta</div>";

    }
}
?>

<div class="container mt-4">
<div class="text-center mb-4">
        <h2 class="display-4">Register</h2>
    </div>
    <form id="formulario" method="post" class="mb-3">
        <div class="form-group">
            <label for="utilizador">Username:</label>
            <input type="text" class="form-control" name="utilizador" minlength="3" maxlength="10" required>
        </div><br>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" name="password" minlength="3" required>
        </div>
        <br>
        <input type="submit" value="Register" class="btn btn-primary">
    </form>
</div>