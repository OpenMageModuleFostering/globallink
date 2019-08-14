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

class Transperfect_Globallink_Adminhtml_GlcreatesubmissionController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('globallink/adminhtml/glcreatesubmission.phtml'));
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function cancelAction() {
    $post = $this->getRequest()->getPost();
    $object_type = $post['object_type'];
    $store_id = $post['store'];
    Mage::getSingleton('adminhtml/session')->unsetData('gl_selected_ids');
    if ($object_type == Mage::helper("globallink")->_get_object_type_product()) {
      $this->_redirect('globallink/adminhtml_glproduct', array('_query' => array('store' => $store_id)));
      return $this;
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_product_attributes()) {
      $this->_redirect('globallink/adminhtml_glproductattributes', array('_query' => array('store' => $store_id)));
      return $this;
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_category()) {
      $this->_redirect('globallink/adminhtml_glcategory', array('_query' => array('store' => $store_id)));
      return $this;
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_block()) {
      $this->_redirect('globallink/adminhtml_glcmsblock', array('_query' => array('store' => $store_id)));
      return $this;
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_page()) {
      $this->_redirect('globallink/adminhtml_glcmspage', array('_query' => array('store' => $store_id)));
      return $this;
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_email()) {
      $this->_redirect('globallink/adminhtml_glemailtemplate', array('_query' => array('store' => $store_id)));
      return $this;
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_newsletter()) {
      $this->_redirect('globallink/adminhtml_glnewslettertemplate', array('_query' => array('store' => $store_id)));
      return $this;
    }
  }

  public function sendAction() {
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Starting Object Upload.");
    $post = $this->getRequest()->getPost();
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);


    $selected_ids = explode(',', Mage::getSingleton('adminhtml/session')->getData('gl_selected_ids'));
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Ids Selected]", $selected_ids);
    $success = TRUE;

    $object_type = $post['object_type'];
    $submitter = Mage::getSingleton('admin/session')->getUser()->getUsername();
    $gl_locales = Mage::helper("globallink")->get_all_gl_locales();
    $selected_store_id = $gl_locales[$post['gl_source_locale']]['store_id'];

    if (!$this->check_active_stores($post['gl_target_locales'])) {
      $this->_redirect('*/*', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
      return $this;
    }

    try {
      $gl_submission_arr = array();
      $gl_submission_arr['gl_submission_name'] = $post['gl_submission_name'];
      $gl_submission_arr['gl_due_date'] = $post['gl_due_date'];
      $gl_submission_arr['gl_priority'] = $post['gl_priority'];
      $gl_submission_arr['gl_source_locale'] = $post['gl_source_locale'];
      $gl_submission_arr['gl_target_locales'] = $post['gl_target_locales'];
      $gl_submission_arr['gl_submission_instructions'] = $post['gl_submission_instructions'];
      $gl_submission_arr['gl_project'] = $post['gl_project'];
      $gl_submission_arr['gl_submitter'] = $submitter;

      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Selected Store]", $selected_store_id);

      $globalLink_arr = array();
      if ($object_type == Mage::helper("globallink")->_get_object_type_product()) {
        $this->read_product_data($selected_store_id, $selected_ids, $globalLink_arr, $gl_submission_arr);
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_product_attributes()) {
        $this->read_product_attribute_data($selected_ids, $globalLink_arr, $gl_submission_arr);
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_category()) {
        $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
        if (is_array($field_config_arr) && count($field_config_arr) == 0) {
          Mage::getSingleton('adminhtml/session')->addError('No fields are configured for translation for type - ' . $object_type . '.');
          $this->_redirect('*/*');
          return $this;
        }
        $this->read_category_data($selected_store_id, $selected_ids, $globalLink_arr, $gl_submission_arr, $field_config_arr);
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_block()) {
        $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
        if (is_array($field_config_arr) && count($field_config_arr) == 0) {
          Mage::getSingleton('adminhtml/session')->addError('No fields are configured for translation for type - ' . $object_type . '.');
          $this->_redirect('*/*');
          return $this;
        }
        $this->read_block_data($selected_store_id, $selected_ids, $globalLink_arr, $gl_submission_arr, $field_config_arr);
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_page()) {
        $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
        if (is_array($field_config_arr) && count($field_config_arr) == 0) {
          Mage::getSingleton('adminhtml/session')->addError('No fields are configured for translation for type - ' . $object_type . '.');
          $this->_redirect('*/*');
          return $this;
        }
        $this->read_page_data($selected_store_id, $selected_ids, $globalLink_arr, $gl_submission_arr, $field_config_arr);
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_email()) {
        $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
        if (is_array($field_config_arr) && count($field_config_arr) == 0) {
          Mage::getSingleton('adminhtml/session')->addError('No fields are configured for translation for type - ' . $object_type . '.');
          $this->_redirect('*/*');
          return $this;
        }
        $this->read_email_data($selected_store_id, $selected_ids, $globalLink_arr, $gl_submission_arr, $field_config_arr);
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_newsletter()) {
        $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
        if (is_array($field_config_arr) && count($field_config_arr) == 0) {
          Mage::getSingleton('adminhtml/session')->addError('No fields are configured for translation for type - ' . $object_type . '.');
          $this->_redirect('*/*');
          return $this;
        }
        $this->read_newsletter_data($selected_store_id, $selected_ids, $globalLink_arr, $gl_submission_arr, $field_config_arr);
      }

      $count = count($globalLink_arr);
      if ($count > 0) {
        Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Preparing to send to GlobalLink Server]", $globalLink_arr);
        sendDocumentsForTranslationToPD($globalLink_arr, $gl_submission_arr['gl_project']);
        $status = "Sent for Translations";
        Mage::helper("globallink")->insert_gl_status($globalLink_arr, $status);
      }
    }
    catch (SoapFault $sf) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink WebService Error - ' . $sf->getMessage());
      $this->_redirect('*/*', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
      return $this;
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
      $this->_redirect('*/*', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
      return $this;
    }

    if ($success) {
      if ($count > 0) {
        Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Successfully sent to GlobalLink Server");
        $message = $this->__('Content has been successfully sent for translation.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
      }
      Mage::getSingleton('adminhtml/session')->unsetData('gl_selected_ids');
      if ($object_type == Mage::helper("globallink")->_get_object_type_product()) {
        $this->_redirect('globallink/adminhtml_glproduct', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_product_attributes()) {
        $this->_redirect('globallink/adminhtml_glproductattributes', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_category()) {
        $this->_redirect('globallink/adminhtml_glcategory', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_block()) {
        $this->_redirect('globallink/adminhtml_glcmsblock', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_page()) {
        $this->_redirect('globallink/adminhtml_glcmspage', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_email()) {
        $this->_redirect('globallink/adminhtml_glemailtemplate', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }
      elseif ($object_type == Mage::helper("globallink")->_get_object_type_newsletter()) {
        $this->_redirect('globallink/adminhtml_glnewslettertemplate', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
        return $this;
      }

      $this->_redirect('*/*');
      return $this;
    }
  }

  private function read_product_data($selected_store_id, $selected_product_ids, &$globalLink_arr, $gl_submission_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_product();
    foreach ($selected_product_ids as $product_id) {
      // Select All Checkbox is set to on if checked
      if ($product_id == 'on') {
        continue;
      }
      $target_locales = $gl_submission_arr['gl_target_locales'];
      $m_product = Mage::getModel($object_type)->setStoreId($selected_store_id)->load($product_id);

      $gl_product = array();
      $gl_product['object_id'] = $product_id;
      $gl_product['object_type'] = $object_type;

      $gl_attr_arr = array();
      $attributes = $m_product->getAttributes();
      $attribute_set_id = $m_product->getAttributeSetId();

      $attr_config_arr = Mage::helper("globallink")->get_attribute_set_config($attribute_set_id);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Product Id Selected]", $selected_product_ids);

      if (is_array($attr_config_arr) && count($attr_config_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError('No attributes are configured for product id - ' . $product_id . '.');
        continue;
      }

      foreach ($attributes as $attribute) {
        if (isset($attr_config_arr[$attribute_set_id])
                && isset($attr_config_arr[$attribute_set_id][$attribute->getAttributeId()])) {
          if ($attr_config_arr[$attribute_set_id][$attribute->getAttributeId()] == 1) {
            if ($attribute->getFrontend()->getValue($m_product) != '') {
              $gl_attr_arr[$attribute->getAttributeCode()] = $attribute->getFrontend()->getValue($m_product);
            }
          }
        }
      }

      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $m_product->getName() . ' is empty.');
        continue;
      }

      $gl_product['attributes'] = $gl_attr_arr;

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_product);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($m_product->getName()) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;

      foreach ($target_locales as $key => $target_locale) {
        if (!$this->check_existing_submission($object_type, $product_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError('Product id - ' . $product_id . ' has already been sent out for translation for target language  - ' . $target_locale . '.');
          unset($target_locales[$key]);
        }
      }

      $globalLink = new GlobalLink;
      $globalLink->objectId = $product_id;
      $globalLink->objectType = $object_type;
      $globalLink->objectName = $m_product->getName();
      $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
      $globalLink->targetLocale = $target_locales;
      $globalLink->sourceXML = $source_xml;
      $globalLink->sourceFileName = $source_name;
      $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
      $globalLink->dueDate = $due_date;
      $globalLink->priority = $gl_submission_arr['gl_priority'];
      $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
      $globalLink->submitter = $gl_submission_arr['gl_submitter'];
      array_push($globalLink_arr, $globalLink);
    }
  }
  
  private function read_product_attribute_data($selected_product_attribute_ids, &$globalLink_arr, $gl_submission_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_product_attributes();
    // Select All Checkbox is set to on if checked
    foreach ($selected_product_attribute_ids as $attribute_id) {
      $model = Mage::getModel('catalog/resource_eav_attribute');
      $model->load($attribute_id);
      
      if (!$model->getId()) {
        continue;
      }
      
      $target_locales = $gl_submission_arr['gl_target_locales'];
      
      $gl_product_attribute = array();
      $gl_product_attribute['object_id'] = $attribute_id;
      $gl_product_attribute['object_type'] = $object_type;

      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Product Attribute Id Selected]", $selected_product_attribute_ids);
      
      $gl_attr_arr = array();
      
      $gl_attr_arr['frontend_label'] = $model->frontend_label;
      
      $attribute = Mage::getSingleton("eav/config")->getAttribute("catalog_product", $model->attribute_code);
      $options_arr = $attribute->getSource()->getAllOptions(false);
      
      if (!empty($options_arr)){
        foreach ($options_arr as $option) {
          $gl_attr_arr[$option['value']] = $option['label'];
        }
      }
      
      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $model->attribute_code . ' is empty.');
        continue;
      }

      $gl_product_attribute['attributes'] = $gl_attr_arr;

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_product_attribute);
      
      //get attribute name
      $source_name = Mage::helper("globallink")->get_formatted_file_name($model->attribute_code) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;

      foreach ($target_locales as $key => $target_locale) {
        if (!$this->check_existing_submission($object_type, $attribute_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError('Product attribute id - ' . $attribute_id . ' has already been sent out for translation for target language  - ' . $target_locale . '.');
          unset($target_locales[$key]);
        }
      }

      $globalLink = new GlobalLink;
      $globalLink->objectId = $attribute_id;
      $globalLink->objectType = $object_type;
      $globalLink->objectName = $model->attribute_code;
      $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
      $globalLink->targetLocale = $target_locales;
      $globalLink->sourceXML = $source_xml;
      $globalLink->sourceFileName = $source_name;
      $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
      $globalLink->dueDate = $due_date;
      $globalLink->priority = $gl_submission_arr['gl_priority'];
      $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
      $globalLink->submitter = $gl_submission_arr['gl_submitter'];
      array_push($globalLink_arr, $globalLink);
    }
  }
  
  private function read_category_data($selected_store_id, $selected_category_ids, &$globalLink_arr, $gl_submission_arr, $field_config_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_category();
    foreach ($selected_category_ids as $category_id) {
      // Select All Checkbox is set to on if checked
      if ($category_id == 'on') {
        continue;
      }
      $t_category = Mage::getModel($object_type)->setStoreId($selected_store_id)->load($category_id);
      Mage::helper("globallink")->gl_debug('Selected Category Object', $t_category);
      $gl_category = array();
      $gl_category['object_id'] = $category_id;
      $gl_category['object_type'] = $object_type;

      $gl_attr_arr = array();
      foreach ($field_config_arr as $field) {
        if ($t_category->getData($field) != '') {
          $gl_attr_arr[$field] = $t_category->getData($field);
        }
      }
      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $t_category->getName() . ' is empty.');
        continue;
      }

      $gl_category['attributes'] = $gl_attr_arr;
      Mage::helper("globallink")->gl_debug('Selected category Arr', $gl_category);

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_category);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($t_category->getName()) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;

      $target_locales = $gl_submission_arr['gl_target_locales'];

      foreach ($target_locales as $key => $target_locale) {
        if (!$this->check_existing_submission($object_type, $category_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError($t_category->getName() . ' has already been sent out for translation for target language ' . $target_locale . '.');
          unset($target_locales[$key]);
        }
      }

      $globalLink = new GlobalLink;
      $globalLink->objectId = $category_id;
      $globalLink->objectType = $object_type;
      $globalLink->objectName = $t_category->getName();
      $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
      $globalLink->targetLocale = $target_locales;
      $globalLink->sourceXML = $source_xml;
      $globalLink->sourceFileName = $source_name;
      $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
      $globalLink->dueDate = $due_date;
      $globalLink->priority = $gl_submission_arr['gl_priority'];
      $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
      $globalLink->submitter = $gl_submission_arr['gl_submitter'];
      array_push($globalLink_arr, $globalLink);
    }
  }

  private function read_block_data($selected_store_id, $selected_block_ids, &$globalLink_arr, $gl_submission_arr, $field_config_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_block();
    foreach ($selected_block_ids as $block_id) {
      // Select All Checkbox is set to on if checked
      if ($block_id == 'on') {
        continue;
      }
      $block = Mage::getModel($object_type)->setStoreId($selected_store_id)->load($block_id);
      Mage::helper("globallink")->gl_debug('Selected block Arr', $block);

      $gl_cmsblock = array();
      $gl_cmsblock['object_id'] = $block_id;
      $gl_cmsblock['object_type'] = $object_type;

      $gl_attr_arr = array();
      foreach ($field_config_arr as $field) {
        if ($block->getData($field) != '') {
          $gl_attr_arr[$field] = $block->getData($field);
        }
      }
      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $block->getTitle() . ' is empty.');
        continue;
      }

      $gl_cmsblock['attributes'] = $gl_attr_arr;
      Mage::helper("globallink")->gl_debug('Selected block Arr', $gl_cmsblock);

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_cmsblock);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($block->getTitle()) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;

      $target_locales = $gl_submission_arr['gl_target_locales'];
      foreach ($target_locales as $key => $target_locale) {
        if (!$this->check_existing_submission($object_type, $block_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError($block->getTitle() . ' has already been sent out for translation for target language ' . $target_locale . '.');
          unset($target_locales[$key]);
        }
      }

      $globalLink = new GlobalLink;
      $globalLink->objectId = $block_id;
      $globalLink->objectType = $object_type;
      $globalLink->objectName = $block->getTitle();
      $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
      $globalLink->targetLocale = $target_locales;
      $globalLink->sourceXML = $source_xml;
      $globalLink->sourceFileName = $source_name;
      $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
      $globalLink->dueDate = $due_date;
      $globalLink->priority = $gl_submission_arr['gl_priority'];
      $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
      $globalLink->submitter = $gl_submission_arr['gl_submitter'];
      array_push($globalLink_arr, $globalLink);
    }
  }

  private function read_page_data($selected_store_id, $selected_page_ids, &$globalLink_arr, $gl_submission_arr, $field_config_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_page();
    foreach ($selected_page_ids as $page_id) {
      // Select All Checkbox is set to on if checked
      if ($page_id == 'on') {
        continue;
      }

      $page = Mage::getModel($object_type)->setStoreId($selected_store_id)->load($page_id);
      $title = $page->getTitle();
      if (isset($page['published_revision_id']) && $page['published_revision_id'] != 0) {
        $page = Mage::getModel('enterprise_cms/page_revision')->load($page['published_revision_id']);
      }

      Mage::helper("globallink")->gl_debug('Selected Page Arr', $page);

      $gl_cmspage = array();
      $gl_cmspage['object_id'] = $page_id;
      $gl_cmspage['object_type'] = $object_type;

      $gl_attr_arr = array();
      foreach ($field_config_arr as $field) {
        if ($page->getData($field) != '') {
          $gl_attr_arr[$field] = $page->getData($field);
        }
        if ($field == 'title') {
          $gl_attr_arr['title'] = $title;
        }
      }
      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $title . ' is empty.');
        continue;
      }

      $gl_cmspage['attributes'] = $gl_attr_arr;
      Mage::helper("globallink")->gl_debug('Selected Page Arr', $gl_cmspage);

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_cmspage);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($title) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;
      $target_locales = $gl_submission_arr['gl_target_locales'];
      foreach ($target_locales as $key => $target_locale) {
        if (!$this->check_existing_submission($object_type, $page_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError($title . ' has already been sent out for translation for target language ' . $target_locale . '.');
          unset($target_locales[$key]);
        }
      }

      $globalLink = new GlobalLink;
      $globalLink->objectId = $page_id;
      $globalLink->objectType = $object_type;
      $globalLink->objectName = $title;
      $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
      $globalLink->targetLocale = $target_locales;
      $globalLink->sourceXML = $source_xml;
      $globalLink->sourceFileName = $source_name;
      $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
      $globalLink->dueDate = $due_date;
      $globalLink->priority = $gl_submission_arr['gl_priority'];
      $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
      $globalLink->submitter = $gl_submission_arr['gl_submitter'];
      array_push($globalLink_arr, $globalLink);
    }
  }

  private function read_email_data($selected_store_id, $selected_template_ids, &$globalLink_arr, $gl_submission_arr, $field_config_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_email();
    foreach ($selected_template_ids as $template_id) {
      // Select All Checkbox is set to on if checked
      if ($template_id == 'on') {
        continue;
      }
      $template = Mage::getModel($object_type)->load($template_id);
      Mage::helper("globallink")->gl_debug('Selected template Arr', $template);
      $gl_emailtemplate = array();
      $gl_emailtemplate['object_id'] = $template_id;
      $gl_emailtemplate['object_type'] = $object_type;

      $gl_attr_arr = array();
      foreach ($field_config_arr as $field) {
        if ($template->getData($field) != '') {
          $gl_attr_arr[$field] = $template->getData($field);
        }
      }

      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $template->getTemplateSubject() . ' is empty.');
        continue;
      }

      $gl_emailtemplate['attributes'] = $gl_attr_arr;
      Mage::helper("globallink")->gl_debug('Selected template Arr', $gl_emailtemplate);

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_emailtemplate);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($template->getTemplateSubject()) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;
      $target_locales = $gl_submission_arr['gl_target_locales'];
      foreach ($target_locales as $key => $target_locale) {
        if (!$this->check_existing_submission($object_type, $template_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError($template->getTemplateCode() . ' has already been sent out for translation for target language ' . $target_locale . '.');
          unset($target_locales[$key]);
        }
      }

      $globalLink = new GlobalLink;
      $globalLink->objectId = $template_id;
      $globalLink->objectType = $object_type;
      $globalLink->objectName = $template->getTemplateCode();
      $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
      $globalLink->targetLocale = $target_locales;
      $globalLink->sourceXML = $source_xml;
      $globalLink->sourceFileName = $source_name;
      $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
      $globalLink->dueDate = $due_date;
      $globalLink->priority = $gl_submission_arr['gl_priority'];
      $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
      $globalLink->submitter = $gl_submission_arr['gl_submitter'];
      array_push($globalLink_arr, $globalLink);
    }
  }

  private function read_newsletter_data($selected_store_id, $selected_newsletter_ids, &$globalLink_arr, $gl_submission_arr, $field_config_arr) {
    $object_type = Mage::helper("globallink")->_get_object_type_newsletter();
    foreach ($selected_newsletter_ids as $newsletter_id) {
      // Select All Checkbox is set to on if checked
      if ($newsletter_id == 'on') {
        continue;
      }
      $newsletter = Mage::getModel($object_type)->load($newsletter_id);
      Mage::helper("globallink")->gl_debug('Selected newsletter Arr', $newsletter);
      $newsletter_id = $newsletter->getId();

      $gl_newslettertemplate = array();
      $gl_newslettertemplate['object_id'] = $newsletter_id;
      $gl_newslettertemplate['object_type'] = $object_type;

      $gl_attr_arr = array();
      foreach ($field_config_arr as $field) {
        if ($newsletter->getData($field) != '') {
          $gl_attr_arr[$field] = $newsletter->getData($field);
        }
      }

      if (count($gl_attr_arr) == 0) {
        Mage::getSingleton('adminhtml/session')->addError($object_type . ' - ' . $newsletter->getTemplateSubject() . ' is empty.');
        continue;
      }

      $gl_newslettertemplate['attributes'] = $gl_attr_arr;
      Mage::helper("globallink")->gl_debug('Selected newsletter Arr', $gl_newslettertemplate);

      $source_xml = Mage::helper("globallink")->create_source_xml($gl_newslettertemplate);
      $source_name = Mage::helper("globallink")->get_formatted_file_name($newsletter->getTemplateSubject()) . ".xml";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source File Name]", $source_name);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source XML]", $source_xml);

      $time_arr = explode('/', $gl_submission_arr['gl_due_date']);
      $due_date = mktime(0, 0, 0, $time_arr[0], $time_arr[1], $time_arr[2]) * 1000;
      $target_locales = $gl_submission_arr['gl_target_locales'];
      foreach ($target_locales as $target_locale) {
        if (!$this->check_existing_submission($object_type, $newsletter_id, $gl_submission_arr['gl_source_locale'], $target_locale)) {
          Mage::getSingleton('adminhtml/session')->addError($newsletter->getTemplateCode() . ' has already been sent out for translation for target language ' . $target_locale . '.');
          continue;
        }
        $globalLink = new GlobalLink;
        $globalLink->objectId = $newsletter_id;
        $globalLink->objectType = $object_type;
        $globalLink->objectName = $newsletter->getTemplateCode();
        $globalLink->sourceLocale = $gl_submission_arr['gl_source_locale'];
        $globalLink->targetLocale = array($target_locale);
        $globalLink->sourceXML = $source_xml;
        $globalLink->sourceFileName = $source_name;
        $globalLink->submissionName = $gl_submission_arr['gl_submission_name'];
        $globalLink->dueDate = $due_date;
        $globalLink->priority = $gl_submission_arr['gl_priority'];
        $globalLink->submissionInstructions = $gl_submission_arr['gl_submission_instructions'];
        $globalLink->submitter = $gl_submission_arr['gl_submitter'];
        array_push($globalLink_arr, $globalLink);
      }
    }
  }

  public function check_existing_submission($object_type, $object_id, $source_locale, $target_locale) {
    $where = " WHERE object_type = '$object_type' AND object_id = $object_id
    AND gl_source_locale = '$source_locale' AND gl_target_locale = '$target_locale'
    AND status = 'Sent for Translations';";

    $rows = Mage::helper("globallink")->get_status_objects("*", $where, FALSE);
    if (count($rows) > 0) {
      return FALSE;
    }

    return TRUE;
  }

  public function check_active_stores($target_locales) {
    $stores = Mage::app()->getStores();
    $store_id_arr = array();
    foreach ($stores as $store) {
      if ($store->getIsActive()) {
        $store_id_arr[$store->getStoreId()] = $store->getStoreId();
      }
    }
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT store_id FROM globallink_locale WHERE gl_locale_code IN ('" . implode("','", $target_locales) . "');";
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if (!in_array($row['store_id'], $store_id_arr)) {
          $sql = "SELECT gl_locale_desc FROM globallink_locale WHERE store_id = " . $row['store_id'] . ";";
          $result = $db->query($sql)->fetch(PDO::FETCH_OBJ);
          Mage::getSingleton('adminhtml/session')->addError($result->gl_locale_desc . ' target language does not have an active store.');
          return FALSE;
        }
      }
    }
    return TRUE;
  }

}