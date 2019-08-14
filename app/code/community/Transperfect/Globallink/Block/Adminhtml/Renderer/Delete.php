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

class Transperfect_Globallink_Block_Adminhtml_Renderer_Delete extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

  public function render(Varien_Object $row) {
    $field_id = $row['globallink_field_id'];
    $user_submitted = $row['user_submitted'];
    if ($user_submitted == 1) {
      $url = Mage::helper("adminhtml")->getUrl("globallink/adminhtml_glfield/delete", array("id" => $field_id, 'object_type' => $this->getRequest()->getParam('selected_object_type')));
      $delete_link = '<p><a href="' . $url . '">Delete</a></p>';
      return $delete_link;
    }
  }

}