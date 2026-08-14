<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$tituloPagina = 'About';
require __DIR__ . '/includes/header.php';
?>

<div class="container form-largo">
    <h1 class="h2 mb-4">About</h1>

    <div class="card sobre">
        <div class="card-body">
            <ul class="sobre__lista">
                <li>Run tournaments using the built-in themes, or create your own.</li>
                <li>Handy when you cannot make up your mind, or for a friendly competition.</li>
                <li>Pick anywhere from <?= MIN_COMPETITORS ?> to <?= MAX_COMPETITORS ?> competitors per tournament.</li>
                <li>Odd numbers work too &mdash; the extra competitors get a bye into the next round.</li>
                <li>You can also play with plain names instead of images.</li>
                <li>Every battle is recorded, so the Statistics page shows which entries actually win.</li>
            </ul>

            <hr>

            <p class="sobre__autor">
                <strong>Sérgio Filipe Azevedo Gonçalves</strong><br>
                <a href="mailto:lipewtf@hotmail.com">lipewtf@hotmail.com</a>
            </p>

            <a href="index.php" class="btn btn-primary">Back to the tournament</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
