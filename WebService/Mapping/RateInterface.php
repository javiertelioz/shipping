<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService\Mapping;

interface RateInterface
{
    /**
     * Retrieve shipping platform attributes.
     *
     * @param $quotingData
     * @return mixed
     */
    public function getRates($quotingData): array;
}
