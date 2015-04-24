<?php

namespace Fsb\StreetMarket\ApiBundle\Controller;

use DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use JMS\Serializer\SerializationContext;

class RestController extends Controller
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

    protected function generateJsonResponse($data, $statusCode = 200, $success = false, $format, $groups = array('list'))
    {
        if (!array_key_exists('success', $data)) {
            $data['success'] = $success;
        }

        $serializer = $this->container->get('jms_serializer');

        // $response = new JsonResponse($data, $statusCode);

        $response = new Response($serializer->serialize($data, $format, SerializationContext::create()
            ->setVersion(1)
            ->enableMaxDepthChecks()
            ->setGroups($groups)
            ->setSerializeNull(true)
        ), $statusCode);

        // Set response as public
        $response->setPublic();

        $cacheValidity = in_array($this->container->get('kernel')->getEnvironment(), array('dev', 'test')) ? 0 : 300;

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
