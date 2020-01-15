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

use Magento\Checkout\Model\Session;
use Envioskanguro\Shipping\WebService\RateRequest\Storage;

use Envioskanguro\Shipping\WebService\TrackingService;

use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

use Envioskanguro\Shipping\WebService\RateRequest\QuotingDataInitializer;

class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string $_code
     */
    protected $_code = 'envioskanguro';

    /**
     * @var StatusFactory
     */
    private $trackStatusFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

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
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * Shipping constructor.
     * 
     * @param Storage $storage,
     * @param QuotingDataInitializer $quotingDataInitializer
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param StatusFactory $trackStatusFactory
     * @param MethodFactory $rateMethodFactory
     * @param Session $checkoutSession
     * @param array $data
     * 
     */
    public function __construct(
        Storage $storage,
        Session $checkoutSession,
        QuotingDataInitializer $quotingDataInitializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        StatusFactory $trackStatusFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->storage = $storage;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->quotingDataInitializer = $quotingDataInitializer;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->trackStatusFactory = $trackStatusFactory;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = [];
        $quote = $this->storage->getRateByCurrentQuote(
            $this->checkoutSession->getQuote()->getId()
        );
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
        return true;
    }

    /**
     * Get tracking information. Original return value annotation is misleading.
     *
     * @param string $trackingNumber
     * @return \Magento\Shipping\Model\Tracking\Result\AbstractResult
     */
    public function getTrackingInfo($trackingNumber)
    {
        /** @var \Magento\Shipping\Model\Tracking\Result\Status $tracking */
        $tracking = $this->trackStatusFactory->create();
        $tracking->setCarrier($this->_code);
        $tracking->setTracking($trackingNumber);

        try {
            $rate = $this->storage->getRateByTrackingNumber($trackingNumber);

            $carrierTitle = $this->getConfigData('title') . DIRECTORY_SEPARATOR . $rate->getShippingCode();
            $trackingUrl = $this->getTrackingUrl($rate->getTrackingNumber());

            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setUrl($trackingUrl);
            
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setUrl('');
        }

        return $tracking;
    }

    /**
     * Get Tracking File
     */
    private function getTrackingUrl($trackingNumber)
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB
            ) . 'pub' . TrackingService::TRACKING_FOLDER . '/' . $trackingNumber . '.pdf';
    }
}
