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

class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getSaveUrl(),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
    }

    public function getSaveUrl()
    {
        $urlParams = array();
        if (Mage::registry('current_filter')->getId() !== null) {
            $urlParams['id'] = Mage::registry('current_filter')->getId();
        }
        $storeId = $this->getRequest()->getParam('store', null);
        if ($storeId !== null) {
            $urlParams['store'] = $storeId;
        }
        return $this->getUrl('*/*/save', $urlParams);
    }
}