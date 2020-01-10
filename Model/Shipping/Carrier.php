<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Model\Shipping;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Envioskanguro\Shipping\WebService\RateRequest\Storage;

use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

use Envioskanguro\Shipping\WebService\RateRequest\QuotingDataInitializer;

class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'envioskanguro';

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var QuotingDataInitializer
     */
    private $quotingDataInitializer;

    /** 
     * @var Storage
     */
    protected $storage;

    /**
     * Shipping constructor.
     * 
     * @param Storage $storage,
     * @param QuotingDataInitializer $quotingDataInitializer
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     * 
     */
    public function __construct(
        Storage $storage,
        QuotingDataInitializer $quotingDataInitializer,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->storage = $storage;
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
        $methods = [];
        $quote = $this->storage->getRateByCurrentQuote();
        $rates = unserialize($quote->getContent());

        foreach ($rates as $rate) {
            $methods[$rate['code']] = $rate['name'];
        }

        return $methods;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        /** @var Result $result */
        $result = $this->_rateResultFactory->create();

        if (!$this->getConfigFlag('active')) {
            return $result;
        }

        try {
            $rates = $this->quotingDataInitializer->getAvailableRates();
        } catch (LocalizedException $e) {
            $this->_logger->log(LogLevel::WARNING, $e->getMessage(), ['exception' => $e]);
            $error = $this->_rateErrorFactory->create(['data' => [
                'carrier' => $this->_code,
                'carrier_title' => $this->getConfigData('title'),
                'error_message' => $this->getConfigData('specificerrmsg'),
            ]]);

            $result->append($error);
        }

        if (empty($rates)) {
            return false;
        }

        foreach ($rates as $rate) {

            /** @var Method $method */
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($rate['code']);
            $method->setMethodTitle($rate['name']);

            $amount = !is_null($rate['custom_price']) ? $rate['custom_price'] : $rate['original_price'];

            $method->setPrice($amount);
            $method->setCost($rate['original_price']);

            $result->append($method);
        }

        if (empty($result->getAllRates())) {
            /** @var Error $error */
            $error = $this->_rateErrorFactory->create(['data' => [
                'carrier' => $this->_code,
                'carrier_title' => $this->getConfigData('title'),
                'error_message' => $this->getConfigData('specificerrmsg'),
            ]]);
            $result->append($error);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return false;
    }
}
