<?php

/**
 * Déconnecte l'utilisateur en cours en détruisant sa session,
 * puis le redirige vers son portail : administration pour un admin / agent SAV,
 * page d'accueil pour un client.
 */

// On démarre la session si ce n'est pas déjà fait, car session_unset()
// et session_destroy() nécessitent une session active pour fonctionner.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// On lit le type de compte avant de détruire la session (2 = admin, 3 = agent SAV).
$wasStaff = in_array((int) ($_SESSION['user_type_id'] ?? 0), [2, 3], true);

// On vide toutes les variables de session (l'utilisateur, le panier, etc.)
// avant de détruire la session elle-même.
session_unset();

// On détruit les données de session côté serveur.
session_destroy();

// On redirige vers le portail d'origine. header() doit être appelé
// avant tout affichage HTML, d'où l'absence de sortie dans ce fichier.
header('Location: ' . ($wasStaff ? 'admin_login.php' : 'index.php'));

// On arrête immédiatement le script pour éviter que du code s'exécute
// après la redirection.
exit();
