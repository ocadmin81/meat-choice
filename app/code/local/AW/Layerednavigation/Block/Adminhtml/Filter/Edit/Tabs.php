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


class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('filter_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Filter Information'));
    }

    protected function _beforeToHtml()
    {
        $filter = $this->getCurrentFilter();
        if ($filter->getId()) {

            $generalTabRenderer = Mage::helper('aw_layerednavigation/type')->getAdminhtmlTabGeneralRendererByTypeCode(
                $filter->getType()
            );

            $generalTabRendererBlock = $this->getLayout()->createBlock($generalTabRenderer);

            if ($generalTabRendererBlock) {
                $this->addTab(
                    $generalTabRendererBlock->getTabCode(),
                    array(
                         'label'   => $generalTabRendererBlock->getTabLabel(),
                         'title'   => $generalTabRendererBlock->getTabTitle(),
                         'content' => $generalTabRendererBlock->toHtml(),
                    )
                );
            }

            $optionsTabRenderer = Mage::helper('aw_layerednavigation/type')->getAdminhtmlTabOptionsRendererByTypeCode(
                $filter->getType()
            );

            $optionsTabRenderer = $this->getLayout()->createBlock($optionsTabRenderer);

            if ($optionsTabRenderer) {
                $this->addTab(
                    $optionsTabRenderer->getTabCode(),
                    array(
                         'label'   => $optionsTabRenderer->getTabLabel(),
                         'title'   => $optionsTabRenderer->getTabTitle(),
                         'content' => $optionsTabRenderer->toHtml(),
                    )
                );
            }

            if ($this->getIsDefaultStore()) {
                $this->addTab(
                    'categories',
                    array(
                        'label' => $this->__('Not Visible In'),
                        'url'   => $this->getUrl('*/*/categories', array('_current' => true)),
                        'class' => 'ajax',
                    )
                );
            }
        }
        return parent::_beforeToHtml();
    }

    public function getCurrentFilter()
    {
        return Mage::registry('current_filter');
    }

    public function getIsDefaultStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        return $storeId === Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }
}