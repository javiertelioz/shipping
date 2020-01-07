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

namespace Envioskanguro\Shipping\Model\Config\Backend;

use \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /** 
     * CollectionFactory
     */
    protected $attributeCollectionFactory;

    public function __construct(
        CollectionFactory $attributecollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributecollectionFactory;
    }

    protected function getAllAttributes()
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addVisibleFilter();

        return $attributeCollection->getItems();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        
        $options[] = array(
            'value' => '',
            'label' => 'Default Value',
        );
        foreach ($this->getAllAttributes() as $attribute) {

            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
            );

        }
        return $options;
    }
}
