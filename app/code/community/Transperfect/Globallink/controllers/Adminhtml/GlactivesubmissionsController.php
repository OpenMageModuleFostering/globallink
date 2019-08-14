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

class Transperfect_Globallink_Adminhtml_GlactivesubmissionsController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function selectAction() {
    sleep(6);
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);

      $selected_row_ids = $post['gl_status_ids'];
      if ($selected_row_ids[0] == 'on') {
        unset($selected_row_ids[0]);
      }
      $row_ids = implode(",", $selected_row_ids);

      $gl_sub_arr = Mage::helper("globallink")->get_status_objects("*", "WHERE globallink_job_id IN ( $row_ids)", FALSE);
      $globallink_arr = array();

      foreach ($gl_sub_arr as $row) {
        $globallink = new GlobalLink;
        $globallink->glRowId = $row['globallink_job_id'];
        $globallink->submissionTicket = $row['submission_id'];
        $globallink->documentTicket = $row['document_id'];
        array_push($globallink_arr, $globallink);
      }

      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $globallink_arr);
      cancel_select_PD_documents($globallink_arr);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $globallink_arr);
      $this->update_gl_row_status_after_cancellation($globallink_arr);
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
      $message = $this->__('Selected document(s) cancelled successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }

  public function cancelAction() {
    sleep(6);
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $selected_submission = urldecode($db->quote($post['selected_submission_name']));
      $what = " DISTINCT submission_id ";
      $status = Mage::helper("globallink")->_get_submission_status_sent() . "','" . Mage::helper("globallink")->_get_submission_status_ready()
              . "','" . Mage::helper("globallink")->_get_submission_status_error();
      $where = " WHERE submission_name = $selected_submission and status IN ('" . $status . "') ";
      $gl_sub_arr = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);

      $globallink = new GlobalLink;
      foreach ($gl_sub_arr as $row) {
        if ($row['submission_id'] != '') {
          $globallink->submissionTicket = $row['submission_id'];
        }
      }

      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $globallink);
      cancel_select_PD_submission($globallink);
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $globallink);
      $this->update_gl_submission_status_after_cancellation($globallink);
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
      $message = $this->__('Submission cancelled successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }
  
  public function clearCancelledAction() {
    $success = TRUE;
    try {
      Mage::helper("globallink")->clear_cancelled_submissions();
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
      $message = $this->__('Cleared cancelled submissions successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }

  function update_gl_submission_status_after_cancellation($globallink) {
    if ($globallink->cancelled == TRUE) {
      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $ticket = $db->quote($globallink->submissionTicket);
      $sql = "UPDATE globallink_status SET status = 'Cancelled', changed = 1 WHERE submission_id = $ticket;";
      $db->query($sql);
    }
  }

  function update_gl_row_status_after_cancellation($globallink_arr) {
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    foreach ($globallink_arr as $globallink) {
      if ($globallink->cancelled == TRUE) {
        $sql = "UPDATE globallink_status SET status = 'Cancelled', changed = 1 WHERE globallink_job_id = $globallink->glRowId;";
        $db->query($sql);
      }
    }
  }

}
