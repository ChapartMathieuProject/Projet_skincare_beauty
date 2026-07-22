<?php require __DIR__ . "/../../../public/includes/header.php"; ?>

<main class="container my-5 text-center">
    <div class="py-5">
        <p class="display-1 fw-bold text-muted mb-3">404</p>
        <h1 class="h3 mb-3">Cette page n'existe pas</h1>
        <p class="text-muted mb-4">
            La page que vous cherchez a peut-être été déplacée ou n'a jamais existé.
        </p>
        <a href="<?= url('/') ?>" class="btn-rose">
            Retour à l'accueil
        </a>
    </div>
</main>

<?php require __DIR__ . "/../../../public/includes/footer.php"; ?>