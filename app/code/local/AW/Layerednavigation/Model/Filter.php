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


class AW_Layerednavigation_Model_Filter extends Mage_Core_Model_Abstract
{
    protected $_instance = null;
    protected $_storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    protected $_optionCollection = null;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter');
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     *
     * @return AW_Layerednavigation_Model_Filter_Type_Abstract
     */
    public function apply(Zend_Controller_Request_Abstract $request)
    {
        return $this->_getInstance()
            ->apply($request)
        ;
    }

    /**
     * @return array
     */
    public function getCurrentValue()
    {
        return $this->_getInstance()->getCurrentValue();
    }

    /**
     * @return array
     */
    public function getCount()
    {
        return $this->_getInstance()->getCount();
    }

    /**
     * @return string
     */
    public function getAdminhtmlFormBlockName()
    {
        return Mage::helper('aw_layerednavigation/type')->getAdminhtmlFormBlockNameByTypeCode($this->getType());
    }

    /**
     * @return string
     */
    public function getFrontendRendererBlockName()
    {
        return Mage::helper('aw_layerednavigation/type')->getFrontendRendererBlockNameByTypeCode($this->getType());
    }

    /**
     * @return AW_Layerednavigation_Model_Filter_Type_Abstract
     */
    protected function _getInstance()
    {
        if (is_null($this->_instance)) {
            $modelName = Mage::helper('aw_layerednavigation/type')->getModelNameByTypeCode($this->getType());
            $this->_instance = Mage::getModel($modelName);
            $this->_instance->setFilter($this);
        }
        return $this->_instance;
    }

    /**
     * @return AW_Layerednavigation_Model_Resource_Filter_Option_Collection
     */
    public function getOptionCollection()
    {
        return $this->_optionCollection = Mage::getResourceModel('aw_layerednavigation/filter_option_collection')
            ->addFilterFilterId($this->getId())
            ->addOptionAttributes($this->getStoreId())
            ->sortByPosition()
        ;
    }

    /**
     * Set store id to filter
     *
     * @param int $storeId
     * @return AW_Layerednavigation_Model_Filter
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return store id from filter
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }
}