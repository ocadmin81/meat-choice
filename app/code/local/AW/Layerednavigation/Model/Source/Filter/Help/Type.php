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


class AW_Layerednavigation_Model_Source_Filter_Help_Type
{
    const ICON_CODE  = 1;
    const ICON_LABEL = "Icon";

    const TEXT_CODE  = 2;
    const TEXT_LABEL = "Text";

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ICON_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::ICON_LABEL),
            ),
            array(
                'value' => self::TEXT_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::TEXT_LABEL),
            ),
        );
    }

    public function toArray()
    {
        return array(
            self::ICON_CODE => Mage::helper('aw_layerednavigation')->__(self::ICON_LABEL),
            self::TEXT_CODE => Mage::helper('aw_layerednavigation')->__(self::TEXT_LABEL),
        );
    }
}