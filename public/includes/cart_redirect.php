<?php

/**
 * Indique si $path est un chemin interne sûr pour une redirection :
 * commence par un seul « / » (pas « // »), sans antislash ni caractère de contrôle
 * (les navigateurs lisent « /\ » comme « // », donc comme un autre site).
 */
function is_safe_local_path(string $path): bool
{
    return $path !== ''
        && $path[0] === '/'
        && !str_starts_with($path, '//')
        && preg_match('/[\x00-\x1F\x7F\\\\]/', $path) === 0;
}

/**
 * Page où renvoyer l'utilisateur après une action panier sans JavaScript.
 * Priorité : champ « redirect » s'il est sûr, sinon le Referer du même hôte réduit
 * à son chemin (sans BASE_PATH, que url() rajoute), sinon l'accueil « / ».
 * Le chemin renvoyé est relatif à l'application (à passer à url()).
 */
function cart_redirect_target(?string $posted, ?string $referer, string $host, string $basePath): string
{
    if ($posted !== null && is_safe_local_path($posted)) {
        return $posted;
    }

    if ($referer !== null && $referer !== '' && $host !== '') {
        $parts = parse_url($referer);
        if (is_array($parts) && isset($parts['host'])) {
            $refererHost = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            $path        = $parts['path'] ?? '/';

            if (strcasecmp($refererHost, $host) === 0 && is_safe_local_path($path)) {
                if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
                    $path = substr($path, strlen($basePath));
                    $path = $path === '' ? '/' : $path;
                }
                return $path;
            }
        }
    }

    return '/';
}
