<?php
/*
	Classe DB : hérite de Singleton.
	C'est la classe qui gère la connection et la communication avec la base de donnée.
*/
class DB extends Singleton {
	/*
	 * Lorsque le nom d'hôte est 'localhost', la connexion est faite par un socket Unix (c.f. Documentation PHP)
	 * Ici on spécifie les différentes variables pour se connecter à la base de donnée du projet
	 */
	
	private $dsn = null;
	
	private static $host = 'db';
	private static $user = 'directory';
	private static $password = 'directory';
	
	private static $dbname = 'directory';
	
	private $handler = null;
	/*
		Lors de la création de l'instance, on initialise le DSN de PDO avec les attributs spécifiés ci-dessus.
		Charset permet d'avoir un encodage cohérent avec la base de donnée, PHP et les pages HTML.
	*/
	protected function __construct() {
		$this->dsn = 'mysql:host='.self::$host.';dbname='.self::$dbname.';charset=utf8';
		//On appelle la fonction connect
		$this->connect();
	}
	/*
		La méthode connect, crée un object PDO connecté à la base de donnée et vérifie la bonne connexion
	*/
	private function connect() {
		try {
			// On instancie la classe PDO avec les identifiants de notre base de donnée en arguments et on enregistre l'objet retourné.
			$this->handler = new PDO($this->dsn, self::$user, self::$password);		
		} catch(PDOException $e) { // Si une exception est levée
			// On affiche le message d'erreur
			echo 'Echec de connexion : ', $e->getMessage(), "\n";
			// On retourne faux et on sort de la fonction, la connexion n'a pas eu lieu
			return false;
		}
		// Si tout c'est bien passé on retourne vrai
		return true;
	}
	/*
		Le getter pour récupérer le Handler de la connection MySQL
	*/
	public function getHandler() {
		return $this->handler;
	}
	/*						
		La méthode query est un raccourci vers la méthode query de la classe PDO.
		Retourne soit une ligne de la base de donnée. Soit un tableau de l'ensemble des résultat.
		Arguments : $query  - Requête à effectuer
					$data   - Données liées à cette requête
					$select - Requête de type select ? (retour de données)
					$all    - Retourne toutes les données ou une seule ligne ?
	*/
	public function query($query, $data = NULL, $select = true, $all = true) {
		$stmt = $this->handler->prepare($query);
		$result = $stmt->execute($data);
		if($result && $select && $all) {
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		} elseif($result && $select) {
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		return $result;
	}
	/*
		La méthode getEnumValues récupère les différentes valeurs Enum d'une colonne d'une table.
		Prends en arguments le nom d'une table, et d'une colonne.
		Retourne les différents valeurs de l'Enum
	*/
	public function getEnumValues($table, $field) {
	    $rows = $this->query("SHOW COLUMNS FROM ".$table." WHERE Field = '".$field."'");
	    $row = $rows[0];
	    preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
	    $enum = explode("','", $matches[1]);
	    return $enum;
	}
	/*
		Cette méthode retourne l'id de la dernière ligne insérée dans la base de donnée
	*/
	public function lastInsertId() {
		$return = $this->handler->query('SELECT LAST_INSERT_ID() AS `id`;');
		$return = $return->fetch(PDO::FETCH_ASSOC);
		return $return['id'];
	}
}