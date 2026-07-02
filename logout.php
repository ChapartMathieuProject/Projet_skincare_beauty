<?php

/**
 * Déconnecte l'utilisateur en cours en détruisant sa session,
 * puis le redirige vers la page d'accueil.
 */

// On démarre la session si ce n'est pas déjà fait, car session_unset()
// et session_destroy() nécessitent une session active pour fonctionner.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// On vide toutes les variables de session (l'utilisateur, le panier, etc.)
// avant de détruire la session elle-même.
session_unset();

// On détruit les données de session côté serveur.
session_destroy();

// On redirige vers la page d'accueil. header() doit être appelé
// avant tout affichage HTML, d'où l'absence de sortie dans ce fichier.
header('Location: index.php');

// On arrête immédiatement le script pour éviter que du code s'exécute
// après la redirection.
exit();
