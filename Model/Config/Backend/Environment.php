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

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Environments
     */
    const PRODUCTION    = 'production';
    const DEVELOPMENT   = 'development';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PRODUCTION,
                'label' => __('Production')
            ],
            [
                'value' => self::DEVELOPMENT,
                'label' => __('Development')
            ],
        ];
    }
}
