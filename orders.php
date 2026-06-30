<?php
// Protection de la page : accès réservé aux utilisateurs connectés
//session_start();

//if (!isset($_SESSION['user_id'])) {
    //header('Location: login.php');
    //exit;
//}

// TODO (CB) : remplacer par une requête BDD récupérant les commandes de l'utilisateur connecté
$orders = [
    [
        'id'     => 'SCB-2024-001',
        'date'   => '12 juin 2025',
        'status' => 'Livré',
        'total'  => '68,00 €',
        'items'  => 3,
    ],
    [
        'id'     => 'SCB-2024-002',
        'date'   => '28 mai 2025',
        'status' => 'En transit',
        'total'  => '42,50 €',
        'items'  => 2,
    ],
    [
        'id'     => 'SCB-2024-003',
        'date'   => '03 mai 2025',
        'status' => 'Livré',
        'total'  => '115,00 €',
        'items'  => 5,
    ],
];

require_once 'public/includes/header.php';
?>

<main class="profile-main">
  <div class="container">

    <nav aria-label="Fil d'Ariane" class="profile-breadcrumb">
      <a href="profile.php">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Mon profil
      </a>
    </nav>

    <h1 class="profile-page-title">
      <i class="fa-solid fa-box" aria-hidden="true"></i>
      Vos Commandes
    </h1>

    <?php if (empty($orders)): ?>
      <div class="profile-empty">
        <i class="fa-solid fa-box-open" aria-hidden="true"></i>
        <p>Vous n'avez pas encore passé de commande.</p>
        <a href="index.php" class="btn-rose">Découvrir la collection</a>
      </div>

    <?php else: ?>
      <div class="orders-list">
        <?php foreach ($orders as $order): ?>
          <div class="order-card">
            <div class="order-card__header">
              <div>
                <span class="order-card__id">Commande #<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="order-card__date">
                  <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                  <?= htmlspecialchars($order['date'], ENT_QUOTES, 'UTF-8') ?>
                </span>
              </div>
              <span class="order-badge order-badge--<?= $order['status'] === 'Livré' ? 'delivered' : 'transit' ?>">
                <?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?>
              </span>
            </div>
            <div class="order-card__body">
              <span>
                <i class="fa-solid fa-cubes-stacked" aria-hidden="true"></i>
                <?= (int) $order['items'] ?> article<?= $order['items'] > 1 ? 's' : '' ?>
              </span>
              <span class="order-card__total">
                <?= htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8') ?>
              </span>
              <a href="#" class="order-card__link">
                Voir le détail
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once 'public/includes/footer.php'; ?>