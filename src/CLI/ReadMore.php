<?php
namespace DMG\ReadMore\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * CLI command to search for DMG Read More block.
 */
class ReadMore extends WP_CLI_Command {

	/**
	 * @var string $after_date
	 */
	private $after_date;

	/**
	 * @var string $before_date
	 */
	private $before_date;

	/**
	 * @var string $block_name
	 */
	private $block_name;

	/**
	 * Add CLI command
	 */
	public function __construct() {
		WP_CLI::add_command(
			'dmg-read-more search',
			[ $this, 'search' ],
			[
				'shortdesc' => 'Search for DMG Read More block within post content.',
				'synopsis'  => [
					[
						'name'        => 'block-name',
						'type'        => 'assoc',
						'description' => 'Search for a specific block name. Format as namespace/block-name',
						'optional'    => true,
					],
					[
						'name'        => 'date-after',
						'type'        => 'assoc',
						'description' => 'Search through posts created after this date (default last 30 days). Format as YYYY-MM-DD',
						'optional'    => true,
					],
					[
						'name'        => 'date-before',
						'type'        => 'assoc',
						'description' => 'Search through posts created before this date. Format as YYYY-MM-DD',
						'optional'    => true,
					],
					[
						'name'        => 'search-with-sql',
						'type'        => 'flag',
						'description' => 'Search using a single MySQL query (default is to search in batches)',
						'optional'    => true,
					],
				],
				'longdesc'  => "## EXAMPLES\n\nwp dmg-read-more search --block-name=dmg/dmg-read-more\n\nwp dmg-read-more search --date-after=2025-01-01 --date-before=2025-02-01\n\nwp dmg-search-more search --search-with-sql",
			]
		);
	}

	/**
	 * Search for block.
	 *
	 * @param array $args Arguments specified.
	 * @param array $assoc_args Associative arguments specified.
	 */
	public function search( $args, $assoc_args ) {
		$this->parse_args( $assoc_args );

		WP_CLI::line(
			sprintf(
				/* translators: block name */
				__( 'Searching for block "%s"...', 'dmg-read-more' ),
				$this->block_name
			)
		);

		$time_start = microtime( true );

		if ( isset( $assoc_args['search-with-sql'] ) ) {
			$this->search_with_mysql();
		} else {
			$this->search_in_batches();
		}

		$time_end = microtime( true );

		WP_CLI::success(
			sprintf(
				/* translators: human readable elapsed time */
				__( 'Completed in %s', 'dmg-read-more' ),
				human_time_diff( $time_start, $time_end )
			)
		);
	}

	/**
	 * Search for block with MySql query
	 */
	private function search_with_mysql() {
		global $wpdb;

		$search = '% wp:' . $wpdb->esc_like( $this->block_name ) . ' %';
		$sql    = "SELECT ID from {$wpdb->posts} WHERE post_type='post' AND post_status='publish' AND post_content LIKE '{$search}' AND post_date_gmt >= '{$this->after_date}'";

		if ( $this->before_date ) {
			$sql .= " AND post_date_gmt <= '{$this->before_date}'";
		}

		$ids   = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
		$count = count( $ids );

		if ( $count < 1 ) {
			WP_CLI::line( __( 'No matching posts found.', 'dmg-read-more' ) );
			return;
		}

		WP_CLI::line(
			sprintf(
				/* translators: matching post count */
				_n(
					'Found %d matching post:',
					'Found %d matching posts:',
					$count,
					'dmg-read-more'
				),
				$count
			)
		);

		foreach ( $ids as $id ) {
			WP_CLI::line( $id );
		}
	}

	/**
	 * Search in batches and compare in PHP.
	 */
	private function search_in_batches() {
		global $wpdb;

		$batch_size   = 250;
		$offset       = 0;
		$rows_matched = 0;
		$batch_count  = 0;
		$count        = 0;

		// Format base SQL
		$sql = "SELECT ID, post_content from {$wpdb->posts} WHERE post_type='post' AND post_status='publish' AND post_date_gmt >= '{$this->after_date}'";

		if ( $this->before_date ) {
			$sql .= " AND post_date_gmt <= '{$this->before_date}'";
		}

		// String to search for (based on pattern in `has_block` function)
		$search = " wp:{$this->block_name} ";

		do {
			$batch_sql = $sql . " LIMIT {$batch_size} OFFSET {$offset}";
			$rows      = $wpdb->get_results( $batch_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
			$count     = count( $rows );

			if ( $count < 1 ) {
				break;
			}

			foreach ( $rows as $row ) {
				if ( str_contains( $row['post_content'], $search ) ) {
					WP_CLI::line( $row['ID'] );
					++$rows_matched;
				}
			}

			$offset += $batch_size;
			++$batch_count;
		} while ( $count > 0 && $count >= $batch_size );

		WP_CLI::line(
			sprintf(
				/* translators: batch count */
				_n(
					'Completed %d batch',
					'Completed %d batches',
					$batch_count,
					'dmg-read-more'
				),
				$batch_count
			)
		);

		if ( $rows_matched < 1 ) {
			WP_CLI::line( __( 'No matching posts found.', 'dmg-read-more' ) );
		}
	}

	/**
	 * Parse command line arguments
	 *
	 * @param array $args
	 */
	private function parse_args( array $args ) {
		global $wpdb;

		if ( empty( $args['block-name'] ) || ! $this->validate_block_name( $args['block-name'] ) ) {
			$this->block_name = 'dmg/dmg-read-more';
		} else {
			$this->block_name = $args['block-name'];
		}

		if ( empty( $args['date-after'] ) || ! $this->validate_date( $args['date-after'] ) ) {
			$this->after_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		} else {
			$this->after_date = $args['date-after'];
		}

		if ( empty( $args['date-before'] ) || ! $this->validate_date( $args['date-before'] ) ) {
			$this->before_date = null;
		} else {
			$this->before_date = $args['date-before'] . ' 23:59:59';
		}
	}

	/**
	 * Validate a block name
	 *
	 * @param string $block_name
	 * @return boolean
	 */
	private function validate_block_name( string $block_name ): bool {
		if ( preg_match( '#^([\w]+/)?[\w-]+$#', $block_name ) !== 1 ) {
			WP_CLI::warning(
				sprintf(
					/* translators: invalid block name */
					__( 'Invalid block name "%s", should be in the format "block-name" or "namespace/block-name"', 'dmg-read-more' ),
					$block_name
				)
			);

			return false;
		}

		return true;
	}

	/**
	 * Validate a date
	 *
	 * @param string $date
	 * @return boolean
	 */
	private function validate_date( string $date ): bool {
		if ( preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date ) !== 1 ) {
			WP_CLI::warning(
				sprintf(
					/* translators: invalid date */
					__( 'Invalid date "%s"', 'dmg-read-more' ),
					$date
				)
			);

			return false;
		}

		return true;
	}
}
