<?php
/**
 * Plugin Name: Test Form Plugin
 * Description: A custom plugin for testing skills
 * Version: 1.0.0
 * Author: Mkrtich Aleksanyan
 */

require_once 'constants.php';

class TestFormPlugin {
    public function __construct() {
        add_action('init', [&$this, 'tfp_init']);
        add_action('wp_enqueue_scripts', [&$this, 'tfp_enqueue_assets']);
        add_shortcode('tfp_form', [&$this, 'tfp_form_shortcode']);
        add_action('admin_notices', [&$this, 'show_admin_error_notice']);
        // Hook for logged-in users
        add_action('wp_ajax_custom_form_submission', [&$this, 'custom_form_submission']);
        // Hook for non-logged-in users
        add_action('wp_ajax_nopriv_custom_form_submission', [&$this, 'custom_form_submission']);
    }


    function add_admin_error_notice($message){
        set_transient('tfp_admin_error_notice', $message, 30);
    }

    /*
     * show warnings or errors in admin page
     * */
    function show_admin_error_notice(){
        if ($message = get_transient('tfp_admin_error_notice')) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';

            // Delete the transient after showing the message
            delete_transient('tfp_admin_error_notice');
        }
    }

    /*
     * function for init plugin
     * */
    function tfp_init(){
        $this->tfp_create_table();
    }

    /*
     * function for creating a new custom table in the current database
     * */
    function tfp_create_table(){
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        if ($mysqli->connect_errno) {
            $this->add_admin_error_notice("Failed to connect to MySQL: " . $mysqli->connect_error);
            return;
        }

        $create_table_sql = "
        CREATE TABLE IF NOT EXISTS " . TABLENAME . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(255) NOT NULL,
            service_address VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

        if (!$mysqli->query($create_table_sql)) {
            $this->add_admin_error_notice("Error creating table: " . $mysqli->error);
            return;
        }

        $mysqli->close();
    }

    /**
     * function to enqueue CSS and JS files
     * */
    function tfp_enqueue_assets(){
        // Enqueue the CSS file
        wp_enqueue_style(
            'tp-style',  // Handle name
            plugin_dir_url(__FILE__) . 'css/style.css',
            array(),
            '1.0'
        );

        // Enqueue the JS files
        wp_enqueue_script(
            'tp-script',  // Handle name
            plugin_dir_url(__FILE__) . 'js/script.js',
            array(),
            '1.0',
            true      // Load in the footer (true = yes)
        );
        wp_enqueue_script(
            'tp-script-ajax',  // Handle name
            plugin_dir_url(__FILE__) . 'js/script-ajax.js',
            array('jquery'),
            '1.0',
            true      // Load in the footer (true = yes)
        );

        wp_localize_script('tp-script-ajax', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    /*
     * function for drawing a form html
     * */
    function tfp_form_shortcode(){
        return '<form class="tfp-form" id="tfp_form">
                <div class="tfp-input">
                    <label for="fname">First Name</label>
                    <input type="text" name="fname" placeholder="John" id="fname">
                </div>    
                <div class="tfp-input">
                    <label for="lname">Last Name</label>
                    <input type="text" name="lname" placeholder="Smith" id="lname">
                </div>    
                <div class="tfp-input">
                    <label for="email">Email address</label>
                    <input type="text" name="email" placeholder="example@example.com" id="email">
                </div>    
                <div class="tfp-input">
                <label for="phone">Phone number</label>
                    <input type="text" name="phone" placeholder="+1 ___-___-__-__" id="phone">
                </div>    
                <div class="tfp-input">
                    <label for="service-address">Service address</label>
                    <input type="text" name="service-address" placeholder="Address..." id="service-address">
                </div>
                <div class="tfp-submit">
                    <input type="submit" value="Send data">
                </div>    
            </form>
            ';
    }

    /*
     * function for submission a form data and inserting it to table
     * */
    function custom_form_submission(){
        $response = ["success" => false, "message" => ''];
        $data = [];

        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        if ($mysqli->connect_errno) {
            $response['message'] = "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit(json_encode($response));
        }


        // Get the data from the AJAX request
        $fname = $mysqli->real_escape_string( sanitize_text_field($_POST['fname']) );
        $lname = $mysqli->real_escape_string( sanitize_text_field($_POST['lname']) );
        $email = $mysqli->real_escape_string( sanitize_text_field($_POST['email']) );
        $phone = $mysqli->real_escape_string( sanitize_text_field($_POST['phone']) );
        $service_address = $mysqli->real_escape_string( sanitize_text_field($_POST['service_address']) );

        // Check if any of the required fields are empty
        if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($service_address)) {
            $response['message'] = 'All fields are required.';
            exit(json_encode($response));
        }

        // Check if the email is valid
        if (!is_email($email)) {
            $response['message'] = 'Invalid email address.';
            exit(json_encode($response));
        }

        $insert_query = "INSERT INTO `" . TABLENAME . "` (`first_name`, `last_name`, `email`, `phone`, `service_address`) 
                        VALUES ('$fname', '$lname', '$email', '$phone', '$service_address')";

        if ($mysqli->query($insert_query) === TRUE) {
            $response = array(
                'success' => true,
                'message' => 'Thank you... Data inserted successfully.',
            );
        }

        $mysqli->close();
        echo json_encode($response);

        wp_die();
    }
}

new TestFormPlugin();






