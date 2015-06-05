<?php

namespace Fsb\StreetMarket\AppBundle\Controller\Pages;

use Fsb\StreetMarket\AppBundle\Controller\FrontController;
use Fsb\StreetMarket\AppBundle\Exception\UnvalidConfigurationException;

class HomeController extends FrontController
{
    public function indexAction()
    {
        $apiKeys = $this->container->getParameter('api_keys');

        if (!$apiKeys || !(is_array($apiKeys)) || !(array_key_exists('google', $apiKeys)) || !(is_array($apiKeys['google'])) || !(array_key_exists('maps', $apiKeys['google']))) {
            throw new UnvalidConfigurationException('Missing Google Maps api key');
        }

        return $this->render('FsbStreetMarketAppBundle:Pages/Home:index.html.twig', array(
            'apiKeys' => $apiKeys
        ));
    }
}
