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

require_once Mage::getBaseDir('app') . '/code/community/Transperfect/Globallink/controllers/Adminhtml/gl_ws/GlobalLink.php';
require_once Mage::getBaseDir('app') . '/code/community/Transperfect/Globallink/controllers/Adminhtml/gl_ws/gl_ws_receive_translations.php';
require_once Mage::getBaseDir('app') . '/code/community/Transperfect/Globallink/controllers/Adminhtml/gl_ws/gl_ws_common.php';

class Transperfect_Globallink_Helper_Data extends Mage_Core_Helper_Abstract {

  function _get_object_type_product() {
    return "catalog/product";
  }

  function _get_object_type_product_attributes() {
    return "catalog/product_attributes";
  }

  function _get_object_type_category() {
    return "catalog/category";
  }

  function _get_object_type_block() {
    return "cms/block";
  }

  function _get_object_type_page() {
    return "cms/page";
  }

  function _get_object_type_email() {
    return "adminhtml/email_template";
  }

  function _get_object_type_newsletter() {
    return "newsletter/template";
  }

  function _get_submission_status_sent() {
    return "Sent for Translations";
  }

  function _get_submission_status_cancelled() {
    return "Cancelled";
  }

  function _get_submission_status_complete() {
    return "Translation Complete";
  }

  function _get_submission_status_deleted() {
    return "Source Deleted";
  }

  function _get_submission_status_ready() {
    return "Ready for Import";
  }

  function _get_submission_status_error() {
    return "Error";
  }

  function gl_debug($from, $message) {
    Mage::log("[" . $from . "][" . print_r($message, true) . "]");
    if ($this->setting_enabled('gl_logging')) {
      Mage::log("[" . $from . "][" . print_r($message, true) . "]", null, 'globallink.log');
    }
  }

  function get_cron_job_code() {
    return "gl_cron_auto_update";
  }

  function get_cron_status_success() {
    return "success";
  }

  function get_cron_status_error() {
    return "error";
  }

  function get_wsdl_path() {
    $burl = Mage::getBaseDir('app');
    $wsdl_path = $burl . "/code/community/Transperfect/Globallink/controllers/Adminhtml/gl_ws/wsdl/";
    return $wsdl_path;
  }

  function get_status_objects($what = "*", $where = "", $quote = TRUE) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    if ($quote) {
      $where = $db->quote($where);
    }

