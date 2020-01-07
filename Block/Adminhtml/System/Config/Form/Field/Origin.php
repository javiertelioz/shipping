<?php

/**
 * Envios Kanguro Shipping
 * Refer to LICENSE.txt distributed with the module for notice of license
 *
 * @package Envioskanguro\Shipping\Block
 * @author  Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://envioskanguro.com/
 * @api
 */

namespace Envioskanguro\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Module\PackageInfo;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Origin extends Field
{
    const ORIGIN_TEXT = 'Source Address';
    /**
     * Version constructor.
     *
     * @param Context $context
     * @param PackageInfo $packageInfo
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('text', self::ORIGIN_TEXT);

        return parent::_getElementHtml($element);
    }
}
