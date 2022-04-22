<?php
use CRM_Lijuapi_ExtensionUtil as E;

class CRM_Lijuapi_BAO_lijuErrorHandler extends CRM_Lijuapi_DAO_lijuErrorHandler {

  /**
   * Create a new LijuErrorHandler based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Lijuapi_DAO_lijuErrorHandler|NULL
   */
  public static function create($params) {
    $className = 'CRM_Lijuapi_DAO_lijuErrorHandler';
    $entityName = 'LijuErrorHandler';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Fetch non consumed items and return them as an array
   * use the newest non consumed item, ignore older ones
   *
   * TODO: do we need a cleanup routine here?
   * @return array
   */
  public static function get_non_consumed_items($contact_id=NULL) {
    $sql = "
          SELECT * FROM civicrm_lijuapi_errorhandler
          WHERE is_consumed IS NOT NULL;";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $return_values = [];
    while ($dao->fetch()) {
      // if we have a contact_id, filter for it
      if(!empty($contact_id)) {
        if($dao->contact_id == $contact_id) {
          $return_values[$dao->id] = $dao->toArray();
        }
      } else {
        $return_values[$dao->id] = $dao->toArray();
      }
    }

    $dao->free();
    return $return_values;
  }

}
