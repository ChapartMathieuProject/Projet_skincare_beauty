<?php
return [
    ['GET', '/',               'HomeController',    'index'],
    ['GET', '/produit/{slug}', 'ProductController', 'show'],
    ['GET', '/produits',       'CategoryController', 'index'],
];