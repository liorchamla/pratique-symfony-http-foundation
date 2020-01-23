<?php

/**
 * TESTABILITE D'UN CODE PHP NATIF
 * -------------
 * Nous nous intéressons ici à la testabilité de notre code PHP natif (sans HttpFoundation). Pour pouvoir tester ce code correctement
 * on a divisé notre code entre un code principal (ci-dessous) et quelques fonctions dans le fichier "functions.php"
 * 
 * Le code principal met en place une sorte de configuration, alors que la fonction "execute()" met en place le traitement.
 * 
 * Le reste du cours est à voir dans le fichier tests/TestApplication.php
 */

require_once "functions.php";

// Les formats possibles
$availableFormats = [
    1 => 'H:i',
    2 => 'd/m/Y',
    3 => 'd/m/Y H:i'
];

execute($availableFormats, 1);
