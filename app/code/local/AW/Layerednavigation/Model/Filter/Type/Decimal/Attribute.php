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


class AW_Layerednavigation_Model_Filter_Type_Decimal_Attribute extends AW_Layerednavigation_Model_Filter_Type_Abstract
{
    protected $_whereCondition = null;
    protected $_extendedSearchParam = null;

    public function apply(Zend_Controller_Request_Abstract $request)
    {
        $this->_currentValue = array();

        $value = $request->getParam($this->getFilter()->getCode(), null);
        if (null === $value) {
            return $this;
        }

        $value = explode(',', $value);

        $rangeList = array();
        switch ($this->getFilter()->getData('display_type')) {
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE:
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::CHECKBOX_CODE:
                $optionCollection = Mage::getModel('aw_layerednavigation/filter_option')->getCollection();
                $optionCollection->addFieldToFilter('option_id', array('in' => $value));

                foreach ($optionCollection as $optionItem) {
                    $rangeList[] = array(
                        'from' => $optionItem->getData('additional_data/from'),
                        'to'   => $optionItem->getData('additional_data/to'),
                    );
                    $this->_currentValue[] = $optionItem;
                }

                if (count($rangeList) <= 0) {
                    return $this;
                }
                break;
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::FROM_TO_CODE:
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::RANGE_CODE:
                $rangeList[] = array(
                    'from' => min($value),
                    'to'   => max($value),
                );
                $this->_currentValue = current($rangeList);
                break;
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $attribute = Mage::getModel('catalog/resource_eav_attribute')
            ->loadByCode(
                Mage_Catalog_Model_Product::ENTITY,
                $this->getFilter()->getData('additional_data/attribute_code')
            )
        ;

        $collection = $this->getFilter()->getLayer()->getProductCollection();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId())
        );

        $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_decimal');
        $collection->getSelect()->join(
            array($tableAlias => $tableName),
            join(' AND ', $conditions),
            array()
        );

        $rangeConditions = array();
        foreach ($rangeList as $range) {
            $rangeConditions[] =
                $connection->quoteInto(
                    "{$tableAlias}.value >= ?",
                    intval($range['from'])
                )
                . " AND "
                . $connection->quoteInto(
                    "{$tableAlias}.value <= ?",
                    intval($range['to'])
                )
            ;
        }

