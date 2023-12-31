<?php

namespace GravityKit\GravityMaps;

use \GFFormsModel;
use GF_Query;

/**
 * Class Search_GF_Query_Radius_Condition
 *
 * @since 2.0
 *
 */
class Search_GF_Query_Radius_Condition extends \GF_Query_Condition {
	/**
	 * Which Longitude will be used to run the query.
	 *
	 * @since 2.0
	 *
	 * @var float
	 */
	protected $longitude;

	/**
	 * Which Latitude will be used to run the query.
	 *
	 * @since 2.0
	 *
	 * @var float
	 */
	protected $latitude;

	/**
	 * Radius we will run this conditional with.
	 *
	 * @since 2.0
	 *
	 * @var float
	 */
	protected $radius;

	/**
	 * Sets the fields that need to be used based on the GF forms array of IDs provided.
	 *
	 * @since 2.0
	 *
	 * @var array[]
	 */
	protected $fields;

	/**
	 * @since 2.2.1
	 *
	 * @var array The aliases we need to join.
	 */
	protected $aliases = [];

	/**
	 * Sets which fields we will use for the SQL for this particular conditional.
	 *
	 * @since 2.0
	 *
	 * @param int[]|string[] $fields Field IDS that will be turned to array of data.
	 * @param string         $type   From which type of field we are looking for.
	 *
	 * @return $this
	 */
	public function set_fields( $fields, $type = 'internal' ) {
		$callbacks = Form_Fields::get_geolocation_fields_meta_key_callback( $type );
		// Don't set fields when there are no callbacks.
		if ( ! $callbacks ) {
			return $this;
		}

		$this->mode = $type;

		$this->fields = array_map( static function ( $id ) use ( $callbacks ) {
			return [
				'id' => $id,
				'lat' => $callbacks['lat']( $id ),
				'long' => $callbacks['long']( $id ),
			];
		}, (array) $fields );

		return $this;
	}

	/**
	 * Sets the Radius for this particular conditional.
	 *
	 * @since 2.0
	 *
	 * @param float $radius
	 *
	 * @return $this
	 */
	public function set_radius( $radius ) {
		$this->radius = (float) $radius;

		return $this;
	}

	/**
	 * Sets the Latitude for this particular conditional.
	 *
	 * @since 2.0
	 *
	 * @param float $latitude
	 *
	 * @return $this
	 */
	public function set_latitude( $latitude ) {
		$this->latitude = (float) $latitude;

		return $this;
	}

	/**
	 * Sets the Longitude for this particular conditional.
	 *
	 * @since 2.0
	 *
	 * @param float $longitude
	 *
	 * @return $this
	 */
	public function set_longitude( $longitude ) {
		$this->longitude = (float) $longitude;

		return $this;
	}

	/**
	 * Add a alias to the ones we need a join for.
	 *
	 * @since 2.2.1
	 *
	 * @param string $alias    The alias generated by the query.
	 * @param string $field_id The field ID.
	 * @param string $sql      The SQL that the alias is used on.
	 *
	 */
	public function add_alias( $alias, $field_id, $sql ): void {
		$this->aliases[ $field_id ] = [
			'name' => $alias,
			'sql' => $sql,
		];
	}

	/**
	 * Gets all the aliases related to the current conditional.
	 *
	 * @since 2.2.1
	 *
	 * @return array
	 */
	public function get_aliases(): array {
		return $this->aliases;
	}

	/**
	 * Generate the SQL based on the params set for this particular type of conditional.
	 *
	 * @param GF_Query The query.
	 *
	 * @return string The SQL this condition generates.
	 */
	public function sql( $query ) {
		global $wpdb;

		if ( ! isset( $this->longitude, $this->latitude, $this->radius, $this->fields ) ) {
			return null;
		}

		$sql = [];

		foreach ( $this->fields as $field ) {
			$alias_lat  = $query->_alias( $field['lat'], $this->latitude, 'geo' );
			$alias_long = $query->_alias( $field['long'], $this->longitude, 'geo' );

			$lat_meta_value  = "$alias_lat.meta_value";
			$long_meta_value = "{$alias_long}.meta_value";

			$haversine = "6371 * ACOS(COS(RADIANS(%s)) * COS(RADIANS({$lat_meta_value})) * COS(RADIANS({$long_meta_value}) - RADIANS(%s)) + SIN(RADIANS(%s)) * SIN(RADIANS({$lat_meta_value})))";

			$distance = $wpdb->prepare( $haversine, $this->latitude, $this->longitude, $this->latitude );
			$sql[]    = $alias_sql = $wpdb->prepare( "(SELECT {$distance} BETWEEN 0 AND %s)", $this->radius );

			$this->add_alias( $alias_lat, $field['lat'], $alias_sql );
			$this->add_alias( $alias_long, $field['long'], $alias_sql );
		}

		// Add the join aliases to the query.
		add_filter( 'gform_gf_query_sql', [ $this, 'include_join_aliases' ], 25 );

		return ' ( ' . implode( ' ' . static::_OR . ' ', $sql ) . ' ) ';
	}

	/**
	 * Ensures that the SQL aliases are included in the query.
	 *
	 * @since 2.2.1
	 *
	 * @param $sql
	 *
	 * @return array
	 */
	public function include_join_aliases( $sql ) {
		$aliases        = $this->get_aliases();
		$meta_table     = GFFormsModel::get_entry_meta_table_name();
		$entry_table    = GFFormsModel::get_entry_table_name();
		$regex          = "/FROM +(?:`?{$entry_table}`?) +AS +`?([^` ]+)`?/i";
		$entry_id_alias = preg_match( $regex, $sql['from'], $matches ) ? $matches[1] : 't1';

		foreach ( $aliases as $field_id => $alias ) {
			// Only add the alias if the query contains the alias SQL.
			if ( strpos( $sql['where'], $alias['sql'] ) === false ) {
				continue;
			}

			$new_join = " LEFT JOIN `{$meta_table}` `{$alias['name']}` ON `{$alias['name']}`.entry_id = `{$entry_id_alias}`.`id` AND `{$alias['name']}`.meta_key = '$field_id'";

			// Don't add the join twice.
			if ( strpos( $sql['join'], $new_join ) !== false ) {
				continue;
			}

			$sql['join'] .= $new_join;
		}

		// This should only run once.
		remove_filter( 'gform_gf_query_sql', [ $this, 'include_join_aliases' ], 25 );

		return $sql;
	}
}