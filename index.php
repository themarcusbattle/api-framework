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
	
	protected $resources;

	protected $parameters;

	public function __construct( API_Request $api_request ) {

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

	public function prepare( API_Query $query, $action ) {
		
		$sql = $action['command'];
		
		// Loop through each resource to build sql statement.
		foreach ( $query->get_resources() as $index => $resource ) {
			
			if ( 0 === $index ) {
				
				switch ( $action['command'] ) {
					
					case 'SELECT':
						$sql .= " * FROM " . $resource['resource'] . " WHERE id = '" . $resource['id'] . "'";
				}
				
			} else {
				
			}

		}
		
		 echo $sql;
	}
	
}

/**
 * API Class. 
 */
class API {
	
	/**
	 * The constructor.
	 */
	public function __construct() {
		
		$this->load_schema();
		
		$this->request  = new API_Request( $_SERVER['REQUEST_URI'] );
		$this->query    = new API_Query( $this->request );
		$this->database = new API_Database();

	}
	
	/**
	 * Loads the schema that defines the API version, resources and mapping. 
	 */
	public function load_schema() {
		
		$schema_path = dirname( __FILE__ ) . '/schema.json';
		
		if ( ! file_exists( $schema_path ) ) {
			return;
		}
		
		$schema = json_decode( file_get_contents( $schema_path ), true );
		echo "<pre>"; print_r( $schema ); exit;
	}

	public function get_response() { 
	
		// Determine database action.
		$action = $this->database->parse_method( $this->request->get_method() );
		
		// Convert the query into SQL.
		$sql  = $this->database->prepare( $this->query, $action );
		
		// 3. Run the query
		// $this->database->$action( $sql );
		$data = array( 'something' => 'big' );
		
		return $data;
	}
	
	public function create() { }
	
	public function read() { }
	
	public function update() { }
	
	public function delete() { }

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

$api = new API();

/* echo '<pre>';
print_r( $api ); */

// Query the database for the requested data.
$response = $api->get_response();

// Send the response to the browser.
$api->return_json( $response );


