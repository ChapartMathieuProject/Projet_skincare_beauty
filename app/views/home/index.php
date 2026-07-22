<?php /** @var Product[] $newest */ ?>
<?php require __DIR__ . '/../../../public/includes/header.php'; ?>

<main class="container my-5">
    <h1 class="mb-4">Nos nouveautés</h1>

    <div class="row g-4">
        <?php foreach ($newest as $product): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 card-title">
                            <?= htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        <p class="fw-bold mb-2">
                            <?= number_format($product->getSellPrice(), 2, ',', ' ') ?> €
                        </p>
                        <a href="<?= url('/produit/' . $product->getSlug()) ?>" class="btn btn-outline-secondary btn-sm">
                            Voir le produit
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/../../../public/includes/footer.php'; ?>