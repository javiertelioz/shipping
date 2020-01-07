<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Model\Shipping;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use EnviosKanguro\Api;
use Envioskanguro\Shipping\WebService\Rate;

use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

use Envioskanguro\Shipping\WebService\RateRequest\QuotingDataInitializer;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'envioskanguro';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var QuotingDataInitializer
     */
    private $quotingDataInitializer;


    /**
     * Shipping constructor.
     *
     * @param QuotingDataInitializer                                      $quotingDataInitializer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     * 
     */
    public function __construct(
        QuotingDataInitializer $quotingDataInitializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->quotingDataInitializer = $quotingDataInitializer;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [
            'envioskanguro_1' => $this->getConfigData('name'),
            'envioskanguro_2' => $this->getConfigData('name'),
            'envioskanguro_3' => $this->getConfigData('name')
        ];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        if (!$this->getConfigFlag('active')) {
            return $result;
        }

        try {
            $rates = $this->quotingDataInitializer->getAvailableRates($request);
        } catch (\Throwable $th) {
            //throw $th;
            $rates = [];
        }

        if (empty($rates)) {
            return false;
        }

        foreach ($rates->rates as $rate) {

            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code . '_' . $rate->best);
            $method->setMethodTitle($rate->name);

            $amount = $rate->total_price; //$this->getShippingPrice();

            $method->setPrice($amount);
            $method->setCost($amount);

            $result->append($method);
        }

        if (empty($result->getAllRates())) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(['data' => [
                'carrier' => $this->_code,
                'carrier_title' => $this->getConfigData('title'),
                'error_message' => $this->getConfigData('specificerrmsg'),
            ]]);
            $result->append($error);
        }


        return $result;
    }
}
