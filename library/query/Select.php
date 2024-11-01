<?php namespace siteguard\security\query;


/**
 * Class Select
 *
 * @package siteguard\security\query
 */
class Select {
	/** @var \wpdb the reference to the wpdb object */
	private $wpdb = null;


	private $from = array( "table" => "post", "alias" => "" );


	private $join = array();
	private $join_args = array();


	private $hints = array();


	private $select = array();


	private $where = array();
	private $where_args = array();


	private $group_by = array();
	private $order_by = array();


	private $offset = 0, $count = 0;


	/**
	 * @param string $from
	 * @param string $alias
	 *
	 * @return Select
	 */
	public static function from( $from = "posts", $alias = "" ) {
		return new Select( trim( $from ), trim( $alias ) );
	}


	/**
	 * @param string $from
	 * @param string $alias
	 */
	private function __construct( $from, $alias ) {
		$this->wpdb = $GLOBALS["wpdb"];

		$this->from = array( "table" => $from, "alias" => ! empty( $alias ) ? $alias : $from );
	}


	/**
	 * @param array  $args The arguments list
	 * @param string $name The arguments name
	 * @param int    $num  The num of arguments to ignore
	 *
	 * @return $this
	 */
	private function appendArgs( $args, $name, $num ) {
		if ( is_array( $args ) ) {
			foreach ( array_slice( $args, $num ) as $arg ) {
				$this->{$name}[] = $arg;
			}
		}

		return $this;
	}


	/**
	 * @param string $hint
	 *
	 * @return $this
	 */
	public function hint( $hint ) {
		$this->hints[] = $hint;

		return $this;
	}


	/**
	 * @param string $select
	 *
	 * @return $this
	 */
	public function select( $select ) {
		$this->select[] = $select;

		return $this;
	}


	/**
	 * @param $type
	 * @param $table
	 * @param $alias
	 * @param $on
	 *
	 * @return $this
	 */
	public function join( $type, $table, $alias, $on ) {
		$this->join[] = "{$type} join {$this->wpdb->prefix}{$table} as {$alias} on {$on}";

		return $this->appendArgs( func_get_args(), "join_args", 4 );
	}


	/**
	 * @param string $where
	 *
	 * @return $this
	 */
	public function where( $where ) {
		$this->where[] = "({$where})";

		return $this->appendArgs( func_get_args(), "where_args", 1 );
	}


	/**
	 * @param string $group_by
	 *
	 * @return $this
	 */
	public function group_by( $group_by ) {
		$this->group_by[] = $group_by;

		return $this;
	}


	/**
	 * @param string $order_by
	 * @param string $mode
	 *
	 * @return $this
	 */
	public function order_by( $order_by, $mode = "asc" ) {
		$this->order_by[] = strtolower( $mode ) === "asc"
			? "{$order_by} asc"
			: "{$order_by} desc";

		return $this;
	}


	/**
	 * @param int $offset
	 * @param int $count
	 *
	 * @return $this
	 */
	public function limit( $offset = 0, $count = 20 ) {
		$this->offset = $offset;
		$this->count  = $count;

		return $this;
	}


	/**
	 * @param string $cmd
	 *
	 * @return mixed
	 */
	public function exec( $cmd ) {
		$args = func_get_args();

		$args[0] = $this->wpdb->prepare(
			$this->sql(),
			array_merge( $this->join_args, $this->where_args )
		);

		return call_user_func_array( array( $this->wpdb, $cmd ), $args );
	}


	/**
	 * @return int
	 */
	public function found_rows() {
		return (int) $this->wpdb->get_var( "select found_rows()" );
	}


	/**
	 * @return string
	 */
	public function sql() {
		$query = "select{$this->implode( " ", $this->hints )}";

		$query .= "{$this->implode( ", ", $this->select)}";

		$query .= " from {$this->wpdb->prefix}{$this->from["table"]}";
		if ( ! empty( $this->from["alias"] ) ) {
			$query .= " as {$this->from["alias"]}";
		}

		$query .= "{$this->implode( " ", $this->join, " " )}";

		$query .= "{$this->implode( " and ", $this->where, " where " )}";
		$query .= "{$this->implode( ", ", $this->group_by, " group by " )}";
		$query .= "{$this->implode( ", ", $this->order_by, " order by " )}";

		if ( $this->offset > 0 || $this->count > 0 ) {
			$query .= " limit {$this->offset}, {$this->count}";
		}

		return $query;
	}


	/**
	 * @param string $glue
	 * @param array  $items
	 * @param string $start
	 *
	 * @return string
	 */
	private function implode( $glue, $items, $start = " " ) {
		return ! empty( $items )
			? $start . implode( $glue, $items )
			: "";
	}
}
