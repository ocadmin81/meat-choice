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

class AW_Layerednavigation_Helper_Type extends Mage_Core_Helper_Data
{
    const CONFIGURATION_FOLDER = 'etc';
    const CONFIGURATION_FILE   = 'type.xml';

    protected $_typeConfig = array();

    public function __construct()
    {
        $configFilePath =  Mage::getModuleDir(self::CONFIGURATION_FOLDER, $this->_getModuleName()) . DS
            . self::CONFIGURATION_FILE
        ;
        $config = new Varien_Simplexml_Config($configFilePath);
        $this->_typeConfig = $config->getNode()->asArray();
    }

    /**
     * @param string $code
     *
     * @return string|null
     */
    public function getModelNameByTypeCode($code)
    {
        if (!array_key_exists($code, $this->_typeConfig)) {
            return null;
        }
        return $this->_typeConfig[$code]['model'];
    }

    /**
     * @param string $code
     *
     * @return string|null
     */
    public function getSynchronizationModelNameByTypeCode($code)
    {
        if (!array_key_exists($code, $this->_typeConfig)) {
            return null;
        }
        return $this->_typeConfig[$code]['synchronization_model'];
    }

    /**
     * @param string $code
     *
     * @return string|null
     */
    public function getFrontendRendererBlockNameByTypeCode($code)
    {
        if (!array_key_exists($code, $this->_typeConfig)) {
            return null;
        }
        return $this->_typeConfig[$code]['frontend_renderer'];
    }

    /**
     * @param string $code
     *
     * @return string|null
     */
    public function getAdminhtmlTabGeneralRendererByTypeCode($code)
    {
        if (!array_key_exists($code, $this->_typeConfig)) {
            return null;
        }
        return $this->_typeConfig[$code]['adminhtml_tab_general_renderer'];
    }

    /**
     * @param string $code
     *
     * @return string|null
     */
    public function getAdminhtmlTabOptionsRendererByTypeCode($code)
    {
        if (!array_key_exists($code, $this->_typeConfig)) {
            return null;
        }
        return $this->_typeConfig[$code]['adminhtml_tab_options_renderer'];
    }
}