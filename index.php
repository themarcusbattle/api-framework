<?php
/**
 * API Generator.
 *
 * @package API_By_Marcus
 */

 /**
  * Parses the API request and prepares it to be processed by the router.
  */
function parse_request() {

    if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
        return false;
    }

    // Remove extraneous values.
    $parsed_request = explode( '/', $_SERVER['REQUEST_URI'], 3 );
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
//     $resources = array_chunk( $endpoint_parts, 2 );
	
	foreach ( array_chunk( $endpoint_parts, 2 ) as $resource ) {
		$resources[] = array(
			'resource' => isset( $resource[0] ) ? $resource[0] : '',
			'id'       => isset( $resource[1] ) ? $resource[1] : '',
		);
	}
	
	$parsed_request['endpoint'] = $resources;
	/* echo '<pre>';
	print_r( $resources );
	echo "</pre>";
	exit; */
//     $request_keys = array( 'resource', 'parameters' );
/*     $paresed_request['endpoint'] = array_combine( $request_keys, $request ); */

    // Set the REQUEST method.
    $parsed_request['method'] = $_SERVER['REQUEST_METHOD'];
	
    return( $parsed_request );
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

    echo json_encode( $data, JSON_FORCE_OBJECT );

}

$data = parse_request();
return_json( $data );