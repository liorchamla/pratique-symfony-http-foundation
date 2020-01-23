<?php

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function getHtml(string $time): string
{
    return '
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
}

function execute(Request $request, array $availableFormats, int $defaultFormat): Response
{

    $response = new Response();

    // On regarde d'abord dans les cookies, sinon on prend le format par défaut
    $format = $request->cookies->getInt('format',  $defaultFormat);

    // Si on a un format dans la request, c'est lui qu'on prend, sinon on garde le format qu'on vient de trouver dans les cookies
    $format = $request->query->getInt('format', $format);

    // Si jamais on se fout de nous, on reprend le format par défaut
    if (!array_key_exists($format, $availableFormats)) {
        $format = $defaultFormat;
    }

    $response->headers->setCookie(Cookie::create('format', $format));
    $response->headers->set('Content-Type', 'text/html');
    $response->setMaxAge(10);

    // La timezone de la France
    $timeZone = new DateTimeZone('GMT+1');

    // La date actuelle y compris heure, minutes, secondes
    $now = new DateTime('now', $timeZone);

    // La chaine du temps en fonction du format choisi (voir $avalableFormats plus haut)
    $time = $now->format($availableFormats[$format]);

    // Le template qu'on va afficher
    $template = getHtml($time);

    // On affiche le contenu
    $response->setContent($template);

    return $response;
}
