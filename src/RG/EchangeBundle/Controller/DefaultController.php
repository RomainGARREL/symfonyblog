<?php

namespace RG\EchangeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RGEchangeBundle:Default:index.html.twig');
    }
}
