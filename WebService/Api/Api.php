<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService\Api;

use EnviosKanguro\Api as EnviosKanguroApi;
use Envioskanguro\Shipping\Plugin\Logger\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Api extends EnviosKanguroApi
{
    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;

        $token = $this->scopeConfig->getValue('carriers/envioskanguro/token');
        $mode = $this->scopeConfig->getValue('carriers/envioskanguro/environment');

        parent::__construct($token, $mode);
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param array $body
     * @param array $params
     * @return mixed
     */
    public function post($path, $body = [], $params = [])
    {
        $this->debug($path, $body, $params);
        return parent::post($path, $body, $params);
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public function get($path, $params = null)
    {
        $this->debug($path, null, $params);
        return parent::get($path, $params);
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @return mixed
     */
    public function put($path, $body = [], $params)
    {
        $this->debug($path, $body, $params);
        return parent::put($path, $body, $params);
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public function delete($path, $params)
    {
        $this->debug($path, null,  $params);
        return parent::put($path, $params);
    }

    /**
     * Debug all Api Call
     */
    protected function debug($url, $body, $params = [])
    {
        $logging = $this->scopeConfig->getValue('carriers/envioskanguro/logging_enabled');

        if (!$logging) {
            return;
        }

        $params = is_array($params) ? var_export($params, true) : $params;
        $body = is_array($body) ? var_export($body, true) : $body;
        $this->logger->debug(
            PHP_EOL . 'Api Call: ' . $url . PHP_EOL . 'Body: ' . $body .
                PHP_EOL . 'Params: ' . $params
        );
    }
}
