<?php

use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Liju.Changelv API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_liju_Changelv_spec(&$spec)
{
  $spec['email']['api.required'] = 1;
  $spec['liju_member_id']['api.required'] = 1;
  $spec['new_lv']['api.required'] = 1;
}

/**
 * Liju.Changelv API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_liju_Changelv($params)
{
  CRM_Lijuapi_Utils::log("Liju.changelv " . json_encode($params));
  try {
    $api_interface = new CRM_Lijuapi_ApiInterface();
    $api_interface->change_lv($params['liju_member_id'], $params['email'], $params['new_lv']);
    return civicrm_api3_create_success(["Contact with LiJu MemberID ({$params['liju_member_id']}) updated to new LV" => $params['new_lv']]);
  } catch (Exception $e) {
    // send notification Message $error_message, $contact_email, $landesverband, $contact_id = NULL
    CRM_Lijuapi_Utils::notify_error("Error occured in Liju.changelv: " . $e->getMessage(), $params['email'], $params['new_lv'], $params['liju_member_id']);
    throw new API_Exception($e->getMessage());
  }
}
