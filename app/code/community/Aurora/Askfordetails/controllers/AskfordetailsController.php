<?php

class Aurora_Askfordetails_AskfordetailsController extends Mage_Core_Controller_Front_Action {

    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
    const XML_PATH_ENABLED = 'contacts/contacts/enabled';
    const XML_PATH_EXPERTMAIL = 'trans_email/ident_custom4/email';
    const XML_PATH_EXPERTMAIL_NAME = 'trans_email/ident_custom4/name';

    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
    }

    public function indexAction() {

        $prod_id = $this->getRequest()->getParam('product_id');
        $prod = Mage::helper('catalog/product')->getProduct($prod_id);

        $current_product = array(
            'prod_id' => $prod_id,
            'name' => $prod->getName(),
            'sku' => $prod->getSku()
        );

        Mage::register('product_info', $current_product);

        $this->loadLayout();
        $this->getLayout()->getBlock('askfordetails')
                ->setFormAction(Mage::getUrl('*/*/post'));
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('askfordetails')->__('Ask for details'));

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->renderLayout();
        return;
    }

    public function postAction() {
        $post = $this->getRequest()->getPost();

        if ($post) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                
                $_product = Mage::getModel('catalog/product')->load((int)$post['prod_id']);
                if ($_product) {
                    $post['product_url'] = Mage::getBaseUrl().$_product->getUrlPath();
                }                
                
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = array();

                if (!Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                    $error['name'] = Mage::helper('askfordetails')->__('Name field cannot be empty.');
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error['email'] = Mage::helper('askfordetails')->__('The format of the e-mail address is incorrect.');
                }

                if ($error)
                    throw new Exception();

                $sender = Array(
                    'name' => trim($post['name']),
                    'email' => trim($post['email']));

                // where mail should be send
                $receivers = array();
                $receivers[] = $expert_mail = array(
                    Mage::getStoreConfig(self::XML_PATH_EXPERTMAIL_NAME) => Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT)
                );
                // config - check if customer should receive feedback                
                if (Mage::getStoreConfig('askfordetailstab_opt/askfordetailsconfig/afdcustomerfeedback') == 1) {
                    $receivers[] = $sender['email'];
                }
                
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->setReplyTo(trim($post['email']))
                        ->sendTransactional(
                                'popupsproduct_email_email_template', $sender, $receivers, null, array('data' => $postObject)
                );

                if (!$mailTemplate->getSentSuccess()) {
                    $error['server'] = Mage::helper('askfordetails')->__('Server encountered an unexpected error while sending email.');
                    throw new Exception();
                }

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('askfordetails')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $params = array(
                    '_query' => array('product_id' => trim($post['prod_id']))
                );
                $this->_redirect("*/*/", $params);

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                if (!empty($error)) {
                    $additional_information = "";
                    foreach ($error as $err => $info)
                        $additional_information .= $info . '<br/>';
                }

                Mage::getSingleton('customer/session')->addError(Mage::helper('askfordetails')->__('Unable to submit your request. Please, try again later<br/>' . $additional_information));
                $params = array(
                    '_query' => array('product_id' => trim($post['prod_id']))
                );

                $this->_redirect("*/*/", $params);
                return;
            }
        } else {
            $params = array(
                '_query' => array('product_id' => trim($post['prod_id']))
            );
            $this->_redirect("*/*/", $params);
        }
    }

}