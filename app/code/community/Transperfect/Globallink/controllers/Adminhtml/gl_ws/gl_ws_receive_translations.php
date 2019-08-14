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

require_once 'GlobalLink.php';
require_once 'gl_ws_common.php';
require_once 'client/SessionService2.php';
require_once 'client/ProjectService2.php';
require_once 'client/TargetService2.php';

function getReadyTranslationsDetailsFromPD() {
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
  $targetService = new TargetService2($wsdl_path . 'TargetService2.wsdl', array(
              'location' => $url . "/services/TargetService2"
          ));

  $globalLink_arr = array();

  $token = @Login($sessionService, $username, $password);
  $projects = GetUserProjects($projectService, $token);
  $proj_ticket_arr = array();
  foreach ($projects as $project) {
    $short_code = $project->projectInfo->shortCode;
    foreach ($proj_arr as $conf_proj) {
      if ($conf_proj == $short_code) {
        $proj_ticket_arr[] = $project->ticket;
      }
    }
  }

  if (count($proj_ticket_arr) > 0) {
    $targets = getCompletedTargetsByProjects($targetService, $proj_ticket_arr, $token, $pdObject);

    $count = 1;
    if (is_array($targets)) {
      foreach ($targets as $target) {
        if (!is_null($target->ticket) && $target->ticket != '') {
          $globalLink = new GlobalLink;

//          $repositoryItem = @downloadTargetResource($targetService, $target->ticket, $token);
//          $targetContent = $repositoryItem->data->_;
//          $globalLink->targetXML = $targetContent;

          $globalLink->submissionTicket = $target->document->documentGroup->submission->ticket;
          $globalLink->submissionName = $target->document->documentGroup->submission->submissionInfo->name;
          $globalLink->documentTicket = $target->document->ticket;
          $globalLink->sourceFileName = $target->document->documentInfo->name;
          $globalLink->targetTicket = $target->ticket;
          $globalLink->sourceLocale = $target->sourceLanguage->locale;
          $globalLink->targetLocale = $target->targetLanguage->locale;
          $globalLink->priority = $target->targetInfo->priority->value;
          $globalLink->dueDate = $target->dueDate->date;

          $globalLink_arr[$count] = $globalLink;
          $count++;
        }
      }
    }
    elseif (!is_null($targets)) {
      $target = $targets;
      if (!is_null($target->ticket) && $target->ticket != '') {
        $globalLink = new GlobalLink;

//        $repositoryItem = @downloadTargetResource($targetService, $target->ticket, $token);
//        $targetContent = $repositoryItem->data->_;
//        $globalLink->targetXML = $targetContent;

        $globalLink->submissionTicket = $target->document->documentGroup->submission->ticket;
        $globalLink->submissionName = $target->document->documentGroup->submission->submissionInfo->name;
        $globalLink->documentTicket = $target->document->ticket;
        $globalLink->sourceFileName = $target->document->documentInfo->name;
        $globalLink->targetTicket = $target->ticket;
        $globalLink->sourceLocale = $target->sourceLanguage->locale;
        $globalLink->targetLocale = $target->targetLanguage->locale;
        $globalLink->priority = $target->targetInfo->priority->value;
        $globalLink->dueDate = $target->dueDate->date;

        $globalLink_arr[$count] = $globalLink;
      }
    }
  }
  return $globalLink_arr;
}

function getCompletedTargetsByProjects($targetService, $projectTickets, $token, $pdObject) {
  $getCompletedTargetsByProjectsRequest = new getCompletedTargetsByProjects;

  $getCompletedTargetsByProjectsRequest->projectTickets = $projectTickets;
  $getCompletedTargetsByProjectsRequest->maxResults = $pdObject->maxTargetCount;
  $getCompletedTargetsByProjectsRequest->userId = $token;

  $getCompletedTargetsByProjectsResponse = $targetService->getCompletedTargetsByProjects($getCompletedTargetsByProjectsRequest);
  return $getCompletedTargetsByProjectsResponse->return;
}

function downloadTargetResource($targetTicket) {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;

  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $targetService = new TargetService2($wsdl_path . 'TargetService2.wsdl', array(
              'location' => $url . "/services/TargetService2"
          ));
  $token = @Login($sessionService, $username, $password);

  $downloadTargetResourceRequest = new downloadTargetResource;

  $downloadTargetResourceRequest->targetId = $targetTicket;
  $downloadTargetResourceRequest->userId = $token;

  $downloadTargetResourceResponse = $targetService->downloadTargetResource($downloadTargetResourceRequest);
  $repositoryItem = $downloadTargetResourceResponse->return;
  $targetContent = $repositoryItem->data->_;

  return $targetContent;
}

function sendDownloadConfirmation($targetTicket) {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;

  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $targetService = new TargetService2($wsdl_path . 'TargetService2.wsdl', array(
              'location' => $url . "/services/TargetService2"
          ));
  $token = Login($sessionService, $username, $password);
  $sendDownloadConfirmationRequest = new sendDownloadConfirmation;

  $sendDownloadConfirmationRequest->targetId = $targetTicket;
  $sendDownloadConfirmationRequest->userId = $token;

  $sendDownloadConfirmationResponse = $targetService->sendDownloadConfirmation($sendDownloadConfirmationRequest);

  return $sendDownloadConfirmationResponse->return;
}