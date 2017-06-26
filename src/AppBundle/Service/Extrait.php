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
    private $max;
    private $suite;

    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session, $max, $suite) {
        $this->session = $session;
        $this->max = $max;
    }

    public function get($texte) {
        $texte = strip_tags($texte);

        if (strlen($texte) > $this->max) {
            $texte = substr($texte, 0, $this->max);
            $texte = substr($texte, 0, strrpos($texte, ' ')) . $this->suite;
        }
        $this->session->getFlashBag()->add('succes', 'Extrait Ok');
        return $texte;
    }

}
