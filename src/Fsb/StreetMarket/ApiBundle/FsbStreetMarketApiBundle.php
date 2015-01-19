<?php

namespace Fsb\StreetMarket\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FsbStreetMarketApiBundle extends Bundle
{
    public function getParent()
    {
        return 'NelmioApiDocBundle';
    }
}
