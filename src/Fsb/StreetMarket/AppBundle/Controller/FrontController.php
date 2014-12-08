<?php

namespace Fsb\StreetMarket\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends Controller
{
    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    protected function generateJsonResponse($data, $statusCode = 200)
    {
        $response = new JsonResponse($data, $statusCode);

        // Set response as public
        $response->setPublic();

        $cacheValidity = 300;

        // Expiration Date
        $expiresAt = new DateTime();
        $expiresAt->modify('+' . $cacheValidity . ' seconds');
        $response->setExpires($expiresAt);

        // Response Max Age
        $response->setMaxAge($cacheValidity);
        $response->setSharedMaxAge($cacheValidity);

        // ETag
        $response->setETag(md5($response->getContent()));
        $response->isNotModified($this->getRequest());

        return $response;
    }
}
