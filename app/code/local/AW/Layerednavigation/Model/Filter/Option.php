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


class AW_Layerednavigation_Model_Filter_Option extends Mage_Core_Model_Abstract
{
    protected $_storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    protected $_filter = null;

    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter_option');
    }

    /**
     * Set store id to option
     *
     * @param int $storeId
     * @return AW_Layerednavigation_Model_Filter_Option
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return store id from option
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }
}