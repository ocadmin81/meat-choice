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

class AW_Layerednavigation_Helper_Layer extends Mage_Core_Helper_Data
{
    protected $_initEnterpriseSearchCollection = false;

    /**
     * @param Enterprise_Search_Model_Search_Layer $layer
     *
     */
    public function initEnterpriseSearchLayerProductCollection($layer)
    {
        if (!$this->_initEnterpriseSearchCollection) {
            $productCollection = $layer->getProductCollection();
            if ($productCollection instanceof Enterprise_Search_Model_Resource_Collection) {
                $_collection = $this->_getEnterpriseSearchProductCollection();
                $_collection->setStoreId($productCollection->getStoreId())
                    ->addSearchFilter($productCollection->getExtendedSearchParams()['query_text'])
                    ->load()
                ;
                $productCollection->getSelect()->where('e.entity_id IN (?)', $_collection->getLoadedIds());
            }
            $this->_initEnterpriseSearchCollection = true;
        }
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $productCollection
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function getRemovedPriceFilterEnterpriseSearchCollection($productCollection) {
        return $this->getRemovedFieldFilterEnterpriseSearchCollection($productCollection, 'price');
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $productCollection
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return mixed
     */
    public function getRemovedFieldFilterEnterpriseSearchCollection($productCollection, $attribute) {
        $_collection = $this->_getEnterpriseSearchProductCollection();
        $_collection->setStoreId($productCollection->getStoreId());
        foreach ($productCollection->getExtendedSearchParams() as $param => $value) {
            if ($param == 'query_text') {
                $_collection->addSearchFilter($value);
            }
            else if ($param != Mage::getResourceSingleton('enterprise_search/engine')->getSearchEngineFieldName($attribute)) {
                $_collection->addFqFilter(array($param => $value));
            }
        }
        $_collection->getSelect()->setPart(
            Zend_Db_Select::FROM,
            $productCollection->getSelect()->getPart(Zend_Db_Select::FROM)
        );
        return $_collection->load();
    }

    protected function _getEnterpriseSearchProductCollection() {
        return Mage::helper('catalogsearch')->getEngine()->getResultCollection();
    }
}