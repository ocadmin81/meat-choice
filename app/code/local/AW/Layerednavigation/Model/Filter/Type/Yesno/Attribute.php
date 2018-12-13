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


class AW_Layerednavigation_Model_Filter_Type_Yesno_Attribute extends AW_Layerednavigation_Model_Filter_Type_Abstract
{
    public function apply(Zend_Controller_Request_Abstract $request)
    {
        $this->_currentValue = array();

        $value = $request->getParam($this->getFilter()->getCode(), null);
        if (null === $value) {
            return $this;
        }
        $value = explode(',', $value);

        $optionCollection = Mage::getModel('aw_layerednavigation/filter_option')->getCollection();
        $optionCollection->addFieldToFilter('option_id', array('in' => $value));
        $valueList = array();
        foreach ($optionCollection as $optionItem) {
            $valueList[] = $optionItem->getData('additional_data/value');
            $this->_currentValue[] = $optionItem;
        }

        if (count($valueList) <= 0) {
            return $this;
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $attribute = Mage::getModel('catalog/resource_eav_attribute')
            ->loadByCode(
                Mage_Catalog_Model_Product::ENTITY,
                $this->getFilter()->getData('additional_data/attribute_code')
            )
        ;

        /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection */
        $collection = $this->getFilter()->getLayer()->getProductCollection();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value IN (?)", $valueList)
        );

        $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_yesno');
        $collection->getSelect()->join(
            array($tableAlias => $tableName),
            implode(' AND ', $conditions),
            array()
        );
        $collection->getSelect()->distinct();
        if ($collection instanceof Enterprise_Search_Model_Resource_Collection) {
            $collection->addFqFilter(array('id' => $collection->getAllIds()));
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        if ($this->_count === null) {
            // clone select from collection with filters
            /** @var Zend_Db_Select $select */
            $select = clone $this->getFilter()->getLayer()->getProductCollection()->getSelect();
            // reset columns, order and limitation conditions
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::GROUP);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);

            $connection = Mage::getSingleton('core/resource')->getConnection('read');
            $attribute = Mage::getModel('catalog/resource_eav_attribute')
                ->loadByCode(
                    Mage_Catalog_Model_Product::ENTITY,
                    $this->getFilter()->getData('additional_data/attribute_code')
                )
            ;
            $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
            $conditions = array(
                "{$tableAlias}.entity_id = e.entity_id",
                $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                $connection->quoteInto("{$tableAlias}.store_id = ?", $this->getFilter()->getStoreId()),
            );

            $fromPart = $select->getPart(Zend_Db_Select::FROM);
            if (array_key_exists($tableAlias, $fromPart)) {
                unset($fromPart[$tableAlias]);
                $select->setPart(Zend_Db_Select::FROM, $fromPart);
            }

            $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_yesno');
            $select
                ->join(
                    array($tableAlias => $tableName),
                    join(' AND ', $conditions),
                    array(
                        'value',
                        'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")
                    )
                )
                ->group("{$tableAlias}.value")
            ;
            $countList = $connection->fetchPairs($select);

            $optionCollection = $this->getFilter()->getOptionCollection()->addIsEnabledFilter();
            $result = array();
            foreach ($optionCollection as $optionItem) {
                $valueOptionId = $optionItem->getData('additional_data/value');
                if (array_key_exists($valueOptionId, $countList)) {
                    $result[$optionItem->getId()] = $countList[$valueOptionId];
                }
            }
            $this->_count = $result;
        }
        return $this->_count;
    }
}