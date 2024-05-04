<?php
/*
 * Plugin Name:       Gazi Event
 * Plugin URI:        https://gaziakter.com/plugins/gazi-event/
 * Description:       Handle the basics event management with this plugin.
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
        // Load text domain for translation
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        //Custom post type for event
        add_action('init', array($this, 'events_custom_post_type'));

        // Event metabox
        add_action('add_meta_boxes', array($this, 'add_event_date_meta_box'));

        // Save meta data
        add_action('save_post', array($this, 'save_event_date_meta_box'));

        // Add custom column to events list
        add_filter('manage_events_posts_columns', array($this, 'add_event_date_column'));

        // Display event date in custom column
        add_action('manage_events_posts_custom_column', array($this, 'display_event_date_column'), 10, 2);

        // Reorder columns
        add_filter('manage_edit-events_columns', array($this, 'reorder_event_columns'));
    }

    // Load text domain for translation
    public function load_textdomain() {
        load_plugin_textdomain('gazi-event', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    //Event custom post type
    public function events_custom_post_type() {
        register_post_type('events', array(
            'labels' => array(
                'name' => __('Events', 'gazi-event'),
                'singular_name' => __('Event', 'gazi-event'),
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
        ));
    }

    // Add event date meta box
    public function add_event_date_meta_box() {
        add_meta_box(
            'event-date-meta-box',
            __('Event Date', 'gazi-event'),
            array($this, 'render_event_date_meta_box'),
            'events',
            'normal',
            'default'
        );
    }

    // Retrieve the current value of the 'event_date' meta field
    public function render_event_date_meta_box($post) {
        // Retrieve the current value of the 'event_date' meta field
        $event_date = get_post_meta($post->ID, 'event_date', true);

        wp_nonce_field('save_event_date', 'event_date_nonce');
        // Render HTML for event date input field
        ?>
        <label for="event-date"><?php _e('Event Date:', 'gazi-event'); ?></label>
        <input type="date" id="event-date" name="event_date" value="<?php echo esc_attr($event_date); ?>">
        <?php
    }

    // Save meta data
    public function save_event_date_meta_box($post_id) {

        // Check if nonce is set
        if (!isset($_POST['event_date_nonce']) || !wp_verify_nonce($_POST['event_date_nonce'], 'save_event_date')) {
            return;
        }

        if (isset($_POST['event_date'])) {
            update_post_meta($post_id, 'event_date', sanitize_text_field($_POST['event_date']));
        }
    }

    // Add "Event Date" column to events list
    public function add_event_date_column($columns) {
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            if ($key === 'title') {
                $new_columns['event_date'] = __('Event Date', 'gazi-event');
            }
        }

        return $new_columns;
    }

    // Display event date in custom column
    public function display_event_date_column($column, $post_id) {
        if ($column === 'event_date') {
            $event_date = get_post_meta($post_id, 'event_date', true);
            echo !empty($event_date) ? esc_html($event_date) : '-';
        }
    }

    // Reorder columns
    public function reorder_event_columns($columns) {
        $date_column = $columns['event_date'];
        unset($columns['event_date']);
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['event_date'] = $date_column;
            }
        }

        return $new_columns;
    }
}

new Gazi_Event();
