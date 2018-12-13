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


class AW_Layerednavigation_Model_Resource_Filter_Option extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields = array(
        'additional_data' => array(null, array()),
    );

    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter_option', 'option_id');
    }

    /**
     * @param $option
     * @param string $attributeCode
     * @return AW_Layerednavigation_Model_Filter_Option_Eav
     */
    public function getAttributeModelByCode($option, $attributeCode)
    {
        return Mage::getModel('aw_layerednavigation/filter_option_eav')
            ->getFilterAttributeModelByCode($option->getId(), $attributeCode, $option->getStoreId())
        ;
    }

    /**
     * Return attribute collection for option by store id
     * if for not default store not exists attribute, default value will be returned
     *
     * @param $option
     * @return AW_Layerednavigation_Model_Resource_Filter_Option_Eav_Collection
     */
    public function getAttributeCollection($option)
    {
        return Mage::getResourceModel('aw_layerednavigation/filter_option_eav_collection')
            ->getAttributeCollectionByStoreId($option->getId(), $option->getStoreId())
        ;
    }

    /**
     * After Load push attribute values for store to model
     *
     * @param Mage_Core_Model_Abstract $option
     *
     * @return AW_Layerednavigation_Model_Resource_Filter_Option
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $option)
    {
        foreach ($this->getAttributeCollection($option) as $attribute) {
            $option->setData($attribute->getName(), $attribute->getValue());
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $option)
    {
        $this->_processOptionAttributes($option);
        return $this;
    }

    protected function _processOptionAttributes($option)
    {
        $defaultAttributeCodeList = array();
        if (is_array($option->getData('use_default'))) {
            $defaultAttributeCodeList = array_keys($option->getData('use_default'));
        }

        foreach (Mage::helper('aw_layerednavigation/attribute')->getOptionAttributes() as $attributeCode) {
            $optionAttributeModel = $this->getAttributeModelByCode($option, $attributeCode);

            if (in_array($attributeCode, $defaultAttributeCodeList)) {
                $optionAttributeModel->delete();
            } else if(null !== $option->getData($attributeCode)) {
                $optionAttributeModel->addData(
                    array(
                         'option_id' => $option->getId(),
                         'store_id'  => $option->getStoreId(),
                         'name'      => $attributeCode,
                         'value'     => $option->getData($attributeCode),
                    )
                );
                $optionAttributeModel->save();
            }
        }
        return $this;
    }
}