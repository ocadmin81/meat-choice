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


class AW_Layerednavigation_Model_Filter_Type_Category extends AW_Layerednavigation_Model_Filter_Type_Abstract
{
    /**
     * @param Zend_Controller_Request_Abstract $request
     *
     * @return $this|AW_Layerednavigation_Model_Filter_Type_Abstract
     */
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
        foreach ($optionCollection as $optionItem) {
            $this->_currentValue[] = $optionItem;
        }

        if (count($this->_currentValue) <= 0) {
            return $this;
        }
        /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection */
        $collection = $this->getFilter()->getLayer()->getProductCollection();
        $this->_addCategoryFilter($collection);
        return $this;
    }

    /**
     * @return array
     */
    public function getCount()
    {
        if ($this->_count === null) {
            $currentCategory = $this->getFilter()->getLayer()->getCurrentCategory();
            $optionCategoryMap = array();
            $optionCollection = $this->getFilter()->getOptionCollection()->addIsEnabledFilter();
            foreach ($optionCollection as $optionItem) {
                $path = explode('/', $optionItem->getData('additional_data/path'));
                $categoryId = $optionItem->getData('additional_data/category_id');
                if (!in_array($currentCategory->getId(), $path) || $currentCategory->getId() == $categoryId) {
                    continue;
                }
                $optionCategoryMap[$categoryId] = $optionItem->getId();
            }

            $categoryCollection = $this->_getCategoryCollection(array_keys($optionCategoryMap));
            $productCollection = clone $this->getFilter()->getLayer()->getProductCollection();

            // Reset category filter for count
            $select = $productCollection->getSelect();
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::GROUP);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);

            $tableAlias = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_category');
            $fromPart = $select->getPart(Zend_Db_Select::FROM);
            if (array_key_exists($tableAlias, $fromPart)) {
                unset($fromPart[$tableAlias]);
                $select->setPart(Zend_Db_Select::FROM, $fromPart);
            }

            if ($this->getFilter()->getLayer() instanceof Mage_CatalogSearch_Model_Layer
                || $this->getFilter()->getLayer() instanceof AW_Advancedsearch_Model_Layer
            ) {
                $fromSelectPart = $productCollection->getSelect()->getPart(Zend_Db_Select::FROM);

                unset($fromSelectPart['cat_index']);
                if (isset($fromSelectPart['perm'])) {
                    unset($fromSelectPart['perm']);
                }
                $productCollection->getSelect()->setPart(Zend_Db_Select::FROM, $fromSelectPart);
                $columnSelectPart = $productCollection->getSelect()->getPart(Zend_Db_Select::COLUMNS);
                foreach ($columnSelectPart as $key => $column) {
                    if ($column[0] == 'cat_index' || $column[0] == 'perm') {
                        unset($columnSelectPart[$key]);
                    }
                }
                $productCollection->getSelect()->setPart(Zend_Db_Select::COLUMNS, $columnSelectPart);
                $whereSelectPart = $productCollection->getSelect()->getPart(Zend_Db_Select::WHERE);
                foreach ($whereSelectPart as $key => $column) {
                    if (strpos($column,'cat_index') || strpos($column,'perm')) {
                        unset($whereSelectPart[$key]);
                    }
                }

                $productCollection->getSelect()->setPart(Zend_Db_Select::WHERE, $whereSelectPart);
            } else if (!$this->getFilter()->getLayer() instanceof AW_Shopbybrand_Model_Layer) {
                $productCollection
                    ->addCategoryFilter(
                        $this->getFilter()->getLayer()->getCurrentCategory()
                    )
                ;
            }
            $this->_addCountToCategories($productCollection, $categoryCollection);
            $this->_addCategoryFilter($productCollection);

            $result = array();
            foreach ($categoryCollection as $category) {
                if (!$category->getProductCount()) {
                    continue;
                }
                $result[$optionCategoryMap[$category->getId()]] = $category->getProductCount();
            }
            $this->_count = $result;
        }
        return $this->_count;
    }

    /**
     * @param array $ids
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getCategoryCollection($ids)
    {
        $collection = Mage::getModel('catalog/category')->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection
            ->addAttributeToSelect('is_anchor')
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
        ;
        if (count($ids) == 0) {
            $ids = array(0);//if no ids => make filter on no result
        }
        if (Mage::helper('core')->isModuleOutputEnabled('AW_Catalogpermissions')) {
            //Catalog permissions by AW compatibility
            $mainTableAliasList = array_keys($collection->getSelect()->getPart(Zend_Db_Select::FROM));
            $collection->getSelect()->where(
                $mainTableAliasList[0] . '.entity_id  in (?)',
                new Zend_Db_Expr(join(',', $ids))
            );
        } else {
            $collection->addIdFilter($ids);
        }
        return $collection->load();
    }

    protected function _addCategoryFilter($productCollection)
    {
        if (count($this->_currentValue) <= 0) {
            return $this;
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $tableAlias = Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_category');

        $categoryConditionList = array();
        foreach ($this->_currentValue as $currentValue) {
            $categoryConditionList[] = $connection->quoteInto(
                "FIND_IN_SET(?, {$tableAlias}.category_ids)", $currentValue->getData('additional_data/category_id')
            );
        }

        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
            '(' . implode(' OR ', $categoryConditionList) . ')'
        );

        $productCollection->getSelect()->join(
            array($tableAlias => Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation/filter_index_category')),
            implode(' AND ', $conditions),
            array()
        );

        $productCollection->getSelect()->distinct();

        if ($productCollection instanceof Enterprise_Search_Model_Resource_Collection) {
            $categoryFilter = array();
            foreach ($this->_currentValue as $currentValue) {
                $categoryFilter[] = $currentValue->getData('additional_data/category_id');
            }
            $productCollection->addFqFilter(array('category_ids' => $categoryFilter));
        }
        return $this;
    }

    protected function _addCountToCategories($productCollection, $categoryCollection) {
        if ($productCollection instanceof Enterprise_Search_Model_Resource_Collection) {
            $_productCollection = clone $productCollection;

            $isAnchor    = array();
            $isNotAnchor = array();
            foreach ($categoryCollection as $category) {
                if ($category->getIsAnchor()) {
                    $isAnchor[]    = $category->getId();
                } else {
                    $isNotAnchor[] = $category->getId();
                }
            }
            $productCounts = array();
            if ($isAnchor || $isNotAnchor) {
                $select = $_productCollection->getProductCountSelect();

                if ($isAnchor) {
                    $anchorStmt = clone $select;
                    $anchorStmt->limit(); //reset limits
                    $anchorStmt->where('count_table.category_id IN (?)', $isAnchor);
                    $productCounts += $_productCollection->getConnection()->fetchPairs($anchorStmt);
                    $anchorStmt = null;
                }
                if ($isNotAnchor) {
                    $notAnchorStmt = clone $select;
                    $notAnchorStmt->limit(); //reset limits
                    $notAnchorStmt->where('count_table.category_id IN (?)', $isNotAnchor);
                    $notAnchorStmt->where('count_table.is_parent = 1');
                    $productCounts += $_productCollection->getConnection()->fetchPairs($notAnchorStmt);
                    $notAnchorStmt = null;
                }
                $select = null;
                $_productCollection->unsProductCountSelect();
            }

            foreach ($categoryCollection as $category) {
                $_count = 0;
                if (isset($productCounts[$category->getId()])) {
                    $_count = $productCounts[$category->getId()];
                }
                $category->setProductCount($_count);
            }
        }
        else {
            $productCollection->addCountToCategories($categoryCollection);
        }
    }
}