<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * LijuErrorHandler API Test Case
 * @group headless
 */
class api_v3_LijuErrorHandlerTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  /**
   * Set up for headless tests.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    $table = CRM_Core_DAO_AllCoreTables::getTableForEntityName('LijuErrorHandler');
    $this->assertTrue($table && CRM_Core_DAO::checkTableExists($table), 'There was a problem with extension installation. Table for ' . 'LijuErrorHandler' . ' not found.');
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testCreateGetDelete() {
    // Boilerplate entity has one data field -- 'contact_id'.
    // Put some data in, read it back out, and delete it.

    $created = $this->callAPISuccess('LijuErrorHandler', 'create', [
      'contact_id' => 1,
      'email' => 'admin@unittest.org',
      'email_id' => 123,
      'landesverband' => 'BB',
      'group_id' => 1,
    ]);
    $this->assertTrue(is_numeric($created['id']));

    $get = $this->callAPISuccess('LijuErrorHandler', 'get', []);
    $this->assertEquals(1, $get['count']);
    $this->assertEquals(1, $get['values'][$created['id']]['contact_id']);

    $this->callAPISuccess('LijuErrorHandler', 'delete', [
      'id' => $created['id'],
    ]);
  }

}
