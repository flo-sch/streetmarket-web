<?php

namespace Fsb\StreetMarket\ApiBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Fsb\StreetMarket\ApiBundle\Decoder\JsonDecoder;
use Fsb\StreetMarket\ApiBundle\Decoder\XmlDecoder;

class BodyListener implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        $contentType = $request->headers->get('Content-Type');
        $content = $request->getContent();

        $data = $this->decodeData($contentType, $content);

        switch ($method) {
            case 'HEAD':
            case 'GET':
                $request = $this->replaceQueryValues($request, $data);
                break;
            case 'POST':
            case 'PUT':
            case 'UPDATE':
            case 'DELETE':
                $request = $this->replaceRequestValues($request, $data);
                break;
        }
    }

    protected function decodeData($contentType, $content)
    {
        $data = array();

        $decoder = $this->getDecoder($contentType);

        if ($decoder) {
            $data = $decoder->decode($content);
        }

        return $data;
    }

    protected function getDecoder($contentType)
    {
        $decoder = null;

        switch ($contentType) {
            case 'json':
            case 'application/json':
                $decoder = new JsonDecoder();
                break;
            case 'xml':
            case 'application/xml':
            case 'application/atom+xml':
                $decoder = new XmlDecoder();
                break;
        }

        return $decoder;
    }

    protected function replaceQueryValues(Request $request, $data)
    {
        $request->query->replace($data);

        return $request;
    }

    protected function replaceRequestValues(Request $request, $data)
    {
        $request->request->replace($data);

        return $request;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 10)),
        );
    }
}