<?php
class Magentothem_Ajaxcartsuper_Block_Adminhtml_System_Config_Form_Field_Customfieldcolor extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $output=parent::_getElementHtml($element);
        if (!Mage::registry('mColorPicker')) {
            $output.='
            <script type="text/javascript" src="'.$this->getJsUrl('lib/jquery/jquery-1.10.2.min.js').'"></script>
            <script type="text/javascript" src="'.$this->getJsUrl('magentothem/mColorPicker.js').'"></script>
            <script type="text/javascript">
                  var image = "'.$this->getJsUrl('magentothem/images/').'";
                 jQuery.fn.mColorPicker.defaults.imageFolder= image;
            </script>
            ';
            Mage::register('mColorPicker', 1);
        }
		$output .= '
        <script type="text/javascript">
            jQuery(function(){
                 jQuery("#'.$element->getHtmlId().'").attr("data-hex", true).mColorPicker();
            })
        </script> ';
        //return $output;
    }
}