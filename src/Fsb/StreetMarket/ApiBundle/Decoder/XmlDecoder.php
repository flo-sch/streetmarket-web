<?php

namespace Fsb\StreetMarket\ApiBundle\Decoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class XmlDecoder implements DecoderInterface
{
    protected $encoder;

    public function __construct()
    {
        $this->encoder = new XmlEncoder();
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        $formattedData = array();

        try {
            $formattedData = $this->encoder->decode($data, 'xml');
        } catch (UnexpectedValueException $e) {}

        return $formattedData;
    }
}