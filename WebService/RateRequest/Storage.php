<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Psr\Log\LoggerInterface;

use Magento\Checkout\Model\Session;
use Envioskanguro\Shipping\Model\RateFactory;

class Storage
{
    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var RateFactory rateFactory
     */
    protected $rateFactory;

    /**
     * @param RateFactory $rateFactory,
     */
    public function __construct(
        LoggerInterface $logger,
        Session $checkoutSession,
        RateFactory $rateFactory
    ) {
        $this->logger = $logger;
        $this->rateFactory = $rateFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Setting Quote in database to be used
     * @param integer $quoteId
     * @param array $rates
     */
    public function setRates($quoteId, $rates)
    {
        $flag = $this->getRateByCurrentQuote()->getId();
        $model = $this->rateFactory->create();
        $rateData = [
            "content" => serialize($rates),
            "quote_id" => $quoteId,
            "session_id" => $this->checkoutSession->getQuote()->getId(),
        ];

        if ($flag === null) {
            $model->addData($rateData);
            $model->save();
        } else {
            $model->load($flag);
            $model->addData($rateData);
            $model->save();
        }
    }

    /**
     * Retrieving Quote from database
     * @return mixed
     */
    public function getRateByCurrentQuote()
    {
        $model = $this->rateFactory->create();
        $quoteId = $this->checkoutSession->getQuote()->getId();

        return $model->load($quoteId, 'session_id');
    }
}
