<?php
// Vérification de la session : l'utilisateur doit être connecté pour accéder au profil.
// En production, remplacer la simulation par une vraie vérification BDD.
session_start();

//if (!isset($_SESSION['user_id'])) {
    // Redirection vers la page de connexion si la session est absente
    //header('Location: login.php');
   // exit;
//}

// Simulation d'un utilisateur connecté pour le développement
$currentUser = [
    'user_id' => $_SESSION['user_id'] ?? 1,
    'user_name' => $_SESSION['user_name'] ?? 'Camil Bernardeau',
    'user_mail' => $_SESSION['user_email'] ?? 'camil@skincarebeauty.fr',
];

/**
 * Génère les deux initiales du nom complet pour l'avatar par défaut.
 *
 * @param string $fullName Nom complet de l'utilisateur
 * @return string Initiales en majuscules (ex : "CB")
 */
function getUserInitials(string $fullName): string
{
    // Traduction en anglais des variables locales de la fonction (Convention Règle 1)
    $nameParts = array_filter(explode(' ', trim($fullName)));
    $initials = '';
    
    foreach ($nameParts as $part) {
        $initials .= strtoupper(mb_substr($part, 0, 1));
        if (strlen($initials) === 2) {
            break;
        }
    }
    return $initials;
}

$userInitials = getUserInitials($currentUser['user_name']);

// Définition des 5 cartes de navigation du profil
$profileCards = [
    [
        'id' => 'orders',
        'title' => 'Vos Commandes',
        'desc' => 'Suivez vos colis, consultez votre historique et téléchargez vos factures.',
        'link' => 'orders.php',
        'icon' => 'fa-solid fa-box',
    ],
    [
        'id' => 'security',
        'title' => 'Connexion & Sécurité',
        'desc' => 'Modifiez votre mot de passe, votre email et gérez la double authentification.',
        'link' => 'security.php',
        'icon' => 'fa-solid fa-shield-halved',
    ],
    [
        'id' => 'addresses',
        'title' => 'Adresses',
        'desc' => 'Gérez vos adresses de livraison et de facturation.',
        'link' => 'adresses.php',
        'icon' => 'fa-solid fa-location-dot',
    ],
    [
        'id' => 'payments',
        'title' => 'Vos Paiements',
        'desc' => 'Consultez vos cartes enregistrées et vos options de facturation.',
        'link' => 'payments.php',
        'icon' => 'fa-solid fa-credit-card',
    ],
    [
        'id' => 'contact',
        'title' => 'Nous Contacter',
        'desc' => 'Contactez notre support, ouvrez un ticket ou consultez la FAQ.',
        'link' => 'contact.php',
        'icon' => 'fa-solid fa-headset',
    ],
];

include 'public/includes/header.php';
?>

<main class="profile-main">
  <div class="container">

    <div class="profile-header">
      <div class="profile-avatar" aria-hidden="true">
        <span><?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="profile-header__info">
        <p class="profile-header__greeting">Bonjour,</p>
        <h1 class="profile-header__name">
          <?= htmlspecialchars($currentUser['user_name'], ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="profile-header__email">
          <i class="fa-regular fa-envelope" aria-hidden="true"></i>
          <?= htmlspecialchars($currentUser['user_mail'], ENT_QUOTES, 'UTF-8') ?>
        </p>
      </div>
      <a href="logout.php" class="btn-logout ms-auto">
        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        Déconnexion
      </a>
    </div>

    <h2 class="profile-section-title">Mon espace</h2>

    <div class="profile-grid">
      <?php foreach ($profileCards as $card): ?>
        <a
          href="<?= htmlspecialchars($card['link'], ENT_QUOTES, 'UTF-8') ?>"
          class="profile-card"
          aria-label="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>"
        >
          <div class="profile-card__icon">
            <i class="<?= $card['icon'] ?>" aria-hidden="true"></i>
          </div>
          <div class="profile-card__body">
            <h3 class="profile-card__title">
              <?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="profile-card__desc">
              <?= htmlspecialchars($card['desc'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
          <i class="fa-solid fa-chevron-right profile-card__arrow" aria-hidden="true"></i>
        </a>
      <?php endforeach; ?>
    </div>

  </div>
</main>

<?php include 'public/includes/footer.php'; ?>