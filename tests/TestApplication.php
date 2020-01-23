<?php

use PHPUnit\Framework\TestCase;

/**
 * DIFFICULTES DES TESTS AVEC UNE APPLICATION PHP NATIVE
 * -------------------------
 * Les problèmes pour tester une application en utilisant les fonctionnalités HTTP natives de PHP sont multiples :
 * 
 * 1) Les tests unitaires doivent être joués dans un process séparé sans quoi l'appel aux headers dans notre code crééra une erreur
 * C'est pour ça que tous nos tests doivent être annotés avec @runInSeparateProcess
 * 2) On doit manipuler les superglobales à la main
 * 3) On doit gérer les buffers (tampons mémoire) de PHP pour pouvoir accéder au contenu qui sera affiché au final dans la page du navigateur
 * 4) On ne peut pas tester les headers sans installer l'extension xdebug pour obtenir la fonction xdebug_get_headers() sachant que la fonction
 * classique de PHP get_headers() ne fonctionne pas en mode console et donc quand on lance phpunit
 */


require_once __DIR__ . '/../functions.php';

class TestApplication extends TestCase
{
    /** 
     * @test 
     * @runInSeparateProcess
     */
    public function le_format_en_cookie_est_bien_pris_en_compte()
    {
        $date = new DateTime('now', new DateTimeZone('GMT+1'));

        $availableFormats = [
            1 => 'H:i',
            2 => 'd/m/Y'
        ];

        // Debut du test :
        $_COOKIE['format'] = "2";

        ob_start();
        execute($availableFormats, 1);

        $rendu = ob_get_clean();

        $this->assertStringContainsString($date->format('d/m/Y'), $rendu);
    }

    /** 
     * @test 
     * @runInSeparateProcess
     */
    public function le_format_en_get_prend_le_dessus_sur_le_cookie()
    {
        $date = new DateTime('now', new DateTimeZone('GMT+1'));

        $availableFormats = [
            1 => 'H:i',
            2 => 'd/m/Y'
        ];

        // Debut du test :
        $_COOKIE['format'] = "peu-importe";

        $_GET['format'] = "2";

        ob_start();
        execute($availableFormats, 1);

        $rendu = ob_get_clean();

        $this->assertStringContainsString($date->format('d/m/Y'), $rendu);
    }

    /** 
     * @test 
     * @runInSeparateProcess
     */
    public function le_format_choisi_est_renvoye_en_cookies()
    {
        $date = new DateTime('now', new DateTimeZone('GMT+1'));

        $availableFormats = [
            1 => 'H:i',
            2 => 'd/m/Y'
        ];

        $_GET['format'] = "2";

        ob_start();
        execute($availableFormats, 1);

        $rendu = ob_get_clean();

        $headers = xdebug_get_headers();

        $this->assertContains("Set-Cookie: format=2", $headers);
    }
}
