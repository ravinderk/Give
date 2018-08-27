<?php
/**
 * Custom Comments & Notes
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Comments
 * @copyright   Copyright (c) 2018, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB_Comments Class
 *
 * This class is for interacting with the comment database table.
 *
 * @since 2.3.0
 */
class Give_DB_Comments extends Give_DB {

	/**
	 * Give_DB_Comments constructor.
	 *
	 * Set up the Give DB Donor class.
	 *
	 * @since  2.3.0
	 * @access public
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_comments';
		$this->primary_key = 'ID';
		$this->version     = '1.0';

		// Install table.
		$this->register_table();

		parent::__construct();
	}

	/**
	 * Get columns and formats
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'ID'               => '%d',
			'comment_content'  => '%s',
			'comment_parent'   => '%d',
			'comment_type'     => '%s',
			'comment_date'     => '%s',
			'comment_date_gmt' => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function get_column_defaults() {
		$comment_create_date     = current_time( 'mysql', 0 );
		$comment_create_date_gmt = get_gmt_from_date( $comment_create_date );

		return array(
			'ID'               => 0,
			'comment_content'  => '',
			'comment_parent'   => 0,
			'comment_type'     => '',
			'comment_date'     => $comment_create_date,
			'comment_date_gmt' => $comment_create_date_gmt,
		);
	}

	/**
	 * Add a comment
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param  array $data
	 *
	 * @return bool|int
	 */
	public function add( $data = array() ) {
		// Valid table columns.
		$table_columns = array_keys( $this->get_columns() );

		// Filter data.
		foreach ( $data as $table_column => $column_data ) {
			if ( ! in_array( $table_column, $table_columns ) ) {
				unset( $data[ $table_column ] );
			}
		}

		// Set default values.
		$current_comment_data = wp_parse_args( $data, $this->get_column_defaults() );

		// Comment parent should be an int.
		$current_comment_data['comment_parent'] = is_numeric( $current_comment_data['comment_parent'] )
			? absint( $current_comment_data['comment_parent'] )
			: $current_comment_data['comment_parent'];

		// Get comment.
		$existing_comment = $this->get_comment_by( $current_comment_data['ID'] );

		// Update an existing comment.
		if ( $existing_comment ) {

			// Create new comment data from existing and new comment data.
			$current_comment_data = array_merge( $current_comment_data, $existing_comment );

			// Update comment data.
			$this->update( $current_comment_data['ID'], $current_comment_data );

			$comment_id = $current_comment_data['ID'];

		} else {
			$comment_id = $this->insert( $current_comment_data, 'comment' );
		}

		return $comment_id;
	}


	/**
	 * Retrieves a single comment from the database
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param int    $comment_id
	 * @param string $by
	 *
	 * @return bool|null|array
	 */
	public function get_comment_by( $comment_id = 0, $by = 'id' ) {
		/* @var WPDB $wpdb */
		global $wpdb;
		$comment = null;

		// Make sure $comment_id is int.
		$comment_id = absint( $comment_id );

		// Bailout.
		if ( empty( $comment_id ) ) {
			return null;
		}

		switch ( $by ) {
			case 'id':
				$comment = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM $this->table_name WHERE ID = %s LIMIT 1",
						$comment_id
					),
					ARRAY_A
				);
				break;

