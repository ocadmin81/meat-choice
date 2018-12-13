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


class AW_Layerednavigation_Model_Synchronization_Category
    extends AW_Layerednavigation_Model_Synchronization_Abstract
{
    protected $_categoryFilter = null;
    protected $_categoryCollection = null;

    protected $_categoryIdList = array();
    protected $_filterCategoryIdList = array();

    /**
     * Prepare categories and filter options
     */
    public function __construct()
    {
        foreach ($this->_getCategoryCollection() as $categoryId => $category) {
            $this->_categoryIdList[$categoryId] = $category->getId();
        }

        foreach ($this->_getCategoryFilter()->getOptionCollection() as $optionId => $optionModel) {
            $filterAdditionalData = $optionModel->getAdditionalData();
            if (array_key_exists('category_id', $filterAdditionalData)) {
                $this->_filterCategoryIdList[$optionId] = $filterAdditionalData['category_id'];
            }
        }
    }

    /**
     * Remove filter options which categories was deleted
     * Create new filter options for new categories
     */
    public function run()
    {
        $categoryFilterModel = $this->_getCategoryFilter();
        $this->_removeCategoryOptions();
        $this->_processCategoryTree($categoryFilterModel);
    }

    public function runObserve($observer)
    {
        $this->run();
    }

    /**
     * Update EAV after category save
     */
    public function categoryPrepareSave($observer)
    {
        $category = $observer->getData('category');
        if (!$category->getId()) {
            return $this;
        }

        $synchMap = array(
            'name'        => 'title',
            'is_active'   => 'is_enabled',
            'description' => 'description',
        );

        $categorySynchList = array_keys($observer->getData('request')->getParam('synchronize', array()));
        $useDefaultList = $observer->getData('request')->getParam('use_default', array());

        $storeId = $category->getStoreId();
        $optionId = null;

        foreach ($this->_getCategoryFilter()->getOptionCollection() as $optionId => $optionModel) {
            if ($optionModel->getData('additional_data/category_id') == $category->getId()) {
                $optionId = $optionModel->getId();
                break;
            }
        }

        $categoryData = $observer->getData('request')->getParam('general', array());

        foreach ($categorySynchList as $attributeCode) {
            if (!array_key_exists($attributeCode, $synchMap)) {
                continue;
            }
            $eavOptionModel = Mage::getModel('aw_layerednavigation/filter_option_eav')->getFilterAttributeModelByCode(
                $optionId, $synchMap[$attributeCode], $storeId
            );
            if (in_array($attributeCode, $useDefaultList)) {
                if ($storeId !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                    $eavOptionModel->delete();
                }
            } elseif (array_key_exists($attributeCode, $categoryData)) {
                $eavOptionModel
                    ->addData(
                        array(
                            'option_id' => $optionId,
                            'store_id'  => $storeId,
                            'name'      => $synchMap[$attributeCode],
                            'value'     => $categoryData[$attributeCode],
                        )
                    )
                    ->save()
                ;
            }
        }
    }

    /**
     * Get category filter, if filter does not exist, create it
     *
     * @return AW_Layerednavigation_Model_Filter
     */
    protected function _getCategoryFilter()
    {
        if ($this->_categoryFilter === null) {
            $collection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
                ->addFieldToFilter(
                    'type', array('eq' => AW_Layerednavigation_Model_Source_Filter_Type::CATEGORY_CODE)
                )
            ;
            $categoryFilterModel = $collection->getFirstItem();

            if (!$categoryFilterModel->getId()) {
                $displayType = AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE;
                $imagePosition = AW_Layerednavigation_Model_Source_Filter_Image_Position::TEXT_ONLY_CODE;
                $categoryFilterData = array(
                    'title'                      => Mage::helper('aw_layerednavigation')->__('Category'),
                    'type'                       => AW_Layerednavigation_Model_Source_Filter_Type::CATEGORY_CODE,
                    'is_enabled'                 => 1,
                    'is_enabled_in_search'       => 1,
                    'code'                       => $this->getUniqueCode('category'),
                    'position'                   => 0,
                    'display_type'               => $displayType,
                    'image_position'             => $imagePosition,
                    'is_row_count_limit_enabled' => self::IS_ROW_COUNT_LIMIT_STATUS,
                    'row_count_limit'            => self::ROW_COUNT_LIMIT,
                    'additional_data'            => array(),
                );
                $categoryFilterModel->setData($categoryFilterData)->save();
            }
            $this->_categoryFilter = $categoryFilterModel;
        }
        return $this->_categoryFilter;
    }

    /**
     * Remove options which category was deleted
     */
    protected function _removeCategoryOptions()
    {
        foreach (array_diff($this->_filterCategoryIdList, $this->_categoryIdList) as $optionId => $categoryId) {
            Mage::getModel('aw_layerednavigation/filter_option')->setId($optionId)->delete();
        }
    }

    /**
     * Create new filter options for new categories
     *
     * @param AW_Layerednavigation_Model_Filter $categoryFilterModel
     */
    protected function _processCategoryTree($categoryFilterModel)
    {
        $categoryIdListForCreate = array_diff($this->_categoryIdList, $this->_filterCategoryIdList);

        $categoryCollection = Mage::getResourceModel('catalog/category_collection');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach ($categoryCollection as $category) {
            if (in_array($category->getId(), $categoryIdListForCreate)) {
                $additionalData = array(
                    'category_id' => $category->getId(),
                    'parent_id'   => $category->getData('parent_id'),
                    'path'        => $category->getData('path'),
                );
                $optionModelData = array(
                    'filter_id'       => $categoryFilterModel->getId(),
                    'position'        => (int)$category->getData('position'),
                    'additional_data' => $additionalData,
                );

                $optionModel = Mage::getModel('aw_layerednavigation/filter_option')->setData($optionModelData)->save();
                $this->_filterCategoryIdList[$optionModel->getId()] = $category->getId();

                try {
                    // Save option labels for stores
                    $this->_createOptionsCategoryName($category, $optionModel);

                    // Save option description for stores
                    $this->_createOptionsCategoryDescription($category, $optionModel);

                    // Save option status for stores
                    $this->_createOptionsCategoryStatus($category, $optionModel);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            //Update filter options for exists categories
            $_optionId = array_search($category->getId(), $this->_filterCategoryIdList);
            if ($_optionId) {
                $_optionModel = Mage::getModel('aw_layerednavigation/filter_option')->load($_optionId);
                $additionalData = $_optionModel->getData('additional_data');
                $additionalData['path'] = $category->getData('path');
                $additionalData['parent_id'] = $category->getData('parent_id');
                try {
                    $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation_filter_option')
                        . ' SET additional_data = ' . $write->quote(serialize($additionalData))
                        . ' WHERE option_id = ' . $_optionId
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }

    /**
     * @param $category
     * @param $optionModel
     *
     * @return $this
     */
    protected function _createOptionsCategoryName($category, $optionModel)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $categoryNameAttribute = $category->getResource()->getAttribute('name');

        $write->query('INSERT INTO ' . $this->_getTableName('aw_layerednavigation_filter_option_eav')
            . ' (option_id, store_id, name, value)'
            . ' SELECT ' . $optionModel->getId()
            . ', _category_name_value.store_id, "title", _category_name_value.value FROM '
            . $this->_getTableName('catalog_category_entity')
            . ' AS e LEFT JOIN ' . $this->_getTableName('catalog_category_entity_varchar')
            . ' AS _category_name_value ON _category_name_value.attribute_id = ' . $categoryNameAttribute->getId()
            . ' AND e.entity_id = _category_name_value.entity_id AND e.entity_id = ' . $category->getId()
            . ' WHERE _category_name_value.value IS NOT NULL'
            . ' GROUP BY _category_name_value.store_id, _category_name_value.value'
        );
        return $this;
    }

    /**
     * @param $category
     * @param $optionModel
     *
     * @return $this
     */
    protected function _createOptionsCategoryStatus($category, $optionModel)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $categoryStatusAttribute = $category->getResource()->getAttribute('is_active');
        $write->query('INSERT INTO ' . $this->_getTableName('aw_layerednavigation_filter_option_eav')
            . ' (option_id, store_id, name, value)'
            . ' SELECT ' . $optionModel->getId()
            . ', _category_status_value.store_id, "is_enabled", _category_status_value.value FROM '
            . $this->_getTableName('catalog_category_entity')
            . ' AS e LEFT JOIN ' . $this->_getTableName('catalog_category_entity_int')
            . ' AS _category_status_value ON _category_status_value.attribute_id = '
            . $categoryStatusAttribute->getId()
            . ' AND e.entity_id = _category_status_value.entity_id AND e.entity_id = ' . $category->getId()
            . ' WHERE _category_status_value.value IS NOT NULL'
            . ' GROUP BY _category_status_value.store_id, _category_status_value.value'
        );
        return $this;
    }

    /**
     * @param $category
     * @param $optionModel
     *
     * @return $this
     */
    protected function _createOptionsCategoryDescription($category, $optionModel)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $categoryDescriptionAttribute = $category->getResource()->getAttribute('description');
        $write->query('INSERT INTO ' . $this->_getTableName('aw_layerednavigation_filter_option_eav')
            . ' (option_id, store_id, name, value)'
            . ' SELECT ' . $optionModel->getId()
            . ', _category_description_value.store_id, "description", _category_description_value.value FROM '
            . $this->_getTableName('catalog_category_entity')
            . ' AS e LEFT JOIN ' . $this->_getTableName('catalog_category_entity_text')
            . ' AS _category_description_value ON _category_description_value.attribute_id = '
            . $categoryDescriptionAttribute->getId()
            . ' AND e.entity_id = _category_description_value.entity_id AND e.entity_id = ' . $category->getId()
            . ' WHERE _category_description_value.value IS NOT NULL'
            . ' GROUP BY _category_description_value.store_id, _category_description_value.value'
        );
        return $this;
    }

    /**
     * Get category collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
     */
    protected function _getCategoryCollection()
    {
        if ($this->_categoryCollection === null) {
            $this->_categoryCollection = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('level', array('gt' => array(1)))
            ;
        }
        return $this->_categoryCollection;
    }

    protected function _getTableName($name)
    {
        return Mage::getSingleton('core/resource')->getTableName($name);
    }
}