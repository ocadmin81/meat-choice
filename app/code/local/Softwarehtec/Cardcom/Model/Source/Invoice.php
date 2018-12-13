<?php
class Softwarehtec_Cardcom_Model_Source_Invoice
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => "0",
                'label' => "No"
            ),
            array(
                'value' => "1",
                'label' => "Yes - Auto invoice language"
            ),
            array(
                'value' => "2",
                'label' => "Yes - Hebrew"
            ),
            array(
                'value' => "3",
                'label' => "Yes - English"
            )
        );
    }
}

