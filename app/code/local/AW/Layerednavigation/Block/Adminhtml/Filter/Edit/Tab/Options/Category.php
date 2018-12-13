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

class AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_Options_Category
    extends AW_Layerednavigation_Block_Adminhtml_Filter_Edit_Tab_Options_Abstract
{
    public function getTabLabel()
    {
        return $this->__('Category Settings');
    }

    public function getTabTitle()
    {
        return $this->__('Category Settings');
    }

    public function getTabCode()
    {
        return 'category_options';
    }

    public function canChangeOptionPosition()
    {
        return false;
    }
}