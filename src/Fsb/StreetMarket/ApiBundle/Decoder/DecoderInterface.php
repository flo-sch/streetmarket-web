<?php

namespace Fsb\StreetMarket\ApiBundle\Decoder;

interface DecoderInterface
{
    /**
     * Decodes any string into PHP data.
     *
     * @param string $data
     *
     * @return array
     */
    public function decode($data);
}