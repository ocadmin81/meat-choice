<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('catalog_product_option_type_value'),
    'descr',
    "varchar (255) default ''"
);