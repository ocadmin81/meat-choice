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


class AW_Layerednavigation_Model_Source_Filter_Image_Position
{
    const TEXT_ONLY_CODE   = 1;
    const TEXT_ONLY_LABEL  = "Text Only";

    const LEFT_CODE        = 2;
    const LEFT_LABEL       = "Left";

    const RIGHT_CODE       = 3;
    const RIGHT_LABEL      = "Right";

    const IMAGE_ONLY_CODE  = 4;
    const IMAGE_ONLY_LABEL = "Image Only";

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::TEXT_ONLY_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::TEXT_ONLY_LABEL)
            ),
            array(
                'value' => self::LEFT_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::LEFT_LABEL)
            ),
            array(
                'value' => self::RIGHT_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::RIGHT_LABEL)
            ),
            array(
                'value' => self::IMAGE_ONLY_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::IMAGE_ONLY_LABEL)
            ),
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::TEXT_ONLY_CODE  => Mage::helper('aw_layerednavigation')->__(self::TEXT_ONLY_LABEL),
            self::LEFT_CODE       => Mage::helper('aw_layerednavigation')->__(self::LEFT_LABEL),
            self::RIGHT_CODE      => Mage::helper('aw_layerednavigation')->__(self::RIGHT_LABEL),
            self::IMAGE_ONLY_CODE => Mage::helper('aw_layerednavigation')->__(self::IMAGE_ONLY_LABEL),
        );
    }
}