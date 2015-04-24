<?php

namespace Fsb\StreetMarket\ApiBundle\Decoder;

class JsonDecoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        $formattedData = @json_decode($data, true);

        if (is_null($formattedData)) {
            $formattedData = array();
        }

        return $formattedData;
    }
}