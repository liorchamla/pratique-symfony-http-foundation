<?php

/**
 * TESTABILITE D'UN CODE AVEC HTTPFOUNDATION
 * -------------
 * Ayant vu les problématiques posées par les tests unitaires sur un code en PHP NATIF, voyons désormais comment cela peut fonctionner
 * si l'on utilise la couche objets fournie par symfony/http-foundation (beaucoup mieux ... oui :)).
 * 
 * Pour ce faire, on garde la même architecture, mais on modifie la fonction execute() de façon à ce qu'on puisse lui passer la requête
 * et qu'elle nous rende une réponse
 */

use Symfony\Component\HttpFoundation\Request;

require_once "vendor/autoload.php";
require_once "functions.php";

$request = Request::createFromGlobals();

// Les formats possibles
$availableFormats = [
    1 => 'H:i',
    2 => 'd/m/Y',
    3 => 'd/m/Y H:i'
];

$response = execute($request, $availableFormats, 1);

$response->send();
