<?
/**
 * API Class. 
 */
class API {

	/**
	 * The API Schema config file.
	 */
	protected $schema;
	
	/**
	 * The database credentials.
	 */
	protected $credentials;

	/**
	 * The constructor.
	 */
	public function __construct() {
		
		$this->schema      = $this->load_config( 'schema' );
		$this->credentials = $this->load_config( 'database' );
		print_r($this->credentials); exit;
		$this->database    = new API_Database( $this->get_credentials() );
		$this->router      = new API_Router( $_SERVER['REQUEST_URI'], $this->database, $this );
		// $this->query    = new API_Query( $this->request);

	}
	
	/**
	 * Loads the schema that defines the API version, resources and mapping. 
	 */
	public function load_config( $config_name = '' ) {
		
		$schema_path = dirname( __FILE__ ) . '/config/' . $config_name . '.json';
		
		if ( ! file_exists( $schema_path ) ) {
			return;
		}
		
		return json_decode( file_get_contents( $schema_path ), true );
		
	}

	public function get_response() { 
		
		// Validate the access token
		if ( ! $this->router->verify_access_token() ) {
			return array(
				'error_description' => 'Your access token is invalid or expired'
			);
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
			'data' => $data 
		);
	}
	
	public function get_schema() {
		return $this->schema;
	}
	
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
