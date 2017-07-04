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
class ExtraitWithLink {

    private $router, $max;

    public function __construct(\Symfony\Component\Routing\RouterInterface $router, $max) {
        $this->router = $router;
        $this->max = $max;
    }

    function get(\AppBundle\Entity\Article $article) {
        $texte = strip_tags($article->getContenu());

        if (strlen($texte) > $this->max) {
            $texte = substr($texte, 0, $this->max);
            $texte = substr($texte, 0, strrpos($texte, ' '));

            $url = $this->router->generate('blog_detail', ['id' => $article->getId()]);
            $texte .= '<a href="' . $url . '">Lire la suite</a>';
        }
        return $texte;
    }

}
