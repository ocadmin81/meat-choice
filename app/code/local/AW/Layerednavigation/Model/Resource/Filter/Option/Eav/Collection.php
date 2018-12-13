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


class AW_Layerednavigation_Model_Resource_Filter_Option_Eav_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_layerednavigation/filter_option_eav');
    }

    /**
     * @param int|array $optionId
     *
     * @return AW_Layerednavigation_Model_Resource_Filter_Eav_Collection
     */
    public function addFilterOptionId($optionId)
    {
        if (is_numeric($optionId)) {
            $this->addFieldToFilter('option_id', array('eq' => (int)$optionId));
        } elseif (is_array($optionId)) {
            $this->addFieldToFilter('option_id', array('in' => $optionId));
        }
        return $this;
    }

    /**
     * @param string $attributeCode
     *
     * @return AW_Layerednavigation_Model_Resource_Filter_Eav_Collection
     */
    public function addFilterAttributeCode($attributeCode)
    {
        $this->addFieldToFilter('name', array('eq' => $attributeCode));
        return $this;
    }

    /**
     * Get attribute collection for store,
     * if for current store value does n't exists,
     * than default store value returned
     *
     * @param int $optionId
     * @param int $storeId
     * @return AW_Layerednavigation_Model_Resource_Filter_Option_Eav_Collection
     */
    public function getAttributeCollectionByStoreId($optionId, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $this->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('eav_id', 'option_id', 'name'))
            ->where('main_table.option_id = ?', $optionId)
            ->where('main_table.store_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->joinLeft(
                array('current_store_attribute' => $this->getTable('aw_layerednavigation/filter_option_eav')),
                "main_table.name = current_store_attribute.name
                AND main_table.option_id = current_store_attribute.option_id
                AND current_store_attribute.store_id = $storeId",
                array(
                     'value'    => 'IFNULL(current_store_attribute.value, main_table.value)',
                     'store_id' => "IFNULL(current_store_attribute.store_id, main_table.store_id)",
                )
            )
        ;
        return $this;
    }
}