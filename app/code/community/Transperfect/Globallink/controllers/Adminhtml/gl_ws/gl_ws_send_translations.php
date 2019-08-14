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
require_once 'client/DocumentService2.php';
require_once 'client/SubmissionService2.php';

function sendDocumentsForTranslationToPD(&$globalLink_arr, $gl_project) {
  $pdObject = get_pd_details();
  $username = $pdObject->username;
  $password = $pdObject->password;
  $url = $pdObject->url;
  $projectShortCode = $gl_project;

  $wsdl_path = Mage::helper("globallink")->get_wsdl_path();

  $sessionService = new SessionService2($wsdl_path . 'SessionService2.wsdl', array(
              'location' => $url . "/services/SessionService2"
          ));
  $projectService = new ProjectService2($wsdl_path . 'ProjectService2.wsdl', array(
              'location' => $url . "/services/ProjectService2"
          ));
  $documentService = new DocumentService2($wsdl_path . 'DocumentService2.wsdl', array(
              'location' => $url . "/services/DocumentService2"
          ));
  $submissionService = new SubmissionService2($wsdl_path . 'SubmissionService2.wsdl', array(
              'location' => $url . "/services/SubmissionService2"
          ));

  $GLOBALS['g_submissionTicket'] = '';
  $GLOBALS['g_document_count'] = 0;
  $clientIdentifier = $projectShortCode . microtime();
  $submitter = Mage::getSingleton('admin/session')->getUser()->getUsername();

  $token = @Login($sessionService, $username, $password);
  $project = @FindProjectByShortCode($projectService, $projectShortCode, $token);
  $submissionInfo = createSubmissionInfo($project->ticket, $clientIdentifier, $submitter);

  $count = 0;
  foreach ($globalLink_arr as $globalLink) {
    $count++;
    $submissionInfo->name = $globalLink->submissionName;
    $dateRequested = new PDDate;
    $dateRequested->date = $globalLink->dueDate;
    $dateRequested->critical = false;
    $submissionInfo->dateRequested = $dateRequested;
    $submissionInfo->submitter = $globalLink->submitter;
    $resourceInfo = createResourceInfo($clientIdentifier, $pdObject, $globalLink);
    $documentInfo = createDocumentInfo($project->ticket, $globalLink, $clientIdentifier);
    if (!is_null($GLOBALS['g_submissionTicket']) && $GLOBALS['g_submissionTicket'] != '') {
      $documentInfo->submissionTicket = $GLOBALS['g_submissionTicket'];
    }
    //Submit a document for the given project
    submitDocumentWithBinaryResource($documentService, $resourceInfo, $documentInfo, $globalLink, $token);
    $GLOBALS['g_submissionTicket'] = $globalLink->submissionTicket;
    $GLOBALS['g_document_count'] = $count;

    $globalLink->glUserId = $pdObject->glUserId;
    $globalLink->glProjectId = $pdObject->glProjectId;
  }

  startSubmission($submissionService, $submissionInfo, $token);
}

function submitDocumentWithBinaryResource($documentService, $resourceInfo, $documentInfo, &$globalLink, $token) {

  $submitDocumentWithBinaryResourceRequest = new submitDocumentWithBinaryResource;

  $submitDocumentWithBinaryResourceRequest->documentInfo = $documentInfo;
  $submitDocumentWithBinaryResourceRequest->resourceInfo = $resourceInfo;
  $submitDocumentWithBinaryResourceRequest->data = $globalLink->sourceXML;
  $submitDocumentWithBinaryResourceRequest->userId = $token;

  $submitDocumentWithBinaryResourceResponse = $documentService->submitDocumentWithBinaryResource($submitDocumentWithBinaryResourceRequest);

  $globalLink->submissionTicket = $submitDocumentWithBinaryResourceResponse->return->submissionTicket;
  $globalLink->documentTicket = $submitDocumentWithBinaryResourceResponse->return->ticketId;
}

function startSubmission($submissionService, $submissionInfo, $token) {
  $startSubmissionRequest = new startSubmission;

  $startSubmissionRequest->submissionId = $GLOBALS['g_submissionTicket'];
  $startSubmissionRequest->submissionInfo = $submissionInfo;
  $startSubmissionRequest->userId = $token;

  $startSubmissionResponse = $submissionService->startSubmission($startSubmissionRequest);

  return $startSubmissionResponse->return;
}

function createSubmissionInfo($projectTicket, $clientIdentifier, $submitter) {
  $submissionInfo = new SubmissionInfo;
  $submissionInfo->clientIdentifier = $clientIdentifier;
  $submissionInfo->projectTicket = $projectTicket;
  $meta = new Metadata;
  $meta->magentoUser = $submitter;
  
  return $submissionInfo;
}

function createDocumentInfo($projectTicket, &$globalLink, $clientIdentifier) {
  $documentInfo = new DocumentInfo;

  $documentInfo->projectTicket = $projectTicket;
  $documentInfo->sourceLocale = $globalLink->sourceLocale;
  $documentInfo->name = $globalLink->sourceFileName;
  $documentInfo->clientIdentifier = $clientIdentifier;
  $dateRequested = new PDDate;
  $dateRequested->date = $globalLink->dueDate;
  $dateRequested->critical = false;
  $documentInfo->dateRequested = $dateRequested;
  $documentInfo->instructions = $globalLink->submissionInstructions;
  
  $targetInfos = array();
  foreach ($globalLink->targetLocale as $key => $value) {
    $targetInfos[$key] = createTargetInfo($value, $globalLink);
  }

  $documentInfo->targetInfos = $targetInfos;

  return $documentInfo;
}

function createTargetInfo($locale, &$globalLink) {
  $targetInfo = new TargetInfo;

  $targetInfo->targetLocale = $locale;
  $dateRequested = new PDDate;
  $dateRequested->date = $globalLink->dueDate;
  $dateRequested->critical = false;
  $targetInfo->dateRequested = $dateRequested;
  $targetInfo->requestedDueDate = $globalLink->dueDate;
  $priority = new Priority();
  $priority->value = isset($globalLink->priority) ? intval($globalLink->priority) : 1;
  $targetInfo->priority = $priority;
  $targetInfo->encoding = "UTF-8";

  return $targetInfo;
}

function createResourceInfo($clientIdentifier, &$pdObject, $globalLink) {
  $resourceInfo = new ResourceInfo;

  $resourceType = new ResourceType;
  $resourceType->value = 0;

  $resourceInfo->type = $resourceType;
  $resourceInfo->mimeType = "text/xml";
  $resourceInfo->classifier = $pdObject->classifier;
  $resourceInfo->name = $globalLink->sourceFileName;
  $resourceInfo->encoding = "UTF-8";
  $resourceInfo->size = strlen($globalLink->sourceXML);
  $resourceInfo->clientIdentifier = $clientIdentifier;
  $resourceInfo->resourceInfoId = $GLOBALS['g_document_count'];

  return $resourceInfo;
}