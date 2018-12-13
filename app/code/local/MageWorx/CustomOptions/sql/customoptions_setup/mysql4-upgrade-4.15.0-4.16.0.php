<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */

/* @var $installer MageWorx_CustomOptions_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('catalog/product_option_type_value');

if (!$installer->getConnection()->tableColumnExists($table, 'extra')) {
    $installer->getConnection()->addColumn(
        $table,
        'extra',
        "varchar(10) NOT NULL DEFAULT ''"
    );
}

if ($installer->getConnection()->tableColumnExists($table, 'customoptions_qty')) {
    $installer->getConnection()->query("UPDATE {$table} set extra = customoptions_qty WHERE (customoptions_qty LIKE'%x%' OR customoptions_qty LIKE'%i%' OR customoptions_qty LIKE'%l%')");
    $installer->getConnection()->query("ALTER TABLE {$table} MODIFY COLUMN customoptions_qty varchar(12) NULL DEFAULT NULL");
    $installer->getConnection()->query("UPDATE {$table} set customoptions_qty = NULL WHERE (customoptions_qty LIKE'%x%' OR customoptions_qty LIKE'%i%' OR customoptions_qty LIKE'%l%' OR customoptions_qty='')");
    $installer->getConnection()->query("ALTER TABLE {$table} MODIFY COLUMN customoptions_qty decimal(12,4) NULL DEFAULT NULL");
}

if (!$installer->getConnection()->tableColumnExists($table, 'customoptions_min_qty')) {
    $installer->getConnection()->addColumn(
        $table,
        'customoptions_min_qty',
        "decimal(12,4) NULL DEFAULT NULL"
    );
}

if (!$installer->getConnection()->tableColumnExists($table, 'customoptions_max_qty')) {
    $installer->getConnection()->addColumn(
        $table,
        'customoptions_max_qty',
        "decimal(12,4) NULL DEFAULT NULL"
    );
}

$installer->endSetup();