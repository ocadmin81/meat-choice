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


class AW_Layerednavigation_Model_Source_Filter_Column_Layout
{
    const ONE_COLUMN_CODE   = 1;
    const ONE_COLUMN_LABEL  = "1 Column";

    const TWO_COLUMN_CODE   = 2;
    const TWO_COLUMN_LABEL  = "2 Column";

    const THREE_COLUMN_CODE     = 3;
    const THREE_COLUMN_LABEL    = "3 Column";

    const FOUR_COLUMN_CODE  = 4;
    const FOUR_COLUMN_LABEL = "4 Column";

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ONE_COLUMN_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::ONE_COLUMN_LABEL)
            ),
            array(
                'value' => self::TWO_COLUMN_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::TWO_COLUMN_LABEL)
            ),
            array(
                'value' => self::THREE_COLUMN_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::THREE_COLUMN_LABEL)
            ),
            array(
                'value' => self::FOUR_COLUMN_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::FOUR_COLUMN_LABEL)
            ),
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::ONE_COLUMN_CODE   => Mage::helper('aw_layerednavigation')->__(self::ONE_COLUMN_LABEL),
            self::TWO_COLUMN_CODE   => Mage::helper('aw_layerednavigation')->__(self::TWO_COLUMN_LABEL),
            self::THREE_COLUMN_CODE => Mage::helper('aw_layerednavigation')->__(self::THREE_COLUMN_LABEL),
            self::FOUR_COLUMN_CODE  => Mage::helper('aw_layerednavigation')->__(self::FOUR_COLUMN_LABEL),
        );
    }
}