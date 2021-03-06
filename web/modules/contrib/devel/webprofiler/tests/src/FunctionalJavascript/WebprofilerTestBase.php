<?php

namespace Drupal\Tests\webprofiler\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use PHPUnit_Framework_AssertionFailedError;
use Drupal\Tests\BrowserTestBase;

/**
 * Class WebprofilerTestBase.
 *
 * @group webprofiler
 */
abstract class WebprofilerTestBase extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Wait until the toolbar is present on page.
   */
  protected function waitForToolbar() {
    $session = $this->getSession();
    $token = $this->getToken();
    $page = $session->getPage();

    $toolbar = $page->findById('webprofiler' . $token);
    $this->assertTrue($toolbar->hasClass('sf-toolbar'), 'Toolbar loader is present in page');

    $session->wait(1000, 'null !== document.getElementById(\'sfToolbarMainContent-' . $token . '\')');

    return $token;
  }

  /**
   * Return the Webprofiler token.
   *
   * @return null|string
   *   The page token
   */
  protected function getToken() {
    //$token = $this->getSession()->getResponseHeader('X-Debug-Token'); // to be removed
    $token = $this->drupalGetHeader('X-Debug-Token');

    if (NULL === $token) {
      throw new PHPUnit_Framework_AssertionFailedError();
    }

    return $token;
  }

  /**
   * Login with a user that can see the toolbar.
   */
  protected function loginForToolbar() {
    $admin_user = $this->drupalCreateUser(
      [
        'view webprofiler toolbar',
      ]
    );
    $this->drupalLogin($admin_user);
  }

  /**
   * Login with a user that can see the toolbar and the dashboard.
   */
  protected function loginForDashboard() {
    $admin_user = $this->drupalCreateUser(
      [
        'view webprofiler toolbar',
        'access webprofiler',
      ]
    );
    $this->drupalLogin($admin_user);
  }

  /**
   * Flush cache.
   */
  protected function flushCache() {
    $module_handler = \Drupal::moduleHandler();
    $module_handler->invokeAll('cache_flush');
  }

}
