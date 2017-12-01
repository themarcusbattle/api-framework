<?
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

	public function __construct( $credentials = array() ) {
		
		print_r( $credentials ); exit;
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

