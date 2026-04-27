<?php
/**
 * Plugin Name: Distributor Registration
 * Description: Self-contained multi-step distributor application form with database storage.
 * Version: 1.0.0
 * Author: BabyBrands
 * Text Domain: distributors-registration
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DREG_VERSION', '1.0.0');
define('DREG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DREG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DREG_POST_ACTION', 'dreg_distributor_register');

require_once DREG_PLUGIN_PATH . 'includes/models/class-dreg-model-distributor-tables.php';
require_once DREG_PLUGIN_PATH . 'includes/class-dreg-distributor-db-migrate.php';
require_once DREG_PLUGIN_PATH . 'includes/class-dreg-distributor-repository.php';
require_once DREG_PLUGIN_PATH . 'includes/class-dreg-distributor-registration-controller.php';

add_action(
    'init',
    static function () {
        if (class_exists('DREG_Distributor_Db_Migrate')) {
            DREG_Distributor_Db_Migrate::maybe_install();
        }
    },
    3
);

/**
 * @return string
 */
function dreg_get_distributor_registration_redirect_url()
{
    $redirectUrl = '';

    if (is_singular()) {
        $postId = get_queried_object_id();
        if ($postId) {
            $redirectUrl = get_permalink($postId);
        }
    }

    if ($redirectUrl === '') {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $redirectUrl = home_url($requestUri);
    }

    return remove_query_arg(['dreg_dr_status', 'dreg_dr_message'], $redirectUrl);
}

/**
 * @return array{status:string,message:string}
 */
function dreg_get_distributor_registration_feedback()
{
    return [
        'status' => isset($_GET['dreg_dr_status']) ? sanitize_key(wp_unslash($_GET['dreg_dr_status'])) : '',
        'message' => isset($_GET['dreg_dr_message']) ? sanitize_text_field(wp_unslash((string) $_GET['dreg_dr_message'])) : '',
    ];
}

/**
 * @return string
 */
function dreg_build_distributor_registration_feedback_url($status, $message = '', $redirectUrl = '')
{
    if ($redirectUrl === '') {
        $redirectUrl = dreg_get_distributor_registration_redirect_url();
    }

    $args = ['dreg_dr_status' => $status];
    if ($message !== '') {
        $args['dreg_dr_message'] = $message;
    }

    return add_query_arg($args, $redirectUrl);
}

/**
 * @return array<string,string>
 */
function dreg_get_canadian_provinces()
{
    return [
        'AB' => 'Alberta',
        'BC' => 'British Columbia',
        'MB' => 'Manitoba',
        'NB' => 'New Brunswick',
        'NL' => 'Newfoundland and Labrador',
        'NS' => 'Nova Scotia',
        'NT' => 'Northwest Territories',
        'NU' => 'Nunavut',
        'ON' => 'Ontario',
        'PE' => 'Prince Edward Island',
        'QC' => 'Quebec',
        'SK' => 'Saskatchewan',
        'YT' => 'Yukon',
    ];
}

/**
 * Resolve distributor-registration table name ({prefix}dis_* first, then legacy shapes).
 *
 * @param string $baseName Logical suffix: country, distributors, province, etc.
 * @return string
 */
function dreg_find_distributor_table_name($baseName)
{
    global $wpdb;

    $baseName = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $baseName);
    if ($baseName === '') {
        return '';
    }

    $candidates = [
        $wpdb->prefix . 'dis_' . $baseName,
        $wpdb->prefix . $baseName,
        'wp_' . $baseName,
        $baseName,
    ];

    foreach ($candidates as $candidate) {
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $candidate));
        if ($exists === $candidate) {
            return $candidate;
        }
    }

    return '';
}

require_once DREG_PLUGIN_PATH . 'includes/dreg-form-shortcode.php';

add_shortcode('distributor_registration_form', 'dreg_render_distributor_registration_form_shortcode');
add_shortcode('distributorregistration', 'dreg_render_distributor_registration_form_shortcode');

function dreg_handle_distributor_registration_submission()
{
    $redirectUrl = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : home_url('/');
    if ($redirectUrl === '') {
        $redirectUrl = home_url('/');
    }

    $successRedirect = isset($_POST['dreg_dr_success_redirect'])
        ? esc_url_raw(wp_unslash($_POST['dreg_dr_success_redirect']))
        : $redirectUrl;
    if ($successRedirect === '') {
        $successRedirect = $redirectUrl;
    }
    $successRedirect = wp_validate_redirect($successRedirect, $redirectUrl);
    $successRedirect = remove_query_arg(['dreg_dr_status', 'dreg_dr_message'], $successRedirect);

    if (
        !isset($_POST['dreg_dr_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dreg_dr_nonce'])), 'dreg_distributor_register')
    ) {
        wp_safe_redirect(
            dreg_build_distributor_registration_feedback_url(
                'error',
                __('Security check failed. Please try again.', 'distributors-registration'),
                $redirectUrl
            )
        );
        exit;
    }

    $controller = new DREG_Distributor_Registration_Controller();
    $result = $controller->handle($_POST);
    $target = ($result['status'] === 'success') ? $successRedirect : $redirectUrl;
    wp_safe_redirect(
        dreg_build_distributor_registration_feedback_url($result['status'], $result['message'], $target)
    );
    exit;
}

add_action('admin_post_nopriv_' . DREG_POST_ACTION, 'dreg_handle_distributor_registration_submission');
add_action('admin_post_' . DREG_POST_ACTION, 'dreg_handle_distributor_registration_submission');
