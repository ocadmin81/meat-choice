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

class AW_Layerednavigation_Model_Synchronization_Decimal_Price
    extends AW_Layerednavigation_Model_Synchronization_Abstract
{
    /**
     *
     * Create new price filter if not exists
     */
    public function run()
    {
        $collection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
            ->addFieldToFilter('type', array('eq' => AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_PRICE_CODE))
        ;
        $priceFilterModel = $collection->getFirstItem();

        if (!$priceFilterModel->getId()) {
            $priceFilterData = array(
                'title'                      => Mage::helper('aw_layerednavigation')->__('Price'),
                'type'                       => AW_Layerednavigation_Model_Source_Filter_Type::DECIMAL_PRICE_CODE,
                'is_enabled'                 => 1,
                'is_enabled_in_search'       => 1,
                'code'                       => $this->getUniqueCode('price'),
                'position'                   => 0,
                'display_type'               => AW_Layerednavigation_Model_Source_Filter_Display_Type::RANGE_CODE,
                'image_position'             => AW_Layerednavigation_Model_Source_Filter_Image_Position::TEXT_ONLY_CODE,
                'is_row_count_limit_enabled' => self::IS_ROW_COUNT_LIMIT_STATUS,
                'row_count_limit'            => self::ROW_COUNT_LIMIT,
                'additional_data'            => array(),
            );
            $priceFilterModel->setData($priceFilterData)->save();
        }
    }
}