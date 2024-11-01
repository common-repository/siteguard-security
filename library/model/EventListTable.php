<?php namespace siteguard\security\model;


use siteguard\security\query\Select;
use siteguard\security\system\Input;


/**
 * Class EventListTable
 *
 * @package siteguard\security\model
 */
class EventListTable extends SGListTable {
	/** Constructor */
	public function __construct() {
		parent::__construct( "event", "events" );
	}


	/**
	 * @param string $done
	 * @param string $skip
	 *
	 * @return string
	 */
	public function showMessageDelete( $done, $skip ) {
		if ( $done !== "0" || $skip !== "0" ) {
			return sprintf(
				__( "Events deleted: <strong>%s</strong>, skipped: <strong>%s</strong>.", "siteguard-security" ),
				$done, $skip
			);
		}

		return "";
	}


	/**
	 * @param string $done
	 * @param string $skip
	 *
	 * @return string
	 */
	public function showMessageReset( $done, $skip ) {
		if ( $done !== "0" || $skip !== "0" ) {
			return sprintf(
				__( "Events reseted: <strong>%s</strong>, skipped: <strong>%s</strong>.", "siteguard-security" ),
				$done, $skip
			);
		}

		return "";
	}


	/**
	 * @param array $args
	 * @param int   $total_items
	 *
	 * @return array
	 */
	protected function queryForItems( $args, &$total_items ) {
		$select = Select::from( "posts" )
		                ->hint( "sql_calc_found_rows" )
		                ->select( "posts.id" )
		                ->where( "posts.post_status not in (%s, %s)", "trash", "auto-draft" )
		                ->limit( $args["offset"], $args["per_page"] );

		// add type filter
		switch ( $filter = Input::read( "t", "" ) ) {
			case "login":
			case "error":
			case "exception":
				$select->where( "posts.post_type = %s", "siteguard_{$filter}" );
				break;
			default:
				$select->where( "posts.post_type in (%s, %s, %s)", "siteguard_login", "siteguard_error", "siteguard_exception" );
				break;
		}

		// add order bys
		if ( isset( $args["orderby"] ) ) {
			switch ( $args["orderby"] ) {
				case "count.meta_value":
					// add the required join
					$select->join( "left outer", "postmeta", "count", "posts.id = count.post_id and count.meta_key = %s", "count" );
					break;
				case "plugin.meta_value":
					// add the required join
					$select->join( "left outer", "postmeta", "plugin", "posts.id = plugin.post_id and plugin.meta_key = %s", "plugin" );
					break;
				default:
					break;
			}

			$select->order_by( $args["orderby"], $args["order"] );
		} else {
			$select->order_by( "posts.post_modified_gmt", "desc" );
		}
		$select->order_by( "posts.id" );

		// add filters
		if ( isset( $args["s"] ) ) {
			$select->where( "(posts.post_title like %s or posts.post_content like %s)", "%{$args["s"]}%", "%{$args["s"]}%" );
		}

		// read data
		$page_ids    = $select->exec( "get_col" );
		$total_items = $select->found_rows();

		// convert
		return array_map( "get_post", $page_ids );
	}


	/**
	 * Get a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			"cb"     => "<input type='checkbox'/>",
			"title"  => esc_html__( "Event", "siteguard-security" ),
			"type"   => esc_html__( "Type", "siteguard-security" ),
			"plugin" => esc_html__( "Plugin", "siteguard-security" ),
			"count"  => esc_html__( "Count", "siteguard-security" ),
			"date"   => esc_html__( "Date", "siteguard-security" ),
		);
	}


	/**
	 * Get a list of sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			"title"  => array(
				"posts.post_title",
				false,
			),
			"type"   => array(
				"posts.post_type",
				false,
			),
			"plugin" => array(
				"plugin.meta_value",
				false,
			),
			"count"  => array(
				"count.meta_value",
				false,
			),
			"date"   => array(
				"posts.post_modified_gmt",
				true,
			),
		);
	}


	/**
	 * Get an associative array ( option_name => option_title ) with the list of bulk actions available on this table.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			"reset"  => esc_html__( "Reset", "siteguard-security" ),
			"delete" => esc_html__( "Delete", "siteguard-security" ),
		);

		return $actions;
	}


	/**
	 * @param string $which
	 *
	 * @return string|void
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === "top" ) {
			$select = $this->getSelect(
				"t", esc_html__( "Type", "siteguard-security" ),
				array(
					""          => esc_html__( "All", "siteguard-security" ),
					"login"     => esc_html__( "Login failed", "siteguard-security" ),
					"error"     => esc_html__( "Error", "siteguard-security" ),
					"exception" => esc_html__( "Exception", "siteguard-security" ),
				)
			);

			$submit = get_submit_button( __( "Filter", "siteguard-security" ), "button", "filter_type", false );

			echo "<div class='alignleft actions'>{$select}{$submit}</div>";
		}
	}


	/**
	 * @param \WP_Post $item
	 *
	 * @return string
	 */
	protected function column_title( $item ) {
		$link = esc_url( $_SERVER["REQUEST_URI"] );
		$page = menu_page_url( $_REQUEST["page"], false );

		$actions = $this->row_actions( array(
			"details" => $this->getActionLink( "{$page}&type=event&id={$item->ID}", "Details" ),
			"reset"   => $this->getActionLink( "{$link}&action=reset&id={$item->ID}", "Reset" ),
			"delete"  => $this->getActionLink( "{$link}&action=delete&id={$item->ID}", "Delete" ),
		) );

		$short_type = substr( $item->post_type, 10 );
		$title      = nl2br( esc_html( wp_strip_all_tags( $this->column_default( $item, "title" ) ) ) );

		$html = "<a href='{$page}&type={$short_type}&id={$item->ID}'>{$title}</a>";

		return "{$html}<br>{$actions}";
	}


	/**
	 * @param \WP_Post $item
	 *
	 * @return string
	 */
	protected function column_plugin( $item ) {
		return Format::getPluginLink( $item->ID );
	}


	/**
	 * @param \WP_Post $item
	 *
	 * @return string
	 */
	protected function column_count( $item ) {
		return Format::getCount( $item->ID, true );
	}


	/**
	 * @param \WP_Post $item
	 *
	 * @return string
	 */
	protected function column_type( $item ) {
		switch ( $item->post_type ) {
			case "siteguard_login":
				return esc_html__( "Login failed", "siteguard-security" );
			case "siteguard_error":
				return esc_html__( "Error", "siteguard-security" );
			case "siteguard_exception":
				return esc_html__( "Exception", "siteguard-security" );
				break;
			default:
				return "";
		}
	}


	/**
	 * @param \WP_Post $item
	 *
	 * @return string
	 */
	protected function column_date( $item ) {
		return $this->format_date( $item, "post_modified_gmt" );
	}
}
