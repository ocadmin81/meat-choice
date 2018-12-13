<?php
class Softwarehtec_Cardcom_Model_Source_Mode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => "1",
                'label' => "iframe"
            ),
            array(
                'value' => "2",
                'label' => "redirect"
            )
        );
    }
}

