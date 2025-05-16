<?php
/**
 * Plugin Name:       DMG Read More
 * Description:       Read more link to a post
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      8.0
 * Author:            Mik
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dmg-read-more
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Init plugin when PHP requirements are met
 */
if ( version_compare( PHP_VERSION, '8.0', '>' ) ) {
	add_action(
		'plugins_loaded',
		function () {
			\DMG\ReadMore\Main::instance();
		}
	);
} else {
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'DMG Read More requires PHP 8.0 or higher', 'dmg-read-more' ); ?></p>
			</div>
			<?php
		}
	);
}
