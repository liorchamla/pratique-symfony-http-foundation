<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseCookieValueSame;

/**
 * DIFFICULTES DES TESTS AVEC LE COMPOSANT HTTP FOUNDATION DE SYMFONY
 * -------------------------
 * Dans l'étape précédente, nous avions vu les problématiques rencontrées lorsqu'il s'agissait de tester les fonctionnalités en 
 * rapport avec le protocole HTTP en PHP NATIF. Voici comment HttpFoundation nous aide !
 * 
 * Les problèmes pour tester une application en utilisant les fonctionnalités HTTP natives de PHP sont multiples :
 * 
 * 1) Les tests unitaires doivent être joués dans un process séparé sans quoi l'appel aux headers dans notre code crééra une erreur
 * C'est pour ça que tous nos tests doivent être annotés avec @runInSeparateProcess
 * => Avec HttpFoundation, les headers ne sont pas envoyés ni ajoutés tant qu'on n'a pas appelé $response->send(), pas besoin de jouer les
 * tests dans un process séparé :)
 * 
 * 2) On doit manipuler les superglobales à la main
 * => On ne manipule plus les superglobales mais simplement l'objet $request :)
 * 
 * 3) On doit gérer les buffers (tampons mémoire) de PHP pour pouvoir accéder au contenu qui sera affiché au final dans la page du navigateur
 * => Comment rien n'est envoyé tant qu'on n'appelle pas $response->send(), on n'a pas à s'occuper des buffers (ob_start, ob_get_clean etc)
 * 
 * 4) On ne peut pas tester les headers sans installer l'extension xdebug pour obtenir la fonction xdebug_get_headers() sachant que la fonction
 * classique de PHP get_headers() ne fonctionne pas en mode console et donc quand on lance phpunit
 * => Désormais, les headers ne sont qu'un ParameterBag comme un autre et on peut les manipuler comme on le veut, MÊME SANS EXTENSION XDEBUG !
 * 
 * BONUS DE STYLE 
 * ----------------
 * Le composant HttpFoundation vient avec de nouvelles contraintes d'assertions qu'on peut utiliser très simplement avec la méthode $this->assertThat :
 * - RequestAttributeValueSame : s'assurer que les attributs de la requêtes contiennent un attribut donné avec une valeur donnée
 * - ResponseCookieValueSame : s'assurer qu'un cookie de la réponse possède bien une certaine valeur
 * - ResponseHasCookie : s'assurer que la réponse contient bien un cookie donné
 * - ResponseHasHeader : s'assurer qu'il existe bien un header en particulier dans la réponse
 * - ResponseHeaderSame : s'assurer que la réponse contient bien un header avec la valeur voulue
 * - ResponseIsRedirected : s'assurer que la réponse est une redirection
 * - ResponseIsSuccessful : s'assurer que la réponse est bien au statut 200
 * - ResponseStatusCodeSame : s'assurer que la réponse a bien un statut voulu (404 par exemple)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../functions.php';

class TestApplication extends TestCase
{
    /** 
     * @test
     */
    public function le_format_en_cookie_est_bien_pris_en_compte()
    {
        $date = new DateTime('now', new DateTimeZone('GMT+1'));

        $availableFormats = [
            1 => 'H:i',
            2 => 'd/m/Y'
        ];

        // Debut du test :
        $request = new Request();
        $request->cookies->set('format', 2);
        // Remplace l'ancien :
        // $_COOKIE['format'] = "2";

        $response = execute($request, $availableFormats, 1);
        // Remplace l'ancien :
        // ob_start();
        // execute($availableFormats, 1);
        // $rendu = ob_get_clean();

        $this->assertStringContainsString($date->format('d/m/Y'), $response->getContent());
        // Remplace l'ancien :
        // $this->assertStringContainsString($date->format('d/m/Y'), $rendu);
    }

    /** 
     * @test 
     */
    public function le_format_en_get_prend_le_dessus_sur_le_cookie()
    {
        $date = new DateTime('now', new DateTimeZone('GMT+1'));

        $availableFormats = [
            1 => 'H:i',
            2 => 'd/m/Y'
        ];

        // Debut du test :
        $request = new Request();
        $request->cookies->set('format', 'peu-importe');
        $request->query->set('format', "2");

        // Remplace l'ancien :
        // $_COOKIE['format'] = "peu-importe";
        // $_GET['format'] = "2";

        $response = execute($request, $availableFormats, 1);

        $this->assertStringContainsString($date->format('d/m/Y'), $response->getContent());
    }

    /** 
     * @test 
     */
    public function le_format_choisi_est_renvoye_en_cookies()
    {
        $date = new DateTime('now', new DateTimeZone('GMT+1'));

        $availableFormats = [
            1 => 'H:i',
            2 => 'd/m/Y'
        ];

        $request = new Request();
        $request->query->set('format', '2');

        $response = execute($request, $availableFormats, 1);

        $this->assertThat($response, new ResponseCookieValueSame("format", 2));

        // Remplace l'ancien :
        // $headers = xdebug_get_headers();
        // $this->assertContains("Set-Cookie: format=2", $headers);
    }
}
