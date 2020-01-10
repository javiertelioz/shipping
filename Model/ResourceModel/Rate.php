<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rate extends AbstractDb
{
    public function _construct()
    {
        $this->_init("envioskanguro_shipping_rates", "rate_id");
    }
}
