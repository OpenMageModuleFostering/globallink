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

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Transperfect_Globallink_Model_Gltranslatecsv extends Mage_Core_Model_Translate {

  public function _construct() {
    parent::_construct();
    $this->_init('globallink/gltranslatecsv');
  }

  public function readCSVFile($locale = "en_US") {
    $arr = array();
    $fileName = 'Mage_Weee';
    $file = $this->_getAdvancedModuleFilePath($locale, $fileName);
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $file);
    $data = $this->_getFileData($file);
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $data);
    $parser = new Varien_File_Csv();
    $parser->setDelimiter(self::CSV_SEPARATOR);
    $new_data = array();
    foreach ($data as $key => $value) {
      $new_data[] = array($key, $value . '11112222');
    }
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $new_data);
    $parser->saveData($file, $new_data);
    return $arr;
  }

  protected function _getAdvancedModuleFilePath($locale, $fileName) {
    $file = Mage::getBaseDir('locale');
    $file.= DS . $locale . DS . $fileName . '.csv';
    return $file;
  }

}

?>
