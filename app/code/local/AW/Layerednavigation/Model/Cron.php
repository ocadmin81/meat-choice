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


class AW_Layerednavigation_Model_Cron
{
    protected $_lastProcessedFilterId = null;

    public function processReindex()
    {
        if (!Mage::helper('aw_layerednavigation/config')->isReindexViaCron()) {
            return $this;
        }
        $this->_initReindex();
        $filterCollection = Mage::getModel('aw_layerednavigation/filter')->getCollection();
        $filterCollection
            ->addFieldToFilter('entity_id', array('from' => $this->_lastProcessedFilterId))
            ->addFieldToFilter('entity_id', array('neq' => $this->_lastProcessedFilterId))
            ->setPageSize(10)
        ;

        $productIds = Mage::getModel('catalog/product')->getCollection()->getAllIds();
        foreach ($filterCollection as $filter) {
            $attribute = null;
            if ($filter->getCode() != 'category') {
                $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $filter->getCode());
            }
            Mage::getResourceModel('aw_layerednavigation/indexer_filter')->reindexAttribute($attribute, $productIds);
            $this->_setLastProcessedFilterId($filter->getId());
        }

        if ($filterCollection->getSize()) {
            Mage::helper('aw_layerednavigation/filter')->setIndexerLock(true);
        } else {
            Mage::helper('aw_layerednavigation/filter')->setIndexerLock(false);
        }
        return $this;
    }

    protected function _setLastProcessedFilterId($filterId)
    {
        $this->_lastProcessedFilterId = $filterId;
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation_reindex_tmp')
            . ' SET filter_id = ' . $filterId
        );
        return $this;
    }

    protected function _initReindex()
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        if (null === $this->_lastProcessedFilterId) {
            $write->query('CREATE TABLE IF NOT EXISTS '
                . Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation_reindex_tmp')
                . ' (id  TINYINT(2) UNSIGNED NOT NULL, filter_id VARCHAR(255), PRIMARY KEY (`id`)) ENGINE = InnoDB;'
                . ' INSERT IGNORE INTO ' . Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation_reindex_tmp')
                . ' VALUES (1,0);'
            );

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $this->_lastProcessedFilterId = (int)$read->fetchOne(
                'SELECT filter_id FROM ' . Mage::getSingleton('core/resource')->getTableName('aw_layerednavigation_reindex_tmp')
            );
        }
        return $this;
    }
}