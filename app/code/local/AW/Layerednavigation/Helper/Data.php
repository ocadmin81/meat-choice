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

class AW_Layerednavigation_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Equivalent of round($value, $precision, PHP_ROUND_HALF_DOWN) in php 5.3
     * @param float $value
     * @param int $precision
     *
     * @return float
     */
    public static function roundHalfDown($value, $precision = 0)
    {
        if (defined("PHP_ROUND_HALF_DOWN")) {
            return round($value, $precision, PHP_ROUND_HALF_DOWN);
        }
        return floor($value * pow(10, $precision)) / pow(10, $precision);
    }

    /**
     * Equivalent of round($value, $precision, PHP_ROUND_HALF_UP) in php 5.3
     * @param float $value
     * @param int $precision
     *
     * @return float
     */
    public static function roundHalfUp($value, $precision = 0)
    {
        if (defined("PHP_ROUND_HALF_UP")) {
            return round($value, $precision, PHP_ROUND_HALF_UP);
        }
        return ceil($value * pow(10, $precision)) / pow(10, $precision);
    }

    /**
     * @param string $version
     *
     * @return bool
     */
    public function checkMageVersion($version = '1.7.0.0', $expression = '<')
    {
        return version_compare(Mage::getVersion(), $version, $expression);
    }
}