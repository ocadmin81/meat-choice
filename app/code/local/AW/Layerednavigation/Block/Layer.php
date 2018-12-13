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


class AW_Layerednavigation_Block_Layer extends Mage_Core_Block_Template
{
    /**
     * @var AW_Layerednavigation_Model_Resource_Filter_Collection
     */
    protected $_filterCollection = null;

    /**
     * @return string
     */
    public function getTemplate()
    {
        return "aw_layerednavigation/layer.phtml";
    }

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        if (count($this->getFilterList()) === 0) {
            return false;
        }
        foreach ($this->getFilterList() as $filter) {
            if ($this->getItemCountByFilter($filter) <= 0) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isLayerHasAppliedValue()
    {
        foreach ($this->getFilterList() as $filter) {
            if ($this->isFilterHasAppliedValues($filter)) {
                return true;
            }
        }
        return false;
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
            ->addCategoryFilter($this->getCurrentCategory())
            ->sortByPosition()
        ;
        foreach ($this->_filterCollection as $filter) {
            $filter->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_filterCollection;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return number
     */
    public function getItemCountByFilter(AW_Layerednavigation_Model_Filter $filter)
    {
        return array_sum($filter->getCount());
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return bool
     */
    public function isFilterHasAppliedValues(AW_Layerednavigation_Model_Filter $filter)
    {
        return count($filter->getCurrentValue()) > 0;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return string
     */
    public function renderHelp(AW_Layerednavigation_Model_Filter $filter)
    {
        $block = Mage::helper('aw_layerednavigation/filter')->createFrontendFilterHelpRendererBlock($filter);
        return $block->toHtml();
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return string
     */
    public function renderFilter(AW_Layerednavigation_Model_Filter $filter)
    {
        $block = Mage::helper('aw_layerednavigation/filter')->createFrontendFilterRendererBlock($filter);
        return $block->toHtml();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getLayer()
    {
        if (@class_exists("Enterprise_Search_Helper_Data")) {
            $layer = Mage::registry('current_layer');
            if ($layer) {
                return $layer;
            }
            $helper = Mage::helper('enterprise_search');
            if ($helper->isThirdPartSearchEngine() && $helper->isActiveEngine() && $helper->getIsEngineAvailableForNavigation()) {
                return Mage::getSingleton('enterprise_search/catalog_layer');
            }
        }
        return Mage::getSingleton('catalog/layer');
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        return Mage::getSingleton('catalog/layer')->getCurrentCategory();
    }

    /**
     * @return boolean
     */
    public function isAjax()
    {
        return Mage::helper('aw_layerednavigation/config')->isUseAjax();
    }

    public function canAddNonFilteredParams() {
        return !($this->getLayer() instanceof Enterprise_Search_Model_Catalog_Layer);
    }

    public function getSearchQueryParamCode() {
        return Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME;
    }

    public function getOverlaySettings()
    {
        return array(
            'color'         => Mage::helper('aw_layerednavigation/config')->getOverlayColor(),
            'opacity'       => Mage::helper('aw_layerednavigation/config')->getOverlayOpacity(),
            'image'         => Mage::helper('aw_layerednavigation/config')->getOverlayImage(),
            'imageWidth'    => Mage::helper('aw_layerednavigation/config')->getOverlayImageHeight(),
            'imageHeight'   => Mage::helper('aw_layerednavigation/config')->getOverlayImageWidth(),
        );
    }

    /**
     * @return AW_Layerednavigation_Block_Layer
     */
    protected function _prepareLayout()
    {
        foreach ($this->getFilterList() as $filter) {
            $filter
                ->setLayer($this->getLayer())
                ->apply($this->getRequest());
            ;
        }
        foreach ($this->getFilterList() as $filter) {
            $filter->getCount();
        }
        return parent::_prepareLayout();
    }
}