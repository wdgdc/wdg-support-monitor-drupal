<?php
/**
 * @file Drush
 * @author Web Development Group (WDG) <https://www.webdevelopmentgroup.com>
 *
 * @code drush wdg-support-monitor-info
 * @code drush wdg-support-monitor-update
 * @code drush wdg-support-monitor-report
 */

use Drupal\wdg_support_monitor\SupportMonitor;

/**
 * Implements hook_drush_command().
 */
function wdg_support_monitor_drush_command() {
  $items = array(
    'wdg-support-monitor-info' => array(
      'description' => 'Output support monitor info',
      'options' => array(
        'format' => 'Choose "json" or "table". Default "json"',
      ),
      'drupal dependencies' => array('wdg_support_monitor'),
      'aliases' => array('supmon-info'),
    ),
    'wdg-support-monitor-update' => array(
      'description' => 'Push update data to WDG Support',
      'options' => array(
        'format' => 'Choose "json" or "table". Default "json"',
      ),
      'drupal dependencies' => array('wdg_support_monitor'),
      'aliases' => array('supmon-update'),
    ),
    'wdg-support-monitor-report' => array(
      'description' => 'Show update data',
      'options' => array(
        'format' => 'Choose "json" or "table". Default "json"',
      ),
      'drupal dependencies' => array('wdg_support_monitor'),
      'aliases' => array('supmon-report'),
    ),
  );
  return $items;
}

/**
 * Info
 *
 * @code drush wdg-support-monitor-info
 * @code drush supmon-info
 */
function drush_wdg_support_monitor_info() {
  $wdg_support_monitor = WDG_Support_Monitor::get_instance();

  // Info
  $info = $wdg_support_monitor->info();

  // Last Run
  $info->last_run = $wdg_support_monitor->get_last_run();

  // Output
  _drush_wdg_support_monitor_format( $info, drush_get_option('format', 'json') );

  drush_log( 'Done!', 'success' );
}

/**
 * Update
 *
 * @code drush wdg-support-monitor-update
 * @code drush supmon-update
 */
function drush_wdg_support_monitor_update() {
  drush_log( 'Updating...', 'ok' );

  $wdg_support_monitor = WDG_Support_Monitor::get_instance();

  $result = $wdg_support_monitor->update();

  if ( is_string( $result ) ) {
    drush_log( $result, 'error' );
    return;
  }

  // Output
  _drush_wdg_support_monitor_format( $result, drush_get_option('format', 'json') );

  drush_log( 'Done!', 'success' );
}

/**
 * Report
 *
 * @code drush wdg-support-monitor-report
 * @code drush supmon-report
 */
function drush_wdg_support_monitor_report() {
  drush_log( 'Generating report...', 'ok' );

  $wdg_support_monitor = WDG_Support_Monitor::get_instance();

  $data = $wdg_support_monitor->report();

  // Output
  _drush_wdg_support_monitor_format( $data, drush_get_option('format', 'json') );

  drush_log( 'Done!', 'success' );
}

/**
 * Format Data
 *
 * @param object $data
 * @param string $format
 */
function _drush_wdg_support_monitor_format( $data, $format ) {
  if ( ! in_array( $format, [ 'json', 'table' ] ) ) {
    drush_log( 'Unknown format!', 'error' );
    return;
  }

  switch ( $format ) {
    case 'json':
      drush_log( PHP_EOL . json_encode( $data, JSON_PRETTY_PRINT ), 'ok' );
      break;
    case 'table':
      $tables = array();
      foreach ( (array) $data as $key => $value ) {
        if ( ! is_scalar( $value ) ) {
          if ( is_array( $value ) || is_object( $value ) ) {
            $value = (array) $value;
            foreach ( $value as $key2 => $value2 ) {
              if ( ! is_scalar( $value2 ) ) {
                if ( is_array( $value2 ) || is_object( $value2 ) ) {
                  $tables[ $key2 ] = $value2;
                }
                unset( $value[ $key2 ] );
              }
            }
            if ( count( $value ) ) {
              $tables[ $key ] = $value;
            }
          }
          unset( $data->$key );
        }
      }

      // Top level data
      $table = drush_key_value_to_array_table( (array) $data );
      drush_print_table( $table );

      // Subdata tables
      foreach ( $tables as $key => $data ) {
        drush_log( PHP_EOL . $key, 'ok' );
        $table = drush_key_value_to_array_table( (array) $data );
        drush_print_table( $table );
      }

      break;
  }
}
