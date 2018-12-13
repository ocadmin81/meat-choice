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

class AW_Layerednavigation_Block_Adminhtml_Filter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_filter';
        $this->_blockGroup = 'aw_layerednavigation';
        parent::__construct();

        $this->_removeButton('delete');

        $this->_addButton(
            'save_and_continue',
            array(
                'label'   => $this->__('Save and Continue Edit'),
                'onclick' => "saveAndContinueEdit('{$this->getSaveAndContinueUrl()}')",
                'class'   => 'save',
                'id'      => 'save-and-continue',
            ),
            -200
        );
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_filter')->getId()) {
            return $this->__('Edit Filter "%s"', $this->escapeHtml(Mage::registry('current_filter')->getTitle()));
        } else {
            return $this->__('Create Filter');
        }
    }

    protected function _prepareLayout()
    {
        $tabsBlockJsObject = 'filter_tabsJsTabs';
        $tabsBlockPrefix   = 'filter_tabs_';

        $this->_formScripts[] = "
            function saveAndContinueEdit(urlTemplate) {
                var tabsIdValue = {$tabsBlockJsObject}.activeTab.id;
                var tabsBlockPrefix = '{$tabsBlockPrefix}';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = template.evaluate({tab_id:tabsIdValue});
                console.log(url);
                editForm.submit(url);
            }
        ";
        return parent::_prepareLayout();
    }

    public function getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            array(
                '_current'   => true,
                'back'       => 'edit',
                'active_tab' => '{{tab_id}}',
            )
        );
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }
}