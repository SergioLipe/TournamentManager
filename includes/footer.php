<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Rodapé comum. Cada página pode definir antes do include:
 *   $scriptsExtra — array de caminhos de scripts a carregar no fim
 */
$scriptsExtra = $scriptsExtra ?? [];
?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="JavaScript/ui.js"></script>
<script src="JavaScript/pwa.js"></script>
<?php foreach ($scriptsExtra as $script) { ?>
<script src="<?= e($script) ?>"></script>
<?php } ?>

</body>
</html>
