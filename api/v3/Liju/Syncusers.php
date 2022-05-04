<?php
use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Liju.Syncusers API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_liju_Syncusers_spec(&$spec) {

}

/**
 * Liju.Syncusers API
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
function civicrm_api3_liju_Syncusers($params) {
  try {
    $user_sync = new CRM_Lijuapi_SyncUsers();
    $user_sync->run();
  } catch(Exception $e) {
    throw new API_Exception("Error Occured: {$e->getMessage()}");
  }
}
