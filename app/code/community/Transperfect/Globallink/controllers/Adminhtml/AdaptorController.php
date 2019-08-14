<?php
/* © 2013 Translations.com, a TransPerfect company 
 * Translations.com, Inc., its affiliates and its licensors (collectively, “Translations.com”) own all right, title and interest, 
 * including but not limited to all intellectual property rights, in and to this software and all associated documentation, updates, 
 * new releases and work product, if any (collectively, the “Software”), in both object code and source code formats.  
 * 
 * By making use of the Software, you agree 
 * (i)	to reproduce the copyright, trademark and other proprietary notices contained on or in the Software as delivered to you (including this Notice)
 *      on any reproductions you cause to be made of the Software and further agree not to remove any such notices (including this Notice) from the Software or any copies thereof; 
 * (ii) the Software shall not be further licensed, sold or otherwise transferred by you, except if otherwise approved in writing by an authorized Translations.com representative; 
 * (iii) not to release the results of any benchmark testing of the Software or use any trademark, logo or proprietary notice of Translations.com (except as required by this Notice or law) without Translations.com’s prior written approval; and 
 * (iv) you shall not modify, change nor create any derivative works of the Software.  
 * Any derivative works of the Software created by you, your employees, agents, or contractors, including any and all modifications or changes to the Software, in any format whatsoever, shall be the exclusive property of Translations.com.
*/

require_once 'gl_ws/GlobalLink.php';
require_once 'gl_ws/gl_ws_send_translations.php';

class Transperfect_Globallink_Adminhtml_AdaptorController extends Mage_Adminhtml_Controller_Action {

  public function postAction() {
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Starting Product Upload.");
    $success = TRUE;
    $gl_submission_arr = array();
    $gl_product = array();

    try {
      $post = $this->getRequest()->getPost();
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);

      $url = $post['current_url'];
      $pos = strpos($url, 'active_tab');
      if ($pos) {
        $end = $pos;
        for ($index = 0; $index < 2; $index++) {
          $end = strpos($url, '/', $end + 1);
        }
        $sub = substr($url, $pos, intval($end - $pos));
        if (isset($sub) && $sub != '') {
          $url = str_replace($sub, 'active_tab/my_global_link_tab', $url);
          $this->_redirectUrl($url);
        }
        else {
          $this->_redirectUrl($post['current_url']);
        }
      }
      else {
        $url = $url . 'active_tab/my_global_link_tab/';
        $this->_redirectUrl($url);
      }

      $gl_submission_arr['gl_submission_name'] = $post['gl_submission_name'];
      $gl_submission_arr['gl_due_date'] = $post['gl_due_date'];
      $gl_submission_arr['gl_priority'] = $post['gl_priority'];
      $gl_submission_arr['gl_source_locale'] = $post['gl_source_locale'];
      $gl_submission_arr['gl_target_locales'] = $post['gl_target_locales'];
      $gl_submission_arr['gl_project'] = $post['gl_project'];

      $product_id = $post['product_id'];
      $selected_attribute_ids = $post['gl_product_attribute_ids'];

      $gl_product['object_id'] = $product_id;
      $object_type = Mage::helper("globallink")->_get_object_type_product();
      $gl_product['object_type'] = $object_type;

      $target_locales = $gl_submission_arr['gl_target_locales'];
      foreach ($target_locales as $target_locale) {
        if (!$this->check_existing_submission($object_type, $product_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError('The selected record has already been sent out for translation.');
          return $this;
        }
      }

      $mapped_locale_arr = Mage::helper("globallink")->get_mapped_locales();
      $source_store_id = $mapped_locale_arr[$gl_submission_arr['gl_source_locale']]['store_id'];
      $model = Mage::getModel($object_type); //getting product model
      $product = $model->setStoreId($source_store_id)->load($product_id);

      $gl_attr_arr = array();

      $attributes = $product->getAttributes();
      foreach ($attributes as $attribute) {
        if ($attribute->getIsVisible() && !$attribute->getIsGlobal()) {
          if ($attribute->getFrontendInput() == 'text' || $attribute->getFrontendInput() == 'textarea') {
            $attr_id = $attribute->getId();
            if (in_array($attr_id, $selected_attribute_ids)) {
              $gl_attr_arr[$attribute->getAttributeCode()] = $attribute->getFrontend()->getValue($product);
            }
          }
        }
      }
      $gl_product['attributes'] = $gl_attr_arr;

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_product);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($product->getName());
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;

      $globalLink_arr = array();

      foreach ($target_locales as $target_locale) {
        $globalLink = new GlobalLink;
        $globalLink->objectId = $product_id;
        $globalLink->objectType = $object_type;
        $globalLink->objectName = $product->getName();
        $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
        $globalLink->targetLocale = array($target_locale);
        $globalLink->sourceXML = $source_xml;
        $globalLink->sourceFileName = $source_name;
        $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
        $globalLink->dueDate = $due_date;
        $globalLink->priority = $gl_submission_arr['gl_priority'];

        array_push($globalLink_arr, $globalLink);
      }
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Preparing to send to GlobalLink Server]", $globalLink_arr);

      sendDocumentsForTranslationToPD($globalLink_arr, $gl_submission_arr['gl_project']);
      $status = "Sent for Translations";
      Mage::helper("globallink")->insert_gl_status($globalLink_arr, $status);
    }
    catch (SoapFault $sf) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink WebService Error - ' . $sf->getMessage());
      return $this;
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
      return $this;
    }

    if ($success) {
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Successfully sent to GlobalLink Server");
      $message = $this->__('Product has been successfully sent for translation.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('adminhtml/catalog_product');
    return $this;
  }

  public function check_existing_submission($object_type, $object_id, $source_locale, $target_locale) {
    $where = " WHERE object_type = '" . $object_type . "' AND object_id = " . $object_id
            . " AND gl_source_locale = '" . $source_locale . "' AND gl_target_locale = '"
            . $target_locale . "' AND status = 'Sent for Translations' ";

    $rows = Mage::helper("globallink")->get_status_objects("*", $where, FALSE);
    if (count($rows) > 0) {
      return FALSE;
    }

    return TRUE;
  }

}
