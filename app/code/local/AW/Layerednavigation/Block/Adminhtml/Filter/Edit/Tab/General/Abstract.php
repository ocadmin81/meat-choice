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


abstract class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_General_Abstract
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    abstract public function getTabCode();

    public function getTabLabel()
    {
        return $this->__('General Settings');
    }

    public function getTabTitle()
    {
        return $this->__('General Settings');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getFormHtml()
    {
        return parent::getFormHtml() . $this->_getInitJs();
    }

    protected function _prepareForm()
    {
        $this
            ->_initForm()
            ->_setFormValues()
        ;
        return parent::_prepareForm();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset(
            'filter_general',
            array('legend' => $this->__('General Settings'))
        );

        $isEnabledElement = $fieldset->addField(
            'is_enabled',
            'select',
            array(
                'label'    => $this->__('Is Enabled'),
                'name'     => 'is_enabled',
                'values'   => Mage::getModel('aw_layerednavigation/source_filter_status')->toOptionArray(),
            )
        );
        $isEnabledElement->setRenderer(
            $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_form_renderer_fieldset_element')
        );

        $isEnabledElement = $fieldset->addField(
            'is_enabled_in_search',
            'select',
            array(
                'label'    => $this->__('Is Enabled in Search Results'),
                'name'     => 'is_enabled_in_search',
                'values'   => Mage::getModel('aw_layerednavigation/source_filter_status')->toOptionArray(),
            )
        );
        $isEnabledElement->setRenderer(
            $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_form_renderer_fieldset_element')
        );

        $titleElement = $fieldset->addField(
            'title',
            'text',
            array(
                'label'    => $this->__('Title'),
                'name'     => 'title',
                'required' => true,
            )
        );
        $titleElement->setRenderer(
            $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_form_renderer_fieldset_element')
        );

        $fieldset->addField(
            'code',
            $this->getIsDefaultStore() ? 'text' : 'hidden',
            array(
                 'label'    => $this->__('Code'),
                 'name'     => 'code',
                 'class'    => 'validate-code',
                 'required' => true,
            )
        );

        $fieldset->addField(
            'position',
            $this->getIsDefaultStore() ? 'text' : 'hidden',
            array(
                'label'    => $this->__('Position'),
                'name'     => 'position',
                'class'    => 'validate-number',
                'required' => true,
            )
        );

        $displayTypes = Mage::getModel('aw_layerednavigation/source_filter_display_type')
            ->setFilterType(Mage::registry('current_filter')->getType())
            ->toArray()
        ;
        $fieldset->addField(
            'display_type',
            $this->getIsDefaultStore() ? 'select' : 'hidden',
            array(
                'label'  => $this->__('Display Type'),
                'name'   => 'display_type',
                'values' => $displayTypes,
            )
        );

        $fieldset->addField(
            'image_position',
            $this->getIsDefaultStore() ? 'select' : 'hidden',
            array(
                 'label'  => $this->__('Image Position'),
                 'name'   => 'image_position',
                 'values' => Mage::getModel('aw_layerednavigation/source_filter_image_position')->toArray(),
            )
        );

        $isHelpEnabledElement = $fieldset->addField(
            'is_help_enabled',
            'select',
            array(
                'label'    => $this->__('Is Help Enabled'),
                'name'     => 'is_help_enabled',
                'values'   => Mage::getModel('aw_layerednavigation/source_yesno')->toOptionArray(),
            )
        );
        $isHelpEnabledElement->setRenderer(
            $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_form_renderer_fieldset_element')
        );

        $helpTypeElement = $fieldset->addField(
            'help_type',
            'select',
            array(
                'label'    => $this->__('Help Type'),
                'name'     => 'help_type',
                'values'   => Mage::getModel('aw_layerednavigation/source_filter_help_type')->toOptionArray(),
            )
        );
        $helpTypeElement->setRenderer(
            $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_form_renderer_fieldset_element')
        );

        $helpContentElement = $fieldset->addField(
            'help_content',
            'editor',
            array(
                'label'    => $this->__('Help Content'),
                'name'     => 'help_content',
                'required' => true,
                'config'   => $this->_getWysiwygConfig(),
            )
        );
        $helpContentElement->setRenderer(
            $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_form_renderer_fieldset_element')
        );

        $fieldset->addField(
            'is_row_count_limit_enabled',
            $this->getIsDefaultStore() ? 'select' : 'hidden',
            array(
                 'label'    => $this->__('Limit Rows Quantity'),
                 'name'     => 'is_row_count_limit_enabled',
                 'values'   => Mage::getModel('aw_layerednavigation/source_yesno')->toOptionArray(),
            )
        );

        $fieldset->addField(
            'row_count_limit',
            $this->getIsDefaultStore() ? 'text' : 'hidden',
            array(
                 'label'    => $this->__('Quantity Of Rows To Display'),
                 'name'     => 'row_count_limit',
                 'class'    => 'validate-number',
                 'required' => true,
            )
        );

        $this->setForm($form);
        return $this;
    }

    protected function _setFormValues()
    {
        $form = $this->getForm();
        $filter = Mage::registry('current_filter');
        $data = array_merge($filter->getData('additional_data'), $filter->getData());

        if ((int)$filter->getStoreId() !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $attributeCollection = Mage::getResourceModel('aw_layerednavigation/filter')
                ->getAttributeCollection($filter)
            ;
            foreach ($attributeCollection as $attribute) {
                if ((int)$attribute->getStoreId() === Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                    $formElement = $form->getElement($attribute->getName());
                    if ($formElement === null) {
                        continue;
                    }
                    $formElement
                        ->setData('is_default', true)
                        ->setData('disabled', true)
                        ->setData('class', 'disabled')
                    ;
                }
            }
        }

        $form->addValues($data);
        return $this;
    }

    public function getIsDefaultStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        return $storeId === Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    protected function _getInitJs()
    {
        return $this->getLayout()
            ->createBlock("aw_layerednavigation/adminhtml_filter_edit_tab_general_abstract_js")
            ->setTemplate("aw_layerednavigation/filter/edit/tab/general/js.phtml")
            ->toHtml()
        ;
    }

    protected function _getWysiwygConfig()
    {
        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $data = $this->_recursiveUrlUpdate($config->getData());
        $config->setData($data);
        return $config;
    }

    protected function _recursiveUrlUpdate($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->_recursiveUrlUpdate($value);
            }
            if (is_string($value)) {
                $data[$key] = str_replace('aw_layerednavigation_admin', 'admin', $value);
            }
        }
        return $data;
    }
}