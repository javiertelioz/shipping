<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Envioskanguro\Shipping\WebService\Mode\Fixed;
use Envioskanguro\Shipping\WebService\Mode\Threshold;

class Mode
{
    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /**
     * @var Fixed
     */
    protected $fixed;

    /**
     * @var Threshold
     */
    protected $threshold;

    /**
     * Mode constructor.
     * @param Logger $logger
     * @param ScopeConfig $scopeConfig
     * @param Fixed $fixed
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Fixed $fixed,
        Threshold $threshold
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->fixed = $fixed;
        $this->threshold = $threshold;
    }

    /** 
     * Get Mode
     */
    protected function getMode()
    {
        return $this->scopeConfig->getValue('carriers/envioskanguro/mode');
    }

    public function getMethods($rates)
    {
        $mode = $this->getMode();

        
        if ($mode === 'Fixed') {
            return $this->fixed->getRate($rates);
        }
        
        if ($mode === 'Threshold') {
            return $this->threshold->getRate($rates);
        }

        return $rates;
    }
}
