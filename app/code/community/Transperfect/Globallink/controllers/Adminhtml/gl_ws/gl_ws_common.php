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

require_once 'ProjectDirector.php';
require_once 'client/SessionService2.php';
require_once 'client/ProjectService2.php';
require_once 'client/DocumentService2.php';
require_once 'client/SubmissionService2.php';

function get_user_PD_projects() {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;
  $projectShortCode = $pdObject->projectShortCode;
  $code_array = array();
  if ($projectShortCode != '') {
    $code_array = explode(',', $projectShortCode);
  }
  Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $code_array);
  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $projectService = new ProjectService2($wsdl_path . 'ProjectService2.wsdl', array(
              'location' => $url . "/services/ProjectService2"
          ));

  $token = @Login($sessionService, $username, $password);
  $projects = GetUserProjects($projectService, $token);

  $proj_arr = array();
  foreach ($projects as $project) {
    $short_code = $project->projectInfo->shortCode;
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $short_code);
    if (array_search($short_code, $code_array) !== false) {
      $proj_arr[$short_code] = $project->projectInfo->name;
    }
  }

  return $proj_arr;
}

function test_GlobalLink_connectivity($username, $password, $url, $projectShortCode, $classifier) {
  $proj_arr = array();
  if ($projectShortCode != '') {
    $arr = explode(',', $projectShortCode);
    foreach ($arr as $value) {
      $proj_arr[$value] = $value;
    }
  }
  
  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();
  
  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $projectService = new ProjectService2($wsdl_path . 'ProjectService2.wsdl', array(
              'location' => $url . "/services/ProjectService2"
          ));

  $success = FALSE;
  //Open a session on the server
  $token = @Login($sessionService, $username, $password);
  //Locate project by short code
  
  foreach ($proj_arr as $proj_code) {
    try {
      $project = @FindProjectByShortCode($projectService, $proj_code, $token);
    }
    catch (SoapFault $sf) {
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      return 'Connection Failed - Invalid Project Code: ' . $proj_code;
    }
    catch (Exception $ex) {
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      return 'Connection Failed - Invalid Project Code: ' . $proj_code;
    }
    if (isset($project->ticket)) {
      if (isset($project->fileFormatProfiles) && is_array($project->fileFormatProfiles)) {
        foreach ($project->fileFormatProfiles as $file_format) {
          if ($classifier == $file_format->profileName) {
            $success = TRUE;
            break;
          }
        }
      }
      elseif (isset($project->fileFormatProfiles)) {
        if ($classifier == $project->fileFormatProfiles->profileName) {
          $success = TRUE;
        }
      }
    }

    if (!$success) {
      throw new Exception('Connection Failed - Invalid Classifier.');
    }
    else {
      $success = FALSE;
    }
  }

  return 'Settings Saved and Connection Test Successful.';
}

function get_project_language_directions() {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;
  $projectShortCode = $pdObject->projectShortCode;
  $proj_arr = array();
  if ($projectShortCode != '') {
    $arr = explode(',', $projectShortCode);
    foreach ($arr as $value) {
      $proj_arr[$value] = $value;
    }
  }
  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();
  
  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $projectService = new ProjectService2($wsdl_path . 'ProjectService2.wsdl', array(
              'location' => $url . "/services/ProjectService2"
          ));
  
  $token = @Login($sessionService, $username, $password);
  
  foreach ($proj_arr as $proj_code) {
    try {
      $project = @FindProjectByShortCode($projectService, $proj_code, $token);
    }
    catch (SoapFault $sf) {
      Mage::helper("globallink")->gl_debug($sf->getFile() . ' - Line ' . $sf->getLine() . ': Globallink WebService Error = ', $sf->getMessage());
      return 'Connection Failed - Invalid Project Code: ' . $proj_code;
    }
    catch (Exception $ex) {
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      return 'Connection Failed - Invalid Project Code: ' . $proj_code;
    }
  }
  
  $project_languages = $project->projectLanguageDirections;
  $project_language_arr = array();
  foreach ($project_languages as $object) {
        $source_language_locale = $object->sourceLanguage->locale;
        $source_language_value = $object->sourceLanguage->value;
        $project_language_arr[$source_language_locale] = $source_language_value;
        $target_language_locale = $object->targetLanguage->locale;
        $target_language_value = $object->targetLanguage->value;
        $project_language_arr[$target_language_locale] = $target_language_value;
      }
  
  return $project_language_arr;
}

