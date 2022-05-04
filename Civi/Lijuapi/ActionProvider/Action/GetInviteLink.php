<?php
/*-------------------------------------------------------+
| LinksJugend API                                        |
| Copyright (C) 2022 SYSTOPIA                            |
| Author: P.Batroff (batroff -at- systopia.de)           |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


namespace Civi\Lijuapi\ActionProvider\Action;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\FormProcessor\API\Exception;
use CRM_Lijuapi_ExtensionUtil as E;

class GetInviteLink extends AbstractAction
{

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * TODO: Do we need default configuration parameters for this action?
   * @return SpecificationBag specs
   */
  public function getConfigurationSpecification()
  {
    return new SpecificationBag([
      // required fields
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('email', 'String', E::ts('Email Address'), true),
    ]);
  }


  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag specs
   */
  public function getParameterSpecification()
  {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag specs
   */
  public function getOutputSpecification()
  {
    return new SpecificationBag([
      new Specification('liju_invite_link', 'String', E::ts('LiJu Invite Link'), false, null, null, null, false),
      new Specification('liju_member_id', 'Integer', E::ts('LiJu MemberId'), false, null, null, null, false),
      new Specification('error', 'String', E::ts('Error Message in case of failure'), false, null, null, null, false),
    ]);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
  {
    try {
      // get Landesverband for User
      $landesverband = \CRM_Lijuapi_Utils::get_lv($parameters->getParameter('contact_id'));

      // get link for User
      $result = civicrm_api3('Liju', 'createinvite', [
        'email' => $parameters->getParameter('email'),
        'liju_member_id' => $parameters->getParameter('contact_id'),
        'verband' => $landesverband,
      ]);
      $liju_link = $result['values']['invite_link'];

      // TODO add invite Link to custom field on Contact
      // Is this needed here, or will this be done via Formprocessor??
      // This will also be needed for the Email to the user.

      // Set link as return parameter
      $output->setParameter('liju_member_id', $parameters->getParameter('contact_id'));
      $output->setParameter('error', '');
      $output->setParameter('liju_invite_link', $liju_link);

    } catch (\Exception $ex) {
      $output->setParameter('liju_member_id', '');
      $output->setParameter('liju_invite_link', '');
      $output->setParameter('error', $ex->getMessage());
    }
  }
}
