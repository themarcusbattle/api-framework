<?
/**
 * API_Query Class.
 *
 * Converts the incoming request into a properly formatted query for the database.
 */
class API_Query {
	
	public $api;
	
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

