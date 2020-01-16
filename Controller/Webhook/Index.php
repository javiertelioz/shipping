<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\WebService\TrackingService;


class Index extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
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

    public function __construct(
        Logger $logger,
        Context $context,
        TrackingService $trackingService,
        JsonFactory $jsonResultFactory
    ) {
        $this->logger = $logger;
        $this->trackingService = $trackingService;
        $this->jsonResultFactory = $jsonResultFactory;
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

            $this->logger->debug('Webhook Request: ' . var_export($json, true));

            $this->trackingService->downloadTracking($json->data->tracking_number);

            $data = [
                'message' => 'success',
                'tracking' => $json->data->tracking_number
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
