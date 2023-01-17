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
function _civicrm_api3_liju_Getusers_spec(&$spec)
{
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
 * @throws API_Exception
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_liju_Getusers($params)
{
  CRM_Lijuapi_Utils::log("Liju.getUsers " . json_encode($params));
  try {
    $api_interface = new CRM_Lijuapi_ApiInterface();
    $liju_users = $api_interface->get_users();
    return civicrm_api3_create_success(["liju_api_users" => $liju_users]);
  } catch (Exception $e) {
    // TODO: Do we need to notify here as well?
    CRM_Lijuapi_Utils::notify_error("Error occured in Liju.getUsers: " . $e->getMessage(), NULL, NULL, NULL);
    throw new API_Exception("Error Occured: {$e->getMessage()}");
  }
}
