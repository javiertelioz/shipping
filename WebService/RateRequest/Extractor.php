<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Magento\Framework\Exception\LocalizedException;

use Magento\Checkout\Model\Session;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Extractor
{
    /** 
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /** 
     * @var Session
     */
    protected $checkoutSession;

    public function __construct(
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Extract quote from rate request.
     *
     * @return \Magento\Quote\Model\Quote
     * @throws LocalizedException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /** 
     * Get Quoting Info
     */
    public function getQuotingData()
    {
        return [
            'identifier'    => '#' . $this->getIdentifier(),
            'origin'        => $this->getShippingOrigin(),
            'destination'   => $this->getShippingDestination(),
            'items'         => $this->getItems()
        ];
    }

    /**
     * Get Identifier
     */
    public function getIdentifier()
    {
        return uniqid();
    }

    /**
     * Normalize rate request items. In rare cases they are not set at all.
     *
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem[]
     */
    public function getItems()
    {
        $items = [];

        if ($this->getQuote()->getAllItems()) {
            foreach ($this->getQuote()->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                $items[] = [
                    'identifier'    => $item->getSku(),
                    'description'   => $item->getName(),
                    'quantity'      => $item->getQty(),
                    'weight'        => $item->getWeight() | 0,
                    'length'        => $item->getProduct()->getData('length') | 0,
                    'width'         => $item->getProduct()->getData('width') | 0,
                    'height'        => $item->getProduct()->getData('height') | 0,
                ];
            }
        }

        return $items;
    }
    /**
     * Get configured Store Shipping Origin
     */
    public function getShippingOrigin()
    {
        return [
            'name'  => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_name'),
            'email' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_email'),
            'home_phone' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_home_phone'),
            'cell_phone' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_cell_phone'),
            'street' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_street'),
            'street_number' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_street_number'),
            'colony' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_colony'),
            'city'  => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_city'),
            'state' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_state'),
            'zip'    => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_zip'),
            'references_1' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_references_1'),
            'references_2' => null,
            'notes' => $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_notes'),
        ];
    }

    /**
     * Extract shipping address from rate request.
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @throws LocalizedException
     */
    public function getShippingDestination()
    {
        return [
            'name' =>
            $this->getQuote()->getShippingAddress()->getFirstname() . ' ' .
                $this->getQuote()->getShippingAddress()->getLastname(),
            'email' =>
            !empty($this->getQuote()->getCustomerEmail()) ?
                $this->getQuote()->getCustomerEmail() :
                $this->scopeConfig->getValue('carriers/envioskanguro/origin_address_email'),
            'home_phone' => $this->getQuote()->getShippingAddress()->getTelephone(),
            'cell_phone' => null,
            'street' => $this->getQuote()->getShippingAddress()->getStreet()[0],
            'street_number' => 'n/a',
            'colony' => 'Colonia',
            'city' => $this->getQuote()->getShippingAddress()->getCity(),
            'state' => $this->getQuote()->getShippingAddress()->getRegion(),
            'zip' => $this->getQuote()->getShippingAddress()->getPostcode(),
            'references_1' => null,
            'references_2' => null,
            'notes' => null,
        ];
    }
}
