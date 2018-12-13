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


class AW_Layerednavigation_Model_Resource_Indexer_Type_Category
    extends AW_Layerednavigation_Model_Resource_Indexer_Type_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter_index_category', 'entity_id');
    }

    protected function _getAttributeCode()
    {
        return null;
    }

    protected function _validateAttribute($attribute)
    {
        return false;
    }

    protected function _prepareIndex($attribute = null, $productIds = array())
    {
        $write    = $this->_getWriteAdapter();
        $idxTable = $this->getIdxTable();
        $select   = $write->select()
            ->from(array('ccpi' => $this->getTable('catalog/category_product_index')),
                array(
                    'entity_id'    => 'ccpi.product_id',
                    'category_ids' => 'GROUP_CONCAT(ccpi.category_id)',
                    'store_id'     => 'ccpi.store_id'
                )
            )
            ->where('ccpi.visibility != ?', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
            ->group(array('ccpi.product_id', 'ccpi.store_id'))
        ;
        if (!empty($productIds)) {
            $select->where('ccpi.product_id IN(?)', $productIds);
        }
        $query = $select->insertFromSelect($idxTable);
        $write->query($query);
        return $this;
    }

    protected function _prepareRelationIndex()
    {
        return $this;
    }
}