        $this->_whereCondition = join(' OR ', $rangeConditions);
        $collection->getSelect()->where($this->_whereCondition);
        $collection->getSelect()->distinct();
        if ($collection instanceof Enterprise_Search_Model_Resource_Collection) {
            foreach ($rangeList as $range) {
                $fieldName = Mage::getResourceSingleton('enterprise_search/engine')
                    ->getSearchEngineFieldName($attribute);
                $this->_extendedSearchParam = array($fieldName => $range);
                $collection->addFqFilter($this->_extendedSearchParam);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getCount()
    {
        if ($this->_count === null) {
            switch ($this->getFilter()->getDisplayType()) {
                case AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE:
                case AW_Layerednavigation_Model_Source_Filter_Display_Type::CHECKBOX_CODE:
                    $this->_count = $this->_getRangeCountList();
                    break;
                case AW_Layerednavigation_Model_Source_Filter_Display_Type::FROM_TO_CODE:
                case AW_Layerednavigation_Model_Source_Filter_Display_Type::RANGE_CODE:
                    $result = $this->_getMaxMinValueList();
                    //don't show block if range equals
                    if (intval($result['min']) === intval($result['max'])) {
                        $result['min'] = null;
                        $result['max'] = null;
                    }
                    $this->_count = $result;
                    break;
                default:
                    $this->_count = array();
            }
        }
        return $this->_count;
    }

    protected function _getRangeCountList()
    {
        // clone select from collection with filters
        /** @var Zend_Db_Select $select */
        $select = clone $this->getFilter()->getLayer()->getProductCollection()->getSelect();

        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $wherePartList = $select->getPart(Zend_Db_Select::WHERE);
        foreach ($wherePartList as $conditionKey => $whereCondition) {
            if (stripos($whereCondition, $this->_whereCondition) !== false) {
                unset($wherePartList[$conditionKey]);

                if (!empty($wherePartList)) {
                    $wherePartList = array_values($wherePartList);
                    $__and = Zend_Db_Select::SQL_AND.' ';
                    $__or = Zend_Db_Select::SQL_OR.' ';
                    if (mb_stripos($wherePartList[0], $__and) === 0) {
                        $wherePartList[0] = mb_substr($wherePartList[0], mb_strlen($__and));
                    }
                    elseif (mb_stripos($wherePartList[0], $__or) === 0) {
                        $wherePartList[0] = mb_substr($wherePartList[0], mb_strlen($__or));
                    }
                }

                $select->setPart(Zend_Db_Select::WHERE, $wherePartList);
                continue;
            }
        }

        $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode(
            Mage_Catalog_Model_Product::ENTITY, $this->getFilter()->getData('additional_data/attribute_code')
        );
        $storeId = $this->getFilter()->getLayer()->getProductCollection()->getStoreId();

        $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_decimal');
        $select->join(
            array('decimal_index' => $tableName),
            "e.entity_id=decimal_index.entity_id AND decimal_index.attribute_id={$attribute->getId()}"
            . " AND decimal_index.store_id={$storeId}",
            array()
        );

        $select->columns(
            array(
                 'count' => new Zend_Db_Expr("COUNT(*)")
            )
        );


        /* @var Varien_Db_Adapter_Pdo_Mysql $connection*/
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $optionCollection = $this->getFilter()->getOptionCollection()->addIsEnabledFilter();
        $result = array();
        foreach ($optionCollection as $optionItem) {
            $result[$optionItem->getId()] = 0;
            $rangeFrom = (int)$optionItem->getData('additional_data/from');
            $rangeTo = (int)$optionItem->getData('additional_data/to');
            $selectPerOption = clone $select;
            $selectPerOption
                ->where(
                    "CAST(decimal_index.value as SIGNED) <= ?", $rangeTo
                )
                ->where(
                    "CAST(decimal_index.value as SIGNED) >= ?", $rangeFrom
                )
            ;
            $count = $connection->fetchCol($selectPerOption);
            $result[$optionItem->getId()] = intval(array_pop($count));
        }
        return array_filter($result);
    }

    protected function _getMaxMinValueList()
    {
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode(
            Mage_Catalog_Model_Product::ENTITY, $this->getFilter()->getData('additional_data/attribute_code')
        );
        $collection = $this->getFilter()->getLayer()->getProductCollection();
        if ($collection instanceof Enterprise_Search_Model_Resource_Collection &&
            $this->getFilter()->getLayer() instanceof Enterprise_Search_Model_Search_Layer &&
            !is_null($this->_extendedSearchParam)
        ) {
            $collection = Mage::helper('aw_layerednavigation/layer')->getRemovedFieldFilterEnterpriseSearchCollection($collection, $attribute);
        }
        $select = clone $collection->getSelect();

        /* @var Zend_Db_Select $select*/
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $wherePartList = $select->getPart(Zend_Db_Select::WHERE);
        foreach ($wherePartList as $conditionKey => $whereCondition) {
            if (stripos($whereCondition, $this->_whereCondition) !== false) {
                if ($conditionKey > 0) {
                    unset($wherePartList[$conditionKey]);
                } else {
                    $wherePartList[$conditionKey] = "1=1"; //condition removing
                }
                $select->setPart(Zend_Db_Select::WHERE, $wherePartList);
                continue;
            }
        }
        /* @var Varien_Db_Adapter_Pdo_Mysql $connection*/
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $storeId = $this->getFilter()->getLayer()->getProductCollection()->getStoreId();

        $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_decimal');
        $select->join(
            array('decimal_index' => $tableName),
            "e.entity_id=decimal_index.entity_id AND decimal_index.attribute_id={$attribute->getId()}"
            . " AND decimal_index.store_id={$storeId}",
            array()
        );
        $select->columns(
            array(
                 'min' => new Zend_Db_Expr('MIN(CAST(decimal_index.value as SIGNED))'),
                 'max' => new Zend_Db_Expr('MAX(CAST(decimal_index.value as SIGNED))'),
            )
        );
        return $connection->fetchRow($select);
    }
}
