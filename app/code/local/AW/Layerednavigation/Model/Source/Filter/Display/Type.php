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


class AW_Layerednavigation_Model_Source_Filter_Display_Type
{
    const CHECKBOX_CODE  = 1;
    const CHECKBOX_LABEL = "Checkbox Group";

    const RADIO_CODE     = 2;
    const RADIO_LABEL    = "Radio Group";

    const RANGE_CODE     = 3;
    const RANGE_LABEL    = "Range";

    const FROM_TO_CODE   = 4;
    const FROM_TO_LABEL  = "From - To";

    private $_filterType = null;

    private $_displayTypeMap = array(
        AW_Layerednavigation_Model_Source_Filter_Type::CATEGORY_CODE          => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::OPTION_CODE            => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::OPTION_ATTRIBUTE_CODE  => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_CODE           => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
            self::RANGE_CODE,
            self::FROM_TO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_ATTRIBUTE_CODE => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
            self::RANGE_CODE,
            self::FROM_TO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_RATING_CODE    => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
            self::RANGE_CODE,
            self::FROM_TO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_PRICE_CODE    => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
            self::RANGE_CODE,
            self::FROM_TO_CODE,
        ),
        AW_Layerednavigation_Model_Source_Filter_Type::YESNO_ATTRIBUTE_CODE => array(
            self::CHECKBOX_CODE,
            self::RADIO_CODE,
        )
    );

    public function setFilterType($filterType)
    {
        $this->_filterType = $filterType;
        return $this;
    }

    public function getFilterType()
    {
        return $this->_filterType;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        if ($this->getFilterType() !== null) {
            if (array_key_exists($this->getFilterType(), $this->_displayTypeMap)) {
                $availableOptions = $this->asArray();
                foreach ($this->_displayTypeMap[$this->getFilterType()] as $code) {
                    $optionArray[] = array(
                        'value' => $code,
                        'label' => $availableOptions[$code],
                    );
                }
            }
        }
        return $optionArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = array();
        if ($this->getFilterType() !== null) {
            if (array_key_exists($this->getFilterType(), $this->_displayTypeMap)) {
                $availableOptions = $this->asArray();
                foreach ($this->_displayTypeMap[$this->getFilterType()] as $code) {
                    $array[$code] = $availableOptions[$code];
                }
            }
        }
        return $array;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array(
            self::CHECKBOX_CODE => Mage::helper('aw_layerednavigation')->__(self::CHECKBOX_LABEL),
            self::RADIO_CODE    => Mage::helper('aw_layerednavigation')->__(self::RADIO_LABEL),
            self::RANGE_CODE    => Mage::helper('aw_layerednavigation')->__(self::RANGE_LABEL),
            self::FROM_TO_CODE  => Mage::helper('aw_layerednavigation')->__(self::FROM_TO_LABEL),
        );
    }
}