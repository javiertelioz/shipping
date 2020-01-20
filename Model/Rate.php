<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Model;

use Magento\Framework\Model\AbstractModel;

class Rate extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Envioskanguro\Shipping\Model\ResourceModel\Rate');
    }
}
