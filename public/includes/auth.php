<?php

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged() && (int) ($_SESSION['user_type_id'] ?? 0) === 2;
}

function is_sav(): bool
{
    return is_logged() && in_array((int) ($_SESSION['user_type_id'] ?? 0), [2, 3], true);
}

function require_sav(): void
{
    if (!is_sav()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void
{
    if (is_admin()) {
        return;
    }
    if (is_sav()) {
        header('Location: admin_tickets.php');
        exit;
    }
    header('Location: login.php');
    exit;
}