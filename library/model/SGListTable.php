<?php namespace siteguard\security\model;


if ( ! class_exists( "WP_List_Table" ) ) {
	require_once ABSPATH . "wp-admin/includes/class-wp-list-table.php";
}


use siteguard\security\system\Input;
use WP_List_Table;


/**
 * Class SGListTable
 *
 * @package siteguard\security\model
 */
abstract class SGListTable extends WP_List_Table {
	private static $tables = array();


	/**
	 * @param string $name
	 *
	 * @return SGListTable
	 */
	public static function getTable( $name ) {
		if ( ! isset( self::$tables[ $name ] ) ) {
			$table_class_name = "\\siteguard\\security\\model\\{$name}ListTable";

			self::$tables[ $name ] = new $table_class_name();
		}

		return self::$tables[ $name ];
	}


	/**
	 * Constructor
	 *
	 * @param string $singular
	 * @param string $plural
	 */
	public function __construct( $singular, $plural ) {
		parent::__construct( array(
			"singular" => $singular,
			"plural"   => $plural,
			"ajax"     => false,
		) );

		add_filter( "manage_{$this->screen->id}_columns", array( $this, "get_columns" ) );

		add_screen_option( "per_page", array(
			"default" => 20,
			"option"  => "siteguard_security_{$plural}_per_page"
		) );
	}


	/**
	 * @return string
	 */
	public function showMessage() {
		$message = explode( ":", Input::read( "message", "" ) );

		if ( isset( $message[0] ) && ! empty( $message[0] ) ) {
			$msg = method_exists( $this, "showMessage{$message[0]}" )
				? call_user_func_array(
					array( $this, "showMessage{$message[0]}" ),
					array_slice( $message, 1 )
				)
				: "";

			if ( ! empty( $msg ) ) {
				return "<div id = 'message' class='updated notice is-dismissible'><p>{$msg}</p></div>";
			}
		}

		return "";
	}


	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		$args = $this->getRequestQueryArgs();

		// read the pre_page item num
		$per_page = $this->get_items_per_page( "siteguard_security_{$this->_args["plural"]}_per_page" );

		// config offset and load items num
		$args["per_page"] = $per_page;
		$args["offset"]   = ( $this->get_pagenum() - 1 ) * $per_page;

		// exec the query to load the items
		$this->items = $this->queryForItems( $args, $total_items );

		// set the info
		$this->set_pagination_args( array(
			"total_items" => $total_items,
			"per_page"    => $per_page,
		) );
	}


	/**
	 * @return bool return true if the search text value is enable
	 */
	protected function hasTextSearch() {
		return true;
	}


	/**
	 * @return array
	 */
	protected function getRequestQueryArgs() {
		$args = array();

		if ( $this->hasTextSearch() && ! empty( $_REQUEST["s"] ) ) {
			$args["s"] = $_REQUEST["s"];
		}

		if ( ! empty( $_REQUEST["orderby"] ) && $this->isOrderByValid( $_REQUEST["orderby"] ) ) {
			$args["orderby"] = $_REQUEST["orderby"];
		}

		if ( ! empty( $args["orderby"] ) ) {
			$args["order"] = ( ! isset( $_REQUEST["order"] ) || strtolower( $_REQUEST["order"] === "asc" ) )
				? "ASC"
				: "DESC";
		}

		return $args;
	}


	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	private function isOrderByValid( $name ) {
		foreach ( $this->get_sortable_columns() as $col => $info ) {
			if ( $info[0] === $name ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param array $args
	 * @param int   $total_items
	 *
	 * @return array
	 */
	protected abstract function queryForItems( $args, &$total_items );


	/**
	 * @param string $field
	 * @param string $label
	 * @param array  $options
	 *
	 * @return string
	 */
	protected function getSelect( $field, $label, $options = array() ) {
		$html = "<label for='siteguard-security-{$field}' class='screen-reader-text'>{$label}</label>";

		$html .= "<select name='{$field}' id='siteguard-security-{$field}'>";

		$current = Input::read( $field, "" );
		foreach ( $options as $k => $v ) {
			$html .= $this->getOption( $k, $v, $current );
		}

		$html .= "</select>";

		return $html;
	}


	/**
	 * @param string $k
	 * @param string $v
	 * @param string $c
	 *
	 * @return string
	 */
	protected function getOption( $k, $v, $c ) {
		$s = (string) selected( $c, $k, false );

		return "<option value='{$k}' {$s}>{$v}</option>";
	}


	/**
	 * @param string $url
	 * @param string $label
	 *
	 * @return string
	 */
	protected function getActionLink( $url, $label ) {
		return sprintf( "<a href='%s'>%s</a>", $url, esc_html__( $label, "siteguard-security" ) );
	}


	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		if ( isset( $item->{"$column_name"} ) ) {
			return $item->{"$column_name"};
		}

		if ( isset( $item->{"post_{$column_name}"} ) ) {
			return $item->{"post_{$column_name}"};
		}

		return "";
	}


	/**
	 * Default render for "cb" column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='{$this->_args["singular"]}[]' value='{$item->ID}'/>";
	}


	/**
	 * Default render for "date" column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	protected function column_date( $item ) {
		return $this->format_date( $item, "date" );
	}


	/**
	 * Default render for "date" column
	 *
	 * @param object $item
	 * @param string $prop
	 *
	 * @return string
	 */
	protected function format_date( $item, $prop ) {
		return Format::formatDateGMT( $item->$prop );
	}
}
