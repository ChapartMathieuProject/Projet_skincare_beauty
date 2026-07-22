<?php require __DIR__ . "/../../../public/includes/header.php"; ?>

<main class="container my-5">
  <h1><?= htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="fs-4 fw-bold">
    <?= number_format($product->getSellPrice(), 2, ',', ' ') ?> €
  </p>
  <a href="<?= url('/produits') ?>" class="btn btn-outline-secondary">
    Retour aux produits
  </a>
</main>

<?php require __DIR__ . "/../../../public/includes/footer.php"; ?>