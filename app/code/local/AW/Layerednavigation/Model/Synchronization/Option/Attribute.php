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

class AW_Layerednavigation_Model_Synchronization_Option_Attribute
    extends AW_Layerednavigation_Model_Synchronization_Abstract
{
    const ATTRIBUTE_TYPE_SELECT = 'select';
    const ATTRIBUTE_TYPE_MULTISELECT = 'multiselect';

    protected $_attributeCodeList = array();
    protected $_filterAttributeCodeList = array();

    /**
     * Prepare attributes and filters
     */
    public function __construct()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection
            ->addFieldToFilter(
                'frontend_input', array('in' => array(self::ATTRIBUTE_TYPE_SELECT, self::ATTRIBUTE_TYPE_MULTISELECT))
            )
            ->getSelect()
            ->where("(`source_model` NOT IN('core/design_source_design')) OR (`source_model` IS NULL)")
        ;

        foreach ($collection as $key => $attribute) {
            if ($attribute->getData('is_user_defined') || $attribute->getData('is_filterable')) {
                $this->_attributeCodeList[$key] = $attribute->getData('attribute_code');
            }
        }

        $filterCollection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
            ->addTypeFilter(AW_Layerednavigation_Model_Source_Filter_Type::OPTION_ATTRIBUTE_CODE)
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

            if (in_array($attributeCode, $this->_filterAttributeCodeList)) {
                $filterId = array_search($attributeCode, $this->_filterAttributeCodeList);
                $filterModel = Mage::getModel('aw_layerednavigation/filter')->load($filterId);
            } else {
                $filterModel = $this->_createFilterByAttribute($attributeModel);
            }
            if (null !== $filterModel) {
                $this->_processOptions($filterModel, $attributeModel);
            }
        }
    }

    public function runObserve($observer)
    {
        $attribute = $observer->getAttribute();
        if (
            !in_array(
                $attribute->getData('frontend_input'),
                array(self::ATTRIBUTE_TYPE_SELECT, self::ATTRIBUTE_TYPE_MULTISELECT)
            )
        ) {
            return $this;
        }

        if (!$attribute->getData('is_user_defined') && !$attribute->getData('is_filterable')) {
            return $this;
        }

        $this->run();

        // Update labels
        $this->_updateLabels($attribute);
        return $this;
    }

    /**
     * Shop By Brand compatibility
     */
    public function runObserveSbb($observer)
    {
        if ($observer->getObject() instanceof AW_Shopbybrand_Model_Brand) {
            $attributeCode = AW_Shopbybrand_Model_Source_Attribute::ATTRIBUTE_CODE;
            $attributeId = array_search($attributeCode, $this->_attributeCodeList);
            if ($attributeId) {
                $attributeModel = Mage::getModel('catalog/entity_attribute')->load($attributeId);
                if (in_array($attributeCode, $this->_filterAttributeCodeList)) {
                    $filterId = array_search($attributeCode, $this->_filterAttributeCodeList);
                    $filterModel = Mage::getModel('aw_layerednavigation/filter')->load($filterId);
                } else {
                    $filterModel = $this->_createFilterByAttribute($attributeModel);
                }
                if (null !== $filterModel) {
                    $this->_processOptions($filterModel, $attributeModel);
                }
            }
        }
        return $this;
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
        if (in_array('option', $synchList)) {
            $this->_dropOptionLabels($filterModel);

            $optionLabels = $this->_getOptionLabelValues($attribute);
            foreach ($filterModel->getOptionCollection() as $optionId => $optionModel) {
                if (!$attribute->getIsUserDefined() && $attribute->getSourceModel()) {
                    $optionIdKey = 'additional_data/option_value';
                } else {
                    $optionIdKey = 'additional_data/option_id';
                }
                if (array_key_exists($optionModel->getData($optionIdKey), $optionLabels)) {
                    foreach ($optionLabels[$optionModel->getData($optionIdKey)] as $storeId => $value) {
                        $data = array(
                            'option_id' => $optionId,
                            'store_id'  => $storeId,
                            'name'      => 'title',
                            'value'     => $value,
                        );
                        try {
                            Mage::getModel('aw_layerednavigation/filter_option_eav')->setData($data)->save();
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                }
            }
        }
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
            'type'                       => AW_Layerednavigation_Model_Source_Filter_Type::OPTION_ATTRIBUTE_CODE,
            'is_enabled'                 => (int)(bool)$attributeModel->getIsFilterable(),
            'is_enabled_in_search'       => (int)(bool)$attributeModel->getIsFilterableInSearch(),
            'code'                       => $this->getUniqueCode($attributeModel->getData('attribute_code')),
            'position'                   => (int)$attributeModel->getPosition(),
            'display_type'               => $this->_getDisplayTypeByFrontendType($attributeModel->getFrontendInput()),
            'image_position'             => AW_Layerednavigation_Model_Source_Filter_Image_Position::TEXT_ONLY_CODE,
            'is_row_count_limit_enabled' => self::IS_ROW_COUNT_LIMIT_STATUS,
            'row_count_limit'            => self::ROW_COUNT_LIMIT,
            'additional_data'            => array('attribute_code' => $attributeModel->getAttributeCode()),
        );
        try {
            $filterModel = Mage::getModel('aw_layerednavigation/filter')->setData($filterData)->save();
            $this->_filterAttributeCodeList[$filterModel->getId()] = $filterModel->getCode();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }

        $this->_saveFilterLabels($filterModel, $attributeModel);
        return $filterModel;
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
     * Remove old attribute options
     *
     * @param AW_Layerednavigation_Model_Filter $filterModel
     * @param Mage_Catalog_Model_Entity_Attribute $attributeModel
     */
    protected function _processOptions($filterModel, $attributeModel)
    {
        $filterOptionValueList = array();
        foreach ($filterModel->getOptionCollection() as $key => $option) {
            $optionAdditionalData = $option->getAdditionalData();
            if (!$attributeModel->getIsUserDefined() && $attributeModel->getSourceModel()) {
                $filterOptionValueList[$key] = $optionAdditionalData['option_value'];
            } else {
                $filterOptionValueList[$key] = $optionAdditionalData['option_id'];
            }
        }

        $attributeOptionValueList = array();
        foreach ($this->_getOptionArrayList($attributeModel) as $optionData) {

            if (!$attributeModel->getIsUserDefined() && $attributeModel->getSourceModel()) {
                if (!empty($optionData['value'])) {
                    $attributeOptionValueList[] = $optionData['value'];
                }
            } else {
                $attributeOptionValueList[] = $optionData['option_id'];
            }
        }

        // Remove old options
        $optionsForDelete = array_diff($filterOptionValueList, $attributeOptionValueList);
        $this->_removeOldOptions($optionsForDelete);

        // Create new options
        $optionsForCreate = array_diff($attributeOptionValueList, $filterOptionValueList);
        $this->_createNewOptions($filterModel, $attributeModel, $optionsForCreate);
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filterModel
     * @return $this
     */
    protected function _dropOptionLabels($filterModel)
    {
        $optionIdList = $filterModel->getOptionCollection()->getAllIds();
        $eavCollection = Mage::getResourceModel('aw_layerednavigation/filter_option_eav_collection')
            ->addFilterOptionId($optionIdList)
            ->addFilterAttributeCode('title')
        ;
        foreach ($eavCollection as $eavItem) {
            $eavItem->delete();
        }
        return $this;
    }

    /**
     * Remove old filter options
     *
     * @param array $optionsForDelete
     *
     * @return int
     */
    protected function _removeOldOptions($optionsForDelete = array())
    {
        foreach (array_flip($optionsForDelete) as $optionId) {
            Mage::getModel('aw_layerednavigation/filter_option')->setId($optionId)->delete();
        }
        return count($optionsForDelete);
    }


    /**
     * Create new options for filter
     * Save option labels for stores
     *
     * @param AW_Layerednavigation_Model_Filter   $filterModel
     * @param Mage_Catalog_Model_Entity_Attribute $attributeModel
     * @param array                               $optionsForCreate
     * @return $this
     */
    protected function _createNewOptions($filterModel, $attributeModel, $optionsForCreate = array())
    {
        // Save filter options
        $optionLabels = $this->_getOptionLabelValues($attributeModel);

        $attributePosition = 0;
        foreach ($this->_getOptionArrayList($attributeModel) as $optionKey => $optionData) {

            $filterOptionValue = null;
            $additionalData = array();
            if (!$attributeModel->getIsUserDefined() && $attributeModel->getSourceModel()) {
                $filterOptionValue = $optionData['value'];
                $additionalData['option_value'] = $filterOptionValue;

                $labelKey = $optionKey;
            } else {
                $filterOptionValue = $optionData['option_id'];
                $additionalData['option_id'] = $filterOptionValue;

                $labelKey = $filterOptionValue;
            }

            if (in_array($filterOptionValue, $optionsForCreate)) {
                if (array_key_exists('sort_order', $optionData)) {
                    $position = (int)$optionData['sort_order'];
                } else {
                    $position = $attributePosition++;;
                }
                $optionModelData = array(
                    'filter_id'       =>  $filterModel->getId(),
                    'is_enabled'      => 1,
                    'position'        => $position,
                    'title'           => $optionLabels[$labelKey][Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID],
                    'additional_data' => $additionalData,
                );

                try {
                    $optionModel = Mage::getModel('aw_layerednavigation/filter_option')
                        ->setData($optionModelData)
                        ->save()
                    ;
                } catch (Exception $e) {
                    Mage::logException($e);
                    return $this;
                }

                // Save option labels for stores

                foreach ($optionLabels[$labelKey] as $storeId => $optionLabel) {
                    if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                        $optionLabelData = array(
                            'option_id' => $optionModel->getId(),
                            'store_id'  => $storeId,
                            'name'      => 'title',
                            'value'     => $optionLabel,
                        );

                        try {
                            Mage::getModel('aw_layerednavigation/filter_option_eav')
                                ->setData($optionLabelData)
                                ->save()
                            ;
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Retrieve attribute option values if attribute input type select or multiselect
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute $attribute
     *
     * @return array
     */
    protected function _getOptionArrayList($attribute)
    {
        $optionArray = array();
        if (!$attribute->getIsUserDefined() && $attribute->getSourceModel()) {
            $allOptions = Mage::getModel($attribute->getSourceModel())->setAttribute($attribute)->getAllOptions(false);
            foreach ($allOptions as $option) {
                if (trim($option['value']) != '') {
                    $optionArray[$option['value']] = $option;
                }
            }
        } else {
            $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setPositionOrder('asc')
                ->setAttributeFilter($attribute->getId())
                ->setStoreFilter($attribute->getStoreId())
            ;
            foreach ($collection as $option) {
                $optionArray[] = $option->getData();
            }
        }

        return $optionArray;
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
     * Retrieve frontend labels of option for each store
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute $attribute
     *
     * @return array
     */
    protected function _getOptionLabelValues($attribute)
    {
        $values = array();
        foreach ($this->_getStores() as $store) {
            foreach ($this->_getStoreOptionValues($attribute, $store->getId()) as $optionId => $optionLabel) {
                if (!$attribute->getIsUserDefined() && $attribute->getSourceModel()) {
                    if (trim($optionLabel['value']) != '') {
                        $values[$optionLabel['value']][$store->getId()] = $optionLabel['label'];
                    }
                } else {
                    $values[$optionId][$store->getId()] = $optionLabel;
                }
            }
        }
        return $values;
    }

    /**
     * Retrieve attribute option values for given store id
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute $attribute
     * @param int $storeId
     *
     * @return array
     */
    protected function _getStoreOptionValues($attribute, $storeId)
    {
        $values = array();
        if (!$attribute->getIsUserDefined() && $attribute->getSourceModel()) {
            $values = Mage::getModel($attribute->getSourceModel())->setAttribute($attribute)->getAllOptions(false);
        } else {
            $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attribute->getId())
                ->setStoreFilter($storeId, false)
            ;
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
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

    /**
     * Retrieve filter display type by attribute frontend type
     *
     * @param string $frontendType
     *
     * @return int
     */
    protected function _getDisplayTypeByFrontendType($frontendType)
    {
        switch ($frontendType) {
            case self::ATTRIBUTE_TYPE_SELECT:
                return AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE;
            case self::ATTRIBUTE_TYPE_MULTISELECT:
                return AW_Layerednavigation_Model_Source_Filter_Display_Type::CHECKBOX_CODE;
            default:
                return AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE;
        }
    }
}