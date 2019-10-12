<?php
/*
	Classe Address : hérite de Model.
	Gère les communication entre le code PHP et la table `addresses` dans la base de données.
	La plupart des attributs correspondent à des colonnes dans la table.
*/
class Address extends Model {
	
	private $type;
	
	private $street;
	private $zip;
	private $city;
	
	private $contact_id;
	
	protected static $table = "addresses";		// Nom de la table correspondante à cette classe.
	
	protected static $select = "SELECT `id`, `type`, `street`, `zip`, `city`, `contact_id` FROM"; // requête SELECT correspondant à cette table
	protected static $update_condition = ' AND `contact_id` = :contact_id'; // update condition utilisé par la méthode parente update()
	
	
	/*
		Le constructeur remplit simplement les attributs de la classe avec les arguments fournis.
	*/
	public function __construct($id, $type, $street, $zip, $city, $contact_id) {
		if(!empty($street) && !empty($zip) && !empty($city)) { // vérifie si les données ne sont pas vide. La rue ET le code postal ET la ville sont nécessaire pour créer une addresse.
			$this->id = $id;
			$this->type = $type;
			$this->street = $street;
			$this->zip = $zip;
			$this->city = $city;
			$this->contact_id = $contact_id;
		} else { // sinon on lève une erreur pour empêcher la création de l'objet
			//throw new Exception('Rue ou CP ou Ville vide');
		}
	}
	
	/*
		Cette méthode construit la requête SQL utilisée pour récupérer les différentes valeurs dans la table et l'execute.
		Elle retourne le résultat de la requête.
	*/
	public static function select($where = NULL) { // l'attribut $where nous permets de spécifier une condition
		$select = self::$select." `".self::$table."` ".(($where != NULL) ? "WHERE $where;" : ";"); // si il n'y a pas de condition, on ajoute pas le WHERE
		$result = DB::getInstance()->query($select);
		return $result;
	}
	
	/*
		Cette méthode construit la requête SQL utilisé par la classe Result pour faire les recherches et l'execute.
		Elle retourne le résultat de la requête.
	*/
	public static function search($query) { // $query contient le terme à rechercher
		$select = self::$select." `".self::$table."` WHERE `street` LIKE :keyword OR `zip` LIKE :keyword OR `city` LIKE :keyword;";
		$data = array(":keyword" => '%'.$query.'%'); // on utilise les fonctionnalités de PDO pour échaper les termes à rechercher. Donc on crée un tableau de valeurs qui sera ensuite passé dans la méthode PDOStatement::execute()
		$result = DB::getInstance()->query($select, $data);
		return $result;
	}
	
	/*
		Les deux méthodes suivantes sont similaires.
		En fonction de l'argument query, elles retournent soit un tableau contenant les valeurs à insérer dans la requête.
		Soit une portion de la requête directement.
	*/
	protected function getInsertValues($query = false) {
		$values = array( // tableau de valeurs a envoyer dans la requête.
			":id" => null,
			":type" => $this->type,
			":street" => $this->street,
			":zip" => $this->zip,
			":city" => $this->city,
			":contact_id" => $this->contact_id
		);
		if($query) { // si on veut récupérer la requête
			$query = implode(', ', array_keys($values)); // on récupère le tableau des clés, et on les joint en chaine de caractères séparés par une virgule.
			return $query; // on retourne la requête
		}
		return $values; // sinon on retourne le tableau de valeurs.
	}
	
	/*
		Voir méthode getInsertValue().
	*/
	protected function getUpdateValues($query = false) {
		$values = array(
			":id" => $this->id,
			":type" => $this->type,
			":street" => $this->street,
			":zip" => $this->zip,
			":city" => $this->city,
			":contact_id" => $this->contact_id
		);
		if($query) {
			/*
				la seule différence ici est qu'on construit la requête update, 
				donc on récupère la clé et la transforme en une partie de la requête grâce à preg_replace
				(i.e. :clé -> `clé` = :clé)
			*/
			$a = preg_replace("/:(\w*)/", "`$1` = :$1", array_keys($values));
			$query = implode(', ', $a); // comme précédent on joint toutes les clés pour faire la requête
			return $query;
		}
		return $values;
	}
}