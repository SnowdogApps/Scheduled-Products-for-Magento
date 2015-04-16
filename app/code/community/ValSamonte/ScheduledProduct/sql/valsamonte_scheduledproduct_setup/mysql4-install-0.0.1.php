<?php
/* @var $this ValSamonte_ScheduledProduct_Model_Mysql4_Setup */
$this->startSetup();
// For performance reasons we should add this fields to main entity table
// Activation date column adding to product entity table
$this->getConnection()->addColumn(
    $this->getTable('catalog/product'),
    ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
    'DATETIME DEFAULT NULL'
);
// Expiry date column adding to product entity table
$this->getConnection()->addColumn(
    $this->getTable('catalog/product'),
    ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
    'DATETIME DEFAULT NULL'
);
// Activation date attribute information adding to the product entity
$this->addAttribute(
    'catalog_product',
    ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
    array(
        'type'      => 'static',
        'input'     => 'date',
        'label'     => 'Activation Date',
        'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'backend'   => 'valsamonte_scheduledproduct/attribute_backend_datetime',
        'visible'   => 1,
        'required'  => 0,
        'position'  => 10,
        'group'     => 'Schedule Settings'
    )
);
// Expiry date attribute information adding to the product entity
$this->addAttribute(
    'catalog_product',
    ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
    array(
        'type'      => 'static',
        'input'     => 'date',
        'label'     => 'Expiry Date',
        'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'backend'   => 'valsamonte_scheduledproduct/attribute_backend_datetime',
        'visible'   => 1,
        'required'  => 0,
        'position'  => 20,
        'group'     => 'Schedule Settings'
    )
);
$this->endSetup();
