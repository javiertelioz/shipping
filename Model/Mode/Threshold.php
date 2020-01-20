<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Model\Mode;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Envioskanguro\Shipping\Model\Mode\Modality\ModalityInterface;

class Threshold implements ModalityInterface
{
    const MODALITY_CODE = 'Threshold';

    /** 
     * @var Session
     */
    protected $checkoutSession;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    public function __construct(
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
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
        $total = (float) $this->checkoutSession->getQuote()->getGrandTotal();

        if ($total > $this->getFreeFromPrice()) {
            $rate[0]['custom_price'] = 0.0;
        } else {
            $price = isset($rate[0]['original_price']) ? $rate[0]['original_price'] : $this->getPrice();
            $rate[0]['custom_price'] = $price;
        }

        return $rate;
    }

    /** 
     * Get Shipping Price
     */
    protected function getPrice()
    {
        return (float) $this->scopeConfig->getValue('carriers/envioskanguro/price');
    }

    /** 
     * Get free price 
     */
    protected function getFreeFromPrice()
    {
        return (float) $this->scopeConfig->getValue('carriers/envioskanguro/free_from');
    }
}
