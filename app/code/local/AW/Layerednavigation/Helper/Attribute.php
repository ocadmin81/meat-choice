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

class AW_Layerednavigation_Helper_Attribute extends Mage_Core_Helper_Data
{
    const CONFIGURATION_FOLDER = 'etc';
    const CONFIGURATION_FILE   = 'attribute.xml';

    const ATTRIBUTE_GROUP_FILTER = 'filter';
    const ATTRIBUTE_GROUP_OPTION = 'option';

    protected $_attributes = array();

    public function __construct()
    {
        $this->_initXmlConfig();
    }

    protected function _initXmlConfig()
    {
        $configFilePath = Mage::getModuleDir(self::CONFIGURATION_FOLDER, $this->_getModuleName()) . DS
            . self::CONFIGURATION_FILE
        ;
        $config = new Varien_Simplexml_Config($configFilePath);

        foreach ($config->getNode('group')->children() as $groupName => $groupNode) {
            foreach ($groupNode->children() as $attribute) {
                $this->_attributes[$groupName][] = (string) $attribute->children();
            }
        }
    }

    public function getAttributesByGroup($groupName)
    {
        if (isset($this->_attributes[$groupName])) {
            return $this->_attributes[$groupName];
        }
        return array();
    }

    public function getFilterAttributes()
    {
        return $this->getAttributesByGroup(self::ATTRIBUTE_GROUP_FILTER);
    }

    public function getOptionAttributes()
    {
        return $this->getAttributesByGroup(self::ATTRIBUTE_GROUP_OPTION);
    }
}