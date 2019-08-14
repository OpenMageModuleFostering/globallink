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
include_once 'app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
$gl_ws_path = Mage::getBaseDir('app') . "/code/community/Transperfect/Globallink/controllers/Adminhtml/gl_ws/";
require_once $gl_ws_path . 'GlobalLink.php';
require_once $gl_ws_path . 'gl_ws_receive_translations.php';

class Transperfect_Globallink_Model_Observer {

  public function __construct() {
    
  }

  public function product_update($observer) {
    $product = $observer->getEvent()->getProduct();
    $orig_data = $product->getOrigData();
    $new_data = $product->getData();
    $attr_id = $orig_data['attribute_set_id'];
    $attr_arr = Mage::helper("globallink")->get_attribute_set_code($attr_id);
    foreach ($attr_arr as $attr) {
      $orig_attr = $orig_data[$attr];
      $new_attr = $new_data[$attr];
      if ($new_attr != $orig_attr) {
        $object_type = Mage::helper("globallink")->_get_object_type_product();
        $object_id = $new_data['entity_id'];
        Mage::helper("globallink")->update_gl_status_changed($object_type, $object_id);
        return $this;
      }
    }

    return $this;
  }

  public function category_update($observer) {
    $category = $observer->getEvent()->getCategory();
    $orig_data = $category->getOrigData();
    $new_data = $category->getData();
    $object_type = Mage::helper("globallink")->_get_object_type_category();
    $attr_arr = Mage::helper("globallink")->get_object_attributes($object_type);
    foreach ($attr_arr as $attr) {
      $orig_attr = $orig_data[$attr];
      $new_attr = $new_data[$attr];
      if ($new_attr != $orig_attr) {
        $object_id = $new_data['entity_id'];
        Mage::helper("globallink")->update_gl_status_changed($object_type, $object_id);
        return $this;
      }
    }

    return $this;
  }
  
  public function run_cron() {
    $gl_cron_enabled = Mage::helper("globallink")->setting_enabled('gl_cron');
    if ($gl_cron_enabled == 1) {
      update_translated_contents_automatically('config.xml');
    }
  }

  public function update_translated_contents_automatically($cron_type) {
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]Cron started from ", $cron_type);
    $end_count = 0;
    $start_count = 0;
    try {
      $translation = array();
      $gl_arr = Mage::helper("globallink")->gl_get_all_completed_targets();
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Completed Targets]", $gl_arr);
      foreach ($gl_arr as $globallink) {
        $row_id = $globallink->glRowId;
        $target_ticket = $globallink->targetTicket;
        $what = 'status';
        $where = 'WHERE globallink_job_id = ' . $row_id;
        $result_arr = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
        if ($result_arr[0]['status'] == Mage::helper("globallink")->_get_submission_status_ready()) {
          try {
            $start_count++;
            $translated_content = downloadTargetResource($target_ticket);
            $translation = array('row_id' => $row_id, 'target_locale' => $globallink->targetLocale,
                'target_ticket' => $target_ticket, 'translated_content' => $translated_content);
            Mage::helper("globallink")->update_translated_record_in_magento($translation);
            Mage::helper("globallink")->update_gl_status($row_id, 'Translation Complete');
            sendDownloadConfirmation($target_ticket);
            $end_count++;
          }
          catch (SoapFault $sf) {
            Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ": Globallink WebService Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $sf->getMessage());
            Mage::helper("globallink")->update_gl_status($row_id, Mage::helper("globallink")->_get_submission_status_error());
            Mage::helper("globallink")->insert_cron_schedule(Mage::helper("globallink")->get_cron_status_error());
          }
          catch (Exception $ex) {
            Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ": Globallink Generic Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $ex->getMessage());
            Mage::helper("globallink")->update_gl_status($row_id, Mage::helper("globallink")->_get_submission_status_error());
            Mage::helper("globallink")->insert_cron_schedule(Mage::helper("globallink")->get_cron_status_error());
          }
        }
        else {
          $what = 'object_name';
          $where = 'WHERE globallink_job_id = ' . $row_id;
          $result_arr = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
        }
      }

      if ($end_count > 0) {
        Mage::helper("globallink")->insert_cron_schedule(Mage::helper("globallink")->get_cron_status_success());
        Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "$end_count records successfully retrieved and imported");
      }
    }
    catch (SoapFault $sf) {
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
    }
    catch (Exception $ex) {
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
    }
  }

}