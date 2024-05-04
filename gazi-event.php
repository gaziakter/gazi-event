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

        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add AJAX actions
        add_action('wp_ajax_get_events', array($this, 'get_events_callback' ) );
        add_action('wp_ajax_nopriv_get_events', array($this, 'get_events_callback' ) );

        add_action('wp_ajax_get_event_details', array($this, 'get_event_details_callback') );
        add_action('wp_ajax_nopriv_get_event_details', array($this, 'get_event_details_callback' ) );
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

    // Add admin menu
    public function add_admin_menu() {
        add_menu_page(
            'Event Calendar',
            'Event Calendar',
            'manage_options',
            'event-calendar',
            array($this, 'event_calendar_page'),
            'dashicons-calendar-alt',
            20
        );
    }

    // Enqueue scripts and styles
    public function enqueue_admin_scripts($hook) {
        if ($hook == 'toplevel_page_event-calendar') {
            wp_enqueue_style('gazi-styles', plugins_url('assets/css/admin-styles.css', __FILE__));
            wp_enqueue_script('gazi-script', plugins_url('assets/js/event-script.js', __FILE__), array('jquery'), null, true);
        }
    }
    

    // Event Calendar Page
    public function event_calendar_page() {
        // Generate calendar for current month
        $current_month = date('m');
        $current_year = date('Y');

        // Fetch events for the current month
        $args = array(
            'post_type' => 'events',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => array(date('Y-m-01', strtotime('first day of this month')), date('Y-m-t', strtotime('last day of this month'))),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );

        $events_query = new WP_Query($args);
        $events = $events_query->posts;

        // Render calendar
        include_once('includes/calendar.php'); // Create this file to generate the calendar

        // Render events
        include_once('includes/events.php'); // Create this file to display events on the calendar
    }

    function get_events_callback() {
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $month = isset($_POST['month']) ? intval($_POST['month']) : date('n');
    
        $args = array(
            'post_type' => 'events',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => array(date('Y-m-01', strtotime("$year-$month-01")), date('Y-m-t', strtotime("$year-$month-01"))),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );
    
        $events_query = new WP_Query($args);
        $events = $events_query->posts;
    
        ob_start();
        include('includes/events.php');
        $response = ob_get_clean();
    
        echo $response;
    
        wp_die();
    }
    
    function get_event_details_callback() {
        $event_date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    
        // Query to get events for the selected date
        $args = array(
            'post_type' => 'events',
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => $event_date,
                )
            )
        );
    
        $events_query = new WP_Query($args);
        $events = $events_query->posts;
    
        $event_details = '';
    
        // Generate event details HTML
        foreach ($events as $event) {
            $event_details .= '<p><strong>' . get_the_title($event->ID) . '</strong>: ' . get_post_meta($event->ID, 'event_date', true) . '</p>';
        }
    
        echo $event_details;
    
        wp_die();
    }
}

new Gazi_Event();
