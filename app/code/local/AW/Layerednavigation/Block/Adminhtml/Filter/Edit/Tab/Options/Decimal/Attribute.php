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


class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_Options_Decimal_Attribute
    extends AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_Options_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_layerednavigation/filter/edit/tab/ranges.phtml');
        $this->_initOptionConfigList();
    }

    public function getTabCode()
    {
        return 'decimal_attribute_options';
    }

    public function getTabLabel()
    {
        return $this->__('Range Settings');
    }

    public function getTabTitle()
    {
        return $this->__('Range Settings');
    }


    public function getOptionList()
    {
        $optionCollection = Mage::registry('current_filter')->getOptionCollection();

        foreach ($optionCollection as $option) {
            $option->addData($option->getAdditionalData());

            if ($option->getImage()) {
                $originalImageUrl = Mage::helper('aw_layerednavigation/image')->getImageUrl(
                    $option->getId(), $option->getImage()
                );
                $resizedImageUrl = Mage::helper('aw_layerednavigation/image')->resizeImage(
                    $option->getId(), $option->getImage(), 20, 20
                );
                $option->addData(
                    array(
                         'original_image' => $originalImageUrl,
                         'resized_image' => $resizedImageUrl,
                    )
                );
            }
        }
        return $optionCollection;
    }

    /**
     * Retrieve 'add' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('aw_layerednavigation_add_button');
    }

    public function getRangeIsEnabledSelectHtml()
    {
        $isEnabledSelect = $this->getLayout()->createBlock('adminhtml/html_select')->setData(
            array(
                 'id'    => 'option_{{id}}_is_enabled',
                 'name'  => "option[{{id}}][is_enabled]",
                 'class' => 'select',
            )
        );

        $optionArray = Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray();

        $isEnabledSelect->setOptions($optionArray);
        return $isEnabledSelect->toHtml();
    }

    protected function _prepareLayout()
    {
        $result = parent::_prepareLayout();

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                     'id'      => 'aw_layerednavigation_add_new_row',
                     'name'    => 'aw_layerednavigation_add_button',
                     'label'   => $this->__('Add New'),
                     'class'   => 'add',
                )
            );

        /* @var $button Mage_Adminhtml_Block_Widget_Button */
        $this->setChild('aw_layerednavigation_add_button', $button);

        return $result;
    }
}