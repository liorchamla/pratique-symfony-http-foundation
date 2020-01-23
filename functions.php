<?php

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

function execute(array $availableFormats, int $defaultFormat)
{
    $format = $defaultFormat;

    if (isset($_COOKIE['format'])) {
        $format = (int) $_COOKIE['format'];
    }

    if (isset($_GET['format']) && ctype_digit($_GET['format']) && array_key_exists($_GET['format'], $availableFormats)) {
        $format = (int) $_GET['format'];
    }

    setcookie('format', $format);
    header('Content-Type: text/html');
    header('Cache-Control: max-age=10');

    // La timezone de la France
    $timeZone = new DateTimeZone('GMT+1');

    // La date actuelle y compris heure, minutes, secondes
    $now = new DateTime('now', $timeZone);

    // La chaine du temps en fonction du format choisi (voir $avalableFormats plus haut)
    $time = $now->format($availableFormats[$format]);

    // Le template qu'on va afficher
    $template = getHtml($time);

    // On affiche le contenu
    echo $template;
}
