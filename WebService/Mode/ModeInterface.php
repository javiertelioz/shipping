<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService\Mode;

interface ModeInterface
{
    /**
     * Retrieve rates.
     *
     * @param $rates
     * @return mixed
     */
    public function getRate($rates): array;
}
