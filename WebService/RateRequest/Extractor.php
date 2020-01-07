<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Exception\LocalizedException;

use Magento\Shipping\Model\Config;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Extractor
{
    /** 
     * Config
     */
    protected $_scopeConfig;

    /** 
     * Checkout Session
     */
    protected $_checkoutSession;

    public function __construct(
        Session $_checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * Extract quote from rate request.
     *
     * @return \Magento\Quote\Model\Quote
     * @throws LocalizedException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /** 
     * Get Quoting Info
     */
    public function getQuotingData(RateRequest $rateRequest)
    {
        return [
            'identifier'    => '#' . $this->getIdentifier(),
            'origin'        => $this->getShippingOrigin(),
            'destination'   => $this->getShippingDestination(),
            'items'         => $this->getItems($rateRequest)
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
     * @param RateRequest $rateRequest
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem[]
     */
    public function getItems(RateRequest $rateRequest)
    {
        $items = [];

        if ($rateRequest->getAllItems()) {
            foreach ($rateRequest->getAllItems() as $item) {

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
            'name'  => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_name'),
            'email' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_email'),
            'home_phone' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_home_phone'),
            'cell_phone' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_cell_phone'),
            'street' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_street'),
            'street_number' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_street_number'),
            'colony' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_colony'),
            'city'  => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_city'),
            'state' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_state'),
            'zip'    => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_zip'),
            'references_1' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_references_1'),
            'references_2' => null,
            'notes' => $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_notes'),
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
                $this->_scopeConfig->getValue('carriers/envioskanguro/origin_address_email'),
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
