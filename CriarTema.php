<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/temas.php';

$tituloPagina = 'Create theme';

if (!autenticado()) {
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="container form-estreito">
        <h1 class="h2 mb-3">Create a theme</h1>
        <p class="lead">You need an account to build your own themes.</p>
        <a href="Login.php" class="btn btn-primary">Log in</a>
        <a href="Registar.php" class="btn btn-outline-primary">Register</a>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    return;
}

$utilizador    = (int) utilizadorId();
$listaPessoais = temasDoUtilizador($utilizador);

// Tema actualmente aberto para gestão.
$temaId = inteiro($_GET, 'temaId');
$tema   = $temaId > 0 ? obterTema($temaId) : null;
if (!podeEditarTema($tema)) {
    $tema = null;
}

$competidores = $tema !== null ? competidoresDoTema((int) $tema['id']) : [];

require __DIR__ . '/includes/header.php';
?>

<div class="container form-largo">
    <h1 class="h2 mb-4">Your themes</h1>

    <div class="row g-4">
        <!-- Criar um tema novo -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 card-title">New theme</h2>
                    <form action="AdicionarCompetidor.php" method="post" enctype="multipart/form-data">
                        <?= campoCsrf() ?>
                        <div class="mb-3">
                            <label class="form-label" for="nomeTema">Theme name</label>
                            <input type="text" id="nomeTema" name="nomeTema" class="form-control"
                                   maxlength="25" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="imagensNovas">Images</label>
                            <input type="file" id="imagensNovas" name="imagens[]" class="form-control"
                                   accept="image/*" multiple required>
                            <div class="form-text">
                                JPEG, PNG, GIF, WEBP or AVIF &mdash; up to
                                <?= (int) (MAX_UPLOAD_BYTES / 1024 / 1024) ?> MB each,
                                <?= MAX_UPLOADS_PER_REQUEST ?> at a time.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" name="criar_tema" value="1">
                            Create theme
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Adicionar a um tema existente -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 card-title">Add to an existing theme</h2>

                    <?php if ($listaPessoais === []) { ?>
                        <p class="text-muted mb-0">Create your first theme to start adding images to it.</p>
                    <?php } else { ?>
                        <form action="AdicionarCompetidor.php" method="post" enctype="multipart/form-data">
                            <?= campoCsrf() ?>
                            <div class="mb-3">
                                <label class="form-label" for="temaExistente">Theme</label>
                                <select id="temaExistente" name="temaId" class="form-select" required>
                                    <?php foreach ($listaPessoais as $opcao) { ?>
                                        <option value="<?= (int) $opcao['id'] ?>"
                                            <?= $tema !== null && (int) $opcao['id'] === (int) $tema['id'] ? 'selected' : '' ?>>
                                            <?= e($opcao['nome']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="imagensExtra">Images</label>
                                <input type="file" id="imagensExtra" name="imagens[]" class="form-control"
                                       accept="image/*" multiple required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="tema_existente" value="1">
                                Upload images
                            </button>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($listaPessoais !== []) { ?>
        <hr class="my-5">

        <h2 class="h4 mb-3">Manage a theme</h2>

        <div class="seletor-temas">
            <?php foreach ($listaPessoais as $opcao) {
                $activo = $tema !== null && (int) $opcao['id'] === (int) $tema['id'];
                ?>
                <a href="CriarTema.php?temaId=<?= (int) $opcao['id'] ?>"
                   class="btn <?= $activo ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                    <?= e($opcao['nome']) ?>
                </a>
            <?php } ?>
        </div>

        <?php if ($tema !== null) { ?>
            <div class="tema-cabecalho">
                <h3 class="h5 mb-0">
                    <?= e($tema['nome']) ?>
                    <span class="text-muted">&middot; <?= count($competidores) ?> competitor<?= count($competidores) === 1 ? '' : 's' ?></span>
                </h3>

                <!-- A mensagem vai num data-attribute em vez de um onsubmit: o
                     nome do tema pode conter plicas, que dentro de uma string
                     JavaScript inline fechariam a string. -->
                <form action="apagar.php" method="post"
                      data-confirmar="Delete the theme &quot;<?= e($tema['nome']) ?>&quot; and all of its images? This cannot be undone.">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="temaId" value="<?= (int) $tema['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete theme</button>
                </form>
            </div>

            <?php if ($competidores === []) { ?>
                <p class="text-muted">No images in this theme yet.</p>
            <?php } else { ?>
                <div class="grelha-cartoes">
                    <?php foreach ($competidores as $competidor) { ?>
                        <article class="cartao">
                            <img class="cartao__img" loading="lazy"
                                 src="<?= urlImagem($competidor['imagem']) ?>"
                                 alt="<?= e($competidor['nome']) ?>">
                            <div class="cartao__corpo">
                                <h4 class="cartao__titulo"><?= e($competidor['nome']) ?></h4>
                                <form action="apagar.php" method="post"
                                      data-confirmar="Delete this competitor?">
                                    <?= campoCsrf() ?>
                                    <input type="hidden" name="competidorId" value="<?= (int) $competidor['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-muted">Pick one of your themes to see and manage its images.</p>
        <?php } ?>
    <?php } ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
