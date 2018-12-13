<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */

class Mageho_Sortproducts_Model_System_Config_Source_Attributes
{
	public $doNotDisplayAttributesArray = array('price', 'tier_price', 'group_price', 'special_price', 'name', 'sku', 
	'description', 'tax_class_id', 'url_key', 'options_container', 'price_view', 'gift_message_available', 
	'custom_design', 'custom_design_from', 'custom_design_to', 'custom_layout_update', 'page_layout',  
	'visibility', 'status', 'enable_googlecheckout');
	
    public function toOptionArray()
    {
        $cols = array();
        $cols[] = array('value' => '', 'label' => Mage::helper('mageho_sortproducts')->__('None'));
        $cols[] = array('value' => 'type_id', 'label' => Mage::helper('mageho_sortproducts')->__('Product Type (simple, bundle, etc)'));
        $cols[] = array('value' => 'attribute_set_id', 'label' => Mage::helper('mageho_sortproducts')->__('Attribute Set'));
        $cols[] = array('value' => 'created_at', 'label' => utf8_decode(Mage::helper('mageho_sortproducts')->__('Date Created')));

        foreach ($this->getAttributes() as $attribute) {
			$label = $attribute->getFrontendLabel() . ' - ' . $attribute->getAttributeCode() . ' #'.$attribute->getAttributeId();
			
            $cols[] = array('value' => $attribute->getAttributeCode(), 'label' => $label);
        }

        return $cols;
    }

    /**
     * @return Mage_Eav_Model_Mysql4_Entity_Attribute_Collection
     */
    protected function getAttributes() 
	{
        if (version_compare(Mage::getVersion(), '1.4', '>=')) {
            $collection = Mage::getResourceModel( 'catalog/product_attribute_collection');
        } else {
            $type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
            $collection = Mage::getResourceModel('eav/entity_attribute_collection');
            $collection->setEntityTypeFilter($type_id);
        }
        
        $collection->addFilter('is_visible', 1);
		
		$allowedAttributes = array();
		foreach ($collection->getItems() as $attribute) {
			if ( in_array($attribute->getFrontendInput(), array('media_image', 'gallery')) ) {
				continue;
			}
			if ( in_array($attribute->getAttributeCode(), $this->doNotDisplayAttributesArray) ) {
				continue;
			}

			$allowedAttributes[] = $attribute;
		}
		
        return $allowedAttributes;
    }
}