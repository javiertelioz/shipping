<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Envioskanguro\Shipping\Model\Mode\Modality;

interface ModalityInterface
{
    /**
     * Retrieve available Rates
     *
     * @param $rates
     * @return mixed
     */
    public function getAvailableRates($rates): array;
}
