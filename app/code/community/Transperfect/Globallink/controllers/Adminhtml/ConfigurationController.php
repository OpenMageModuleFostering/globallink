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

require_once 'gl_ws/gl_ws_common.php';

class Transperfect_Globallink_Adminhtml_ConfigurationController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('globallink/adminhtml/configuration.phtml'));
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function postAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $globallink_user_id = isset($post['globallink_user_id']) ? $post['globallink_user_id'] : 0;
      $globallink_project_id = isset($post['globallink_project_id']) ? $post['globallink_project_id'] : 0;
      $globallink_url = isset($post['globallink_url']) ? $post['globallink_url'] : "";
      $project_short_code = isset($post['project_short_code']) ? $post['project_short_code'] : "";
      $classifier = isset($post['classifier']) ? $post['classifier'] : "";
      $mime_type = isset($post['mime_type']) ? $post['mime_type'] : "";
      $files_per_submission = isset($post['files_per_submission']) ? $post['files_per_submission'] : 100;
      $max_target_count = isset($post['max_target_count']) ? $post['max_target_count'] : 10;
      $globallink_user_name = isset($post['globallink_user_name']) ? $post['globallink_user_name'] : "";
      $globallink_user_password = isset($post['globallink_user_password']) ? $post['globallink_user_password'] : "";

      $message = test_GlobalLink_connectivity($globallink_user_name, $globallink_user_password, $globallink_url, $project_short_code, $classifier);

      $db = Mage::getSingleton('core/resource')->getConnection('core_write');

      if ($globallink_project_id == 0) {
        $sql = "INSERT INTO globallink_projects (globallink_url, project_short_code, classifier, mime_type, files_per_submission, max_target_count)
         VALUES ( '" . $globallink_url . "', '" . $project_short_code . "', '" . $classifier . "', '" . $mime_type . "', " . $files_per_submission . ", " . $max_target_count . ");";
        $db->query($sql);
      }
      else {
        $sql = "UPDATE globallink_projects SET globallink_url = '" . $globallink_url . "',
        project_short_code = '" . $project_short_code . "', classifier = '" . $classifier . "',
          mime_type = '" . $mime_type . "', files_per_submission = " . $files_per_submission . ", max_target_count = " . $max_target_count . "
            WHERE globallink_project_id = " . $globallink_project_id . ";";
        $db->query($sql);
      }

      if ($globallink_user_id == 0) {
        $sql = "INSERT INTO globallink_users (globallink_user_name, globallink_user_password) VALUES ( '" . $globallink_user_name . "', '" . Mage::helper("globallink")->encrypt_gl_pw($globallink_user_name, $globallink_user_password) . "');";
        $db->query($sql);
      }
      else {
        $sql = "UPDATE globallink_users SET globallink_user_name = '" . $globallink_user_name . "',
        globallink_user_password = '" . Mage::helper("globallink")->encrypt_gl_pw($globallink_user_name, $globallink_user_password) .
                "' WHERE globallink_user_id = " . $globallink_user_id . ";";
        $db->query($sql);
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
      if (!isset($message) || $message == '') {
        $message = $this->__('Settings saved successfully.');
      }
      if (strstr($message, 'Fail') != FALSE) {
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
      else {
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
      }
    }

    Mage::getSingleton('adminhtml/session')->unsetData('gl_projects');
    $this->_redirect('*/*');
  }

  public function settingsAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $settings_arr = array();
      $settings_arr['gl_logging'] = isset($post['globallink_adaptor_logging']) ? $post['globallink_adaptor_logging'] : 0;
      $settings_arr['gl_cron'] = isset($post['globallink_adaptor_cron']) ? $post['globallink_adaptor_cron'] : 0;

      Mage::helper("globallink")->save_settings($settings_arr);
    }
    catch (Exception $e) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($e->getFile() . ' - Line ' . $e->getLine() . ': Globallink Generic Error = ', $e->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $e->getMessage());
    }

    if ($success) {
      $message = $this->__('Settings saved successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }

  public function downloadAction() {
    $file = Mage::getBaseDir('base') . '\var\log\globallink.log';
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Downloading GlobalLink Log]", $file);
    if (file_exists($file)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename=' . basename($file));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      ob_clean();
      flush();
      readfile($file);
    }
    $this->_redirect('*/*');
  }

}