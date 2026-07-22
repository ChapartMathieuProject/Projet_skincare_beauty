<?php
return [
    ['GET', '/',               'HomeController',    'index'],
    ['GET', '/produit/{slug}', 'ProductController', 'show'],
];