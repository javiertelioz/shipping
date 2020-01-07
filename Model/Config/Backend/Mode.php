<?php

/**
 * Envios Kanguro Shipping
 * Refer to LICENSE.txt distributed with the module for notice of license
 *
 * @package Envioskanguro\Shipping\Block
 * @author  Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://envioskanguro.com/
 * @api
 */

namespace Envioskanguro\Shipping\Model\Config\Backend;


class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Mode
     */
    const FIXED= 'Fixed';
    const STANDARD= 'Standard';
    const THRESHOLD= 'Threshold';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STANDARD,
                'label' => __('Standard')
            ],
            [
                'value' => self::FIXED,
                'label' => __('Fixed')
            ],
            [
                'value' => self::THRESHOLD,
                'label' => __('Threshold')
            ],
        ];
    }
}
