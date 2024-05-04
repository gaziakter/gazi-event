<?php
/*
 * Plugin Name:       Gazi Event
 * Plugin URI:        https://gaziakter.com/plugins/gazi-event/
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gazi Akter
 * Author URI:        https://gaziakter.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gazi-event
 * Domain Path:       /languages
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Gazi_Event {

    // Constructor
    public function __construct() {
        // Initialize plugin
        add_action('init', array($this, 'init'));

        // Load text domain for translation
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    // Initialize plugin
    public function init() {
        // Add hooks and filters here
        
    }

    // Load text domain for translation
    public function load_textdomain() {
        load_plugin_textdomain('plugin-template', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

}

new Gazi_Event();
