<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Controller\Webhook;

use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\WebService\TrackingService;
use Envioskanguro\Shipping\Model\Actions\OrderActions;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

use Magento\Framework\Controller\Result\JsonFactory;


class Index extends Action implements CsrfAwareActionInterface
{
    /**
     * @var Logger $logger
     * 
     */
    protected $logger;

    /**
     * @var TrackingService $trackingService
     */
    protected $trackingService;

    /**
     * @var JsonFactory $jsonResultFactory
     */
    protected $jsonResultFactory;

    /**
     * @var OrderActions $orderActions
     */
    protected $orderActions;

    public function __construct(
        Logger $logger,
        Context $context,
        TrackingService $trackingService,
        JsonFactory $jsonResultFactory,
        OrderActions $orderActions
    ) {
        $this->logger = $logger;
        $this->trackingService = $trackingService;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->orderActions = $orderActions;

        parent::__construct($context);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $json = $this->getRequest()->getContent();
        $result = $this->jsonResultFactory->create();

        if ($json = json_decode($json)) {
            
            $trackingNumber = $json->data->tracking_number;
            
            $this->logger->debug('Webhook Request: ' . var_export($json, true));            
            $this->orderActions->executeByTrackingNumber($trackingNumber);

            $this->trackingService->downloadTracking($trackingNumber);

            $data = [
                'message' => 'success',
                'tracking' => $trackingNumber
            ];

            $result->setData($data);
        } else {
            $data = [
                'message' => 'No tracking information.',
                'tracking' => null
            ];
            $result->setData($data);
        }

        $result->setData($data);
        return $result;
    }
}
