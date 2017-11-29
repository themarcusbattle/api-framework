<?php
/**
 * API Framework.
 *
 * @package API_By_Marcus
 */

/**
 * API_Request Class.
 *
 * Captures details of the incoming request.
 */
class API_Request {

    protected $request;
    
    protected $query;

    protected $method;

	protected $version;

	protected $endpoint;

	/**
	 * The constructor.
	 */
    public function __construct( $request = '' ) {

		// Save all of the initial requests to the API.
        $request_parts  = parse_url( $request );
        $this->request  = isset( $request_parts['path'] ) ? $request_parts['path'] : '';
		$this->query    = isset( $request_parts['query'] ) ? $request_parts['query'] : '';
        $this->method   = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		
		// Parse the required variables.
		$this->version   = $this->parse_version();
		$this->endpoint  = $this->parse_endpoint();
		
    }
    
    public function parse_version() {
    	
    	if ( ! $this->get_request() ) {
    		return '';
    	}
    	
    	$request_parts = explode( '/', substr( $this->request, 1 ) );
    	
    	return $request_parts[0];
    }

    public function parse_endpoint() {
    	
    	if ( ! $this->get_request() || ! $this->get_version() ) {
    		return '';
    	}

        return str_replace( '/' . $this->get_version(), '', $this->get_request() );
    }
	
	public function verify_request( $schema = array() ) {
		
		// Check to see if the API version is defined.
		if ( ! isset( $schema[ $this->get_version() ] ) ) {
			return;
		}
		
		// Check to see if the requested endpoint is valid
		if ( ! $this->endpoint_exists( $schema ) ) {
			return;
		}
		
		return true;
	}
    
    public function endpoint_exists( $schema ) {
    	
    	$endpoints = array_keys( $schema[ $this->get_version() ]['endpoints'] );
    	
    	if ( in_array( $this->endpoint, $endpoints ) ) {
    		return true;
    	}
    	
    	return false;
    }
     
    public function get_request() {
        return $this->request;
    }

	public function get_query() {
		return $this->query;
	}

    public function get_method() {
        return $this->method;
    }
    
    public function get_version() {
        return $this->version;
    }
    
    public function get_endpoint() {
    	return $this->endpoint;
    }
}

/**
 * API_Query Class.
 *
 * Converts the incoming request into a properly formatted query for the database.
 */
class API_Query {
	
	public $request;
	
	protected $resources;

	protected $parameters;

	public function __construct( API_Request $api_request ) {

		$this->request    = $api_request;
		$this->resources  = $this->parse_resources( $api_request );
		$this->parameters = $this->parse_parameters( $api_request );
	}
	
    
    public function parse_resources( $api_request ) {

        $resource_parts = explode( '/', substr( $api_request->get_endpoint(), 1 ) );

        foreach ( array_chunk( $resource_parts, 2 ) as $resource ) {
            $resources[] = array(
                'resource' => isset( $resource[0] ) ? $resource[0] : '',
                'id'       => isset( $resource[1] ) ? $resource[1] : '',
            );
        }

        return $resources;
    }

	public function parse_parameters( $api_request ) {

        $query_parts = explode( '&', $api_request->get_query() );

        $parameters = array();

        foreach ( $query_parts as $query ) {

            $parameter_parts = explode( '=', $query );

            $parameters[] = array(
                'parameter' => $parameter_parts[0],
                'value'     => $parameter_parts[1],
            );
        }
        
        return $parameters;
    }

	public function get_resources() {
		return $this->resources;
	}
	
	public function get_parameters() {
		return $this->parameters;
	}
}

/**
 * API_Database Class.
 *
 * Processes the queries to manipulate or retrieve the requested data. 
 */
class API_Database {
	
	protected $host;
	
	protected $name;
	
	protected $username;
	
	protected $password;
	
	protected $actions;

	public $connection;

	public function __construct() {
		
		$this->actions = array(
			'GET' => array(
				'action'  => 'get',
				'command' => 'SELECT',
			),
			'POST' => array(
				'action'  => 'insert',
				'command' => 'INSERT',
			),
		);

		// Load class-settings-database.php to grab credentials.
 
		$this->host     = 'localhost';
		$this->name     = 'api-test';
		$this->username = 'c3c19e377130';
		$this->password = 'G!v3t8k3';
		$this->charset  = 'utf8mb4';

		try {
			$this->connection = $this->make_connection();
		}
		
		catch ( Exception $e ) {
			echo 'Message: ' .$e->getMessage();
		}

	}
	
	private function make_connection() {
		
		$dsn = sprintf( "mysql:host=%s;dbname=%s;charset=%s", $this->host, $this->name, $this->charset );
		$pdo = new PDO( $dsn, $this->username, $this->password );
		
		if ( ! $pdo instanceof PDO ) {
			throw new Exception( "Database credentials are incorrect. Please check and try your request again." );
		}
		
		return $pdo;
	}
	
	public function parse_method( $method = 'GET' ) {
		return $this->actions[ $method ];
	}

	public function get_results( API_Query $query, $schema ) {
		
		$endpoint        = $query->request->get_endpoint();
		$endpoint_schema = $schema[ $query->request->get_version() ]['endpoints'][ $endpoint ];
		$sql             = $endpoint_schema['queries'][ $query->request->get_method() ];
		
		// Execute the statement. 
		$statement       = $this->connection->prepare( $sql );
		$statement->execute();
		
		$results = $statement->fetchAll(PDO::FETCH_ASSOC);
		
		return $results;
	}
	
}

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
		
		// Verify that the request is valid.
		if ( ! $this->request->verify_request( $this->get_schema() ) ) {
			return array( 'error' => 'This request is invalid. Check the API version or endpoint and try again.' );
		}
		
		// Convert the query into SQL.
		return $this->database->get_results( $this->query, $this->schema );
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

// Define a new API.
$api = new API();

// Query the database for the requested data.
$response = $api->get_response();

// Send the response to the browser.
$api->return_json( $response );
