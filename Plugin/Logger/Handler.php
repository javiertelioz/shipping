<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Plugin\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logger Type
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * Filename 
     * @var string
     */
    protected $fileName = '/var/log/envios_kanguro.log';
}
