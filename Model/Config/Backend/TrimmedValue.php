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

use Magento\Framework\App\Config\Value;

class TrimmedValue extends Value
{
    /**
     * Trim value before save
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        $this->setValue(trim($this->getValue()));

        return parent::beforeSave();
    }
}
