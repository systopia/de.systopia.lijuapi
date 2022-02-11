<?php
use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Liju.Getusers API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_liju_Getusers_spec(&$spec) {
//  $spec['email']['api.required'] = 1;
}

/**
 * Liju.Getusers API
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
function civicrm_api3_liju_Getusers($params) {
  try {
    $api_interface = new CRM_Lijuapi_ApiInterface();
    $liju_users = $api_interface->get_users();
    return civicrm_api3_create_success(["liju_api_users" => $liju_users]);
  } catch (Exception $e) {
    throw new API_Exception("Error Occured: {$e->getMessage()}",'12345');
  }
}
