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


class AW_Layerednavigation_Model_Resource_Filter extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields = array(
        'additional_data' => array(null, array()),
    );

    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter', 'entity_id');
    }


    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     * @param string $attributeCode
     *
     * @return AW_Layerednavigation_Model_Filter_Eav
     */
    public function getAttributeModelByCode($filter, $attributeCode)
    {
        return Mage::getModel('aw_layerednavigation/filter_eav')
            ->getFilterAttributeModelByCode($filter->getId(), $attributeCode, $filter->getStoreId())
        ;
    }

    /**
     * Return attribute collection for filter by store id
     * if for not default store not exists attribute, default value will be returned
     *
     * @param $filter
     * @return AW_Layerednavigation_Model_Resource_Filter_Eav_Collection
     */
    public function getAttributeCollection($filter)
    {
        return Mage::getResourceModel('aw_layerednavigation/filter_eav_collection')
            ->getAttributeCollectionByStoreId($filter->getId(), $filter->getStoreId())
        ;
    }

    /**
     * After Load push attribute values for store to model
     *
     * @param Mage_Core_Model_Abstract $filter
     *
     * @return AW_Layerednavigation_Model_Resource_Filter
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $filter)
    {
        foreach ($this->getAttributeCollection($filter) as $attribute) {
            $filter->setData($attribute->getName(), $attribute->getValue());
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $filter)
    {
        $this->_processFilterAttributes($filter);
        return $this;
    }

    protected function _processFilterAttributes($filter)
    {
        $defaultAttributeCodeList = array();
        if (is_array($filter->getData('use_default'))) {
            $defaultAttributeCodeList = array_keys($filter->getData('use_default'));
        }

        foreach (Mage::helper('aw_layerednavigation/attribute')->getFilterAttributes() as $attributeCode) {
            $filterAttributeModel = $this->getAttributeModelByCode($filter, $attributeCode);
            if (in_array($attributeCode, $defaultAttributeCodeList)) {
                $filterAttributeModel->delete();
            } else {
                $filterAttributeModel->addData(
                    array(
                         'filter_id' => $filter->getId(),
                         'store_id'  => $filter->getStoreId(),
                         'name'      => $attributeCode,
                         'value'     => $filter->getData($attributeCode),
                    )
                );
                $filterAttributeModel->save();
            }
        }
        return $this;
    }
}