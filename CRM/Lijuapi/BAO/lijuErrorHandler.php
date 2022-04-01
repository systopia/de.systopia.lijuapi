<?php
use CRM_Lijuapi_ExtensionUtil as E;

class CRM_Lijuapi_BAO_lijuErrorHandler extends CRM_Lijuapi_DAO_lijuErrorHandler {

  /**
   * Create a new LijuErrorHandler based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Lijuapi_DAO_lijuErrorHandler|NULL
   *
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
  } */

}
