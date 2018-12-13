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


class AW_Layerednavigation_Model_Filter_Type_Option_Attribute extends AW_Layerednavigation_Model_Filter_Type_Abstract
{
    protected $_setIds = array();

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
        $optionIds = array();
        foreach ($optionCollection as $optionItem) {
            $optionId = $optionItem->getData('additional_data/option_id');
            if (null === $optionId) {
                $optionId = $optionItem->getData('additional_data/option_value');
            }
            $optionIds[] = $optionId;
            $this->_currentValue[] = $optionItem;
        }

        if (count($optionIds) <= 0) {
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
        $this->_setIds = $collection->getSetIds();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value IN (?)", $optionIds)
        );

        $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_option');
        $collection->getSelect()->join(
            array($tableAlias => $tableName),
            implode(' AND ', $conditions),
            array()
        );
        $collection->getSelect()->distinct();
        if ($collection instanceof Enterprise_Search_Model_Resource_Collection) {
            $fieldName = Mage::getResourceSingleton('enterprise_search/engine')
                ->getSearchEngineFieldName($attribute, 'nav');
            $collection->addFqFilter(array($fieldName => $optionIds));
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        if ($this->_count === null) {
            $productCollection = $this->getFilter()->getLayer()->getProductCollection();

            // clone select from collection with filters
            /** @var Zend_Db_Select $select */
            $select = clone $productCollection->getSelect();
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

            $tableName = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_option');
            $select
                ->join(
                    array($tableAlias => $tableName),
                    join(' AND ', $conditions),
                    array(
                         'value',
                         'count' => new Zend_Db_Expr("COUNT(DISTINCT {$tableAlias}.entity_id)")
                    )
                )
                ->group("{$tableAlias}.value")
            ;
            if (empty($this->_setIds)) {
                $this->_setIds = $productCollection->getSetIds();
            }
            $setIds = implode(',', $this->_setIds);
            if (!empty($setIds)) {
                $select->join(
                    array('entity_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/entity_attribute')),
                    join(' AND ', array(
                        "entity_attribute.attribute_id = {$tableAlias}.attribute_id",
                        "entity_attribute.attribute_set_id = e.attribute_set_id"
                    ))
                );
                $select->where("e.attribute_set_id IN({$setIds}) ");
            }
            $countList = $connection->fetchPairs($select);

            $optionCollection = $this->getFilter()->getOptionCollection()->addIsEnabledFilter();
            $result = array();
            foreach ($optionCollection as $optionItem) {
                $attrOptionId = $optionItem->getData('additional_data/option_id');
                if (null === $attrOptionId) {
                    $attrOptionId = $optionItem->getData('additional_data/option_value');
                }
                if (null !== $attrOptionId && array_key_exists($attrOptionId, $countList)) {
                    $result[$optionItem->getId()] = $countList[$attrOptionId];
                } else {
                    $result[$optionItem->getId()] = 0;
                }
            }
            $this->_count = $result;
        }
        return $this->_count;
    }
}