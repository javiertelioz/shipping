<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Model;

use Envioskanguro\Shipping\Model\Mode\Fixed;
use Envioskanguro\Shipping\Model\Mode\Threshold;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Mode
{
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

    public function __construct(
        Fixed $fixed,
        Threshold $threshold,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->fixed = $fixed;
        $this->threshold = $threshold;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get modality from config
     * 
     * @return string mode
     */
    protected function getMode(): string
    {
        return $this->scopeConfig->getValue('carriers/envioskanguro/mode');
    }

    /**
     * Get the available methods from
     * envios kanguro rates
     * 
     * @return array rates
     */
    public function getShippingMethods($rates): array
    {
        $modality = $this->getMode();

        if(empty($rates)){
            return [];
        }
        
        if ($modality === Fixed::MODALITY_CODE) {
            return $this->fixed->getAvailableRates($rates);
        }

        if ($modality === Threshold::MODALITY_CODE) {
            return $this->threshold->getAvailableRates($rates);
        }

        return $rates;
    }
}
