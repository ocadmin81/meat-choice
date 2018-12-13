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

class AW_Layerednavigation_Block_Adminhtml_Filter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('aw_layerednavigation_filter_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $storeId = Mage::app()->getRequest()->getParam('store', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $collection = Mage::getResourceModel('aw_layerednavigation/filter_collection')
            ->addFilterAttributes($storeId)
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            array(
                'header' => $this->__('Filter'),
                'index'  => 'title',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'code',
            array(
                 'header' => $this->__('Code'),
                 'index'  => 'code',
                 'type'   => 'text',
                 'width'  => '150px',
            )
        );

        $this->addColumn(
            'type',
            array(
                'header'   => $this->__('Type'),
                'index'    => 'type',
                'type'     => 'options',
                'options'  => Mage::getModel('aw_layerednavigation/source_filter_type')->toArray(),
                'width'    => '150px',
            )
        );

        $this->addColumn(
            'display_type',
            array(
                 'header'   => $this->__('Display Type'),
                 'index'    => 'display_type',
                 'type'     => 'options',
                 'options'  => Mage::getModel('aw_layerednavigation/source_filter_display_type')->asArray(),
                 'width'    => '150px',
            )
        );

        $this->addColumn(
            'image_position',
            array(
                 'header'   => $this->__('Image Position'),
                 'index'    => 'image_position',
                 'type'     => 'options',
                 'options'  => Mage::getModel('aw_layerednavigation/source_filter_image_position')->toArray(),
                 'width'    => '150px',
            )
        );

        $this->addColumn(
            'is_enabled',
            array(
                 'header'   => $this->__('Is Enabled'),
                 'index'    => 'is_enabled',
                 'type'     => 'options',
                 'options'  => Mage::getModel('aw_layerednavigation/source_filter_status')->toArray(),
                 'width'    => '150px',
            )
        );

        $this->addColumn(
            'is_enabled_in_search',
            array(
                'header'   => $this->__('Is Enabled in Search'),
                'index'    => 'is_enabled_in_search',
                'type'     => 'options',
                'options'  => Mage::getModel('aw_layerednavigation/source_filter_status')->toArray(),
                'width'    => '150px',
            )
        );

        $this->addColumn(
            'position',
            array(
                 'header'   => $this->__('Position'),
                 'index'    => 'position',
                 'type'     => 'number',
                 'width'    => '100px',
            )
        );
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('aw_layerednavigation_filter');

        $this->getMassactionBlock()->addItem(
            'change_status',
            array(
                 'label'      => $this->__('Change Status'),
                 'url'        => $this->getUrl('*/*/massChangeStatus'),
                 'additional' => array(
                     'visibility' => array(
                         'name'   => 'status',
                         'type'   => 'select',
                         'class'  => 'required-entry',
                         'label'  => $this->__('Status'),
                         'values' => Mage::getModel('aw_layerednavigation/source_filter_status')->toOptionArray(),
                     )
                 )
            )
        );

        $this->getMassactionBlock()->addItem(
            'change_status_in_search',
            array(
                'label'      => $this->__('Change Status in Search'),
                'url'        => $this->getUrl('*/*/massChangeStatusInSearch'),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Status'),
                        'values' => Mage::getModel('aw_layerednavigation/source_filter_status')->toOptionArray(),
                    )
                )
            )
        );

        return $this;
    }

    public function getRowUrl($row)
    {
        $urlParams = array(
            'id' => $row->getId()
        );
        $storeId = $this->getRequest()->getParam('store', null);
        if ($storeId !== null) {
            $urlParams['store'] = $storeId;
        }
        return $this->getUrl('*/*/edit/', $urlParams);
    }
}