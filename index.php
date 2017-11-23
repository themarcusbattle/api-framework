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
    $parsed_reuqest = explode( '/', $_SERVER['REQUEST_URI'], 3 );
    array_shift( $parsed_reuqest );

    // Convert the API call into the proper format.
    $api_call_keys   = array( 'version', 'endpoint' );
    $paresed_request = array_combine( $api_call_keys, $parsed_reuqest );

    // Prepare the request.
    $request = $paresed_request['endpoint'];
    $request = explode( '/', $request, 2 );

    $request_keys = array( 'resource', 'parameters' );
    $paresed_request['endpoint'] = array_combine( $request_keys, $request );

    // Set the REQUEST method.
    $paresed_request['method'] = $_SERVER['REQUEST_METHOD'];

    return( $paresed_request );
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