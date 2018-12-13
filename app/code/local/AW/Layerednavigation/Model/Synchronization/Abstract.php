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

abstract class AW_Layerednavigation_Model_Synchronization_Abstract
{
    const IS_ROW_COUNT_LIMIT_STATUS = 0;
    const ROW_COUNT_LIMIT           = 10;

    abstract public function run();

    /**
     * Generate Unique filter code
     * if filter with $filterCode exists, add suffix,
     * if suffix exists, increment it
     *
     * @param string $filterCode
     *
     * @return string
     */
    public function getUniqueCode($filterCode)
    {
        $suffix = 1;
        $uniqueFilterCode = $filterCode;
        while (true) {
            $filterModel = Mage::getModel('aw_layerednavigation/filter')->load($uniqueFilterCode, 'code');
            if (!$filterModel->getId()) {
                return $uniqueFilterCode;
            }
            $uniqueFilterCode = $filterCode . '_' . $suffix++;
        }
    }
}