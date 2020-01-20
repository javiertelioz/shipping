<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService;

use Envioskanguro\Shipping\WebService\Api\Api;
use Envioskanguro\Shipping\Plugin\Logger\Logger;

use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Envioskanguro\Shipping\WebService\RateRequest\Storage;

class TrackingService
{
    /** 
     * Resource url
     */
    const RESOURCE_URL = 'order';
    /** 
     * Folder Envios Kanguro
     */
    const TRACKING_FOLDER = '/envios_kanguro';

    /**
     * @var Api $api
     */
    protected $api;

    /** 
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var File $file
     */
    protected $file;

    /**
     * @var DirectoryList $directory
     */
    protected $directory;

    /** 
     * @var ScopeConfig $scopeConfig
     */
    protected $scopeConfig;

    /** 
     * @var LabelGenerator $labelGenerator
     */
    protected $labelGenerator;

    /**
     * @var FileFactory $fileFactory
     */
    protected $fileFactory;

    /** 
     * @var Storage $storage
     */
    protected $storage;

    public function __construct(
        Api $api,
        File $file,
        Logger $logger,
        Storage $storage,
        DirectoryList $directory,
        FileFactory $fileFactory,
        LabelGenerator $labelGenerator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->api = $api;
        $this->file = $file;
        $this->logger = $logger;
        $this->storage = $storage;
        $this->directory = $directory;
        $this->fileFactory = $fileFactory;
        $this->scopeConfig = $scopeConfig;
        $this->labelGenerator = $labelGenerator;
    }

    /**
     * Download Tracking guide
     * 
     * @param string $trackingNumber
     */
    public function downloadTracking($trackingNumber)
    {
        $request = $this->api->get(
            self::RESOURCE_URL,
            ['tracking_number' => $trackingNumber]
        );

        if (isset($request['body']->data->file)) {
            $this->save($trackingNumber, $request['body']->data->file);
        }
    }

    /** 
     * Get Pdf
     * 
     * @param string $trackingNumber
     */
    public function get($trackingNumber)
    {
        $filePath = $this->getFilePath() . DIRECTORY_SEPARATOR . $trackingNumber . '.pdf';
        $file = file_get_contents($filePath);

        return $file;
    }

    /** 
     * Save Pdf
     * 
     * @param string $trackingNumber
     * @param string $pdfData
     */
    protected function save($trackingNumber, $pdfData)
    {
        $this->checkAndCreateFolder();
        $file = $this->getFilePath() . DIRECTORY_SEPARATOR . $trackingNumber . '.pdf';

        $pdf = base64_decode($pdfData);
        file_put_contents($file, $pdf);
    }

    /** 
     * Check if folder exist and return path
     */
    protected function getFilePath()
    {
        $trackingFolder = $this->directory->getPath('pub') . self::TRACKING_FOLDER;
        return $trackingFolder;
    }

    /** 
     * Check if folder exist 
     */
    protected function checkAndCreateFolder()
    {
        $this->file->checkAndCreateFolder($this->getFilePath());
    }
}
