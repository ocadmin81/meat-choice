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


class AW_Layerednavigation_Block_Filter_Type_Option extends AW_Layerednavigation_Block_Filter_Type_Abstract
{
    /**
     * @return string
     */
    protected function _getTemplate()
    {
        switch($this->getFilter()->getDisplayType()) {
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::CHECKBOX_CODE:
                return 'aw_layerednavigation/filter/checkbox.phtml';
                break;
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE:
                return 'aw_layerednavigation/filter/radiogroup.phtml';
                break;
            default:
                return '';
        }
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        $columnLayout = $this->getFilter()->getColumnLayout();
        if (!$columnLayout) {
            return parent::getColumnLayout();
        }
        return intval($columnLayout);
    }
}
