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

class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_General_Category
    extends AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_General_Abstract
{

    public function getTabCode()
    {
        return 'category_general';
    }

    protected function _initForm()
    {
        $result = parent::_initForm();
        $fieldset = $this->getForm()->getElements()->searchById('filter_general');
        $fieldset->addField(
            'show_as_tree',
            $this->getIsDefaultStore() ? 'select' : 'hidden',
            array(
                'label'  => $this->__('Show as Tree'),
                'name'   => 'show_as_tree',
                'values' => Mage::getModel('aw_layerednavigation/source_yesno')->toOptionArray(),
            ),
            'row_count_limit'
        );

        $fieldset->addType('category', 'AW_Layerednavigation_Block_Adminhtml_Filter_Form_Renderer_Element_Category');
        $fieldset->addField(
            'additional_data',
            'category',
            array(
                'label'  => $this->__('Filter Source'),
                'name'   => 'category_link',
            ),
            'code'
        );

        return $result;
    }
}