			default:
				$comment = apply_filters( "give_get_comment_by_{$by}", $comment, $comment_id );
		}

		return $comment;
	}

	/**
	 * Retrieve comments from the database.
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return mixed
	 */
	public function get_comments( $args = array() ) {
		global $wpdb;
		$sql_query = $this->get_sql( $args );

		// Get comment.
		if ( ! ( $comments = Give_Cache::get( 'give_comments', true, $sql_query ) ) ) {
			$comments = $wpdb->get_results( $sql_query );
			Give_Cache::set( 'give_comments', $comments, 3600, true, $sql_query );
		}

		return $comments;
	}


	/**
	 * Count the total number of comments in the database
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return int
	 */
	public function count( $args = array() ) {
		/* @var WPDB $wpdb */
		global $wpdb;
		$args['number'] = - 1;
		$args['fields'] = 'ID';
		$args['count']  = true;

		$sql_query = $this->get_sql( $args );

		if ( ! ( $count = Give_Cache::get( 'give_comments_count', true, $sql_query ) ) ) {
			$count = $wpdb->get_var( $sql_query );
			Give_Cache::set( 'give_comments_count', $count, 3600, true, $args );
		}

		return absint( $count );
	}

	/**
	 * Create the table
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
        ID bigint(20) NOT NULL AUTO_INCREMENT,
        comment_title longtext NOT NULL,
        comment_content longtext NOT NULL,
      	comment_parent bigint(20) NOT NULL,
        comment_type mediumtext NOT NULL,
        comment_date datetime NOT NULL,
        comment_date_gmt datetime NOT NULL,
        PRIMARY KEY  (ID)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version, false );
	}


	/**
	 * Get sql query from quaried array.
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_sql( $args = array() ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'paged'   => 0,
			'orderby' => 'date',
			'order'   => 'DESC',
			'fields'  => 'all',
			'count'   => false,
		);

		$args = wp_parse_args( $args, $defaults );

		// validate params.
		$this->validate_params( $args );

		if ( $args['number'] < 1 ) {
			$args['number'] = 99999999999;
		}

		// Where clause for primary table.
		$where = '';

		// Get sql query for meta.
		if ( ! empty( $args['meta_query'] ) ) {
			$meta_query_object = new WP_Meta_Query( $args['meta_query'] );
			$meta_query        = $meta_query_object->get_sql( 'comment', $this->table_name, 'id' );
			$where             = implode( '', $meta_query );
		}

		$where .= ' WHERE 1=1 ';

		// Set offset.
		if ( empty( $args['offset'] ) && ( 0 < $args['paged'] ) ) {
			$args['offset'] = $args['number'] * ( $args['paged'] - 1 );
		}

		// Set fields.
		$fields = "{$this->table_name}.*";
		if ( is_string( $args['fields'] ) && ( 'all' !== $args['fields'] ) ) {
			$fields = "{$this->table_name}.{$args['fields']}";
		}

		// Set count.
		if ( $args['count'] ) {
			$fields = "COUNT({$fields})";
		}

		// Specific comments.
		if ( ! empty( $args['ID'] ) ) {

			if ( ! is_array( $args['ID'] ) ) {
				$args['ID'] = explode( ',', $args['ID'] );
			}
			$comment_ids = implode( ',', array_map( 'intval', $args['ID'] ) );

			$where .= " AND {$this->table_name}.ID IN( {$comment_ids} ) ";
		}

		// Comments created for a specific date or in a date range
		if ( ! empty( $args['date_query'] ) ) {
			$date_query_object = new WP_Date_Query( $args['date_query'], "{$this->table_name}.comment_date" );
			$where             .= $date_query_object->get_sql();
		}

		// Comments create for specific parent.
		if ( ! empty( $args['comment_parent'] ) ) {
			if ( ! is_array( $args['comment_parent'] ) ) {
				$args['comment_parent'] = explode( ',', $args['comment_parent'] );
			}
			$parent_ids = implode( ',', array_map( 'intval', $args['comment_parent'] ) );

			$where .= " AND {$this->table_name}.comment_parent IN( {$parent_ids} ) ";
		}

		// Comments create for specific type.
		// is_array check is for backward compatibility.
		if ( ! empty( $args['comment_type'] ) && ! is_array( $args['comment_type'] ) ) {
			if ( ! is_array( $args['comment_type'] ) ) {
				$args['comment_type'] = explode( ',', $args['comment_type'] );
			}

			$comment_types = implode( '\',\'', array_map( 'trim', $args['comment_type'] ) );

			$where .= " AND {$this->table_name}.comment_type IN( '{$comment_types}' ) ";
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'comment_date' : $args['orderby'];

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		return $wpdb->prepare(
			"SELECT {$fields} FROM {$this->table_name} {$where} ORDER BY {$this->table_name}.{$args['orderby']} {$args['order']} LIMIT %d,%d;",
			absint( $args['offset'] ),
			absint( $args['number'] )
		);
	}


	/**
	 * Validate query params.
	 *
	 * @since  2.3.0
	 * @access private
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	private function validate_params( &$args ) {
		// fields params
		$args['fields'] = 'ids' === $args['fields']
			? 'ID'
			: $args['fields'];
		$args['fields'] = array_key_exists( $args['fields'], $this->get_columns() )
			? $args['fields']
			: 'all';
	}
}

// @todo: update cache logic.
// @todo: create issue for log cache logic.
