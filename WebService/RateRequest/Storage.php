<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Magento\Checkout\Model\Session;
use Envioskanguro\Shipping\Model\RateFactory;

class Storage
{
    /** 
     * @var Session $checkoutSession
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
        Session $checkoutSession,
        RateFactory $rateFactory
    ) {
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
        $sessionId = $this->checkoutSession->getQuote()->getId();
        $flag = $this->getRateByCurrentQuote($sessionId)->getId();

        $model = $this->rateFactory->create();
        $rateData = [
            "content" => serialize($rates),
            "quote_id" => $quoteId,
            "session_id" => $sessionId,
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
    public function getRateByCurrentQuote($quoteId)
    {
        $model = $this->rateFactory->create();

        return $model->load($quoteId, 'session_id');
    }

    /**
     * Update Quote from database
     * 
     * @param integer $orderId
     * @param array $quote
     * 
     * @return void
     */
    public function updateQuote($orderId, $quote)
    {
        $model = $this->rateFactory->create();

        $model->load($orderId, 'session_id');
        $model->addData($quote);
        $model->save();
    }
}
