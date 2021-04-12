<?php

namespace Drupal\wdg_support_monitor\Commands;

use \Drush\Commands\DrushCommands;
use \Drupal\wdg_support_monitor\SupportMonitor;
use \Consolidation\OutputFormatters\StructuredData\PropertyList;


/**
 * A drush command file.
 *
 * @package Drupal\wdg_support_monitor\Commands
 */

class SupportMonitorCommands extends DrushCommands {

  /**
   * Output support monitor info.
   *
   * @command wdg-support-monitor:info
   * @aliases supmon:info
   * @option format
   *   Choose "json" or "table". Default "table"
   * @usage wdg-support-monitor:info
   */
  public function info( $options = ['format' => 'json'] ) {

    $wdg_support_monitor = SupportMonitor::get_instance();

    // Info
    $info = $wdg_support_monitor->info();

    // Last Run
    $info->last_run = $wdg_support_monitor->get_last_run();

    // Output
    return new PropertyList($info);

  }


  /**
   * Show update data.
   *
   * @command wdg-support-monitor:report
   * @aliases supmon:report
   * @option format
   *   Choose "json" or "table". Default "json"
   * @usage wdg-support-monitor:report
   */
  public function report( $options = ['format' => 'json'] ) {

    $wdg_support_monitor = SupportMonitor::get_instance();

    $data = $wdg_support_monitor->report();

    return new PropertyList($data);

  }


  /**
   * Push update data to WDG Support.
   *
   * @command wdg-support-monitor:update
   * @aliases supmon:update
   * @option format
   *   Choose "json" or "table". Default "json"
   * @usage wdg-support-monitor:update
   */
  public function update( $options = ['format' => 'json'] ) {

    $wdg_support_monitor = SupportMonitor::get_instance();

    $result = $wdg_support_monitor->update();

    return new PropertyList($result);

  }

}