    $sql = " SELECT $what FROM globallink_status $where ;";
    $data = $db->query($sql);
    $arr = array();
    if ($data) {
      while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
        array_push($arr, $row);
      }
    }
    return $arr;
  }

  function get_all_gl_locales($remove_mapped = FALSE) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_locale ORDER BY gl_locale_desc;";

    $locales = array();

    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $store_id = 'NULL';
        if (isset($row['store_id'])) {
          if ($remove_mapped == TRUE) {
            continue;
          }
          $store_id = $row['store_id'];
        }
        $locales[$row['gl_locale_code']] = array('gl_locale_code' => $row['gl_locale_code'],
            'gl_locale_desc' => $row['gl_locale_desc'], 'store_id' => $store_id);
      }
    }

    return $locales;
  }

  function get_gl_locale_options() {
    $arr = $this->get_mapped_locales();
    $options = array();
    foreach ($arr as $gl_locale) {
      $options[$gl_locale['gl_locale_code']] = $gl_locale['gl_locale_desc'];
    }
    return $options;
  }

  function get_gl_locale_desc($gl_locale_code) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT gl_locale_desc FROM globallink_locale WHERE gl_locale_code = '$gl_locale_code';";

    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if (isset($row['gl_locale_desc'])) {
          $gl_locale_desc = $row['gl_locale_desc'];
        }
      }
    }

    return $gl_locale_desc;
  }

  function get_gl_status_options() {
    $statuses = array(
        $this->_get_submission_status_ready() => $this->_get_submission_status_ready(),
        $this->_get_submission_status_deleted() => $this->_get_submission_status_deleted(),
        $this->_get_submission_status_error() => $this->_get_submission_status_error()
    );
    return $statuses;
  }

  public function getSubmissionNames() {
    $gl_sub_arr = $this->get_status_objects("DISTINCT submission_name", "WHERE status IN ('"
            . $this->_get_submission_status_sent() . "','" . $this->_get_submission_status_deleted() . "','" . $this->_get_submission_status_cancelled() . "','"
            . $this->_get_submission_status_error() . "','" . $this->_get_submission_status_ready() . "') ORDER BY submission_name ", FALSE);
    $submissions = array();
    foreach ($gl_sub_arr as $submission_row) {
      $submissions[] = $submission_row['submission_name'];
    }
    return $submissions;
  }

  public function getActiveSubmissionNames() {
    $this->get_submission_status();
    $gl_sub_arr = $this->get_status_objects("DISTINCT submission_name", "WHERE status IN ('"
            . $this->_get_submission_status_sent() . "','" . $this->_get_submission_status_ready() . "','" . $this->_get_submission_status_cancelled() . "','"
            . $this->_get_submission_status_error() . "') ORDER BY submission_name ", FALSE);
    $submissions = array();
    foreach ($gl_sub_arr as $submission_row) {
      $submissions[] = $submission_row['submission_name'];
    }
    return $submissions;
  }

  public function getSubmitters() {
    $gl_sub_arr = $this->get_status_objects("DISTINCT submitter", "WHERE status IN ('"
            . $this->_get_submission_status_sent() . "','" . $this->_get_submission_status_ready() . "','" . $this->_get_submission_status_cancelled() . "','"
            . $this->_get_submission_status_error() . "') ORDER BY submitter ", FALSE);
    $submitters = array();
    foreach ($gl_sub_arr as $submitter_row) {
      $submitters[] = $submitter_row['submitter'];
    }
    return $submitters;
  }

  public function getSubmissionOptions() {
    $arr = $this->getSubmissionNames();
    $options = array();
    foreach ($arr as $option) {
      $options[$option] = $option;
    }
    return $options;
  }

  public function getObjectTypeOptions($remove_field_arr = NULL) {
    $options = array(
        'catalog/product' => 'Product',
        'catalog/product_attributes' => 'Product Attributes',
        'catalog/category' => 'Product Category',
        'cms/block' => 'CMS Block',
        'cms/page' => 'CMS Page',
        'adminhtml/email_template' => 'Transactional Email Template',
        'newsletter/template' => 'Newsletter Template',
    );

    if ($remove_field_arr != NULL) {
      foreach ($remove_field_arr as $remove_field) {
        unset($options[$remove_field]);
      }
    }

    asort($options);

    return $options;
  }

  function get_default_gl_store_id() {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_locale WHERE store_id IS NOT NULL AND gl_default = 1;";
    $store_id = '';
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if (isset($row['store_id'])) {
          $store_id = $row['store_id'];
          break;
        }
      }
    }

    return $store_id;
  }

  function get_mapped_locales() {
    $stores = Mage::app()->getStores();
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    $admin_store = Mage::app()->getStore();
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_locale WHERE store_id IS NOT NULL ORDER BY gl_locale_desc;";

    $targetLanguages = array();

    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $store_id = 'NULL';
        $store_name = 'NULL';
        if (isset($row['store_id'])) {
          $store_id = $row['store_id'];
          if ($store_id == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $store_name = $admin_store->getName();
          }
          else {
            foreach ($stores as $store) {
              if ($store->getId() == $store_id) {
                $store_name = $store->getName();
              }
            }
          }
        }
        $targetLanguages[$row['gl_locale_code']] = array('gl_locale_code' => $row['gl_locale_code'],
            'gl_locale_desc' => $row['gl_locale_desc'], 'store_id' => $store_id, 'store_name' => $store_name, 'default' => $row['gl_default']);
      }
    }

    return $targetLanguages;
  }

  function get_gl_locale_codes($what = "*", $where = "") {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = " SELECT $what FROM globallink_locale $where ;";
    $data = $db->query($sql);

    $arr = array();
    if ($data) {
      while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row[$what];
      }
    }
    return $arr;
  }

  function get_gl_configurations() {
    $gl_config = array();
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_projects;";
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $gl_config[$row['globallink_project_id']] = array('globallink_project_id' => $row['globallink_project_id'],
            'globallink_url' => $row['globallink_url'], 'project_short_code' => $row['project_short_code'],
            'classifier' => $row['classifier'], 'mime_type' => $row['mime_type'],
            'files_per_submission' => $row['files_per_submission'],
            'max_target_count' => $row['max_target_count'],
        );
      }
    }
    $usql = "SELECT * FROM globallink_users;";
    $uresults = $db->query($usql);
    if ($uresults) {
      while ($row = $uresults->fetch(PDO::FETCH_ASSOC)) {
        $keys = array_keys($gl_config);
        foreach ($keys as $key) {
          $gl_config[$key]['globallink_user_id'] = $row['globallink_user_id'];
          $gl_config[$key]['globallink_user_name'] = $row['globallink_user_name'];
          $gl_config[$key]['globallink_user_password'] = $this->decrypt_gl_pw($row['globallink_user_name'], $row['globallink_user_password']);
        }
      }
    }
    return $gl_config;
  }

  function encrypt_gl_pw($user_id, $pw) {
    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($user_id), $pw, MCRYPT_MODE_CBC, md5(md5($user_id))));
    return $encrypted;
  }

  function decrypt_gl_pw($user_id, $pw) {
    $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($user_id), base64_decode($pw), MCRYPT_MODE_CBC, md5(md5($user_id))), "\0");
    return $decrypted;
  }

  function insert_child_element($dom, $root, $elem_name, $elem_value, $attributes = NULL) {
    if ($elem_name) {
      $item = $dom->createElement($elem_name);
      if (isset($attributes) && is_array($attributes)) {
        foreach ($attributes as $key => $value) {
          $item->setAttribute($key, $value);
        }
      }
      $text = $dom->createTextNode($elem_value);
      $item->appendChild($text);
      $root->appendChild($item);
    }
  }

  function parse_target_xml($target_xml) {
    $dom = new DomDocument;

    try {
      $dom->preserveWhiteSpace = FALSE;
      $dom->loadXML($target_xml);

      $gl_object = array();

      $contents = $dom->getElementsByTagName('content');
      foreach ($contents as $content) {
        if (!is_null($content->attributes)) {
          foreach ($content->attributes as $attrName => $attrNode) {
            if ('object_type' == $attrName) {
              $gl_object['object_type'] = $attrNode->value;
            }
            elseif ('object_id' == $attrName) {
              $gl_object['object_id'] = $attrNode->value;
            }
          }
        }
      }

      $names = $dom->getElementsByTagName('name');
      foreach ($names as $name) {
        $gl_object['name'] = $name->nodeValue;
      }

      $attributes = $dom->getElementsByTagName('attribute');
      $attr_arr = array();

      foreach ($attributes as $attribute) {
        $translated_attr = $attribute->nodeValue;
        if (!is_null($attribute->attributes)) {
          $attr_id = '';
          $attr_code = '';
          foreach ($attribute->attributes as $attrName => $attrNode) {
            switch ($attrName) {
              case 'attribute_id':
                $attr_id = $attrNode->value;
                continue 2;
              case 'attribute_code':
                $attr_code = $attrNode->value;
                continue 2;
            }
          }
          $attr_arr[$attr_code] = $translated_attr;
        }
      }

      $gl_object['attributes'] = $attr_arr;

      return $gl_object;
    }
    catch (Exception $ex) {
      $this->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Exception in parsing target XML ', $ex->getMessage());
      throw $ex;
    }
  }

  function create_source_xml($gl_object) {

    if (!isset($gl_object) || count($gl_object) == 0) {
      return "";
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = TRUE;

    try {
      $root = $dom->createElement("content");
      $dom->appendChild($root);

      $type = $dom->createAttribute('object_type');
      $type->value = $gl_object['object_type'];
      $root->appendChild($type);

      $pid = $dom->createAttribute('object_id');
      $pid->value = $gl_object['object_id'];
      $root->appendChild($pid);

      if (isset($gl_object['name'])) {
        $this->insert_child_element($dom, $root, "name", $gl_object['name']);
      }

      if (isset($gl_object['attributes'])) {
        $attr_arr = $gl_object['attributes'];
        foreach ($attr_arr as $attr_code => $attr_value) {
          if ($attr_value != "") {
            $this->insert_child_element($dom, $root, "attribute", $attr_value, array('attribute_code' => $attr_code));
          }
        }
      }
    }
    catch (Exception $ex) {
      $this->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Exception in creating XML ', $ex->getMessage());
      throw $ex;
    }

    return $dom->saveXML();
  }

  function get_formatted_file_name($string) {
    $bad = array_merge(
            array_map('chr', range(0, 31)), array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));

    $name = str_replace($bad, "_", $string);

    if (strlen($string) > 50) {
      $name = substr($string, 0, 50);
    }
    return $name;
  }

  public function insert_gl_status($gl_array, $status) {
    foreach ($gl_array as $globallink) {
      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $target_locale_arr = $globallink->targetLocale;
      $due_date = intval($globallink->dueDate / 1000);
      foreach ($target_locale_arr as $target_locale) {
        $where = " WHERE object_type = '" . $globallink->objectType . "' AND object_id = " . $globallink->objectId
                . " AND gl_source_locale = '" . $globallink->sourceLocale . "' AND gl_target_locale = '" . $target_locale . "'";

        $existing_row = $this->get_status_objects("*", $where, FALSE);
        $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][globallink_status UPDATE SQL]", $existing_row);
        if (count($existing_row) > 0) {
          $job_id = $existing_row[0]['globallink_job_id'];
          $time = time();
          $object_name = $db->quote($globallink->objectName);
          $submission_name = $db->quote($globallink->submissionName);
          $document_name = $db->quote($globallink->sourceFileName);
          $sql = "UPDATE globallink_status SET globallink_user_id =  $globallink->glUserId ,
                globallink_project_id = $globallink->glProjectId,
                object_type = '$globallink->objectType', object_id = $globallink->objectId,
                object_name = $object_name, document_name = $document_name,
                document_id = '$globallink->documentTicket', submission_name = $submission_name,
                submission_id = '$globallink->submissionTicket', gl_source_locale = '$globallink->sourceLocale',
                gl_target_locale = '$target_locale', create_date = $time , update_date = $time,
                due_date = $due_date , status = '$status', submitter = '$globallink->submitter', last_modified = $time, changed = 0 WHERE globallink_job_id = $job_id ;";

          $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][globallink_status UPDATE SQL]", $sql);
          $db->query($sql);
        }
        else {
          $time = time();
          $object_name = $db->quote($globallink->objectName);
          $submission_name = $db->quote($globallink->submissionName);
          $document_name = $db->quote($globallink->sourceFileName);
          $sql = "INSERT INTO globallink_status (
                globallink_user_id, globallink_project_id, object_type,
                object_id, object_name, document_name, document_id,
                submission_name, submission_id, gl_source_locale,
                gl_target_locale, create_date, update_date,
                due_date, status, submitter, last_modified, changed) VALUES (
                $globallink->glUserId, $globallink->glProjectId,
                '$globallink->objectType', $globallink->objectId, $object_name,
                $document_name, '$globallink->documentTicket', $submission_name,
                '$globallink->submissionTicket', '$globallink->sourceLocale',
                '$target_locale', $time, $time, $due_date, '$status', '$globallink->submitter', $time, 0);";

          $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][globallink_status INSERT SQL]", $sql);
          $db->query($sql);
        }
      }
    }
  }

  public function gl_get_all_completed_targets() {
    $gl_tgt_array = getReadyTranslationsDetailsFromPD();
    $gl_arr = array();
    $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Completed Targets Before]", $gl_tgt_array);
    foreach ($gl_tgt_array as &$gl_target) {
      $document_id = $gl_target->documentTicket;
      $submission_id = $gl_target->submissionTicket;
      $target_locale = $gl_target->targetLocale;
      $where = " WHERE document_id = '" . $document_id . "' AND submission_id ='" . $submission_id . "' AND gl_target_locale = '" . $target_locale . "'";
      $arr = $this->get_status_objects("*", $where, FALSE);
      $_arr_count = 0;
      foreach ($arr as $row) {
        $_arr_count++;
        $object_type = $row['object_type'];
        $object_id = $row['object_id'];
        $row_id = $row['globallink_job_id'];
        if ($row['status'] == $this->_get_submission_status_sent()) {
          $status_massage = $this->_get_submission_status_ready();
          $this->update_gl_status($row_id, $status_massage);
        }
        $source = $this->check_gl_translation_source($object_type, $object_id);
        if (!$source) {
          $status_massage = $this->_get_submission_status_deleted();
          $row['status'] = $status_massage;
          $this->update_gl_status($row_id, $status_massage);
          $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Status Update -]", $source);
        }
        $gl_target->glRowId = $row['globallink_job_id'];
        $gl_target->objectType = $row['object_type'];
        $gl_target->objectId = $row['object_id'];
        $gl_target->objectName = $row['object_name'];
        $gl_target->status = $row['status'];
        $gl_target->glUserId = $row['globallink_user_id'];
        $gl_target->glProjectId = $row['globallink_project_id'];
      }
      if ($_arr_count > 0) {
        $gl_arr[$gl_target->glRowId] = $gl_target;
      }
    }
    $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Completed Targets After]", $gl_arr);
    return $gl_arr;
  }

  function delete_and_update_attribute_set_config($attr_set_id, $attr_arr) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "DELETE FROM  globallink_attribute_sets WHERE attribute_set_id = $attr_set_id;";
    $db->query($sql);
    foreach ($attr_arr as $attr_id => $attribute) {
      $attr_code = $db->quote($attribute['attribute']['attribute_code']);
      $translatable = $attribute['translatable'];
      $sql = "INSERT INTO globallink_attribute_sets VALUES ($attr_set_id, $attr_id, $attr_code, $translatable);";
      $db->query($sql);
    }
  }

  function get_attribute_set_config($attr_set_id = "") {
    $where = "";
    if ($attr_set_id != "") {
      $where = "WHERE attribute_set_id = " . $attr_set_id . " ";
    }
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_attribute_sets " . $where . ";";
    $arr = array();
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if ($row['include_in_translation'] == 1) {
          $arr[$row['attribute_set_id']][$row['attribute_id']] = $row['include_in_translation'];
        }
      }
    }
    return $arr;
  }

  function get_attribute_set_code($attr_set_id = "") {
    $where = "";
    if ($attr_set_id != "") {
      $where = "WHERE attribute_set_id = " . $attr_set_id . " ";
    }
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_attribute_sets " . $where . ";";
    $arr = array();
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if ($row['include_in_translation'] == 1) {
          $arr[] = $row['attribute_code'];
        }
      }
    }
    return $arr;
  }

  function get_object_attributes($object_type) {
    $where = "WHERE object_type = '" . $object_type . "'";
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_field " . $where . ";";
    $arr = array();
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if ($row['include_in_translation'] == 1) {
          $arr[] = $row['field_code'];
        }
      }
    }
    return $arr;
  }

  public function getIncludeIds($objectType) {
    $sql = "SELECT globallink_field_id FROM globallink_field WHERE object_type = '$objectType' AND include_in_translation = 1;";
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $arr = array();
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row['globallink_field_id'];
      }
    }

    return $arr;
  }

  public function getFieldIds($objectType) {
    $sql = "SELECT globallink_field_id FROM globallink_field WHERE object_type = '$objectType';";
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $arr = array();
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row['globallink_field_id'];
      }
    }

    return $arr;
  }

  public function getIncludeFields($object_type) {
    $sql = "SELECT field_code FROM globallink_field WHERE object_type = '$object_type' AND include_in_translation = 1;";
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $arr = array();
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row['field_code'];
      }
    }

    return $arr;
  }

  public function get_pd_user_projects() {
    if (Mage::getSingleton('adminhtml/session')->getData('gl_projects') != null) {
      return Mage::getSingleton('adminhtml/session')->getData('gl_projects');
    }
    else {
      $proj_arr = get_user_PD_projects();
      Mage::getSingleton('adminhtml/session')->setData('gl_projects', $proj_arr);
      return $proj_arr;
    }
  }

  public function cron_update_all_completed_records() {
    $success = TRUE;
    $count = 0;
    try {
      $translation = array();
      $gl_arr = $this->gl_get_all_completed_targets();
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Completed Targets]", $gl_arr);
      foreach ($gl_arr as $globallink) {
        $row_id = $globallink->glRowId;
        $target_ticket = $globallink->targetTicket;
        try {
          $translated_content = downloadTargetResource($target_ticket);
          $translation = array('row_id' => $row_id, 'target_locale' => $globallink->targetLocale,
              'target_ticket' => $target_ticket, 'translated_content' => $translated_content);
          $this->update_translated_record_in_magento($translation);
          $this->update_gl_status($row_id, 'Translation Complete');
          sendDownloadConfirmation($target_ticket);
          $count++;
        }
        catch (SoapFault $sf) {
          $success = FALSE;
          $this->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ": Globallink WebService Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $sf->getMessage());
          $this->update_gl_status($row_id, 'Sent for Translations');
        }
        catch (Exception $ex) {
          $success = FALSE;
          $this->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ": Globallink Generic Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $ex->getMessage());
          $this->update_gl_status($row_id, 'Sent for Translations');
        }
      }

      if ($count > 0) {
        $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "$count records successfully retrieved and imported");
        $message = $this->__($count . ' translated documents retrieved and imported into Magento.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
        if ($count != count($gl_arr)) {
          $warning = "There was some error while importing $count documents into Magento. Please check logs for more details.";
          Mage::getSingleton('adminhtml/session')->addWarning($warning);
        }
      }
      else {
        $message = $this->__($count . ' translated documents retrieved and imported into Magento.');
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
    }
    catch (SoapFault $sf) {
      $success = FALSE;
      $this->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink WebService Error - ' . $sf->getMessage());
    }
    catch (Exception $ex) {
      $success = FALSE;
      $this->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    return $success;
  }

  public function update_translated_record_in_magento($translation) {

    $gl_object = $this->parse_target_xml($translation['translated_content']);
    $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Translated Content]", $gl_object);
    $mapped_langs = $this->get_mapped_locales();
    $tgt_lang_store_id = $mapped_langs[$translation['target_locale']]['store_id'];
    $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Translation Store Id]", $tgt_lang_store_id);

    if ($gl_object['object_type'] == $this->_get_object_type_product()) {
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
      Mage::getSingleton('catalog/product_action')->updateAttributes(array($gl_object['object_id']), $gl_object['attributes'], intval($tgt_lang_store_id));
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Attributes saved successfully for Product Id]", $gl_object['object_id']);
    }
    elseif ($gl_object['object_type'] == $this->_get_object_type_product_attributes()) {
      $session = Mage::getSingleton('adminhtml/session');
      $attr_model = Mage::getModel('catalog/resource_eav_attribute');
      $attr_model->load($gl_object['object_id']);
      if (!$attr_model->getId()) {
        $warning = "Product attribute has been deleted. Cannot import translation.";
        Mage::getSingleton('adminhtml/session')->addWarning($warning);
        return FALSE;
      }
      $option_arr = array();
      $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attr_model->attribute_code);
      foreach ($attribute->getSource()->getAllOptions(false) as $option) {
        $option_arr[$option['value']] = $option['label'];
      }

      $product = Mage::getModel('catalog/product')->load();
      $attribute_title = $product->getResource()->getAttribute($attr_model->attribute_code)->getStoreLabels();
      $attribute_title[0] = $attr_model->frontend_label;
      $stores = Mage::app()->getStores();
      $attribute_arr = array();
      foreach ($stores as $store_key => $object) {
        $store_id = Mage::app()->getStore($store_key)->getId();
        $config = Mage::getModel('eav/config');
        $get_attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attr_model->attribute_code);
        $attribute_val = $get_attribute->setStoreId($store_id)->getSource()->getAllOptions();
        unset($attribute_val[0]);
        foreach ($attribute_val as $val) {
          $attribute_arr[$val['value']][$store_id] = $val['label'];
        }

        foreach ($option_arr as $key => $option) {
          $attribute_arr[$key][0] = $option;
        }
      }

      if (!empty($gl_object['attributes']['frontend_label'])) {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $delete_query = "DELETE FROM eav_attribute_label WHERE attribute_id = '{$gl_object['object_id']}' AND store_id = '{$tgt_lang_store_id}'";
        $db->query($delete_query);
        $data = Mage::helper('catalog')->stripTags($gl_object['attributes']['frontend_label']);
        $insert_query = "INSERT INTO eav_attribute_label (attribute_id, store_id, value) VALUES ('{$gl_object['object_id']}', '{$tgt_lang_store_id}', '{$data}')";
        $db->query($insert_query);
      }
      $data = array();
      $data['option'] = array('value' => $attribute_arr);
      $attributes = $gl_object['attributes'];
      foreach ($attributes as $key => $attribute) {
        if ($key != 'frontend_label') {
          if (!empty($option_arr[$key])) {
            $data['option']['value'][$key][$tgt_lang_store_id] = $attribute;
          }
        }
      }

      $attr_model->addData($data);
      $attr_model->save();
      // Clear translation cache because attribute labels are stored in translation
      //Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
      $session->setAttributeData(false);
    }
    elseif ($gl_object['object_type'] == $this->_get_object_type_page()) {
      $page_id = $gl_object['object_id'];
      $source_page = Mage::getModel('cms/page')->load($page_id);
      $identifier = $source_page->getIdentifier() . '-' . strtolower($translation['target_locale']);

      //Check if a page exist with same identifier
      $t_page = Mage::getModel('cms/page')->load($identifier, 'identifier');
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][T page]", $t_page);
      if (!is_null($t_page) && is_object($t_page)) {
        $t_page->delete();
      }

      $new_page = unserialize(serialize($source_page));
      $new_page->setId(NULL);
      $object_type = $this->_get_object_type_page();
      $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
      $new_page_arr = array();
      $new_page_arr['identifier'] = $identifier;

      if (isset($source_page['root_template']))
        $new_page_arr['root_template'] = $source_page['root_template'];

      foreach ($field_config_arr as $field) {
        if (isset($gl_object['attributes'][$field]))
          $new_page_arr[$field] = $gl_object['attributes'][$field];
      }

      $new_page->setData($new_page_arr);
      $new_page->setStores(array($tgt_lang_store_id));
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source Page]", $source_page);
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source Page]", $new_page);
      $new_page->save();
    }
    elseif ($gl_object['object_type'] == $this->_get_object_type_block()) {
      $block_id = $gl_object['object_id'];
      $source_block = Mage::getModel('cms/block')->load($block_id);
      $identifier = $source_block->getIdentifier() . '-' . strtolower($translation['target_locale']);
      $title = $source_block->getTitle() . ' - ' . $translation['target_locale'];

      //Check if a block exist with same identifier
      $t_block = Mage::getModel('cms/block')->load($identifier, 'identifier');
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][T block]", $t_block);
      if (!is_null($t_block) && is_object($t_block)) {
        $t_block->delete();
      }

      $new_block = unserialize(serialize($source_block));
      $new_block->setId(NULL);
      $object_type = $this->_get_object_type_block();
      $field_config_arr = Mage::helper("globallink")->getIncludeFields($object_type);
      $new_block_arr = array();
      $new_block_arr['identifier'] = $identifier;
      $new_block_arr['title'] = $title;

      foreach ($field_config_arr as $field) {
        if (isset($gl_object['attributes'][$field]))
          $new_block_arr[$field] = $gl_object['attributes'][$field];
      }

      $new_block->setData($new_block_arr);
      $new_block->setStores(array($tgt_lang_store_id));

      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source block]", $source_block);
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source block]", $new_block);
      $new_block->save();
    }
    elseif ($gl_object['object_type'] == $this->_get_object_type_category()) {
      $category_id = $gl_object['object_id'];
      $source_category = Mage::getModel('catalog/category')->load($category_id);
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source category]", $source_category);

      $new_category = unserialize(serialize($source_category));
      if (isset($gl_object['attributes']['name']))
        $new_category->setName($gl_object['attributes']['name']);
      if (isset($gl_object['attributes']['description']))
        $new_category->setDescription($gl_object['attributes']['description']);
      if (isset($gl_object['attributes']['meta_title']))
        $new_category->setMetaTitle($gl_object['attributes']['meta_title']);
      if (isset($gl_object['attributes']['meta_keywords']))
        $new_category->setMetaKeywords($gl_object['attributes']['meta_keywords']);
      if (isset($gl_object['attributes']['meta_description']))
        $new_category->setMetaDescription($gl_object['attributes']['meta_description']);

      $new_category->setStoreId($tgt_lang_store_id);

      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source category]", $new_category);
      $new_category->save();
      unset($new_category);
    }
    elseif ($gl_object['object_type'] == $this->_get_object_type_email()) {
      $template_id = $gl_object['object_id'];
      $source_template = Mage::getModel('adminhtml/email_template')->load($template_id);
      $code = $source_template->getTemplateCode() . '-' . strtolower($translation['target_locale']);

      $new_template = unserialize(serialize($source_template));

      //Check if a template exist with same identifier
      $t_template = Mage::getModel('adminhtml/email_template')->load($code, 'template_code');
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][T template]", $t_template);
      if (!is_null($t_template) && is_object($t_template)) {
        $t_template->delete();
      }
      else {
        $new_template->setAddedAt(NULL);
      }

      $new_template->setId(NULL);
      $new_template->setTemplateCode($code);
      if (isset($gl_object['attributes']['template_subject']))
        $new_template->setTemplateSubject($gl_object['attributes']['template_subject']);
      if (isset($gl_object['attributes']['template_text']))
        $new_template->setTemplateText($gl_object['attributes']['template_text']);

      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source template]", $source_template);
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source template]", $new_template);
      $new_template->save();
    }
    elseif ($gl_object['object_type'] == $this->_get_object_type_newsletter()) {
      $newsletter_id = $gl_object['object_id'];
      $source_newsletter = Mage::getModel('newsletter/template')->load($newsletter_id);
      $code = $source_newsletter->getTemplateCode() . '-' . strtolower($translation['target_locale']);

      $new_newsletter = unserialize(serialize($source_newsletter));

      //Check if a newsletter exist with same identifier
      $t_newsletter = Mage::getModel('newsletter/template')->load($code, 'template_code');
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][T newsletter]", $t_newsletter);
      if (!is_null($t_newsletter) && is_object($t_newsletter)) {
        $t_newsletter->delete();
      }
      else {
        $now = $lastDate = date('yyyy-mm-dd H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $new_newsletter->setAddedAt($now);
      }

      $new_newsletter->setId(NULL);
      $new_newsletter->setTemplateCode($code);
      if (isset($gl_object['attributes']['template_subject']))
        $new_newsletter->setTemplateSubject($gl_object['attributes']['template_subject']);
      if (isset($gl_object['attributes']['template_text']))
        $new_newsletter->setTemplateText($gl_object['attributes']['template_text']);

      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source newsletter]", $source_newsletter);
      $this->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Source newsletter]", $new_newsletter);
      $new_newsletter->save();
    }
    
    return TRUE;
  }

  public function update_gl_status($row_id, $status) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "UPDATE globallink_status SET status = '$status' WHERE globallink_job_id = '$row_id';";
    $db->query($sql);
  }

  public function update_gl_status_changed($object_type, $object_id) {
    $last_modified = time();
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "UPDATE globallink_status SET last_modified = '$last_modified', changed = 1 WHERE object_type = '$object_type' AND object_id = '$object_id';";
    $db->query($sql);
  }

  public function check_gl_translation_source($object_type, $object_id) {
    if ($object_type == $this->_get_object_type_product() || $object_type == $this->_get_object_type_page() ||
            $object_type == $this->_get_object_type_block() || $object_type == $this->_get_object_type_category()) {
      $source_product = Mage::getModel($object_type)->load($object_id);
      if ($source_product->getId()) {
        return TRUE;
      }
    }
    elseif ($object_type == $this->_get_object_type_email() || $object_type == $this->_get_object_type_newsletter()) {
      $source_template = Mage::getModel($object_type)->load($object_id);
      if ($source_template->getTemplateId()) {
        return TRUE;
      }
    }
    elseif ($object_type == $this->_get_object_type_product_attributes()) {
      $source_attribute = Mage::getModel('catalog/resource_eav_attribute')->load($object_id);
      if ($source_attribute->getId()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function get_gl_object_id_status($store_id, $object_id, $object_type) {
    $store_id_what = "gl_locale_code";
    $store_id_where = "WHERE store_id = $store_id";
    $store_id_results = $this->get_gl_locale_codes($store_id_what, $store_id_where);
    $store_id_local = $store_id_results[0];

    $mapped_locale_where = "WHERE store_id <> $store_id AND store_id IS NOT NULL";
    $mapped_locale_results = $this->get_gl_locale_codes($store_id_what, $mapped_locale_where);

    $what = "COUNT(*)";
    $where = "WHERE object_type = '$object_type' AND object_id = '$object_id'
                 AND gl_source_locale = '$store_id_local' AND gl_target_locale IN ('" . implode("','", $mapped_locale_results) . "')";
    $locale_count = $this->get_status_objects($what, $where, FALSE);

    if ($locale_count[0]['COUNT(*)'] == count($mapped_locale_results)) {
      $where = "WHERE object_type = '$object_type' AND object_id = '$object_id'
             AND gl_source_locale = '$store_id_local' AND changed = 1";
      $changed_count = $this->get_status_objects($what, $where, FALSE);

      if ($changed_count[0]['COUNT(*)'] == 0) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function insert_cron_schedule($status) {
    $timecreated = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
    $timescheduled = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i") + 5, date("s"), date("m"), date("d"), date("Y")));
    $jobCode = $this->get_cron_job_code();
    try {
      $schedule = Mage::getModel('cron/schedule');
      $schedule->setJobCode($jobCode)
              ->setCreatedAt($timecreated)
              ->setScheduledAt($timescheduled)
              ->setStatus($status)
              ->save();
    }
    catch (Exception $e) {
      throw new Exception(Mage::helper('cron')->__('Unable to save Cron expression'));
    }
  }

  public function save_settings($settings_arr) {
    try {
      foreach ($settings_arr as $key => $settings) {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE globallink_settings SET gl_setting_info = '" . serialize($settings) . "'WHERE gl_setting_name = '" . $key . "';";
        $db->query($sql);
      }
    }
    catch (Exception $e) {
      throw new Exception(Mage::helper('cron')->__('Unable to save settings'));
    }
  }

  function get_gl_settings() {
    $gl_setting = array();
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT * FROM globallink_settings;";
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $gl_setting[$row['gl_setting_name']] = unserialize($row['gl_setting_info']);
      }
    }
    return $gl_setting;
  }

  public function setting_enabled($setting) {
    $settings_arr = $this->get_gl_settings();
    return $settings_arr[$setting];
  }

  public function update_gl_submission_ticket_status($submission_id, $status) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "UPDATE globallink_status SET status = '$status' WHERE submission_id = '$submission_id';";
    $db->query($sql);
  }

  public function get_submission_status() {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT DISTINCT submission_id FROM globallink_status WHERE status in ('" . $this->_get_submission_status_sent() . "',
            '" . $this->_get_submission_status_error() . "','" . $this->_get_submission_status_ready() . "');";
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if ($row['submission_id']) {
          try {
            $status = get_status($row['submission_id']);
            if (!$status || $status == 'CANCELLED') {
              $this->update_gl_submission_ticket_status($row['submission_id'], $this->_get_submission_status_cancelled());
            }
          }
          catch (SoapFault $sf) {
            $this->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ": Globallink WebService Error submission has been deleted ", $sf->getMessage());
            $this->update_gl_submission_ticket_status($row['submission_id'], $this->_get_submission_status_cancelled());
          }
          catch (Exception $ex) {
            $this->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ": Globallink Generic Error submission has been deleted", $ex->getMessage());
            $this->update_gl_submission_ticket_status($row['submission_id'], $this->_get_submission_status_cancelled());
          }
        }
      }
    }
  }

  public function clear_cancelled_submissions() {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $sql = "SELECT DISTINCT submission_id FROM globallink_status WHERE status = '" . $this->_get_submission_status_cancelled() . "';";
    $results = $db->query($sql);
    if ($results) {
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        if ($row['submission_id']) {
          $this->update_gl_submission_ticket_status($row['submission_id'], '');
        }
      }
    }
  }
}