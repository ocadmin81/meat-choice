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

class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_General_Decimal_Price
    extends AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_General_Abstract
{
    public function getTabCode()
    {
        return 'decimal_price_general';
    }

    protected function _prepareForm()
    {
        $this->_initForm();
        $this->_addNoteToDisplayTypeField();
        $this->_setFormValues();
        return $this;
    }

    protected function _addNoteToDisplayTypeField()
    {
        $fieldset = $this->getForm()->getElements()->searchById('filter_general');
        $displayTypeField = $fieldset->getElements()->searchById('display_type');
        $displayTypeField->setData(
            'after_element_html',
            "<div style=\"font-size:11px\">"
            . $this->__(
                "In case of several currencies set, it is recommended to use 'Range' or 'From-To' Display Types"
            )
            . "</div>"
        );
        $fieldset->removeField('display_type');
        $fieldset->addElement($displayTypeField, 'position');
    }
}