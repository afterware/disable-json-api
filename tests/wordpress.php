<?php
// Focused checks run in an isolated WordPress installation.
$GLOBALS['wp']->query_vars['rest_route'] = '/wp/v2/users';
wp_set_current_user(0);
$result = apply_filters('rest_authentication_errors', null);
if (!is_wp_error($result)) throw new RuntimeException('Anonymous request was not rejected');
add_filter('dra_allow_rest_api', '__return_true');
if (is_wp_error(apply_filters('rest_authentication_errors', null))) throw new RuntimeException('Explicit allow override did not work');
echo "PASS anonymous REST request and explicit override\n";

require_once '/plugin/classes/admin.php';
$malicious = "/unsafe/' onmouseover='alert(1)";
add_filter('rest_endpoints', function ($routes) use ($malicious) { $routes[$malicious] = array(); return $routes; });
ob_start();
DRA_Admin::display_route_checkboxes('none');
$html = ob_get_clean();
if (strpos($html, esc_attr($malicious)) === false || strpos($html, "value='" . $malicious . "'") !== false) {
    throw new RuntimeException('REST route was not escaped in the settings page');
}
echo "PASS REST route names are escaped in settings\n";
