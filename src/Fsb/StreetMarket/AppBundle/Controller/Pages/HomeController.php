<?php

namespace Fsb\StreetMarket\AppBundle\Controller\Pages;

use Fsb\StreetMarket\AppBundle\Controller\FrontController;

class HomeController extends FrontController
{
    public function indexAction()
    {
        return $this->render('FsbStreetMarketAppBundle:Pages/Home:index.html.twig');
    }
}
