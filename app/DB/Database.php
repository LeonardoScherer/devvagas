<?php 

namespace App\DB;

use \PDO;

class Database{

	/**
	*Host de conexão com o DB
	*@var string
	*/
	const HOST = '127.0.0.1';

	/**
	*nome do DB
	*@var string
	*/
	const NAME = 'dev_vagas';

	/**
	*Usuário do DB
	*@var string
	*/
	const USER = 'root';

	/**
	*Senha de acesso ao DB
	*@var string
	*/
	const PASS = '';

	/**
	*Nome da tabela a ser manipulado
	*@var string
	*/
	private $table;

	/**
	*Instancia de conexão com o DB
	*@var PDO
	*/
	private $connection;

	/**
	*Define a tabela de instancia e conexão
	*@var string $table
	*/
	public function __construct($table = null){
		$this->table = $table;
		$this->setConnection();
	}

	
	/**
	*Método responsável por criar uma conexão com o DB
	*/
	private function setConnection(){
		try {
			$this->connection = new PDO('mysql:host='.self::HOST.';dbname='.self::NAME,self::USER,self::PASS);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			//Nunca expor erro do DB em produção! 
			die("ERROR: ".$e->getMessage());		
		}
	}

	/**
	*Método responsável por executar queries dentro do DB
	*@param string $query
	*@param array $params
	*@return PDOStatement
	*/
	public function execute($query, $params = []){
		try {
			$statement = $this->connection->prepare($query);
			$statement->execute($params);
			return $statement;			
		} catch (PDOException $e) {
			//Nunca expor erro do DB em produção! 
			die("ERROR: ".$e->getMessage());		
		}
	}

	/**
	*Método responsável por inserir dados no db
	*@param array $values [ field => value ]
	*@return integer ID inserido
	*/
	public function insert($values){
		//Dados da query
		$fields = array_keys($values);
		$binds = array_pad([], count($fields), '?');

		//Monta a query
		$query = 'INSERT INTO '.$this->table.' ('.implode(',', $fields).') VALUES ('.implode(',', $binds).')';

		// Executa o insert
		$this->execute($query, array_values($values));

		//Retorna o ID inserido
		return $this->connection->lastInsertId(); 
	}

	/**
	*Método responsavel por executar uma consulta no DB
	*@param string $where
	*@param string $order
	*@param string $limit
	*@param string $fields
	*@return PDOStatement
	*/
	public function select($where = null, $order = null, $limit = null, $fields = '*' ){
		//Dados da query
		$where = strlen($where) ? 'WHERE '.$where : '';
		$order = strlen($order) ? 'ORDER BY '.$order : '';
		$limit = strlen($limit) ? 'LIMIT '.$limit : '';

		$query = 'SELECT '.$fields.' FROM '.$this->table.' '.$where.' '.$order.' '.$limit;

		return $this->execute($query); 
	}

	/**
	 * Método responsável por executar atualizações no DB
	 *@param string $where
	 *@param array $values [ field => value ]
	 *@return boolean
	 */
	public function update($where, $values){
		//Dados da query
		$fields = array_keys($values);

		//Monta query
		$query = 'UPDATE '.$this->table.' SET '.implode('=?,',$fields).'=? WHERE '.$where;
		
		//Executar a query
		$this->execute($query, array_values($values));

		//Executa a query
		return true;

	}

	/**
	 * Método responsável por excluir dados do DB
	 *@return boolean
	 */
	public function delete($where){
		//Monta a query
		$query = 'DELETE FROM '.$this->table.' WHERE '.$where;

		//Executa a query
		$this->execute($query);

		//Retorna sucesso
		return true;
	}

}

?>