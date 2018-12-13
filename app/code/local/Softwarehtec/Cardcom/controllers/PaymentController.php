<?php

class Softwarehtec_Cardcom_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
	public function getOrder()
	{
		Mage::log("Get Order in PaymentController");
		if ($this->_order == null) {
			$session = Mage::getSingleton('checkout/session');
			$this->_order = Mage::getModel('sales/order');
			$this->_order->loadByIncrementId($session->getLastRealOrderId());
		}
		return $this->_order;
	}

 

	public function successAction()
	{
  	 	$this->successReturn();
		return true;
	}


	public function errorAction()
	{
  	 	$this->errorReturn();
		return true;
	}



	public function redirectAction()
	{
Mage::log("Redirect Action in PaymentController");
		$model = Mage::getModel('softcardcom/payment');
		$session = Mage::getSingleton('checkout/session');
		$session->setsoftdecidirPaymentQuoteId($session->getQuoteId());

		$order = $this->getOrder();

		if (!$order->getId()) {
			$this->norouteAction();
			return;
		}
 
//		$order->addStatusToHistory($model->getConfigData('order_status'),"Redirecting..");
//		$order->save();
// Do Not Active !
		$this->loadLayout();
        $block = $this->getLayout()->createBlock('Softwarehtec_Cardcom_Block_Redirect','softcardcom',array('template' => 'softcardcom/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
		// $this->loadLayout();
		// $this->renderLayout();
 
	}

	public function notifyAction()
	{
		 try
		 {
	 		$model = Mage::getModel('softcardcom/payment');

	 		$terminalnumber = $model->getConfigData('terminalnumber');
			$username = $model->getConfigData('username');

			$orderid = htmlentities($_GET["oid"]);


			$order = Mage::getModel('sales/order');
			$order->loadByIncrementId($orderid );
			if (!$order->getId()) {

				echo "OrderNotFound:".$orderid;
				return false;
			}

			$lowprofilecode = $_GET["lowprofilecode"];
			if($lowprofilecode== null){
				echo "LPCIsIsNull";
				return false;
			}	

			if ($this->IsLowProfileCodeDealOneOK($lowprofilecode ,$terminalnumber,$username,$orderid)== '0')
			{
				$order->addStatusToHistory(
					$model->getConfigData('order_successful_status')
					,"Internal Deal Number : " . $this->InternalDealNumberPro,"",true
				);


				// Original :
				$order->setState($model->getConfigData('order_successful_status'));
			    $order->setStatus($model->getConfigData('order_successful_status'));

				$order->save();


				// Test For Custom :				
				//if(saveInvoice($order)){
				//	echo "invoice ok ";
				//}else{
				//	echo "invoice not ";
				//}


				foreach ($order->getAllItems() as $item) {
 					   $item->setQtyCanceled(0);
    					$item->save();
				}
				
				$order->sendNewOrderEmail();
				$order->sendOrderUpdateEmail(true, '');
				echo "UpdateOK-successful";
				return true;

			}
			else
			{
					$order->addStatusToHistory($model->getConfigData('order_failed_status'),"Billing Error " . $this->DealResponePro,fasle);
				if($model->getConfigData('order_failed_status')=='panding'){
					$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, 'Gateway has declined the payment.');
				}
				
				if($order->canCancel() && $model->getConfigData('order_failed_status') =='canceled') 
				{
					$order->cancel();
					$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.');
				}
				
				$order->save();
				echo "UpdateOK-failed";
 				return false;
			}
			echo "EndOfMathod";
			return false;
		}
		catch (Exception $e)
		{
				echo "e:".$e->getMessage();
				return false;
		}
	}
 	protected $InternalDealNumberPro;
 	protected $DealResponePro;
	function IsLowProfileCodeDealOneOK($lpc,$terminal,$username,$orderid)
	{
		$vars = array( 
			'TerminalNumber'=>$terminal, 
            	'LowProfileCode'=>$lpc, 
			'UserName'=>$username 
		);
		
		# encode information
		$urlencoded = http_build_query($vars);
		$CR = curl_init();
		curl_setopt($CR, CURLOPT_URL, 'https://secure.cardcom.co.il/Interface/BillGoldGetLowProfileIndicator.aspx');
		curl_setopt($CR, CURLOPT_POST, 1);
		curl_setopt($CR, CURLOPT_FAILONERROR, true);
		curl_setopt($CR, CURLOPT_POSTFIELDS, $urlencoded );
		curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($CR, CURLOPT_FAILONERROR,true);
		$result = curl_exec( $CR );
		curl_close( $CR );
		parse_str($result);

		$this->InternalDealNumberPro = 0;
		$this->DealResponePro = -1;

		if (isset($InternalDealNumber)){
				$this->InternalDealNumberPro = $InternalDealNumber +"?";
		}

		if (isset($DealResponse)) #  OK!
		{
			$this->DealResponePro = $DealResponse;
		}
		else if (isset($SuspendedDealResponseCode)) #  Suspend Deal
		{
		      $this->DealResponePro = $SuspendedDealResponseCode;
		}



		if (isset($DealResponse)&& $DealResponse == '0' && $ReturnValue==$orderid) #  OK!
		{
			return '0'; // ok
		}
		else if (isset($SuspendedDealResponseCode)&& $SuspendedDealResponseCode== '0' && $ReturnValue==$orderid) #  Suspend Deal
		{
		      return '0'; // ok
		}



		return '1';
	} 
	


	protected function saveInvoice(Mage_Sales_Model_Order $order)
	{
		if ($order->canInvoice()) {
			$convertor = Mage::getModel('sales/convert_order');
			$invoice = $convertor->toInvoice($order);
			foreach ($order->getAllItems() as $orderItem) {
				if (!$orderItem->getQtyToInvoice()) {
					continue;
				}
				$item = $convertor->itemToInvoiceItem($orderItem);
				$item->setQty($orderItem->getQtyToInvoice());
				$invoice->addItem($item);
			}
			$invoice->collectTotals();
			$invoice->register()->capture();
			Mage::getModel('core/resource_transaction')
               ->addObject($invoice)
               ->addObject($invoice->getOrder())
               ->save();
			return true;
		}

		return false;
	}


	function errorReturn()
	{
		$model = Mage::getModel('softcardcom/payment');
		$mode = $model->getConfigData('mode');
		if($mode == 1)
		{
		echo '
		<script type="text/javascript">
		window.top.location.href = "'.	Mage::getUrl("checkout/onepage/failure").'"; 
		</script>
		';
		die();

		}

		$this->_redirect('checkout/onepage/failure');
	}
 
 	function successReturn()
	{
		$session = Mage::getSingleton('checkout/session');
		$session->setsoftdecidirPaymentQuoteId($session->getQuoteId());
		$session->unsQuoteId();

		$model = Mage::getModel('softcardcom/payment');
		$mode = $model->getConfigData('mode');
		if($mode == 1)
		{
		echo '
		<script type="text/javascript">
		window.top.location.href = "'.	Mage::getUrl("checkout/onepage/success").'"; 
		</script>
		';
		die();

		}

		$this->_redirect('checkout/onepage/success');
	}
}
