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


class AW_Layerednavigation_Block_Adminhtml_Catalog_Attribute_Js extends Mage_Core_Block_Template
{
    public function isAllowedAttribute()
    {
        $attribute = Mage::registry('entity_attribute');
        $optionSynchModelName = Mage::helper('aw_layerednavigation/type')->getSynchronizationModelNameByTypeCode(
            AW_Layerednavigation_Model_Source_Filter_Type::OPTION_ATTRIBUTE_CODE
        );
        $optionSynch =  Mage::getModel($optionSynchModelName);
        $decimalSynchModelName = Mage::helper('aw_layerednavigation/type')->getSynchronizationModelNameByTypeCode(
            AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_ATTRIBUTE_CODE
        );
        $decimalSynch = Mage::getModel($decimalSynchModelName);
        $yesnoSynchModelName = Mage::helper('aw_layerednavigation/type')->getSynchronizationModelNameByTypeCode(
            AW_Layerednavigation_Model_Source_Filter_Type::YESNO_ATTRIBUTE_CODE
        );
        $yesnoSynch = Mage::getModel($yesnoSynchModelName);
        return $optionSynch->isAllowedAttribute($attribute)
            || $decimalSynch->isAllowedAttribute($attribute)
            || $yesnoSynch->isAllowedAttribute($attribute)
        ;
    }
}
