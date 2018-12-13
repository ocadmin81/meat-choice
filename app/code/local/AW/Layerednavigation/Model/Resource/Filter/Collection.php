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


class AW_Layerednavigation_Model_Resource_Filter_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_layerednavigation/filter');
    }

    /**
     * Add attribute values to collection as columns
     * if for current store value not exists
     * default value will be chosen
     *
     * @param int $storeId
     * @return AW_Layerednavigation_Model_Resource_Filter_Collection
     */
    public function addFilterAttributes($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        foreach (Mage::helper('aw_layerednavigation/attribute')->getFilterAttributes() as $attributeCode) {
            $defaultStoreAlias = $attributeCode . '_ds';
            $currentStoreAlias = $attributeCode . '_cs';
            $this->getSelect()
                ->joinLeft(
                    array($defaultStoreAlias => $this->getTable('aw_layerednavigation/filter_eav')),
                    "main_table.entity_id = {$defaultStoreAlias}.filter_id AND
                    {$defaultStoreAlias}.name = '{$attributeCode}' AND
                    {$defaultStoreAlias}.store_id = " . (int)Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                    array()
                )
                ->joinLeft(
                    array($currentStoreAlias => $this->getTable('aw_layerednavigation/filter_eav')),
                    "main_table.entity_id = {$currentStoreAlias}.filter_id
                    AND {$currentStoreAlias}.name = '{$attributeCode}'
                    AND {$currentStoreAlias}.name = {$defaultStoreAlias}.name
                    AND {$currentStoreAlias}.store_id = {$storeId}",
                    array($attributeCode => "IFNULL({$currentStoreAlias}.value, {$defaultStoreAlias}.value)")
                )
            ;
        }
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        $this->addFieldToFilter('type', array('eq' => $type));
        return $this;
    }

    /**
     * @return $this
     */
    public function addIsEnabledFilter()
    {
        $this->addFieldToFilter(
            'is_enabled',
            array('eq' => AW_Layerednavigation_Model_Source_Filter_Status::ENABLED_CODE)
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function addIsEnabledInSearchFilter()
    {
        $this->addFieldToFilter(
            'is_enabled_in_search',
            array('eq' => AW_Layerednavigation_Model_Source_Filter_Status::ENABLED_CODE)
        );
        return $this;
    }

    public function addCategoryFilter($category)
    {
        if ($category instanceof Mage_Catalog_Model_Category) {
            $categoryId = $category->getId();
        } elseif (is_numeric($category)) {
            $categoryId = $category;
        } else {
            return $this;
        }
        $finsetCondition = $this->_getConditionSql('category_ids', array('finset' => $categoryId));
        $this->getSelect()->where("{$finsetCondition} IS NULL OR NOT {$finsetCondition}");
        return $this;
    }

    /**
     * @return AW_Layerednavigation_Model_Resource_Filter_Option_Collection
     */
    public function sortByPosition()
    {
        $this->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);
        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'title':
                $this->getSelect()->where('IFNULL(title_cs.value, title_ds.value) LIKE ?', $condition['like']);
                return $this;
            case 'is_enabled':
                $this->getSelect()->where('IFNULL(is_enabled_cs.value, is_enabled_ds.value) = ?', $condition['eq']);
                return $this;
            case 'is_enabled_in_search':
                $this->getSelect()->where(
                    'IFNULL(is_enabled_in_search_cs.value, is_enabled_in_search_ds.value) = ?', $condition['eq']
                );
                return $this;
            default:
                parent::addFieldToFilter($field, $condition);
                return $this;
        }
    }

    /**
     * Process loaded collection data
     *
     * @return AW_Layerednavigation_Model_Resource_Filter_Collection
     */
    protected function _afterLoadData()
    {
        foreach ($this->getItems() as $item) {
            $this->getResource()->unserializeFields($item);
            $item->setDataChanges(false);
        }
        return parent::_afterLoadData();
    }
}