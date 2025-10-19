<?php

namespace Drupal\wdg_support_monitor\Commands;

use Drush\Commands\DrushCommands;
use Drupal\wdg_support_monitor\SupportMonitor;
use Consolidation\OutputFormatters\StructuredData\PropertyList;

/**
 * A Drush command file for the Support Monitor.
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
   *   Choose "json" or "table". Default "json"
   * @usage wdg-support-monitor:info
   */
  public function info($options = ['format' => 'json']) {
    // Get instance of the support monitor.

    $wdg_support_monitor = SupportMonitor::get_instance();

    // Retrieve the info and last run data.
    $info = $wdg_support_monitor->info();
    $info->last_run = $wdg_support_monitor->get_last_run();

    // Return output in specified format.
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
  public function report($options = ['format' => 'json']) {
    // Get instance of the support monitor.
    $wdg_support_monitor = SupportMonitor::get_instance();

    // Retrieve the report data.
    $data = $wdg_support_monitor->report();

    // Return output in specified format.
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
  public function update($options = ['format' => 'json']) {
    // Get instance of the support monitor.
    $wdg_support_monitor = SupportMonitor::get_instance();

    // Push the update and retrieve the result.
    $result = $wdg_support_monitor->update();

    // Return output in specified format.
    return new PropertyList($result);
  }
}
