<?php
namespace DMG\ReadMore;

/**
 * Main plugin class
 */
class Main {

	/**
	 * @var string $plugin_dir
	 */
	private $plugin_dir;

	/**
	 * Return an instance of the main plugin class.
	 *
	 * @return Main
	 */
	public static function instance(): Main {
		static $plugin = null;

		if ( $plugin === null ) {
			$plugin = new Main();
		}

		return $plugin;
	}

	/**
	 * Initialize main plugin
	 */
	public function __construct() {
		$this->plugin_dir = dirname( __DIR__ );
		add_action( 'init', [ $this, 'blocks_init' ] );

		if ( class_exists( 'WP_CLI' ) ) {
			new CLI\ReadMore();
		}
	}

	/**
	 * Initialize blocks
	 */
	public function blocks_init() {
		$build_dir     = $this->plugin_dir . '/build';
		$manifest_file = $build_dir . '/blocks-manifest.php';

		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
			wp_register_block_types_from_metadata_collection( $build_dir, $manifest_file );
			return;
		}

		if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( $build_dir, $manifest_file );
		}

		$manifest_data = require $manifest_file;
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			register_block_type( "{$build_dir}/{$block_type}" );
		}
	}
}
