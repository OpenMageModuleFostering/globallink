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

class Transperfect_Globallink_Adminhtml_GlfieldController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function deleteAction() {
    $field_id = (int) $this->getRequest()->getParam('id');
    $selected_object_type = $this->getRequest()->getParam('object_type');
    try {
      if ($field_id) {
        $field = Mage::getModel('globallink/glfield')->load($field_id);
        $field->delete();
      }
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      $message = $this->__('Field successfully deleted.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }
    else {
      $message = $this->__('Field successfully deleted.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }
    // get object type
    $this->_redirect('*/*', array('_query' => array('selected_object_type' => $selected_object_type)));
  }

  public function updateAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $selected_row_ids = $post['gl_field_ids'];
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);
      $selected_object_type = $post['selected_object_type'];
      $all_row_ids = Mage::helper("globallink")->getFieldIds($selected_object_type);
      foreach ($selected_row_ids as $row_id) {
        // Select All Checkbox is set to on if checked
        if ($row_id == 'on') {
          continue;
        }
        $field = Mage::getModel('globallink/glfield')->load($row_id);
        $field->setData('include_in_translation', 1);
        $field->save();
        $key = array_search($row_id, $all_row_ids);
        if ($key !== FALSE) {
          unset($all_row_ids[$key]);
        }
      }

      if (is_array($all_row_ids) && count($all_row_ids) > 0) {
        Mage::helper("globallink")->gl_debug('all row ids', $all_row_ids);
        foreach ($all_row_ids as $id) {
          $field = Mage::getModel('globallink/glfield')->load($id);
          $field->setData('include_in_translation', 0);
          $field->save();
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
      $message = $this->__('Configurations saved successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*', array('_query' => array('selected_object_type' => $selected_object_type)));
  }

  public function insertAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);
      $selected_object_type = $post['selected_object_type'];
      $field_code = $post['field_code'];
      $field_name = $post['field_name'];
      $include_in_translation = $post['include_in_translation'];
      $where = "WHERE object_type = '$selected_object_type' AND field_code = '$field_code'";
      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
      $sql = " SELECT * FROM globallink_field $where ;";
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $sql);
      $data = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

      if (!$data) {
        $field = Mage::getModel('globallink/glfield');
        $field->setData('object_type', $selected_object_type);
        $field->setData('field_code', $field_code);
        $field->setData('field_name', $field_name);
        $field->setData('include_in_translation', $include_in_translation);
        $field->setData('user_submitted', 1);
        $field->save();
      }
      else {
        $success = FALSE;
      }
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      $message = $this->__('Configurations saved successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }
    else {
      $message = $this->__('Configuration field already exist.');
      Mage::getSingleton('adminhtml/session')->addError($message);
    }

    $this->_redirect('*/*', array('_query' => array('selected_object_type' => str_replace('%2F', '-', rawurlencode($selected_object_type)))));
  }
  
  
}