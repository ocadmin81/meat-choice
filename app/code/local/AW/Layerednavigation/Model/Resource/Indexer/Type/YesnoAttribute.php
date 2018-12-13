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


class AW_Layerednavigation_Model_Resource_Indexer_Type_YesnoAttribute
    extends AW_Layerednavigation_Model_Resource_Indexer_Type_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter_index_yesno', 'entity_id');
    }

    protected function _validateAttribute($attribute)
    {
        $yesnoAttribute = AW_Layerednavigation_Model_Synchronization_Yesno_Attribute::ATTRIBUTE_TYPE_YESNO;
        if ($attribute->getFrontendInput() == $yesnoAttribute) {
            return true;
        }
        return false;
    }

    protected function _getAttributeCode()
    {
        return AW_Layerednavigation_Model_Source_Filter_Type::YESNO_ATTRIBUTE_CODE;
    }
}