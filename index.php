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

use Symfony\Component\HttpFoundation\Request;

// On inclue l'autoloader pour travailler avec HttpFoundation
require_once 'vendor/autoload.php';

/**
 * UTILISATION DE LA CLASSE REQUEST :
 * -----------------------
 * Lorsque l'on s'intéresse à la Requête HTTP, PHP nous donne certains outils très divers et variés (les superglobales):
 * - Les tableaux associatifs $_GET et $_POST qui représentent les paramètres GET (dans l'URL) ou POST (dans le corps de la requête)
 * - Les tableaux associatifs $_SESSION et $_COOKIES qui représentent les données stockées en SESSION ou dans les COOKIES du navigateur
 * - Le tableau associatif $_SERVER qui représente les données de l'environnement (URL tapée, nom du fichier, version du navigateur etc)
 * 
 * C'est déjà super mais l'utilisation de toutes ces informations sous la forme de simples tableaux associatifs implique parfois
 * des pirouettes assez lourdes que se propose de corriger la classe Request
 */

// Créons une instance de la classe Request en se basant sur les superglobales de PHP
$request = Request::createFromGlobals();

/**
 * LES PARAMETRES DE L'APPLICATION :
 * ----------------
 *  
 * L'utilisateur peut :
 * - appeler index.php : il recevra par exemple "14:55"
 * - appeler index.php?format=1 : il recevra aussi "14:55"
 * - appeler index.php?format=2 : il recevra alors "12/01/2020"
 * - appeler index.php?format=3 : il recevra alors "12/01/2020 14:55"
 * 
 * Le format par défaut est donc "14:55" si on ne demande rien de précis
 * 
 * ----------------
 * Pour la tester :
 * 1) Allez dans le terminal
 * 2) Tapez : php -S localhost:3000
 * 3) Lancez http://localhost:3000 dans votre navigateur
 */

// Les formats possibles
$availableFormats = [
    1 => 'H:i',
    2 => 'd/m/Y',
    3 => 'd/m/Y H:i'
];

// Format par défaut : si on n'a ni Cookie ni information dans l'URL (en GET), on prendra le format 1 (donc "14:55")
$format = 1;

/**
 * OPTIMISATIONS D'USAGE
 * ---------------------
 * Afin de simplifier l'utilisation de l'application pour les utilisateurs, nous souhaitons que 
 * si l'utilisateur demande un format particulier, ce format soit "sauvé" afin qu'ensuite si il rappelle
 * la page sans préciser de format, on garde le format qu'il avait choisi auparavant. Donc :
 * - si l'utilisateur appelle une première fois : index.php?format=2 il recevra par exemple "12/01/2020"
 * - si l'utilisateur rappelle simplement index.php (sans préciser de format), il recevra à nouveau "12/01/2020"
 * 
 * Pour gérer ça, on passe par un cookie qui sera stocké sur le navigateur et qui contient le dernier format choisi
 */
$format = $request->cookies->getInt('format', $format);
// if (isset($_COOKIE['format'])) {
//     $format = $_COOKIE['format'];
// }


// Mais si on précise un format dans l'URL (GET), alors ça prend le dessus
if (isset($_GET['format']) && ctype_digit($_GET['format']) && array_key_exists($_GET['format'], $availableFormats)) {
    $format = $_GET['format'];
}

// Une fois qu'on connait le format choisi (soit en GET, soit qui vient du Cookie)
// On le remet en place dans le Cookie "format"
setcookie('format', $format);

/**
 * --------------------------------------------------
 * OPTIMISATIONS HTTP (EN-TÊTES HTTP == HTTP HEADERS)
 * --------------------------------------------------
 * Ca ne sert à rien de faire travailler le serveur quand l'utilisateur rappelle l'application au bout de 
 * seulement quelques secondes. On veut donc expliquer au navigateur que la réponse reste bonne pendant 10 secondes. Donc :
 * - si l'utilisateur appelle index.php à 14:55:30 : il reçoit "14:55"
 * - si l'utilisateur rappelle index.php dans les 10 secondes qui suivent (par exemple à 14:55:35), le navigateur ne doit pas
 * rappeler le serveur.
 * 
 * Pour gérer ça, on explique la politique de Cache HTTP au navigateur
 */
header('Content-Type: text/html');
header('Cache-Control: max-age=10');

/**
 * ALGORITHME ET CORPS DE LA REPONSE :
 * --------------------
 * On peut enfin commencer l'algorithme et envoyer le corps de la réponse (ce qui s'affichera sur la page réellement)
 * 
 * 1) On met en place la timezone de la France (parce que cocorico)
 * 2) On créé un objet DateTime sur l'instant présent
 * 3) On créé la chaine de caractère en fonction du format choisi
 * 4) On envoie le texte avec un lien qui permet de relancer
 */

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

// On affiche le contenu
echo $template;
