<?php
/**
 * API_Database Class.
 *
 * @category Class
 * @package  API
 * @author   Marcus Battle
 */

/**
 * API_Database Class.
 *
 * Processes the queries to manipulate or retrieve the requested data.
 */
class API_Database {

	/**
	 * The database credentials.
	 *
	 * @var array
	 */
	protected $credentials;

	protected $host;
	
	protected $name;
	
	protected $username;
	
	protected $password;
	
	protected $actions;

	public $connection;

	public function __construct( $credentials = array() ) {
		
		$this->set_credentials( $credentials );

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
	
		try {
			$this->connection = $this->make_connection();
		}
		
		catch ( Exception $e ) {
			echo 'Message: ' .$e->getMessage();
		}

	}
	
	public function set_credentials( $credentials = array() ) {

		$default_credential_args = array(
			'host'      => '',
			'name'      => '',
			'username'  => '',
			'password'  => '',
			'charset'   => 'utf8mb4'
		);

		$this->credentials = array_merge( $default_credential_args, $credentials );
	}

	private function make_connection() {
		
		$dsn = sprintf( "mysql:host=%s;dbname=%s;charset=%s", $this->credentials['host'], $this->credentials['name'], $this->credentials['charset'] );
		$pdo = new PDO( $dsn, $this->credentials['username'], $this->credentials['password'] );
		
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

