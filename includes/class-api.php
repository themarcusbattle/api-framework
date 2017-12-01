<?php
/**
 * API Class.
 *
 * @category Class
 * @package  API
 * @author   Marcus Battle
 */

/**
 * API Class.
 */
class API {

	/**
	 * The API Schema config file.
	 *
	 * @var array
	 */
	protected $schema;

	/**
	 * The database credentials.
	 *
	 * @var array
	 */
	protected $credentials;

	/**
	 * The root directory of this framework.
	 *
	 * @var string
	 */
	public $dir;

	/**
	 * The constructor.
	 */
	public function __construct() {

		// Define all required variables.
		$this->dir         = dirname( dirname( __FILE__ ) ) . '/';
		$this->schema      = $this->load_config( 'schema' );
		$this->credentials = $this->load_config( 'database' );

		// Initialize all class objects.
		$this->database    = new API_Database( $this->get_credentials() );
		$this->router      = new API_Router( $_SERVER['REQUEST_URI'], $this->database, $this );

	}

	/**
	 * Loads the schema that defines the API version, resources and mapping.
	 *
	 * @param string $config_name Name of the configuration file without the extension.
	 */
	public function load_config( $config_name = '' ) {

		$schema_path = $this->dir . 'config/' . $config_name . '.json';

		if ( ! file_exists( $schema_path ) ) {
			return;
		}

		return json_decode( file_get_contents( $schema_path ), true );

	}

	/**
	 * Returns the response.
	 *
	 * @since 1.0.0
	 */
	public function get_response() {

		// Validate the access token.
		if ( ! $this->router->verify_access_token() ) {
			return array( 'error_description' => 'Your access token is invalid or expired' );
		}

		// Verify that the request is valid.
		if ( ! $this->router->verify_request( $this->get_schema() ) ) {
			return array( 'error' => 'This request is invalid. Check the API version or endpoint and try again.' );
		}

		// Convert the query into SQL.
		$data = $this->database->get_results( $this->query, $this->schema );

		return array(
			'meta' => array(
				'code' => 200,
			),
			'data' => $data,
		);
	}

	/**
	 * Returns the schema.
	 *
	 * @since 1.0.0
	 */
	public function get_schema() {
		return $this->schema;
	}

	/**
	 * Returns the credentials.
	 *
	 * @since 1.0.0
	 */
	public function get_credentials() {
		return $this->credentials;
	}

	/**
	 * Returns the JSON data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The requested data.
	 */
	function return_json( $data = array() ) {

		header( 'Content-type: application/json' );

		echo json_encode( $data );

	}
}
