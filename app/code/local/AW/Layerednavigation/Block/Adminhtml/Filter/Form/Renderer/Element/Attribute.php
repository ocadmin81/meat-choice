<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Layerednavigation
 * @version    1.3.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Layerednavigation_Block_Adminhtml_Filter_Form_Renderer_Element_Attribute
    extends AW_Layerednavigation_Block_Adminhtml_Filter_Form_Renderer_Element_Abstract
{
    /**
     * @return string
     */
    public function getUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl(
            'adminhtml/catalog_product_attribute/edit', array('attribute_id' => $this->getAttributeId())
        );
    }

    /**
     * @return string
     */
    public function getUrlLabel()
    {
        $additionalData = $this->getValue();
        if (!is_array($additionalData) || !array_key_exists('attribute_code', $additionalData)) {
            return Mage::helper('aw_layerednavigation')->__('Edit Attribute');
        }
        return $additionalData['attribute_code'];
    }

    /**
     * @return int|null
     */
    public function getAttributeId()
    {
        $additionalData = $this->getValue();
        if (!is_array($additionalData) || !array_key_exists('attribute_code', $additionalData)) {
            return null;
        }
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode(
            Mage_Catalog_Model_Product::ENTITY, $additionalData['attribute_code']
        );
        return $attribute->getId();
    }
}