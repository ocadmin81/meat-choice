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


class AW_Layerednavigation_Block_Catalogsearch_Layer extends AW_Layerednavigation_Block_Layer
{
    protected function _construct()
    {
        parent::_construct();
        Mage::register('current_layer', $this->getLayer(), true);
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getLayer()
    {
        /**
         * "@" added for fix php warnings on include file during autoload
         * because magento does not check file on exists
         */
        if (@class_exists("Enterprise_Search_Helper_Data")) {
            $helper = Mage::helper('enterprise_search');
            if ($helper->isThirdPartSearchEngine() && $helper->isActiveEngine()) {
                $layer = Mage::getSingleton('enterprise_search/search_layer');
                Mage::helper('aw_layerednavigation/layer')->initEnterpriseSearchLayerProductCollection($layer);
                return $layer;
            }
        }
        return Mage::getSingleton('catalogsearch/layer');
    }

    /**
     * @return AW_Layerednavigation_Model_Resource_Filter_Collection|null
     */
    public function getFilterList()
    {
        if (null !== $this->_filterCollection) {
            return $this->_filterCollection;
        }
        $this->_filterCollection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
            ->addFilterAttributes(Mage::app()->getStore()->getId())
            ->addIsEnabledFilter()
            ->addIsEnabledInSearchFilter()
            ->sortByPosition()
        ;
        foreach ($this->_filterCollection as $filter) {
            $filter->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_filterCollection;
    }

    public function canAddNonFilteredParams() {
        return !($this->getLayer() instanceof Enterprise_Search_Model_Search_Layer);
    }
}