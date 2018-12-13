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


class AW_Layerednavigation_Block_Category_View extends Mage_Catalog_Block_Category_View
{
    public function isContentMode()
    {
        if (!Mage::helper("aw_layerednavigation/config")->isEnabled()) {
            return parent::isContentMode();
        }
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PAGE) {
            $res = true;
            if ($category->getIsAnchor()) {
                $filters = $this->getFilterList();
                $params = $this->getRequest()->getParams();
                foreach($filters as $filter) {
                    if (array_key_exists($filter->getCode(), $params)){
                        $res = false;
                    }
                }
            }
        }
        return $res;
    }

    public function getFilterList()
    {
        $filterCollection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
            ->addFilterAttributes(Mage::app()->getStore()->getId())
            ->addIsEnabledFilter()
            ->addCategoryFilter($this->getCurrentCategory())
            ->sortByPosition()
        ;

        return $filterCollection;
    }

    public function getCmsBlockHtml()
    {
        if (!$this->getData('cms_block_html')) {
            $html = '<div class="category_cms_block">';
            $html .= $this->getLayout()->createBlock('cms/block')
                ->setBlockId($this->getCurrentCategory()->getLandingPage())
                ->toHtml();
            $html .= '</div>';
            $this->setData('cms_block_html', $html);
        }
        return $this->getData('cms_block_html');
    }
}