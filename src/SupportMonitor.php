<?php

namespace Drupal\wdg_support_monitor;

use Drupal\Core\Utility\ProjectInfo;

final class SupportMonitor {

  /**
   * Name of our last run setting
   *
   * @access public
   */
  const LAST_RUN_KEY = 'wdg_support_monitor.last_run';

  /**
   * API Endpoint
   *
   * @var string
   */
  private $api_endpoint = 'http://localhost';

  /**
   * API Secret
   *
   * @var string
   */
  private $api_secret = '';

  /**
   * Site URL
   *
   * @var string
   */
  private $url = '';

  /**
   * Last run data
   *
   * @var array|null
   */
  private $last_run;

  /**
   * Project data
   *
   * @var array|null
   */
  private $project_data;

  /**
   * Singleton instance
   *
   * @access private
   */
  private static $_instance;

  /**
   * Singleton method
   *
   * @access public
   */
  public static function get_instance() {
    if ( ! isset( self::$_instance ) ) {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  /**
   * Constructor
   */
  private function __construct() {
    if ( defined( 'WDG_SUPPORT_MONITOR_API_ENDPOINT' ) && ! empty( WDG_SUPPORT_MONITOR_API_ENDPOINT ) ) {
      $this->api_endpoint = WDG_SUPPORT_MONITOR_API_ENDPOINT;
    }

    if ( defined( 'WDG_SUPPORT_MONITOR_API_SECRET' ) && ! empty( WDG_SUPPORT_MONITOR_API_SECRET ) ) {
      $this->api_secret = WDG_SUPPORT_MONITOR_API_SECRET;
    } else {
      $this->api_secret = hash( 'sha256', php_uname( 'n' ) );
    }

    $this->url = $GLOBALS['base_url'];
  }

  /**
   * Get module info
   *
   * @access public
   * @return object
   */
  public function info() {
    $data = new \StdClass;
    $data->api_endpoint = $this->api_endpoint;
    $data->api_secret = $this->api_secret;
    $data->url = $this->url;

    return $data;
  }

  /**
   * Get last run data
   *
   * @access public
   * @return array|null
   */
  public function get_last_run() {
    if ( ! empty( $this->last_run ) ) {
      return $this->last_run;
    }

    // Fetch variable
    $this->last_run = \Drupal::state()->get( self::LAST_RUN_KEY, NULL );

    return $this->last_run;
  }

  /**
   * Set last run data
   *
   * @access private
   * @param object $data
   */
  private function set_last_run( $data ) {
    $this->last_run = $data;

    // Update data
    \Drupal::state()->set( self::LAST_RUN_KEY, $this->last_run );
  }

  /**
   * Get Project Data
   * Note: this is very intensive and should not be done on the front-end
   *
   * @see \Drupal\Update\UpdateManager::getProjects() internal functions to generate project list with enabled and disabled modules
   *
   * @access private
   * @return array|false
   */
  private function get_project_data() {
    if ( ! empty( $this->project_data ) ) {
      return $this->project_data;
    }

    // @TODO!
    return FALSE;

    // Trigger update fetch
    // @FIXME
    $available = update_get_available( TRUE );
    if ( empty( $available ) ) {
      return FALSE;
    }

    // @see \Drupal::service('update.manager')->getProjects();
    $projects = array();
    $module_data = system_rebuild_module_data();
    $theme_data = $this->themeHandler->rebuildThemeData(); // @FIXME
    $project_info = new ProjectInfo();
    $project_info->processInfoList($this->projects, $module_data, 'module', TRUE);
    $project_info->processInfoList($this->projects, $module_data, 'module', FALSE);
    $project_info->processInfoList($this->projects, $theme_data, 'theme', TRUE);
    $project_info->processInfoList($this->projects, $theme_data, 'theme', FALSE);

    // @FIXME
    // @see update_calculate_project_data()
    update_process_project_info( $projects );
    foreach( $projects as $project => $project_info ) {
      if ( isset( $available[ $project ] ) ) {
        // Sets ['recommended'] key
        // @FIXME
        update_calculate_project_update_status( $project, $projects[ $project ], $available[ $project ] );
      } else {
        // Update unknown
      }
    }

    // Store project data
    $this->project_data = $projects;
    return $this->project_data;
  }

  /**
   * Compile core data
   *
   * @access private
   * @return object
   */
  private function compile_core() {
    $data = new \StdClass();
    $data->current = VERSION;

    $project_data = $this->get_project_data();
    $data->recommended = ! empty( $project_data['drupal']['recommended'] ) ? $project_data['drupal']['recommended'] : NULL;

    return $data;
  }

  /**
   * Compile addon data
   *
   * @access private
   * @return array
   */
  private function compile_addons() {
    $data = array();

    $project_data = $this->get_project_data();
    if ( empty( $project_data ) ) {
      return $data;
    }

    foreach( $project_data as $project_name => $project ) {
      if ( 'drupal' === $project_name ) continue; // Ignore core

      $addon = new \StdClass();
      $addon->name = $project_name;
      $addon->display = $project['info']['name'];
      $addon->type = preg_replace( '/-disabled$/', '', $project['project_type'] ); // Strip -disabled suffix
      $addon->current = $project['existing_version'];
      $addon->recommended = ! empty( $project['recommended'] ) ? $project['recommended'] : NULL;
      $addon->active = $project['project_status'];

      array_push( $data, $addon );
    }

    return $data;
  }

  /**
   * Compile report data
   *
   * @access private
   * @param int $timestamp Compile timestamp
   * @return object
   */
  private function compile( $timestamp ) {
    // Key is hash of site URL, secret, and timestamp
    $key = hash( 'sha256', $this->url . $this->api_secret . $timestamp );

    // Compile data
    $data = new \StdClass;
    $data->url = $this->url;
    $data->timestamp = $timestamp;
    $data->key = $key;
    $data->core = $this->compile_core();
    $data->addons = $this->compile_addons();

    return $data;
  }

  /**
   * Compile report
   *
   * @access public
   * @return object
   */
  public function report() {
    return $this->compile( REQUEST_TIME );
  }

  /**
   * Push update to WDG support
   *
   * @todo make real request
   * @access public
   * @param bool $blocking
   * @return object|string Last run object on success, string error message on failure
   */
  public function update( $blocking = false ) {

    flush(); // flush the output just in case we're on the front end

    if ( filter_var( $this->api_endpoint, FILTER_VALIDATE_URL ) === false ) {
      // Invalid API Endpoint
      return sprintf( 'Invalid API Endpoint! %s', $this->api_endpoint );
    }

    $data = $this->compile( REQUEST_TIME );

    $options = array(
      'body' => json_encode( $data ),
      'headers' => array(
        'Content-Type' => 'application/json'
      ),
    );

    $response = \Drupal::httpClient()->post( $this->api_endpoint, $options );

    // Store last run regardless of success
    $last_run = new \StdClass();
    $last_run->success = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    $last_run->timestamp = REQUEST_TIME;
    $last_run->request = new \StdClass();
    $last_run->request->url = $this->api_endpoint;
    $last_run->request->options = $options;
    $last_run->response = new \StdClass();
    $last_run->response->status = $response->getStatusCode();
    $last_run->response->headers = $response->getHeaders();
    $last_run->response->body = $response->getBody();
    $this->set_last_run( $last_run );

    if ( ! $last_run->success ) {
      // Failed!
      return sprintf( 'Unable to update! Response code: %s', $response->getStatusCode() );
    }

    return $last_run;
  }

}
