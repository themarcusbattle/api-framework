<?
/**
 * API_Request Class.
 *
 * Captures details of the incoming request.
 */
class API_Router {

	public $api = NULL;

	public $database;

    protected $request;
    
    protected $query;

    protected $method;

	protected $version;

	protected $endpoint;

	protected $access_token;

	/**
	 * The constructor.
	 */
    public function __construct( $request = '', API_Database $database = NULL, API $api = NULL ) {
	
		$this->api          = $api;
		$this->database     = $database;

		// Save all of the initial requests to the API.
        $request_parts      = parse_url( $request );
        $this->request      = isset( $request_parts['path'] ) ? $request_parts['path'] : '';
		$this->query        = isset( $request_parts['query'] ) ? $request_parts['query'] : '';
        $this->method       = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		$this->access_token = isset( $_REQUEST['access_token'] ) ? $_REQUEST['access_token'] : '';

		// Parse the required variables.
		$this->version      = $this->parse_version();
		$this->endpoint     = $this->parse_endpoint();
		$this->resources    = $this->parse_resources();
		$this->parameters   = $this->parse_parameters();
		
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
    
    public function verify_access_token() {
    	
    	if ( ! $access_token = $this->get_access_token() ) {
    		return;
    	}
    	
    	$args = array(
    		'access_token' => $access_token
    	);

    	$result = $this->database->get( 'tokens', $args );
    	
    	if ( ! $result ) {
    		return;
    	}
    	
    	// @todo: Set user ID
		$this->set_active_user( $result );

    	return true;
    }
	
	public function set_active_user( $token = array() ) {
		
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
    
    public function parse_resources() {

        $resource_parts = explode( '/', substr( $this->get_endpoint(), 1 ) );

        foreach ( array_chunk( $resource_parts, 2 ) as $resource ) {
            $resources[] = array(
                'resource' => isset( $resource[0] ) ? $resource[0] : '',
                'id'       => isset( $resource[1] ) ? $resource[1] : '',
            );
        }

        return $resources;
    }

	public function parse_parameters() {

        $query_parts = explode( '&', $this->get_query() );

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
    
    public function get_access_token() {
    	return $this->access_token;
    }
}

