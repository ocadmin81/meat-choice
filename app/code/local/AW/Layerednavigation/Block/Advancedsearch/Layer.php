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


class AW_Layerednavigation_Block_Advancedsearch_Layer extends AW_Layerednavigation_Block_Layer
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
        return Mage::getSingleton('awadvancedsearch/layer');
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

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        $resultsBlock = $this->getLayout()->getBlock('search.result');
        if (
            !$resultsBlock
            || $resultsBlock->getCurrentType() != AW_Advancedsearch_Model_Source_Catalogindexes_Types::CATALOG
        ) {
            return false;
        }
        return parent::canShowBlock();
    }
}