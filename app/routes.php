<?php

return [
    ['GET',  '/',                'HomeController',    'index'],
    ['GET',  '/produits',        'ProductController', 'index'],
    ['GET',  '/produit/{slug}',  'ProductController', 'show'],
];