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


abstract class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_Options_Abstract
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_optionConfigList = array();

    abstract public function getTabCode();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_layerednavigation/filter/edit/tab/options.phtml');
        $this->_initOptionConfigList();
    }

    public function getTabLabel()
    {
        return $this->__('Options Settings');
    }

    public function getTabTitle()
    {
        return $this->__('Options Settings');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function canChangeOptionPosition()
    {
        return true;
    }

    protected function _initOptionConfigList()
    {
        $filter = Mage::registry('current_filter');
        $data = array();
        if ((int)$filter->getStoreId() !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            foreach ($this->getOptionList() as $option) {
                $option->setStoreId($filter->getStoreId());
                $attributeCollection = Mage::getResourceModel('aw_layerednavigation/filter_option')
                    ->getAttributeCollection($option)
                ;
                foreach ($attributeCollection as $attribute) {
                    $isDefault = (int)$attribute->getStoreId() === Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
                    $data[$option->getId()][$attribute->getName()] = $isDefault;
                }
            }
        }
        $this->_optionConfigList = $data;
    }

    public function isAttributeDefault($optionId, $attributeCode)
    {
        if (count($this->_optionConfigList) == 0) {
            return false;
        }
        return $this->_optionConfigList[$optionId][$attributeCode];
    }

    public function getOptionList()
    {
        return Mage::registry('current_filter')->getOptionCollection();
    }

    public function canDisplayUseDefault()
    {
        return Mage::registry('current_filter')->getStoreId() !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    public function getIsEnabledSelectHtml($option)
    {
        $isStatusDefault = $this->isAttributeDefault($option->getId(), 'is_enabled');
        $isEnabledSelect = $this->getLayout()->createBlock('adminhtml/html_select')->setData(
            array(
                'name'         => "option[{$option->getId()}][is_enabled]",
                'class'        => 'select' . ($isStatusDefault ? ' disabled' : ''),
                'value'        => $option->getData('is_enabled'),
                'extra_params' => ($isStatusDefault ? 'disabled' : ''),
            )
        );

        $optionArray = Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray();

        foreach ($optionArray as $key => $optionValue) {
            if ($optionValue['value'] == $option->getData('is_enabled')) {
                $optionArray[$key]['params'] = array('selected' => 'selected');
            }
        }

        $isEnabledSelect->setOptions($optionArray);
        return $isEnabledSelect->toHtml();
    }

    public function getIsDefaultStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        return $storeId === Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }
}