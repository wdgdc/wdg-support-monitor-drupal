<?php
/**
 * @code drush wdg-support-monitor-info
 * @code drush wdg-support-monitor-update
 * @code drush wdg-support-monitor-report
 */

use Drupal\wdg_support_monitor\WDG_Support_Monitor;

/**
 * Implements hook_drush_command().
 */
function wdg_support_monitor_drush_command() {
  $items = array(
    'wdg-support-monitor-info' => array(
      'description' => 'Output support monitor info',
      'drupal dependencies' => array('wdg_support_monitor'),
      'aliases' => array('supmon-info'),
    ),
    'wdg-support-monitor-update' => array(
      'description' => 'Push update data to WDG Support',
      'drupal dependencies' => array('wdg_support_monitor'),
      'aliases' => array('supmon-update'),
    ),
    'wdg-support-monitor-report' => array(
      'description' => 'Show update data',
      'drupal dependencies' => array('wdg_support_monitor'),
      'aliases' => array('supmon-report'),
    ),
  );
  return $items;
}

function drush_wdg_support_monitor_info() {
  // Info
  $info = WDG_Support_Monitor::get_instance()->info();
  $table = drush_key_value_to_array_table( (array) $info );
  drush_print_table( $table );

  // Last Run
  $last_run = WDG_Support_Monitor::get_instance()->get_last_run();
  drush_log( 'Last Run:' . "\n" . json_encode( $last_run, JSON_PRETTY_PRINT ), 'ok' );
  
  drush_log( 'Done!', 'success' );
}

function drush_wdg_support_monitor_update() {
  drush_log( 'Updating...', 'ok' );

  $result = WDG_Support_Monitor::get_instance()->update();

  if ( is_string( $result ) ) {
    drush_log( $result, 'error' );
    return;
  }

  drush_log( PHP_EOL . json_encode( $result, JSON_PRETTY_PRINT ), 'ok' );
  drush_log( 'Done!', 'success' );
}

function drush_wdg_support_monitor_report() {
  drush_log( 'Generating report...', 'ok' );

  $data = WDG_Support_Monitor::get_instance()->report();

  drush_log( PHP_EOL . json_encode( $data, JSON_PRETTY_PRINT ), 'ok' );
  drush_log( 'Done!', 'success' );
}