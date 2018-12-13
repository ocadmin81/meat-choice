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


class AW_Layerednavigation_Model_Resource_Indexer_Type_OptionAttribute
    extends AW_Layerednavigation_Model_Resource_Indexer_Type_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_layerednavigation/filter_index_option', 'entity_id');
    }

    protected function _validateAttribute($attribute)
    {
        if (in_array($attribute->getFrontendInput(),
            array(
                AW_Layerednavigation_Model_Synchronization_Option_Attribute::ATTRIBUTE_TYPE_SELECT,
                AW_Layerednavigation_Model_Synchronization_Option_Attribute::ATTRIBUTE_TYPE_MULTISELECT
            )
        )) {
            return true;
        }
        return false;
    }

    protected function _getAttributeCode()
    {
        return AW_Layerednavigation_Model_Source_Filter_Type::OPTION_ATTRIBUTE_CODE;
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
            ->joinLeft(array('cpev' => $this->getTable('catalog/product_index_eav')),
                'cpev.entity_id = ccpi.product_id AND cpev.attribute_id = ea.attribute_id AND cpev.store_id = ccpi.store_id',
                array()
            )
            ->joinLeft(array('cpei' => $this->getValueTable('catalog/product', 'int')),
                'cpei.entity_id = ccpi.product_id AND cpei.attribute_id = ea.attribute_id AND cpei.store_id = ccpi.store_id',
                array()
            )
            ->joinLeft(array('cpevar' => $this->getValueTable('catalog/product', 'varchar')),
                'cpevar.entity_id = ccpi.product_id AND cpevar.attribute_id = ea.attribute_id AND cpevar.store_id = ccpi.store_id',
                array()
            )
            ->joinLeft(array('cpevar_def' => $this->getValueTable('catalog/product', 'varchar')),
                'cpevar_def.entity_id = ccpi.product_id AND cpevar_def.attribute_id = ea.attribute_id AND cpevar_def.store_id = 0',
                array()
            )
            ->joinLeft(array('cpei_def' => $this->getValueTable('catalog/product', 'int')),
                'cpei_def.entity_id = ccpi.product_id AND cpei_def.attribute_id = ea.attribute_id AND cpei_def.store_id = 0',
                array(
                    'value' => 'IFNULL(IFNULL(cpev.value, IFNULL(cpei.value, cpei_def.value)), IFNULL(cpevar.value, IFNULL(cpevar.value, cpevar_def.value)) )',
                    'store_id' => 'ccpi.store_id',
                )
            )
            ->where('ea.attribute_code IN(?)', $attributesCodes)
            ->group(array('entity_id', 'attribute_id', 'store_id', 'value'))
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
                array('attribute_id', 'i.value', 'store_id')
            )
            ->join(
                array('cp' => $this->getTable('catalog/product_index_price')),
                'cp.entity_id = i.entity_id',
                array()
            )
            ->group(
                array(
                    'l.parent_id', 'i.attribute_id', 'i.store_id', 'i.value'
                )
            )
        ;
        $query = $select->insertIgnoreFromSelect($idxTable);
        $write->query($query);
        return $this;
    }
}