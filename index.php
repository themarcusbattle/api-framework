<?php
/**
 * API Generator.
 *
 * @package API_By_Marcus
 */

class API_Request {

    public $request;

    public $method;

    public $queries;

    public $resources;

    public function __construct( $request = '' ) {

        $request_parts = parse_url( $request );

        $this->parse_request( $request_parts );
        $this->parse_query( $request_parts );
        $this->parse_endpoint( $request_parts );

        $this->method  = $_SERVER['REQUEST_METHOD'];

    }

    public function parse_request( $request_parts = array() ) {
        $this->request = isset( $request_parts['path'] ) ? $request_parts['path'] : '';
    }

    public function parse_query( $request_parts = array() ) {

        $query       = isset( $request_parts['query'] ) ? $request_parts['query'] : '';
        $query_parts = explode( '&', $query );

        $queries = array();

        foreach ( $query_parts as $part ) {

            $parts = explode( '=', $part );

            $queries[] = array(
                'parameter' => $parts[0],
                'value'     => $parts[1],
            );
        }
        
        $this->queries = $queries;
    }

    public function parse_endpoint( $request_parts = array() ) {

        $endpoint_parts = explode( '/', substr( $request_parts['path'], 1 ) );

        foreach ( array_chunk( $endpoint_parts, 2 ) as $resource ) {
            $resources[] = array(
                'resource' => isset( $resource[0] ) ? $resource[0] : '',
                'id'       => isset( $resource[1] ) ? $resource[1] : '',
            );
        }

        $this->resources = $resources;
    }

    public function get_request() {
        return $this->request;
    }

    public function get_method() {
        return $this->method;
    }
}

$api_request = new API_Request( $_SERVER['REQUEST_URI'] );

print_r( $api_request ); exit;

 /**
  * Parses the API request and prepares it to be processed by the router.
  */
function parse_request() {

    if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
        return false;
    }

   $uri_parts = parse_url( $_SERVER['REQUEST_URI'] );
   $endpoint  = isset( $uri_parts['path'] ) ? $uri_parts['path'] : '';
   $query     = isset( $uri_parts['query'] ) ? $uri_parts['query'] : '';

    // Remove extraneous values.
    $parsed_request = explode( '/', $endpoint, 3 );
    array_shift( $parsed_request );
	
	if ( 2 > count( $parsed_request ) ) {
		return array(
			'error' => "Your endpoint isn't properly formatted"
		);
	}
	
    // Convert the API call into the proper format.
    $api_call_keys   = array( 'version', 'endpoint' );
    $parsed_request  = array_combine( $api_call_keys, $parsed_request );

    // Prepare the request.
    $endpoint_parts = explode( '/', $parsed_request['endpoint'] );
	parse_str( $query, $query_parts );
	print_r( $query_parts ); exit;
	foreach ( array_chunk( $endpoint_parts, 2 ) as $resource ) {
		$resources[] = array(
			'resource' => isset( $resource[0] ) ? $resource[0] : '',
			'id'       => isset( $resource[1] ) ? $resource[1] : '',
		);
	}
	
	foreach ( array_chunk( $query_parts, 2 ) as $query ) {
		$queries[] = array(
			'param' => isset( $query[0] ) ? $query[0] : '',
			'value' => isset( $query[1] ) ? $query[1] : '',
		);
	}
	
	$parsed_request['endpoint'] = array( 
        'resources' => $resources,
        'queries'   => $queries,
    );


    // Set the REQUEST method.
    $parsed_request['method'] = $_SERVER['REQUEST_METHOD'];
	
    return( $parsed_request );
}

/**
 * Detect the version number from the API request
 */
function parse_api_version( $api_request ) {
    return 'v1';
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

$data = parse_request();
return_json( $data );