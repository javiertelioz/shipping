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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Extractor
{
    /** 
     * @var ScopeInterface $scopeConfig 
     */
    protected $scopeConfig;

    /** 
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /** 
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    public function __construct(
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
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
        if (is_null($this->getQuote()->getShippingAddress()->getPostcode())) {
            return [];
        }

        return [
            'identifier'    => $this->getIdentifier(),
            'currency'      => $this->getCurrency(),
            'origin'        => $this->getShippingOrigin(),
            'destination'   => $this->getShippingDestination(),
            'items'         => $this->getItems(),
        ];
    }

    /**
     * Get Identifier
     */
    public function getIdentifier()
    {
        return '#' . uniqid();
    }

    public function getCurrency()
    {
        return $this->storeManager->getStore()
            ->getCurrentCurrency()->getCode();
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
