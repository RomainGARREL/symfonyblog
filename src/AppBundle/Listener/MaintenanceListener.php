<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Description of MaintenanceListener
 *
 * @author Human Booster
 */
class MaintenanceListener {

    private $templating, $actif;

    public function __construct(TwigEngine $templating, $actif) {
        $this->templating = $templating;
        $this->actif = $actif;
    }

    private function maintenance(Response $response) {
        $html = $this->templating->render('maintenance/index.html.twig');
        $response->setContent($html);
        return $response;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        if ($this->actif) {
            $response = $this->maintenance(new Response);
            $event->setResponse($response);
        }
    }

}
