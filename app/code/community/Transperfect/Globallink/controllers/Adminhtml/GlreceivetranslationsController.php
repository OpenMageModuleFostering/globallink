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
require_once 'gl_ws/gl_ws_receive_translations.php';

class Transperfect_Globallink_Adminhtml_GlreceivetranslationsController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function refreshAction() {
    sleep(2);
    $this->_redirect('*/*');
    return $this;
  }

  public function updateAction() {
    $success = TRUE;
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
        if ($result_arr[0]['status'] == Mage::helper("globallink")->_get_submission_status_ready() ||
                $result_arr[0]['status'] == Mage::helper("globallink")->_get_submission_status_error()) {
          try {
            $start_count++;
            $translated_content = downloadTargetResource($target_ticket);
            $translation = array('row_id' => $row_id, 'target_locale' => $globallink->targetLocale,
                'target_ticket' => $target_ticket, 'translated_content' => $translated_content);
            $status = Mage::helper("globallink")->update_translated_record_in_magento($translation);
            if ($status) {
              Mage::helper("globallink")->update_gl_status($row_id, 'Translation Complete');
              sendDownloadConfirmation($target_ticket);
              $end_count++;
            }
          }
          catch (SoapFault $sf) {
            // set status error here
            $success = FALSE;
            Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ": Globallink WebService Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $sf->getMessage());
            Mage::helper("globallink")->update_gl_status($row_id, Mage::helper("globallink")->_get_submission_status_error());
          }
          catch (Exception $ex) {
            $success = FALSE;
            Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ": Globallink Generic Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $ex->getMessage());
            Mage::helper("globallink")->update_gl_status($row_id, Mage::helper("globallink")->_get_submission_status_error());
          }
        }
        else {
          $what = 'object_name';
          $where = 'WHERE globallink_job_id = ' . $row_id;
          $result_arr = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
          $message = $this->__($result_arr[0]['object_name'] . ' - source is deleted.');
          Mage::getSingleton('adminhtml/session')->addError($message);
        }
      }

      if ($end_count > 0) {
        Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "$end_count records successfully retrieved and imported");
        $message = $this->__($end_count . ' translated documents retrieved and imported into Magento.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
        if ($start_count != $end_count) {
          $warning = "There was some error while importing $start_count documents into Magento. Please check logs for more details.";
          Mage::getSingleton('adminhtml/session')->addWarning($warning);
        }
      }
      else {
        $message = $this->__($end_count . ' translated documents retrieved and imported into Magento.');
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
    }
    catch (SoapFault $sf) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink WebService Error - ' . $sf->getMessage());
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    sleep(3);
    $this->_redirect('*/*');
    return $this;
  }

  public function selectAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $row_ids = $post['gl_status_ids'];
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);
      $gl_arr = Mage::helper("globallink")->gl_get_all_completed_targets();
      $completed_row_ids = array_keys($gl_arr);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]completed row ids", $completed_row_ids);
      $start_count = 0;
      $end_count = 0;
      // Select All Checkbox is set to on if checked
      if ($row_ids[0] == 'on') {
        unset($row_ids[0]);
      }
      foreach ($row_ids as $row_id) {
        if (array_search($row_id, $completed_row_ids) !== FALSE) {
          $glObj = $gl_arr[$row_id];
          $target_ticket = $glObj->targetTicket;
          $what = 'status';
          $where = 'WHERE globallink_job_id = ' . $row_id;
          $result_arr = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
          if ($result_arr[0]['status'] != Mage::helper("globallink")->_get_submission_status_deleted() ||
                $result_arr[0]['status'] == Mage::helper("globallink")->_get_submission_status_error()) {
            try {
              $start_count++;
              $translated_content = downloadTargetResource($target_ticket);
              $translated_arr = array('row_id' => $row_id, 'target_locale' => $glObj->targetLocale,
                  'target_ticket' => $target_ticket, 'translated_content' => $translated_content);
              Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Translations Array]", $translated_arr);
              $status = Mage::helper("globallink")->update_translated_record_in_magento($translated_arr);
              if ($status) {
                Mage::helper("globallink")->update_gl_status($row_id, 'Translation Complete');
                sendDownloadConfirmation($target_ticket);
                $end_count++;
              }
            }
            catch (SoapFault $sf) {
              $success = FALSE;
              Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ": Globallink WebService Error for [$glObj->glRowId][$glObj->objectType][$glObj->objectName]", $sf->getMessage());
              Mage::helper("globallink")->update_gl_status($row_id, Mage::helper("globallink")->_get_submission_status_error());
            }
            catch (Exception $ex) {
              $success = FALSE;
              Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ": Globallink Generic Error for [$glObj->glRowId][$glObj->objectType][$glObj->objectName]", $ex->getMessage());
              Mage::helper("globallink")->update_gl_status($row_id, Mage::helper("globallink")->_get_submission_status_error());
            }
          }
          else {
            $what = 'object_name';
            $where = 'WHERE globallink_job_id = ' . $row_id;
            $result_arr = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
            $message = $this->__($result_arr[0]['object_name'] . ' - source is deleted.');
            Mage::getSingleton('adminhtml/session')->addError($message);
          }
        }
      }

      if ($end_count > 0) {
        Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Successfully retrieved and imported");
        $message = $this->__($end_count . ' translated documents retrieved and imported into Magento.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
        if ($start_count != $end_count) {
          $warning = "There was some error while importing $start_count documents into Magento. Please check logs for more details.";
          Mage::getSingleton('adminhtml/session')->addWarning($warning);
        }
      }
      else {
        $message = $this->__($end_count . ' translated documents retrieved and imported into Magento.');
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
    }
    catch (SoapFault $sf) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink WebService Error - ' . $sf->getMessage());
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }
    sleep(3);
    $this->_redirect('*/*');
    return $this;
  }

  function clearAction() {
    $success = TRUE;
    try {
      $gl_arr = Mage::helper("globallink")->gl_get_all_completed_targets();
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Completed Targets]", $gl_arr);
      $count = 0;
      foreach ($gl_arr as $globallink) {
        $row_id = $globallink->glRowId;
        $target_ticket = $globallink->targetTicket;
        try {
          $where = " WHERE globallink_job_id = '" . $row_id . "'";
          $status_arr = Mage::helper("globallink")->get_status_objects("status", $where, FALSE);
          Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Status Array]", $status_arr);

          if ($status_arr[0]['status'] == Mage::helper("globallink")->_get_submission_status_deleted()) {
            downloadTargetResource($target_ticket);
            sendDownloadConfirmation($target_ticket);
            $count++;
          }
        }
        catch (SoapFault $sf) {
          $success = FALSE;
          Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ": Globallink WebService Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $sf->getMessage());
          Mage::helper("globallink")->update_gl_status($row_id, 'Sent for Translations');
        }
        catch (Exception $ex) {
          $success = FALSE;
          Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ": Globallink Generic Error for [$globallink->glRowId][$globallink->objectType][$globallink->objectName]", $ex->getMessage());
          Mage::helper("globallink")->update_gl_status($row_id, 'Sent for Translations');
        }
      }
    }
    catch (SoapFault $sf) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink WebService Error - ' . $sf->getMessage());
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Successfully Cleared Deleted Records");
      $message = $this->__('Successfully cleared ' . $count);
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    sleep(1);

    $this->_redirect('*/*');
    return $this;
  }

}