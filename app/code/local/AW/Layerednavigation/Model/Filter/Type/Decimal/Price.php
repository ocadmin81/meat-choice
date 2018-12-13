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


class AW_Layerednavigation_Model_Filter_Type_Decimal_Price extends AW_Layerednavigation_Model_Filter_Type_Abstract
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
        $collection = $this->getFilter()->getLayer()->getProductCollection();

        $currencyRate = floatval(Mage::app()->getStore()->getCurrentCurrencyRate());
        $reverseCurrencyRate = 1/$currencyRate;
        $rangeConditions = array();
        foreach ($rangeList as $range) {
            $rangeConditions[] = join(
                " AND ",
                array(
                     $connection->quoteInto(
                         "{$this->_getPriceExpression()} >= ?", $this->_getPriceConditionValue($range['from'], $reverseCurrencyRate)
                     ),
                     $connection->quoteInto(
                         "{$this->_getPriceExpression()} <= ?", $this->_getPriceConditionValue($range['to'], $reverseCurrencyRate)
                     )
                )
            );
        }

        $this->_whereCondition = join(' OR ', $rangeConditions);
        $collection->getSelect()->where($this->_whereCondition);
        if ($collection instanceof Enterprise_Search_Model_Resource_Collection) {
            foreach ($rangeList as $range) {
                $fieldName = Mage::getResourceSingleton('enterprise_search/engine')
                    ->getSearchEngineFieldName('price');
                $this->_extendedSearchParam = array($fieldName => array(
                    'from' => $this->_getPriceConditionValue($range['from'], $reverseCurrencyRate),
                    'to' => $this->_getPriceConditionValue($range['to'], $reverseCurrencyRate)
                ));
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

        // reset
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $wherePartList = $select->getPart(Zend_Db_Select::WHERE);
        foreach ($wherePartList as $conditionKey => $whereCondition) {
            if (stripos($whereCondition, $this->_whereCondition) !== false) {
                unset($wherePartList[$conditionKey]);
                $select->setPart(Zend_Db_Select::WHERE, $wherePartList);
                continue;
            }
        }

        $currencyRate = Mage::app()->getStore()->getCurrentCurrencyRate();
        $priceExpression = new Zend_Db_Expr(
            "ROUND({$this->_getPriceExpression()} * {$currencyRate}, 2)"
        );
        $select->columns(array('entity_id', 'min_price' => $priceExpression));

        $connection = Mage::getSingleton('core/resource')->getConnection('read');

        $prices = $connection->fetchPairs($select);

        $optionCollection = $this->getFilter()->getOptionCollection()->addIsEnabledFilter();
        $result = array();
        foreach ($optionCollection as $optionItem) {
            $result[$optionItem->getId()] = 0;
            $rangeFrom = (int)$optionItem->getData('additional_data/from');
            $rangeTo = (int)$optionItem->getData('additional_data/to');
            foreach ($prices as $value) {
                if ($value >= $rangeFrom && $value <= $rangeTo) {
                    $result[$optionItem->getId()] += 1;
                }
            }
        }
        return array_filter($result);
    }

    protected function _getMaxMinValueList()
    {
        $collection = $this->getFilter()->getLayer()->getProductCollection();
        if ($collection instanceof Enterprise_Search_Model_Resource_Collection &&
            $this->getFilter()->getLayer() instanceof Enterprise_Search_Model_Search_Layer &&
            !is_null($this->_extendedSearchParam)
        ) {
            $collection = Mage::helper('aw_layerednavigation/layer')->getRemovedPriceFilterEnterpriseSearchCollection($collection);
        }

        $select = clone $collection->getSelect();

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
        $select->columns(
            array(
                'min' => "MIN({$this->_getPriceExpression()})",
                'max' => "MAX({$this->_getPriceExpression()})",
            )
        );
        $select->where('price_index.min_price IS NOT NULL');

        $connection = Mage::getSingleton('core/resource')->getConnection('read');

        $result = $connection->fetchRow($select);
        $currencyRate = Mage::app()->getStore()->getCurrentCurrencyRate();
        if ($result) {
            $result['min'] = AW_Layerednavigation_Helper_Data::roundHalfDown($result['min'] * $currencyRate, 2);
            $result['max'] = AW_Layerednavigation_Helper_Data::roundHalfUp($result['max'] * $currencyRate, 2);
        }
        return $result;
    }

    protected function _getPriceExpression()
    {
        return 'price_index.min_price' . Mage::helper('tax')->getPriceTaxSql(
            'price_index.min_price', 'price_index.tax_class_id'
        );
    }

    protected function _getPriceConditionValue($value, $reverseCurrencyRate) {
        return floatval(intval($value) * $reverseCurrencyRate);
    }
}