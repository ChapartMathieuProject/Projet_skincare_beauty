<?php
function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}