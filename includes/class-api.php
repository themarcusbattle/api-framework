<?
/**
 * API Class. 
 */
class API {
	
	protected $schema;
	
	/**
	 * The constructor.
	 */
	public function __construct() {
		
		$this->schema   = $this->load_config( 'schema' );
		
		$this->request  = new API_Request( $_SERVER['REQUEST_URI'] );
		$this->query    = new API_Query( $this->request );
		$this->database = new API_Database();

	}
	
	/**
	 * Loads the schema that defines the API version, resources and mapping. 
	 */
	public function load_config( $config_name = '' ) {
		
		$schema_path = dirname( __FILE__ ) . '/' . $config_name . '.json';
		
		if ( ! file_exists( $schema_path ) ) {
			return;
		}
		
		return json_decode( file_get_contents( $schema_path ), true );
		
	}

	public function get_response() { 
		
		// Validate the access token
		if ( ! $this->request->verify_access_token() ) {
			return array(
				'error_description' => 'Your access token is invalid or expired'
			);
		}
		
		// Verify that the request is valid.
		if ( ! $this->request->verify_request( $this->get_schema() ) ) {
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
