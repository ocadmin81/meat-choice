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


abstract class AW_Layerednavigation_Model_Resource_Indexer_Type_Abstract extends Mage_Index_Model_Mysql4_Abstract
{
    abstract protected function _getAttributeCode();

    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->clearTemporaryIndexTable();
            $this->_prepareIndex();
            $this->_prepareRelationIndex();
            $this->_removeNotVisibleEntityFromIndex();
            $this->syncData();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    public function reindexAttribute($attribute = null, $productIds = array())
    {
        if (null !== $attribute && !$this->_validateAttribute($attribute)) {
            return $this;
        }
        $this->useIdxTable(true);
        $this->clearTemporaryIndexTable();
        $this->_prepareIndex($attribute, $productIds);
        $this->_prepareRelationIndex();
        $this->_removeNotVisibleEntityFromIndex();
        $this->_synchronizeAttributeIndexData($attribute, $productIds);
        return $this;
    }

    protected function _synchronizeAttributeIndexData($attribute = null, $productIds)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            // remove index by attribute
            $where = $adapter->quoteInto('entity_id IN (?)', $productIds);
            if (null !== $attribute) {
                $where .= ' AND ' . $adapter->quoteInto('attribute_id = ?', $attribute->getId());
            }
            $adapter->delete($this->getMainTable(), $where);

            // remove additional data from index
            $where = $adapter->quoteInto('entity_id NOT IN (?)', $productIds);
            if (null !== $attribute) {
                $where .= ' OR ' . $adapter->quoteInto('attribute_id != ?', $attribute->getId());
            }
            $adapter->delete($this->getIdxTable(), $where);

            // insert new index
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        return $this;
    }

    public function deleteProduct($productId)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $where = $adapter->quoteInto('entity_id = ?', $productId);
            $adapter->delete($this->getMainTable(), $where);
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        return $this;
    }

    protected function _prepareIndex($attribute = null, $productIds = array())
    {
        if (null === $attribute) {
            $attributesCodes = Mage::getResourceModel('aw_layerednavigation/filter_collection')
                ->addTypeFilter($this->_getAttributeCode())
                ->getColumnValues('code')
            ;
        } else {
            $attributesCodes = array($attribute->getAttributeCode());
        }
        $write    = $this->_getWriteAdapter();
        $idxTable = $this->getIdxTable();
        $select   = $this->_prepareSelect($write->select(), $attributesCodes);

        if (!empty($productIds)) {
            $select->where('ccpi.product_id  IN(?)', $productIds);
        }
        $query = $select->insertIgnoreFromSelect($idxTable);
        $write->query($query);
        return $this;
    }

    protected function _prepareSelect($select, $attributesCodes)
    {
        $select
            ->from(array('ccpi' => $this->getTable('catalog/category_product_index')),
                array(
                    'entity_id' => 'ccpi.product_id',
                )
            )
            ->joinLeft(array('cpe' => $this->getTable('catalog/product')),
                'cpe.entity_id = ccpi.product_id',
                array()
            )
            ->joinLeft(array('ea' => $this->getTable('eav/attribute')),
                'ea.entity_type_id = cpe.entity_type_id',
                array('attribute_id' => 'ea.attribute_id')
            )
            ->joinLeft(array('cpev' => $this->getValueTable('catalog/product', 'varchar')),
                'cpev.entity_id = ccpi.product_id AND cpev.attribute_id = ea.attribute_id AND cpev.store_id = ccpi.store_id',
                array()
            )
            ->joinLeft(array('cpev_def' => $this->getValueTable('catalog/product', 'varchar')),
                'cpev_def.entity_id = ccpi.product_id AND cpev_def.attribute_id = ea.attribute_id AND cpev_def.store_id = 0',
                array()
            )
            ->joinLeft(array('cpei' => $this->getValueTable('catalog/product', 'int')),
                'cpei.entity_id = ccpi.product_id AND cpei.attribute_id = ea.attribute_id AND cpei.store_id = ccpi.store_id',
                array()
            )
            ->joinLeft(array('cpei_def' => $this->getValueTable('catalog/product', 'int')),
                'cpei_def.entity_id = ccpi.product_id AND cpei_def.attribute_id = ea.attribute_id AND cpei_def.store_id = 0',
                array(
                    'value' => 'IFNULL(IFNULL(cpev.value, cpev_def.value), IFNULL(cpei.value, cpei_def.value))',
                    'store_id' => 'ccpi.store_id',
                )
            )
            ->where('ea.attribute_code IN(?)', $attributesCodes)
            ->group(array('entity_id', 'attribute_id', 'store_id'))
            ->having('value is not null')
        ;
        return $select;
    }

    protected function _prepareRelationIndex()
    {
        $write      = $this->_getWriteAdapter();
        $idxTable   = $this->getIdxTable();
        $select = $write->select()
            ->from(array('l' => $this->getTable('catalog/product_relation')), 'parent_id')
            ->join(
                array('cs' => $this->getTable('core/store')),
                '',
                array()
            )
            ->join(
                array('i' => $idxTable),
                'l.child_id = i.entity_id AND cs.store_id = i.store_id',
                array('attribute_id', 'GROUP_CONCAT(i.value)', 'store_id')
            )
            ->group(
                array(
                    'l.parent_id', 'i.attribute_id', 'i.store_id'
                )
            )
        ;
        $query = $select->insertIgnoreFromSelect($idxTable);
        $write->query($query);
        return $this;
    }

    protected function _removeNotVisibleEntityFromIndex()
    {
        $idxTable   = $this->getIdxTable();
        $read       = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('idx' => $idxTable), 'entity_id')
            ->joinLeft(array('ccpi' => $this->getTable('catalog/category_product_index')),
                'ccpi.product_id = idx.entity_id',
                array()
            )
            ->where('ccpi.visibility = ?', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
            ->distinct()
        ;
        $productIds = $read->fetchCol($select->__toString(), 'entity_id');
        if (count($productIds) > 0) {
            $write  = $this->_getWriteAdapter();
            $select = $write->select()
                ->from($idxTable, null)
                ->where('entity_id IN(?)', $productIds)
            ;
            $query = $select->deleteFromSelect($idxTable);
            $write->query($query);
        }
        return $this;
    }
}