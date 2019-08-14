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

class Transperfect_Globallink_Adminhtml_LocaleconfigController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('globallink/adminhtml/localeconfig.phtml'));
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function editAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $mapped_locale_codes = $post['mapped_locale_code'];

      $db = Mage::getSingleton('core/resource')->getConnection('core_write');

      $sql = "SELECT * FROM globallink_status WHERE status = '" . Mage::helper("globallink")->_get_submission_status_sent() . "' AND
      (gl_source_locale IN ('" . implode("','", $mapped_locale_codes) . "') OR
      gl_target_locale IN ('" . implode("','", $mapped_locale_codes) . "'));";

      $rows = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

      if ($rows == "") {
        $sql = "UPDATE globallink_locale SET store_id = NULL,
        gl_default = 0 WHERE gl_locale_code IN ('" . implode("','", $mapped_locale_codes) . "');";
        $db->query($sql);
      }
      else {
        $success = FALSE;
        $message = $this->__('Mapping delete unsuccessful. Cannot delete source or target mapping for active submissions.');
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      $message = $this->__('Mapping deleted successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }

  public function postAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);

      $gl_locale = $post['gl_locale_code'];
      $store_id = $post['gl_store'];
      $gl_default = 0;
      
      $project_language_arr = get_project_language_directions();
      
      if (!array_key_exists($gl_locale, $project_language_arr)) {
        throw new Exception($gl_locale . ' is not configured in GlobalLink');
      }
      
      if (isset($post['gl_default'])) {
        $gl_default = $post['gl_default'];
      }

      if ($gl_default == 1) {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE globallink_locale SET gl_default = 0 WHERE gl_default = 1;";
        $db->query($sql);
      }

      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $sql = "UPDATE globallink_locale SET store_id = " . $store_id . ",
      gl_default = $gl_default WHERE gl_locale_code = '" . $gl_locale . "';";

      $db->query($sql);
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      $message = $this->__('Mapping saved successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }

  public function defaultAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $store_id = $post['gl_store'];

      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $sql = "UPDATE globallink_locale SET gl_default = 0 WHERE gl_default = 1;";
      $db->query($sql);

      $sql1 = "UPDATE globallink_locale SET gl_default = 1 WHERE store_id = $store_id;";
      $db->query($sql1);
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      $message = $this->__('Default store changed successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*');
  }

}