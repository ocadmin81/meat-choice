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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_category')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `category_ids` varchar(255) DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_category_idx')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `category_ids` varchar(255) DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_decimal')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `attribute_id` smallint(5) unsigned NOT NULL,
        `value` varchar(255) DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`attribute_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_decimal_idx')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `attribute_id` smallint(5) unsigned NOT NULL,
        `value` varchar(255) DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`attribute_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_option')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `attribute_id` smallint(5) unsigned NOT NULL,
        `value` varchar(255) DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`attribute_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_option_idx')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `attribute_id` smallint(5) unsigned NOT NULL,
        `value` varchar(255) DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`attribute_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_yesno')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `attribute_id` smallint(5) unsigned NOT NULL,
        `value` TINYINT UNSIGNED DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`attribute_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_index_yesno_idx')}` (
        `entity_id` int(10) unsigned NOT NULL,
        `attribute_id` smallint(5) unsigned NOT NULL,
        `value` TINYINT UNSIGNED DEFAULT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`entity_id`,`attribute_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

if (!$installer->tableExists($this->getTable('aw_layerednavigation/filter_eav'))) {
    $oldEavTableName = (string)Mage::getConfig()->getTablePrefix() . 'aw_layerednavigation_filter_label';
    $newEavTableName = $this->getTable('aw_layerednavigation/filter_eav');

    // Rename table and column
    $installer->run("
        RENAME TABLE {$oldEavTableName} TO {$newEavTableName};
        ALTER TABLE {$newEavTableName} CHANGE `label_id` `eav_id` INT;
        ALTER TABLE {$newEavTableName} DROP PRIMARY KEY;
        ALTER TABLE {$newEavTableName} ADD PRIMARY KEY (`eav_id`, `filter_id`);
        ALTER TABLE {$newEavTableName} MODIFY COLUMN `eav_id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
    ");

    // Save is_enabled attribute values from filter table to eav table
    $select = clone Mage::getResourceModel('aw_layerednavigation/filter_collection')->getSelect();
    $select->reset(Zend_Db_Select::COLUMNS);
    $select->columns(array('entity_id', 'is_enabled'));

    $readConnection = Mage::getSingleton('core/resource')->getConnection('read');
    $filterStatusList = $readConnection->fetchPairs($select);

    $columnList = array('filter_id', 'store_id', 'name', 'value');
    $preparedData = array();
    foreach ($filterStatusList as $filterId => $filterStatus) {
        $preparedData[] = array(
            'filter_id' => $filterId,
            'store_id'  => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
            'name'      => 'is_enabled',
            'value'     => (int)$filterStatus,
        );
        $preparedData[] = array(
            'filter_id' => $filterId,
            'store_id'  => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
            'name'      => 'is_enabled_in_search',
            'value'     => (int)$filterStatus,
        );
    }
    $writeConnection = Mage::getSingleton('core/resource')->getConnection('write');
    $writeConnection->insertArray($newEavTableName, $columnList, $preparedData);

    // Drop is_enabled column from filter table
    $installer->run("
        ALTER TABLE {$installer->getTable('aw_layerednavigation/filter')} DROP `is_enabled`;
    ");
}

if (!$installer->tableExists($this->getTable('aw_layerednavigation/filter_option_eav'))) {
    $oldOptionEavTableName = (string)Mage::getConfig()->getTablePrefix() . 'aw_layerednavigation_filter_option_label';
    $newOptionEavTableName = $this->getTable('aw_layerednavigation/filter_option_eav');

    // Rename table and column
    $installer->run("
        RENAME TABLE {$oldOptionEavTableName} TO {$newOptionEavTableName};
        ALTER TABLE {$newOptionEavTableName} CHANGE `label_id` `eav_id` INT;
        ALTER TABLE {$newOptionEavTableName} DROP PRIMARY KEY;
        ALTER TABLE {$newOptionEavTableName} ADD PRIMARY KEY (`eav_id`, `option_id`);
        ALTER TABLE {$newOptionEavTableName} MODIFY COLUMN `eav_id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
    ");

    // Save is_enabled attribute values from filter option table to eav table
    $select = clone Mage::getResourceModel('aw_layerednavigation/filter_option_collection')->getSelect();
    $select->reset(Zend_Db_Select::COLUMNS);
    $select->columns(array('option_id', 'is_enabled'));

    $readConnection = Mage::getSingleton('core/resource')->getConnection('read');
    $optionStatusList = $readConnection->fetchPairs($select);

    $columnList = array('option_id', 'store_id', 'name', 'value');
    $preparedData = array();
    foreach ($optionStatusList as $optionId => $optionStatus) {
        $preparedData[] = array(
            'option_id' => $optionId,
            'store_id'  => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
            'name'      => 'is_enabled',
            'value'     => (int)$optionStatus,
        );
    }
    $writeConnection = Mage::getSingleton('core/resource')->getConnection('write');
    $writeConnection->insertArray($newOptionEavTableName, $columnList, $preparedData);

    // Drop is_enabled column from filter option table
    $installer->run("
        ALTER TABLE {$installer->getTable('aw_layerednavigation/filter_option')} DROP `is_enabled`;
    ");
}

$installer->run("
    ALTER TABLE {$installer->getTable('aw_layerednavigation/filter')}
        ADD `category_ids` VARCHAR(255) DEFAULT NULL
        AFTER `row_count_limit`;
");

$installer->endSetup();

try {
    Mage::getModel('aw_layerednavigation/synchronization')->run();
} catch (Exception $e) {
    Mage::logException($e);
}