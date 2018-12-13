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


class AW_Layerednavigation_Block_Filter_Type_Category extends AW_Layerednavigation_Block_Filter_Type_Abstract
{
    /**
     * @return string
     */
    protected function _getTemplate()
    {
        switch($this->getFilter()->getDisplayType()) {
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::CHECKBOX_CODE:
                return 'aw_layerednavigation/filter/category/checkbox.phtml';
                break;
            case AW_Layerednavigation_Model_Source_Filter_Display_Type::RADIO_CODE:
                return 'aw_layerednavigation/filter/category/radiogroup.phtml';
                break;
            default:
                return '';
        }
    }

    /**
     * @return AW_Layerednavigation_Model_Resource_Filter_Option_Collection
     */
    public function getOptionList()
    {
        $optionList = parent::getOptionList();
        if ($this->isCanShowCategoryAsTree()) {
            $optionList = $this->getFilter()->getOptionCollection()->getItems();
        }
        //group options by nesting level
        $optionGroup = array();
        foreach ($optionList as $option) {
            $path = explode("/", $option->getData('additional_data/path'));
            if (!array_key_exists(count($path), $optionGroup)) {
                $optionGroup[count($path)] = array();
            }
            $optionGroup[count($path)][] = $option;
        }
        ksort($optionGroup);
        //sort every group
        foreach ($optionGroup as $level => $group) {
            uasort($group, array($this, '_sortByPosition'));
            $optionGroup[$level] = $group;
        }
        //merge in single array
        $resultList = array();
        foreach ($optionGroup as $level => $group) {
            foreach ($group as $option) {
                $resultList = array_merge($resultList, $this->_mergeGroup($option, $level, $optionGroup));
            }
            break;
        }
        if ($this->isCanShowCategoryAsTree()) {
            //remove all zero option without non-zero childs
            $originalCountOfResultList = count($resultList);
            for ($i = 0; $i < $originalCountOfResultList; $i++) {
                $count = $this->getItemCountByOption($resultList[$i]);
                if ($count > 0) {
                    continue;
                }
                $optionNestingLevel = $this->getNestingLevel($resultList[$i]);
                $isHasChildWithNonZeroCount = false;
                for ($j = $i + 1; $j < $originalCountOfResultList; $j++) {
                    if ($optionNestingLevel >= $this->getNestingLevel($resultList[$j])) {
                        break;
                    }
                    if ($this->getItemCountByOption($resultList[$j]) > 0) {
                        $isHasChildWithNonZeroCount = true;
                        break;
                    }
                }
                if (!$isHasChildWithNonZeroCount || $optionNestingLevel < 0) {
                    unset($resultList[$i]);
                }
            }
        }
        //Compatibility with Enterprise_CatalogPermissions
        if (Mage::helper('core')->isModuleOutputEnabled('Enterprise_CatalogPermissions')) {
            foreach ($resultList as $key => $result) {
                $data = $result->getAdditionalData();
                $categoryId = $data['category_id'];
                $storeId = Mage::app()->getStore()->getId();
                $customerGroupId = Mage::getModel('customer/session')->getId();

                $permResource = Mage::getResourceModel('enterprise_catalogpermissions/permission_index')
                    ->getIndexForCategory($categoryId, $customerGroupId, $storeId);
                foreach ($permResource as $index) {
                    if (isset($index['grant_catalog_category_view']) && $index['grant_catalog_category_view'] == -2) {
                            unset($resultList[$key]);
                    }
                }
            }
        }
        return $resultList;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter_Option $option
     * @param int                                      $basicUnit
     *
     * @return int|mixed
     */
    public function getNestingLevel(AW_Layerednavigation_Model_Filter_Option $option, $basicUnit = 10)
    {
        $currentCategory = $this->getFilter()->getLayer()->getCurrentCategory();
        $path = explode("/", $option->getData('additional_data/path'));
        $rootLevel = array_search($currentCategory->getId(), $path);
        if ($rootLevel === false) {
            $rootLevel = 0;
        }
        $optionLevel = array_search($option->getData('additional_data/category_id'), $path);
        if ($optionLevel === false) {
            $optionLevel = 0;
        }
        $level = $optionLevel - $rootLevel - 1;
        return $level * $basicUnit;
    }

    /**
     * @return bool
     */
    public function isCanShowCollapse()
    {
        if ($this->isCanShowCategoryAsTree()) {
            return false;
        }
        return parent::isCanShowCollapse();
    }

    /**
     * @return bool
     */
    public function isCanShowCategoryAsTree()
    {
        return !!$this->getFilter()->getShowAsTree();
    }

    private function _sortByPosition($a, $b)
    {
        if ($a->getData('position') > $b->getData('position')) {
            return 1;
        } else if ($a->getData('position') < $b->getData('position')) {
            return -1;
        }
        return 0;
    }

    private function _mergeGroup($rootOption, $currentLevel, $optionGroup)
    {
        $result = array($rootOption);
        $newLevel = $currentLevel + 1;
        if (!array_key_exists($newLevel, $optionGroup)) {
            return $result;
        }
        foreach ($optionGroup[$newLevel] as $childOption) {
            $parentId   = $childOption->getData('additional_data/parent_id');
            $categoryId = $rootOption->getData('additional_data/category_id');
            if ($parentId == $categoryId) {
                $childResult = $this->_mergeGroup($childOption, $newLevel, $optionGroup);
                $result = array_merge($result, $childResult);
            }
        }
        return $result;
    }
}
