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
    $config = CRM_Core_Config::singleton();
    //Right now this config issue largely affects Wordpress only. This check could be applied to other CMS' but for now it's just WP
    if ($config->userFramework != 'WordPress') {

      return [];
    }
    $clean = $civicrm_setting['CIVICRM_CLEANURL'];
    if ($clean == 1) {
      //cleanURLs are enabled in CiviCRM, let's make sure the wordpress permalink settings and cache are actually correct by checking the first active contribution page
      $contributionPages = \Civi\Api4\ContributionPage::get(FALSE)
        ->addSelect('id')
        ->addWhere('is_active', '=', TRUE)
        ->setLimit(1)
        ->execute();
      if (count($contributionPages) > 0) {
        $activePageId = $contributionPages[0]['id'];
        $message = self::checkCleanPage('/contribute/transact/?reset=1&id=', $activePageId, $config);

        return $message;
      }
      else {
        //no active contribution pages, we can check an event page. This probably won't ever happen.
        $eventPages = \Civi\Api4\Event::get(FALSE)
          ->addSelect('id')
          ->addWhere('is_active', '=', TRUE)
          ->setLimit(1)
          ->execute();
        if (count($eventPages) > 0) {
          $activePageId = $eventPages[0]['id'];
          $message = self::checkCleanPage('/event/info/?reset=1&id=', $activePageId, $config);

          return $message;
        }
        else {
          //If there are no active event or contribution pages, we'll skip this check for now.

          return [];
        }
      }
    }
    else {
      //cleanURLs aren't enabled or aren't defined correctly in CiviCRM, admin should check civicrm.settings.php
      $warning = 'Clean URLs are not enabled correctly in CiviCRM. This can lead to "valid id" errors for users registering for events or making donations. Check civicrm.settings.php and review <a href="https://docs.civicrm.org/sysadmin/en/latest/integration/wordpress/clean-urls/">the documentation</a> for more information.';

      return [
        new CRM_Utils_Check_Message(
          __FUNCTION__,
          $warning,
          ts('Clean URLs Not Enabled'),
          \Psr\Log\LogLevel::WARNING,
          'fa-wordpress'
        ),
      ];
    }
  }

  private static function checkCleanPage($slug, $id, $config) {
    $client = new GuzzleHttp\Client();
    $res = $client->head($config->userFrameworkBaseURL . $config->wpBasePage . $slug . $id);
    $httpCode = $res->getStatusCode();

    if ($httpCode == 404) {
      $warning = 'Go to Settings > Permalinks and click "Save". Ideally this would be a button that just refreshes that cache';

      return [
        new CRM_Utils_Check_Message(
          __FUNCTION__,
          $warning,
          ts('Wordpress Permalinks cache needs to be refreshed.'),
          \Psr\Log\LogLevel::WARNING,
          'fa-wordpress'
          ),
      ];
    }

    //sanity
    return [];
  }

}
