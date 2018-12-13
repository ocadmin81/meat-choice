<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Payment
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 

class Softwarehtec_Cardcom_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

	protected $_code  = 'softcardcom_payment';
	protected $_formBlockType = 'softcardcom/form';
	protected $_infoBlockType = 'softcardcom/info';



	protected $_canAuthorize            = false;
	protected $_canCapture              = false;
	protected $_canCapturePartial       = false;
	protected $_canRefund               = false;
	protected $_canVoid                 = false;
	protected $_canUseInternal          = false;
	protected $_canUseCheckout          = true;

	public function getCardcomUrl()
	{
		$terminalnumber =$this->getConfigData('terminalnumber');

		return "https://secure.cardcom.co.il/external/LowProfileClearing2.aspx?terminalnumber=".$terminalnumber."&rspcode=";
	}


	public function getOrderPlaceRedirectUrl()
	{

		return Mage::getUrl('softcardcom/payment/redirect');

	}


	public function getOrder()
	{
		if ($this->_order == null) {
			$session = Mage::getSingleton('checkout/session');
			$this->_order = Mage::getModel('sales/order');
			$this->_order->loadByIncrementId($session->getLastRealOrderId());
		}
		return $this->_order;
	}
	
	public function getStandardCheckoutFormFields()
	{

		$session = Mage::getSingleton('checkout/session');
        
		$order = $this->getOrder();

		if (!($order instanceof Mage_Sales_Model_Order)) {
			Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
		}


		$operation = $this->getConfigData('operation');
		$terminalnumber =$this->getConfigData('terminalnumber');
		$username =$this->getConfigData('username');
		$maxpayment =$this->getConfigData('maxpayment');
		$invoice = $this->getConfigData('invoice');
		$billing = $order->getBillingAddress();
		$fname=$billing->getFirstname();
		$lname=$billing->getLastname();
		$invoicevatfree = $this->getConfigData('invoicevatfree');
		$PageLang = $this->GetLanguage(Mage::app()->getLocale()->getLocaleCode());
		$fields = array(
	            'TerminalNumber'=>$terminalnumber,
	            'UserName'=>$username,
	            'CodePage'=>'65001',
	            'SumToBill'=>number_format($order->getGrandTotal(), 2, '.', ''),
	            'Operation'=>$operation,
	            'Languge'=>$PageLang,
	            'CoinID'=>$this->GetCoinID($order->getOrderCurrencyCode()),
	            'ErrorRedirectUrl'=> Mage::getUrl('softcardcom/payment/error'),
	            'SuccessRedirectUrl'=>Mage::getUrl('softcardcom/payment/success'),
				'ProductName' => "Order Id:".$order->getRealOrderId(),
				'ReturnValue' => $order->getRealOrderId(),
				'APILevel' =>'9',
		);

		// 'IndicatorUrl'=> Mage::getUrl('softcardcom/payment/notify')."?oid=".$order->getRealOrderId(),
		$tmpIndicatorUrl = Mage::getUrl('softcardcom/payment/notify');
		if (strpos($tmpIndicatorUrl,'?') !== false) {
			$tmpIndicatorUrl = $tmpIndicatorUrl."&oid=".$order->getRealOrderId();
		}else{
			$tmpIndicatorUrl = $tmpIndicatorUrl."?oid=".$order->getRealOrderId();
		}
		$fields['IndicatorUrl'] = $tmpIndicatorUrl;


 		if ($this->getConfigData('mode') != '1') // redirect
		{
			$fields['CancelType']='2';
			$fields['CancelUrl']=Mage::getUrl('softcardcom/payment/error');
		}
	        
		if ($operation == '4') // Req Params for Suspend Deal
		{
			$fields['SuspendedDealJValidateType'] = "2";
			$fields['SuspendedDealGroup'] = "1";

		}

		if(!empty($maxpayment)&& $maxpayment>="1")
		{
			$fields['MaxNumOfPayments']	= $maxpayment;
		}
		// Custoemr Support
		$fields['CustomeFields.Field20'] =  $billing->getTelephone();

		$AddAtEnd = "0";
		if($invoice == '1' || 
			$invoice == '2' ||
			$invoice == '3')
		{
			if ($PageLang=='he')
				$invlang = 'he';
			else
				$invlang = 'en';
			//$invlang = $this->GetLanguageInvoice(Mage::app()->getLocale()->getLocaleCode(),$invoice);
				$address2 = $billing->getStreet(2);
				$fields['IsCreateInvoice']				= "true";
				$fields['InvoiceHead.CustName']			= $fname." ".$lname;
				$fields['InvoiceHead.CustAddresLine1']	= $billing->getStreet(1);
				$fields['InvoiceHead.CustCity']			= $billing->getCity();
		    	$fields['InvoiceHead.CustAddresLine2']	= (empty($address2 ) ? $address2: '');
		    	$fields['InvoiceHead.CustLinePH']		= $billing->getTelephone();
				$fields['InvoiceHead.Language']			= $invlang;
				$fields['InvoiceHead.Email']			= $order->getCustomerEmail();
				$fields['InvoiceHead.SendByEmail']		= 'true';
				$fields['InvoiceHead.CoinID']			= $this->GetCoinID($order->getOrderCurrencyCode());
				$fields['InvoiceHead.Comments']			= $order->getRealOrderId();

				if($invoicevatfree == '1'){
					$fields['InvoiceHead.ExtIsVatFree']		= 'true';
				}else{
					$fields['InvoiceHead.ExtIsVatFree']		= 'false';
				}

				$ItemsCount = 1;

				$items = $order->getAllItems();
				$fields['ItemsCount'] = count($items);
				$LeftOver = number_format($order->getGrandTotal(), 2, '.', '');
				foreach ($items as $itemId => $item)
				{
					//$type = $item->getProductType();
					//if($type != "configurable")
					//{				
						if ($item->getPrice()!=0)
						{
							// selected  Products:
							$ItemPrice = number_format($item->getPrice(), 2, '.', ''); 
							$LeftOver = $LeftOver-($ItemPrice*intval($item->getQtyOrdered()));
							//$fields['InforEch'.$ItemsCount] = $LeftOver;
							$fields['InvoiceLines'.$ItemsCount.'.Description']= substr(strip_tags(preg_replace("/&#\d*;/", " ", $item->getName()) ), 0, 50);
							$fields['InvoiceLines'.$ItemsCount.'.Price']= $ItemPrice; 
							$fields['InvoiceLines'.$ItemsCount.'.Quantity']=  intval($item->getQtyOrdered());
							$fields['InvoiceLines'.$ItemsCount.'.IsPriceIncludeVAT']= 'true';
							$fields['InvoiceLines'.$ItemsCount.'.ProductID']= $item->getSku();
							// SKU not getProductId $item->getSku();
							$ItemsCount = $ItemsCount +1;
						}
						
					//}	
				}	
				if ($order->getShippingAmount()!=0)
				{
					$ShippingAmount = number_format($order->getShippingAmount(), 2, '.', ''); 
					$LeftOver = $LeftOver-$ShippingAmount;
					$fields['InvoiceLines'.$ItemsCount.'.Description']= substr(strip_tags(preg_replace("/&#\d*;/", " ", $order->getShippingDescription()) ), 0, 50);
					$fields['InvoiceLines'.$ItemsCount.'.Price']= $ShippingAmount;
					$fields['InvoiceLines'.$ItemsCount.'.Quantity']= '1';
					$fields['InvoiceLines'.$ItemsCount.'.IsPriceIncludeVAT']= 'true';
					$fields['InvoiceLines'.$ItemsCount.'.ProductID']= '';
					$ItemsCount = $ItemsCount +1;

				}
			/*
				if ($order->getDiscountAmount()!=0)
				{
					$fields['InvoiceLines'.$ItemsCount.'.Description']= $order->getDiscountDescription();
					$fields['InvoiceLines'.$ItemsCount.'.Price']=$order->getDiscountAmount();
					$LeftOver = $LeftOver-$order->getDiscountAmount();
					$fields['InvoiceLines'.$ItemsCount.'.Quantity']= '1';
					$fields['InvoiceLines'.$ItemsCount.'.IsPriceIncludeVAT']= 'true';
					$fields['InvoiceLines'.$ItemsCount.'.ProductID']= '';
					$ItemsCount = $ItemsCount +1;
				}
			*/

	 			if ($LeftOver!=0)
	 			{
	 				if ($invlang=='en')
	 				{
						$fields['InvoiceLines'.$ItemsCount.'.Description']= "Discount";
	 				}
	 				else
	 				{
	 					$AddAtEnd = "&InvoiceLines".$ItemsCount.".Description=%D7%94%D7%A0%D7%97%D7%94"; // Heb Discount
	 				}
					$fields['InvoiceLines'.$ItemsCount.'.Price']= number_format($LeftOver, 2, '.', '');
					$fields['InvoiceLines'.$ItemsCount.'.Quantity']= '1';
					$fields['InvoiceLines'.$ItemsCount.'.IsPriceIncludeVAT']= 'true';
					$fields['InvoiceLines'.$ItemsCount.'.ProductID']= 'Auto';
					$ItemsCount = $ItemsCount +1;
					
				}
			}

	    	$urlencoded = http_build_query($fields);
	    	if ($AddAtEnd!='0')
	    		$urlencoded= $urlencoded.$AddAtEnd;
	    	//  $invlang
	    	$curl = curl_init();
	    	curl_setopt($curl, CURLOPT_URL, 'https://secure.cardcom.co.il/BillGoldLowProfile.aspx');
	    	curl_setopt($curl, CURLOPT_POST, 1);
	    	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	    	curl_setopt($curl, CURLOPT_POSTFIELDS, $urlencoded );
	    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    	curl_setopt($curl, CURLOPT_FAILONERROR,true);
	    	$result = curl_exec($curl);

	    	if ($result===false)
	    	{
				$parameter2 = array(
					'RequestURL' => curl_error($curl),
					'Response' => "Curl Error",
				);
				return $parameter2;

	    		// Mage::throwException($this->_getHelper()->__('CURL Exception ,'. curl_error($curl)));
	    	}
 			
	    	$exp = explode(';',$result);
			if($exp[0] == "0")
			{  	
				$parameter = array(
					'profile' => $exp[1],
					'languageCode' => Mage::app()->getLocale()->getLocaleCode(),
				);
			}
			else
			{
				$parameter = array(
					'RequestURL' => $urlencoded,
					'Response' => $result,
				);
			}
	    	
		return $parameter;
	}
    
	function GetCoinID($CardCoinID)
	{
		if ($CardCoinID =='NIS') // NIS
				return '1';
			else if ($CardCoinID =='USD') // USD
				return '2';
			else if ($CardCoinID =='GBP') // British pound 
				return '826';
			else if ($CardCoinID =='EUR') // Euro
				return '978';
			else if ($CardCoinID =='AUD') // Australian dollar 
				return '36';
			else
				return '1'; // NIS

	}

	function GetLanguage($language_code)
	{

		if(strpos($language_code,"en")!==FALSE){
			return 'en';
		} else if(strpos($language_code,"ru")!==FALSE){
			return 'ru';
		} else if(strpos($language_code,"ar")!==FALSE){
			return 'ar';
		} else if(strpos($language_code,"es")!==FALSE){
			return 'sp';
		} else if(strpos($language_code,"pt")!==FALSE){
			return 'pt';
		} 
	//	if ($language_code=='en_US')
				//return 'en';
		//else if ($language_code=='ru-RU')
				//return 'ru';
		//else if ($language_code=='ar-AR')
				//return 'ar';
		//else if($language_code=='es-ES'){
			//return 'sp';
		//}else  if($language_code=='pt-BR'){
			//return 'pt';
		//}
		return 'he';
	}

	
	function GetLanguageInvoice($language_code,$Invoice)
	{
		if($Invoice == '1')
		{
			if ($language_code == 'he' || $language_code== 'iw')
				return 'he'; // HEB
		}

		if ($Invoice == '2')
			return 'he';
		if ($Invoice == '3')
			return 'en';

		return 'en';
	}
}
?>