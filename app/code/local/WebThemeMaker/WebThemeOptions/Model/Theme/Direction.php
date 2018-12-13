<?php
class WebThemeMaker_WebThemeOptions_Model_Theme_Direction {
	
	public function toOptionArray() {
		return array(
				array('value' => 'ltr', 'label' => Mage::helper('webthemeoptions')->__('Left to right')),
				array('value' => 'rtl', 'label' => Mage::helper('webthemeoptions')->__('Right to left'))
		);
	}
}