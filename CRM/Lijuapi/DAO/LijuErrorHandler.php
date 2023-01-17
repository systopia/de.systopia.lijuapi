<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from de.systopia.lijuapi/xml/schema/CRM/Lijuapi/lijuErrorHandler.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:f365ff3214d31cdd2f22f91c7057ca70)
 */

use CRM_Lijuapi_ExtensionUtil as E;

/**
 * Database access object for the LijuErrorHandler entity.
 */
class CRM_Lijuapi_DAO_LijuErrorHandler extends CRM_Core_DAO
{
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_lijuapi_errorhandler';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique LijuErrorHandler ID
   *
   * @var int
   */
  public $id;

  /**
   * FK to Contact
   *
   * @var int
   */
  public $contact_id;

  /**
   * Email that is to be changed
   *
   * @var string
   */
  public $email;

  /**
   * FK to CiviCRM-Email
   *
   * @var int
   */
  public $email_id;

  /**
   * True means the API call is succesfull executed
   *
   * @var bool
   */
  public $is_consumed;

  /**
   * Landesverband Abbrevation
   *
   * @var string
   */
  public $landesverband;

  /**
   * FK to CiviCRM-GroupID
   *
   * @var int
   */
  public $group_id;

  /**
   * Date the Change was attempted
   *
   * @var timestamp
   */
  public $timestamp;

  /**
   * Error Code from LiJu API
   *
   * @var string
   */
  public $errorcode;

  /**
   * Class constructor.
   */
  public function __construct()
  {
    $this->__table = 'civicrm_lijuapi_errorhandler';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE)
  {
    return $plural ? E::ts('Liju Error Handlers') : E::ts('Liju Error Handler');
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields()
  {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('Unique LijuErrorHandler ID'),
          'required' => TRUE,
          'where' => 'civicrm_lijuapi_errorhandler.id',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'add' => NULL,
        ],
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('FK to Contact'),
          'where' => 'civicrm_lijuapi_errorhandler.contact_id',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => NULL,
        ],
        'email' => [
          'name' => 'email',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Email'),
          'description' => E::ts('Email that is to be changed'),
          'required' => TRUE,
          'maxlength' => 256,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_lijuapi_errorhandler.email',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => '4.6',
        ],
        'email_id' => [
          'name' => 'email_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('FK to CiviCRM-Email'),
          'where' => 'civicrm_lijuapi_errorhandler.email_id',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => NULL,
        ],
        'is_consumed' => [
          'name' => 'is_consumed',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'description' => E::ts('True means the API call is succesfull executed'),
          'required' => TRUE,
          'where' => 'civicrm_lijuapi_errorhandler.is_consumed',
          'default' => '0',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => '4.6',
        ],
        'landesverband' => [
          'name' => 'landesverband',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Landesverband'),
          'description' => E::ts('Landesverband Abbrevation'),
          'required' => TRUE,
          'maxlength' => 4,
          'size' => CRM_Utils_Type::FOUR,
          'where' => 'civicrm_lijuapi_errorhandler.landesverband',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => '4.6',
        ],
        'group_id' => [
          'name' => 'group_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('FK to CiviCRM-GroupID'),
          'where' => 'civicrm_lijuapi_errorhandler.group_id',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => NULL,
        ],
        'timestamp' => [
          'name' => 'timestamp',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Timestamp'),
          'description' => E::ts('Date the Change was attempted'),
          'required' => TRUE,
          'where' => 'civicrm_lijuapi_errorhandler.timestamp',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => '4.6',
        ],
        'errorcode' => [
          'name' => 'errorcode',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Errorcode'),
          'description' => E::ts('Error Code from LiJu API'),
          'required' => TRUE,
          'maxlength' => 1024,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_lijuapi_errorhandler.errorcode',
          'table_name' => 'civicrm_lijuapi_errorhandler',
          'entity' => 'LijuErrorHandler',
          'bao' => 'CRM_Lijuapi_DAO_LijuErrorHandler',
          'localizable' => 0,
          'add' => '4.6',
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys()
  {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName()
  {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog()
  {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE)
  {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'lijuapi_errorhandler', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE)
  {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'lijuapi_errorhandler', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE)
  {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
