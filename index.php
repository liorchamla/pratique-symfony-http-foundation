<?php

/**
 * COURS SUR LE COMPOSANT SYMFONY/HTTP-FOUNDATION
 * -------------------------------
 * Bienvenue dans cette toute petite application dont le but est très simple : vous donner l'heure ou la date !
 * Le but est de vous montrer comment le composant HttpFoundation transforme de nombreuses et diverses fonctionnalités
 * de PHP pour gérer la Requête (paramètres GET, POST, SESSION, COOKIES etc) 
 * et la Réponse (en-têtes, Cookies, Status et corps de la réponse) par des objets et des méthodes simples
 * et claires
 */

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once 'vendor/autoload.php';

$request = Request::createFromGlobals();

// Les formats possibles
$availableFormats = [
    1 => 'H:i',
    2 => 'd/m/Y',
    3 => 'd/m/Y H:i'
];

// On regarde si il y a un cookie et si oui, on le prend
$format = $request->cookies->getInt('format', $format);

// Mais si on précise un format dans l'URL (GET), alors ça prend le dessus
$format = $request->query->getInt('format', $format);

// Si le format est problématique (mal tapé dans le GET, ou modification malveillante dans le cookie) on met à 1 par défaut
if (!array_key_exists($format, $availableFormats)) {
    // Format par défaut : si on n'a ni Cookie ni information dans l'URL (en GET), on prendra le format 1 (donc "14:55")
    $format = 1;
}

// La timezone de la France
$timeZone = new DateTimeZone('GMT+1');

// La date actuelle y compris heure, minutes, secondes
$now = new DateTime('now', $timeZone);

// La chaine du temps en fonction du format choisi (voir $avalableFormats plus haut)
$time = $now->format($availableFormats[$format]);

// Le template qu'on va afficher
$template = '
<html>
    <head>
        <title>Application qui ne sert à rien qu\' à vous donner la date :D</title>
    </head>
    <body>
        <h1>Bienvenue dans cette application inutile :D</h1>
        <p>Temps actuel : <strong>' . $time . '</strong></p>
        <a href="">Actualiser</a>
    </body>
</html>
';


$response = new Response($template);
$response->headers->setCookie(Cookie::create('format', $format));
$response->headers->set('Content-Type', 'text/html');
$response->setMaxAge(10);
$response->send();
