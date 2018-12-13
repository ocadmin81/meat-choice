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


class AW_Layerednavigation_Model_Source_Filter_Type
{
    const CATEGORY_CODE           = "category";
    const CATEGORY_LABEL          = "Category";

    const OPTION_CODE             = "option";
    const OPTION_LABEL            = "Option";

    const OPTION_ATTRIBUTE_CODE   = "option_attribute";
    const OPTION_ATTRIBUTE_LABEL  = "Option Attribute";

    const YESNO_ATTRIBUTE_CODE    = "yesno_attribute";
    const YESNO_ATTRIBUTE_LABEL   = "Yes/No Attribute";

    const DECIMAL_CODE            = "decimal";
    const DECIMAL_LABEL           = "Decimal";

    const DECIMAL_ATTRIBUTE_CODE  = "decimal_attribute";
    const DECIMAL_ATTRIBUTE_LABEL = "Decimal Attribute";

    const DECIMAL_RATING_CODE     = "decimal_rating";
    const DECIMAL_RATING_LABEL    = "Rating";

    const DECIMAL_PRICE_CODE      = "decimal_price";
    const DECIMAL_PRICE_LABEL     = "Price";

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::CATEGORY_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::CATEGORY_LABEL),
            ),
            array(
                'value' => self::OPTION_ATTRIBUTE_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::OPTION_ATTRIBUTE_LABEL),
            ),
            array(
                'value' => self::YESNO_ATTRIBUTE_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::YESNO_ATTRIBUTE_LABEL),
            ),
            array(
                'value' => self::DECIMAL_ATTRIBUTE_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::DECIMAL_ATTRIBUTE_LABEL),
            ),/*
            array(
                'value' => self::DECIMAL_RATING_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::DECIMAL_RATING_LABEL),
            ),*/
            array(
                'value' => self::DECIMAL_PRICE_CODE,
                'label' => Mage::helper('aw_layerednavigation')->__(self::DECIMAL_PRICE_LABEL),
            ),
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::CATEGORY_CODE               => Mage::helper('aw_layerednavigation')->__(self::CATEGORY_LABEL),
            self::OPTION_ATTRIBUTE_CODE       => Mage::helper('aw_layerednavigation')->__(self::OPTION_ATTRIBUTE_LABEL),
            self::YESNO_ATTRIBUTE_CODE        => Mage::helper('aw_layerednavigation')->__(self::YESNO_ATTRIBUTE_LABEL),
            self::DECIMAL_ATTRIBUTE_CODE      =>
                Mage::helper('aw_layerednavigation')->__(self::DECIMAL_ATTRIBUTE_LABEL),
            //self::DECIMAL_RATING_CODE    => Mage::helper('aw_layerednavigation')->__(self::DECIMAL_RATING_LABEL),
            self::DECIMAL_PRICE_CODE          => Mage::helper('aw_layerednavigation')->__(self::DECIMAL_PRICE_LABEL),
        );
    }
}