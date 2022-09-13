<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */
class CRM_Utils_Check_Component_Cleanurls extends CRM_Utils_Check_Component {

  /**
   * For sites running in WordPress, make sure clean URLs are properly set in settings file.
   *
   * @return CRM_Utils_Check_Message[]
   */
  public static function checkWpCleanurls() {
    return CRM_Core_Config::singleton()->userSystem->checkCleanurls();
  }

}
