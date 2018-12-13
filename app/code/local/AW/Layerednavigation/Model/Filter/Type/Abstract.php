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


abstract class AW_Layerednavigation_Model_Filter_Type_Abstract
{
    protected $_filter = null;
    protected $_currentValue = array();
    protected $_count = null;

    /**
     * @param Zend_Controller_Request_Abstract $request
     *
     * @return AW_Layerednavigation_Model_Filter_Type_Abstract
     */
    abstract public function apply(Zend_Controller_Request_Abstract $request);

    /**
     * return array(optionId => count)
     * @return array
     */
    abstract public function getCount();

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return $this
     */
    public function setFilter(AW_Layerednavigation_Model_Filter $filter)
    {
        $this->_filter = $filter;
        return $this;
    }

    /**
     * @return AW_Layerednavigation_Model_Filter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @return array
     */
    public function getCurrentValue()
    {
        return $this->_currentValue;
    }
}