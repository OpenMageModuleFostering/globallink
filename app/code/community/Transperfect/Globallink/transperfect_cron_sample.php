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


/**
 * Move this file in magento root folder and rename to transperfect_cron.php.
 * Add a new cron job in the operating system cron and set the time interval as desired.
 * In the cron tab, execute the URL - http://magento_site_url/transperfect_cron.php
 * Also, for security add the following lines to the .htaccess file in your Magento install root folder.
 * <Files "transperfect_cron.php">
 *   Order deny,allow
 *   Allow from xxx.xxx.xxx.xxx
 *   Deny from all
 * </Files>
 */
require_once getcwd() . '/app/code/community/Transperfect/Globallink/Model/Observer.php';

$gl_cron_enabled = Mage::helper("globallink")->setting_enabled('gl_cron');
if ($gl_cron_enabled == 2) {
  Transperfect_Globallink_Model_Observer::update_translated_contents_automatically('transperfec_cron.php');
}