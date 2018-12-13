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


abstract class AW_Layerednavigation_Block_Filter_Type_Abstract extends Mage_Core_Block_Template
{
    protected $_filter = null;

    /**
     * @return AW_Layerednavigation_Model_Resource_Filter_Option_Collection
     */
    public function getOptionList()
    {
        $items = $this->getFilter()->getOptionCollection()->getItems();
        $countList = $this->getFilter()->getCount();
        return array_intersect_key($items, $countList);
    }

    /**
     * @return bool
     */
    public function isCanShowCollapse()
    {
        if (!$this->getFilter()->getIsRowCountLimitEnabled()) {
            return false;
        }
        if (count($this->getOptionList()) <= ($this->getFilter()->getRowCountLimit() * $this->getColumnCount())) {
            return false;
        }
        return true;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter_Option $option
     * @return integer
     */
    public function getItemCountByOption(AW_Layerednavigation_Model_Filter_Option $option)
    {
        $countList = $this->getFilter()->getCount();
        if (!array_key_exists($option->getId(), $countList)) {
            return 0;
        }
        return intval($countList[$option->getId()]);
    }

    /**
     * @param AW_Layerednavigation_Model_Filter_Option $option
     *
     * @return string
     */
    public function getTitleHtmlByOption(AW_Layerednavigation_Model_Filter_Option $option)
    {
        $block = $this->getLayout()
            ->createBlock('aw_layerednavigation/filter_option_title')
            ->setOption($option)
            ->setImagePosition($this->getFilter()->getImagePosition())
        ;
        return $block->toHtml();
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return $this
     */
    public function setFilter(AW_Layerednavigation_Model_Filter $filter)
    {
        $this->_filter = $filter;
        $this->_filter->setStoreId(Mage::app()->getStore()->getId());
        return $this;
    }

    /**
     * @return AW_Layerednavigation_Model_Filter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->_getTemplate();
    }

    /**
     * @return bool
     */
    public function getIsFilterApplied()
    {
        return count($this->getFilter()->getCurrentValue()) > 0;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter_Option $option
     *
     * @return bool
     */
    public function isOptionChecked(AW_Layerednavigation_Model_Filter_Option $option)
    {
        $currentValue = $this->getFilter()->getCurrentValue();
        foreach ($currentValue as $value) {
            if ($option->getId() == $value->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return AW_Layerednavigation_Model_Source_Filter_Column_Layout::ONE_COLUMN_CODE;
    }

    /**
     * @return string
     */
    abstract protected function _getTemplate();
}