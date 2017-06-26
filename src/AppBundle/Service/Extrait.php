<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Service;

/**
 * Description of Extrait
 *
 * @author Human Booster
 */
class Extrait {

    private $session;

    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session) {
        $this->session = $session;
    }

    public function get($texte) {
        $texte = strip_tags($texte);

        if (strlen($texte) >= 150) {
            $texte = substr($texte, 0, 150);
            $texte = substr($texte, 0, strrpos($texte, ' ')) . ' ...';
        }
        return $texte;
    }

}
