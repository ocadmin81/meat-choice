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


class AW_Layerednavigation_Block_Filter_Option_Title extends Mage_Core_Block_Template
{
    protected $_template = 'aw_layerednavigation/filter/option/title.phtml';
    protected $_option = null;

    /**
     * @param AW_Layerednavigation_Model_Filter_Option $option
     * @return $this;
     */
    public function setOption(AW_Layerednavigation_Model_Filter_Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * @return AW_Layerednavigation_Model_Filter_Option
     */
    public function getOption()
    {
        return $this->_option;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter_Option $option
     *
     * @return string
     */
    public function getOptionImageUrl(AW_Layerednavigation_Model_Filter_Option $option)
    {
        return Mage::helper('aw_layerednavigation/image')->resizeImage($option->getId(), $option->getImage(), 20, 20);
    }
}