function cancel_select_PD_submission(&$globalLink) {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;

  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  //Create instances for each ws service stub class
  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $submissionService = new SubmissionService2($wsdl_path . 'SubmissionService2.wsdl', array(
              'location' => $url . "/services/SubmissionService2"
          ));

  $token = @Login($sessionService, $username, $password);

  $cancelSubmissionRequest = new cancelSubmission;
  $cancelSubmissionRequest->userId = $token;
  $cancelSubmissionRequest->submissionId = $globalLink->submissionTicket;

  $submissionService->cancelSubmission($cancelSubmissionRequest);

  $globalLink->cancelled = TRUE;
}

function cancel_select_PD_documents(&$globalLink_arr) {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;

  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  //Create instances for each ws service stub class
  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $documentService = new DocumentService2($wsdl_path . 'DocumentService2.wsdl', array(
              'location' => $url . "/services/DocumentService2"
          ));

  $token = @Login($sessionService, $username, $password);

  foreach ($globalLink_arr as $globalLink) {
    $documentTicket = new DocumentTicket();
    $documentTicket->submissionTicket = $globalLink->submissionTicket;
    $documentTicket->ticketId = $globalLink->documentTicket;

    $cancelDocumentRequest = new cancelDocument();
    $cancelDocumentRequest->documentTicket = $documentTicket;
    $cancelDocumentRequest->userId = $token;

    $documentService->cancelDocument($cancelDocumentRequest);

    $globalLink->cancelled = TRUE;
  }
}

function GetUserProjects($projectService, $token) {
  $getUserProjects = new getUserProjects;

  $getUserProjects->isSubProjectIncluded = FALSE;
  $getUserProjects->userId = $token;

  $getUserProjectsResponse = $projectService->getUserProjects($getUserProjects);
  $projects = $getUserProjectsResponse->return;

  if (!is_array($projects)) {
    $arr = array($projects);
    return $arr;
  }

  return $projects;
}

function FindProjectByShortCode($projectService, $projectShortCode, $token) {
  $findProjectByShortCode = new findProjectByShortCode;

  $findProjectByShortCode->projectShortCode = $projectShortCode;
  $findProjectByShortCode->userId = $token;

  $findProjectByShortCodeResponse = $projectService->findProjectByShortCode($findProjectByShortCode);

  $project = $findProjectByShortCodeResponse->return;

  return $project;
}

function Login($sessionService, $username, $password) {

  $login = new login;

  $login->username = $username;
  $login->password = $password;

  $loginResponse = $sessionService->login($login);
  
  $token = $loginResponse->return;

  return $token;
}

function get_pd_details() {
  $gl_config_arr = Mage::helper("globallink")->get_gl_configurations();
  $pd_object = new ProjectDirector;
  foreach ($gl_config_arr as $gl_config) {
    $r_url = strrev($gl_config['globallink_url']);
    if (ord($r_url) == 47) {
      $r_url = substr($r_url, 1);
    }
    $url = strrev($r_url);
    $pd_object->url = $url;
    $pd_object->projectShortCode = $gl_config['project_short_code'];
    $pd_object->classifier = $gl_config['classifier'];
    $pd_object->mimeType = $gl_config['mime_type'];
    $pd_object->maxTargetCount = $gl_config['max_target_count'];
    $pd_object->filesPerSubmission = $gl_config['files_per_submission'];
    $pd_object->username = $gl_config['globallink_user_name'];
    $pd_object->password = $gl_config['globallink_user_password'];
    $pd_object->glUserId = $gl_config['globallink_user_id'];
    $pd_object->glProjectId = $gl_config['globallink_project_id'];
  }
  return $pd_object;
}

function get_status($submissionTicket) {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;

  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $submissionService = new SubmissionService2($wsdl_path . 'SubmissionService2.wsdl', array(
              'location' => $url . "/services/SubmissionService2"
          ));

  $token = @Login($sessionService, $username, $password);

  $ticketRequest = new findByTicket();
  $ticketRequest->userId = $token;
  $ticketRequest->ticket = $submissionTicket;

  $findByTicketResponse = $submissionService->findByTicket($ticketRequest);

  $result = $findByTicketResponse->return;
  $status = '';

  if (isset($result->status))
    $status = $result->status->name;

  return $status;
}