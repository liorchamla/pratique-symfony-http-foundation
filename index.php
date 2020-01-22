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
 * 
 * Ne vous en faites pas, tout ce qu'on pouvait trouver dans les superglobales se retrouve dans notre objet, et en plus on gagne
 * des méthodes simples et claires pour les exploiter !
 * - $request->query correspond à $_GET
 * - $request->request correspond à $_POST
 * - $request->getSession() correspond à $_SESSION
 * - $request->cookies correspond à $_COOKIE
 * - $request->server correspond à $_SERVER
 * - $request->files correspond à $_FILES
 * 
 * Toutes ces données sont des ParameterBags, elles ont donc les mêmes méthodes pour faciliter l'accès aux informations 
 * - VOUS UNIFIEZ VOTRE MANIERE DE TRAITER TOUTES CES DONNEES
 * - VOUS GAGNEZ DE L'AUTOCOMPLETION ET DE LA CLARTE
 * - VOUS GAGNEZ DES OUTILS COOLS
 * => Conclusion : vous gagnez en expérience de développeur (ce qu'on appelle la DX, le plaisir que vous avez à travailler)
 * 
 * Et encore, je ne décris pas tous les outils qu'offre la classe Request
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

// Equivalent de l'ancien :
// if (isset($_COOKIE['format'])) {
//     $format = (int) $_COOKIE['format'];
// }


// Mais si on précise un format dans l'URL (GET), alors ça prend le dessus
$format = $request->query->getInt('format', $format);
if (!array_key_exists($format, $availableFormats)) {
    $format = 1;
}

// Equivalent avec une logique un peu modifiée de l'ancien :
// if (isset($_GET['format']) && ctype_digit($_GET['format']) && array_key_exists($_GET['format'], $availableFormats)) {
//     $format = (int) $_GET['format'];
// }

/**
 * FIN DE L'UTILISATION ICI DE LA CLASSE REQUEST
 */


/**
 * UTILISATION DE LA CLASSE RESPONSE :
 * -----------------------
 * Lorsque l'on veut formuler une réponse HTTP avec PHP, on a là encore à faire à des outils divers et variés avec lesquels 
 * il est facile de se perdre :
 * - la fonction "echo" qui permet de définir ce qui doit s'afficher (le contenu de la réponse)
 * - la fonction "header" qui permet de définir des en-têtes HTTP
 * - la fonction "setcookie" qui permet de définir des cookies
 * - la fonction "http_response_code" qui permet de déifnir le status HTTP de la réponse
 * - et plein d'autres
 * 
 * La classe Response nous permet de manipuler toutes ces informations de façon simple via un simple objet :
 * - VOUS GAGNEZ DE L'AUTOCOMPLETION ET DE LA CLARTE
 * - VOUS GAGNEZ DES OUTILS COOLS
 * => Conclusion : là encore, la DX (Expérience Développeur) est améliorée !
 */
$response = new Response();

// Une fois qu'on connait le format choisi (soit en GET, soit qui vient du Cookie)
// On le remet en place dans le Cookie "format"
$formatCookie = Cookie::create('format', $format);
$response->headers->setCookie($formatCookie);

// Equivalent à l'ancien :
// setcookie('format', $format);

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

$response->headers->set('Content-Type', 'text/html');
// Equivalent à l'ancien :
// header('Content-Type: text/html');

$response->setMaxAge(10);
// Equivalent à l'ancien :
// header('Cache-Control: max-age=10');
// Peut aussi se faire comme ça : $response->headers->set('Cache-Control', 'max-age=10');

// Et les deux informations peuvent aussi se donner comme suit :
// $response->headers->add([
//    'Content-Type' => 'text/html', 
//    'Cache-Control' => 'max-age=10'
// ]);

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

$response->setContent($template);

// Equivalent à l'ancien :
// echo $template;

// On affiche le contenu
$response->send();

/**
 * POINT OPTIMISATION DE CODE POUR LA RESPONSE :
 * -----------------
 * Tout ce qu'on a fait précédemment concernant la Response aurait pu être très réduit :
 * 
 * $response = new Response($template, 200, ['Content-Type' => 'text/html', 'Cache-Control' => 'max-age=10']);
 * $response->headers->setCookie(Cookie::create('format', $format));
 * $response->send();
 */
