<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService;

use EnviosKanguro\Api;
use Envioskanguro\Shipping\WebService\Mapping\RateInterface;

use Envioskanguro\Shipping\WebService\Mode;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Rate implements RateInterface
{
    /**
     * @var Api
     */
    protected $client;

    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /** 
     * @var Mode
     */
    protected $mode;

    /**
     * @var Registry
     */
    protected $_registry;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Mode $mode/*,
        \Magento\Framework\Registry $registry*/
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->mode = $mode;

        $token = $this->scopeConfig->getValue('carriers/envioskanguro/token');
        $environment = $this->scopeConfig->getValue('carriers/envioskanguro/environment');
        
        $this->client = new Api($token, $environment);

        //$this->registry = $registry;
    }

    /**
     * Create a order for quoting purposes.
     *
     * The order is being built from the quote and rate request.
     * 
     * @param $quotingData
     */
    public function getRates($quotingData): array
    {
        /*$zip = $quotingData['destination']['zip'];
        $regiter_zip = $this->_registry->registry('current_zip');

        if(!empty($zip) && $zip == $regiter_zip ){
            $rates = $this->_registry->registry('Rates');
        } else {*/
            $rates = $this->client->post('rate', $quotingData);
            /*$this->registry->register($rates, 'Rates');
            $this->registry->register($quotingData['destination']['zip'], 'current_zip');
        }*/
        
        // $this->logger->debug(var_export($rates['body']->data, true));

        if (isset($rates['body']->data)) {
            return $this->mapping($rates['body']->data);
        }

        return [];
    }

    /**
     * Generate Code
     * 
     * @return string code
     */
    protected function generateCode($name) {
        return strtolower(strtok($name, " "));
    }

    /** 
     * Mapping Request
     * 
     * @return array
     */
    protected function mapping($rates)
    {
        $available = [];

        foreach ($rates->rates as $rate) {
            $available[] = [
                'code'      => $this->generateCode($rate->name),
                'name'      => $rate->name,
                'best'      => $rate->best,
                'rate_id'   => $rate->id,
                'quote_id'  => $rates->id,
                'ranking'   => $rate->ranking,
                'original_price' => $rate->total_price,
                'custom_price' => null,
                'created_at' => $rates->created_at,
            ];
        }

        return $this->mode->getMethods($available);  
    }
}
