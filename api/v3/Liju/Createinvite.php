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
  $spec['contact_id']['api.required'] = 0;
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
  try {
    $api_interface = new CRM_Lijuapi_ApiInterface();
    $invite_link = $api_interface->get_invite_link();
    return civicrm_api3_create_success(["liju_api_invite_link" => $invite_link]);
  } catch (Exception $e) {
    throw new API_Exception('Error Message','12345');
  }
}
