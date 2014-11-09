<?php
/**
 * Observer for core events handling
 *
 */
class ValSamonte_ScheduledProduct_Model_Observer
{
  /**
   * Observes event 'adminhtml_catalog_product_edit_prepare_form'
   * and adds custom format for date input
   *
   * @param Varien_Event_Observer $observer
   * @return void
   */
  public function observeProductEditFortInitialization(Varien_Event_Observer $observer)
  {
    $form = $observer->getEvent()->getForm();
    $elementsToCheck = array(
      ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
      ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE
    );
    foreach ($elementsToCheck as $elementCode) {
      $element = $form->getElement($elementCode);
      if (!$element) {
        continue;
      }
      $element->setFormat(
        Mage::app()->getLocale()->getDateTimeFormat(
          Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        )
      );
      $element->setTime(true);
    }
  }
  /**
   * Cron job for processing of scheduled products
   *
   * @return void
   */
  public function cronProcessScheduledProducts()
  {
    $currentDate = Mage::app()->getLocale()->date()->toString(
      Varien_Date::DATETIME_INTERNAL_FORMAT
    );
    $productModel = Mage::getModel('catalog/product');

    $expiredProductsCollection = $productModel->getCollection()
      ->addFieldToFilter(
        ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
        array(
          'nnull' => 1,
          'lteq' => $currentDate
        )
      )
      ->addFieldToFilter(
        ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_STATUS,
        Mage_Catalog_Model_Product_Status::STATUS_ENABLED
      );

    $expiredProductIds = $expiredProductsCollection->getAllIds();
    unset($expiredProductsCollection);
    if ($expiredProductIds) {
      Mage::getSingleton('catalog/product_action')
        ->updateAttributes(
           $expiredProductIds,
           array('status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED),
           Mage_Core_Model_App::ADMIN_STORE_ID
        );
    }
    $this->sendEmail(array(1,2,3));

    $activatedProductsCollection = $productModel->getCollection()
      ->addFieldToFilter(
        ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
        array(
          'nnull' => 1,
          'lteq' => $currentDate
        )
      )
      ->addFieldToFilter(
        ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
        array(
          array('null' => 1),
          array('gt' => $currentDate)
        )
      )
      ->addFieldToFilter(
        ValSamonte_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_STATUS,
        Mage_Catalog_Model_Product_Status::STATUS_DISABLED
      );

    $activatedProductIds = $activatedProductsCollection->getAllIds();
    unset($activatedProductsCollection);
    if ($activatedProductIds) {
      Mage::getSingleton('catalog/product_action')
        ->updateAttributes(
           $activatedProductIds,
           array('status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED),
           Mage_Core_Model_App::ADMIN_STORE_ID
        );
    }
  }

  public function sendEmail($ids) {
    $emailTemplate = Mage::getModel('core/email_template')->loadDefault('expired_product_email_template');

    $senderName = Mage::getStoreConfig('trans_email/ident_general/name');
    $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');

    $customerName = Mage::getStoreConfig('trans_email/ident_custom2/name');
    $customerEmail = Mage::getStoreConfig('trans_email/ident_custom2/email');

    //Variables for Confirmation Mail.
    $emailTemplateVariables = array();
    $emailTemplateVariables['ids'] = json_encode($ids);

    //Appending the Custom Variables to Template.
    $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
    //Sending E-Mail to Customers.
    $mail = Mage::getModel('core/email')
     ->setToName($senderName)
     ->setToEmail($customerEmail)
     ->setBody($processedTemplate)
     ->setSubject('Subject : Expired products report.')
     ->setFromEmail($senderEmail)
     ->setFromName($senderName)
     ->setType('html');
    try{
      //Confimation E-Mail Send
      $mail->send();
    }
    catch(Exception $error) {
      Mage::getSingleton('core/session')->addError($error->getMessage());
      return false;
    }
    return true;
  }
}
