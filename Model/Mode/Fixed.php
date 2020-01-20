<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Model\Mode;

use Magento\Framework\App\Config\ScopeConfigInterface;

use Envioskanguro\Shipping\Model\Mode\Modality\ModalityInterface;

class Fixed implements ModalityInterface
{
    const MODALITY_CODE = 'Fixed';

    /** 
     * @var ScopeConfig $scopeConfig
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve rates.
     *
     * @param $rates
     * @return mixed
     */
    public function getAvailableRates($rates): array
    {
        $rate = array_slice($rates, 0, 1);
        $rate[0]['custom_price'] = $this->getPrice();

        return $rate;
    }

    /** 
     * Get Shipping Price
     */
    protected function getPrice()
    {
        return (float) $this->scopeConfig->getValue('carriers/envioskanguro/price');
    }
}
