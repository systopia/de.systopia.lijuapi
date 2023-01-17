<?php
use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Liju.Createinvite API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_liju_Createinvite_spec(&$spec) {
  $spec['email']['api.required'] = 1;
  $spec['liju_member_id']['api.required'] = 1;
  $spec['verband']['api.required'] = 1;
}

/**
 * Liju.Createinvite API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_liju_Createinvite($params) {
  CRM_Lijuapi_Utils::log("Liju.createinvite " . json_encode($params));
  try {
    $api_interface = new CRM_Lijuapi_ApiInterface();
    $invite_link = $api_interface->get_invite_link($params['email'], $params['liju_member_id'], $params['verband']);
    return civicrm_api3_create_success(["invite_link" => $invite_link]);
  } catch (Exception $e) {
    CRM_Lijuapi_Utils::notify_error("Error occured in Liju.createinvite: " . $e->getMessage(), $params['email'], $params['verband'], $params['liju_member_id']);
    throw new API_Exception($e->getMessage());
  }
}
