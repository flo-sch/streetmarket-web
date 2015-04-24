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

    protected function generateJsonResponse($data, $statusCode = 200, $success = false, $format = 'json', $groups = array('list'), $lastModificationDate = null)
    {
        $response = new Response();

        // Set response as public
        $response->setPublic();

        // Check data format
        if (!array_key_exists('success', $data)) {
            $data['success'] = $success;
        }

        // Serialize Response
        $serializer = $this->container->get('jms_serializer');
        $response->setContent($serializer->serialize($data, $format, SerializationContext::create()
            ->setVersion(1)
            ->enableMaxDepthChecks()
            ->setGroups($groups)
            ->setSerializeNull(true)
        ));

        // Set HTTP status code
        $response->setStatusCode($statusCode);

        // Set ETag
        $response->setETag(md5($response->getContent()));


        // Check if the response was modified, in order to send a 304 one otherwise
        if ($lastModificationDate) {
            $response->setLastModified($lastModificationDate);
        }

        if ($response->isNotModified($this->getRequest())) {
            // Send a 304 Response
            $response->setNotModified();
        } else {
            // Set Up cache validity
            $cacheValidity = in_array($this->container->get('kernel')->getEnvironment(), array('dev', 'test')) ? 10 : 300;

            // Expiration Date
            $expiresAt = new DateTime();
            $expiresAt->modify('+' . $cacheValidity . ' seconds');
            $response->setExpires($expiresAt);

            // Response Max Age
            $response->setMaxAge($cacheValidity);
            $response->setSharedMaxAge($cacheValidity);
            $response->headers->addCacheControlDirective('must-revalidate', true);
        }

        return $response;
    }
}
