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


class AW_Layerednavigation_Model_Synchronization_Yesno_Attribute
    extends AW_Layerednavigation_Model_Synchronization_Abstract
{
    const ATTRIBUTE_TYPE_YESNO = 'boolean';

    const ROW_COUNT_LIMIT      = 2;

    protected $_attributeCodeList = array();
    protected $_filterAttributeCodeList = array();

    public function __construct()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');

        $collection
            ->addFieldToFilter(
                'frontend_input', array('in' => array(self::ATTRIBUTE_TYPE_YESNO))
            )
        ;

        foreach ($collection as $key => $attribute) {
            $this->_attributeCodeList[$key] = $attribute->getData('attribute_code');
        }

        $filterCollection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
            ->addTypeFilter(AW_Layerednavigation_Model_Source_Filter_Type::YESNO_ATTRIBUTE_CODE)
        ;

        foreach ($filterCollection as $key => $filter) {
            $filterAdditionalData = $filter->getAdditionalData();
            if (array_key_exists('attribute_code', $filterAdditionalData)) {
                $this->_filterAttributeCodeList[$key] = $filterAdditionalData['attribute_code'];
            }
        }
    }

    public function isAllowedAttribute($attributeModel)
    {
        return in_array($attributeModel->getAttributeCode(), $this->_attributeCodeList);
    }

    /**
     * Remove filters which attributes was deleted
     * Create new filter for new attributes
     * Remove filter options which attribute
     */
    public function run()
    {
        $this->_removeFilters();
        foreach ($this->_attributeCodeList as $attributeId => $attributeCode) {
            $attributeModel = Mage::getModel('catalog/entity_attribute')->load($attributeId);
            if (!in_array($attributeCode, $this->_filterAttributeCodeList)) {
                $filterModel = $this->_createFilterByAttribute($attributeModel);
                $this->_processOptions($filterModel, $attributeModel);
            }
        }
    }

    public function runObserve($observer)
    {
        $attribute = $observer->getAttribute();
        if (!$this->isAllowedAttribute($attribute)) {
            return $this;
        }
        $this->run();

        // Update labels
        $this->_updateLabels($attribute);
        return $this;
    }

    /**
     * Remove filters which attributes was deleted
     * @return int
     */
    protected function _removeFilters()
    {
        $oldAttributeCodeList = array_diff($this->_filterAttributeCodeList, $this->_attributeCodeList);
        foreach (array_flip($oldAttributeCodeList) as $filterId) {
            Mage::getModel('aw_layerednavigation/filter')->setId($filterId)->delete();
        }
        return count($oldAttributeCodeList);
    }

    /**
     * Create filter by attribute
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attributeModel
     *
     * @return AW_Layerednavigation_Model_Filter|null
     */
    protected function _createFilterByAttribute($attributeModel)
    {
        $filterData = array(
            'title'                      => $attributeModel->getFrontendLabel(),
            'type'                       => AW_Layerednavigation_Model_Source_Filter_Type::YESNO_ATTRIBUTE_CODE,
            'is_enabled'                 => 0,
            'is_enabled_in_search'       => 0,
            'code'                       => $this->getUniqueCode($attributeModel->getData('attribute_code')),
            'position'                   => (int)$attributeModel->getPosition(),
            'display_type'               => AW_Layerednavigation_Model_Source_Filter_Display_Type::CHECKBOX_CODE,
            'image_position'             => AW_Layerednavigation_Model_Source_Filter_Image_Position::TEXT_ONLY_CODE,
            'is_row_count_limit_enabled' => self::IS_ROW_COUNT_LIMIT_STATUS,
            'row_count_limit'            => self::ROW_COUNT_LIMIT,
            'additional_data'            => array('attribute_code' => $attributeModel->getAttributeCode()),
        );
        try {
            $filterModel = Mage::getModel('aw_layerednavigation/filter')->setData($filterData)->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }

        $this->_saveFilterLabels($filterModel, $attributeModel);
        return $filterModel;
    }

    protected function _updateLabels($attribute)
    {
        $synchList = $attribute->getData('synchronize');
        if ($synchList === null) {
            return $this;
        }
        $synchList = array_keys($synchList);

        $attribute = Mage::getModel('catalog/entity_attribute')->load($attribute->getId());

        $filterId = array_search($attribute->getAttributeCode(), $this->_filterAttributeCodeList);
        $filterModel = Mage::getModel('aw_layerednavigation/filter')->load($filterId);
        if (in_array('attribute', $synchList)) {
            $this->_dropFilterLabels($filterModel);
            $data = array(
                'filter_id' => $filterId,
                'store_id'  => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                'name'      => 'title',
                'value'     => $attribute->getFrontendLabel(),
            );
            try {
                Mage::getModel('aw_layerednavigation/filter_eav')->setData($data)->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->_saveFilterLabels($filterModel, $attribute);
        }
    }

    /**
     * Drop filter labels
     *
     * @param AW_Layerednavigation_Model_Filter $filterModel
     */
    protected function _dropFilterLabels($filterModel)
    {
        $eavCollection = Mage::getResourceModel('aw_layerednavigation/filter_eav_collection')
            ->addFilterFilterId($filterModel->getId())
            ->addFilterAttributeCode('title')
        ;
        foreach ($eavCollection as $eavItem) {
            $eavItem->delete();
        }
    }

    /**
     * Process attribute options
     * Create new filter options
     *
     * @param AW_Layerednavigation_Model_Filter $filterModel
     * @param Mage_Catalog_Model_Entity_Attribute $attributeModel
     */
    protected function _processOptions($filterModel, $attributeModel)
    {
        $booleanOptionArray = array();
        if ($sourceModel = Mage::getModel($attributeModel->getSourceModel())) {
            $booleanOptionArray = $sourceModel->getOptionArray();
        } else {
            $booleanOptionArray = array(
                0 => Mage::helper('aw_layerednavigation')->__('No'),
                1 => Mage::helper('aw_layerednavigation')->__('Yes'),
            );
        }

        foreach ($booleanOptionArray as $value => $title) {
            $optionModelData = array(
                'filter_id'       => $filterModel->getId(),
                'is_enabled'      => 1,
                'position'        => $value,
                'title'           => $title,
                'additional_data' => array('value' => $value),
            );
            try {
                $optionModel = Mage::getModel('aw_layerednavigation/filter_option')
                    ->setData($optionModelData)
                    ->save()
                ;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Create labels for filter store view
     *
     * @param AW_Layerednavigation_Model_Filter $filterModel
     * @param Mage_Catalog_Model_Entity_Attribute $attributeModel
     */
    protected function _saveFilterLabels($filterModel, $attributeModel)
    {
        $filterLabels = $this->_getLabelValues($attributeModel);
        foreach ($filterLabels as $storeId => $filterLabel) {
            $filterLabelData = array(
                'filter_id' => $filterModel->getId(),
                'store_id'  => $storeId,
                'name'      => 'title',
                'value'     => $filterLabel,
            );
            try {
                Mage::getModel('aw_layerednavigation/filter_eav')->setData($filterLabelData)->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Retrieve frontend labels of attribute for each store
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute $attribute
     *
     * @return array
     */
    protected function _getLabelValues($attribute)
    {
        $values = array();
        $storeLabels = $attribute->getStoreLabels();
        foreach ($this->_getStores() as $store) {
            if (($store->getId() != 0) && (isset($storeLabels[$store->getId()]))) {
                if (isset($storeLabels[$store->getId()])) {
                    $values[$store->getId()] = $storeLabels[$store->getId()];
                }
            }
        }
        return $values;
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return Mage_Core_Model_Mysql4_Store_Collection
     */
    protected function _getStores()
    {
        $stores = Mage::getModel('core/store')
            ->getResourceCollection()
            ->setLoadDefault(true)
            ->load()
        ;
        return $stores;
    }
}