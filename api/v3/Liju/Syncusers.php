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
  $spec['group_id']['api.required'] = 0;
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
  CRM_Lijuapi_Utils::log("Liju.syncusers " . json_encode($params));
  try {
    $group_id = null;
    if(!empty($params['group_id'])) {
      $group_id = $params['group_id'];
    }
    $user_sync = new CRM_Lijuapi_SyncUsers();
    $user_sync->run($group_id);
    return civicrm_api3_create_success("Liju API Member sync finished");
  } catch(Exception $e) {
    throw new API_Exception("Error Occured: {$e->getMessage()}");
  }
}
