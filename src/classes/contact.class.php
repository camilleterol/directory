<?php
/*
	Classe Contact : hérite de Model.
	Gère les communication entre le code PHP et la table `contacts` dans la base de données.
	La plupart des attributs correspondent à des colonnes dans la table.
*/
class Contact extends Model{
	private $first_name;
	private $last_name;
	
	private $type;
	
	private $full_name;
	private $initials;
	
	private $addresses = array();
	private $phones = array();
	private $emails = array();
	
	protected static $table = "contacts";		// Nom de la table correspondante à cette classe.
	protected static $select = "SELECT `id`, `first_name`, `last_name`, CONCAT_WS(' ', `first_name`, `last_name`) AS `full_name`, `type`"; // requête SELECT correspondant à cette table
	
	/*
		Le constructeur remplit les attributs de la classe avec les arguments fournis.
		Il récupère aussi les différentes coordonées reliés à ce contact si $get_details est vrai.
	*/
	public function __construct($id, $first_name, $last_name, $type, $get_details = false) {
		if(!empty($first_name) || !empty($last_name)) { // Si le prénom et le nom sont vide on ne crée pas l'objet.
			$this->id = $id;
			
			$this->first_name = $first_name;
			$this->last_name = $last_name;
			
			$this->type = $type;
			
			$this->full_name = $this->first_name . ' ' . $this->last_name; // crée le nom complet pour simplifier le code en d'autres endroits.
			$this->initials = strtoupper(substr($this->first_name, 0, 1)) . strtoupper(substr($this->last_name, 0, 1)); // récupère les initiales du nom

			if($get_details) { // récupère les différentes coordonnées si $get_details est vrai.
				$this->getDetails();
			}
		} else { // sinon on lève une erreur pour empêcher la création de l'objet
			//throw new Exception('Prénom et Nom vides');
		}
	}
	
	/*
		Cette méthode construit la requête SQL utilisée pour récupérer les différentes valeurs dans la table et l'execute.
		Elle retourne le résultat de la requête.
	*/
	public static function select($where = NULL, $order = "ORDER BY `last_name`, `first_name`") {
		$select = self::$select.", UPPER(CONCAT(SUBSTR(`first_name`, 1, 1),SUBSTR(`last_name`, 1, 1))) AS `initials` FROM `".self::$table."`".(($where != NULL) ? " WHERE $where " : " ").$order.";";
		$result = DB::getInstance()->query($select);
		return $result;
	}
	
	/*
		Cette méthode crée et retourne un objet en fonction d'une requête SQL.
		Arguments : $where est la condition WHERE de SQL qu'on retrouve comme argument dans la méthode Select.
					$as_array nous permet de récuperer directement l'objet sous forme de tableau.
	*/
	public static function selectObject($where, $as_array = false) {
		$result = self::select($where);
		if(isset($result[0])) {
			$contact = new Contact($result[0]['id'], $result[0]['first_name'], $result[0]['last_name'], $result[0]['type'], true);
			if($as_array) {
				$result = $contact->getAsArray();
			} else {
				$result = $contact;
			}	
			return $result;
		}
	}
	
	/*
		Cette méthode construit la requête SQL utilisé par la classe Result pour faire les recherches et l'execute.
		Elle retourne le résultat de la requête.
	*/
	public static function search($query) {
		$select = self::$select." FROM `".self::$table."` WHERE `first_name` LIKE :keyword OR `last_name` LIKE :keyword;";
		$data = array(":keyword" => '%'.$query.'%');
		$result = DB::getInstance()->query($select, $data);
		return $result;
	}
	
	/*
		Cette méthode construit la requête SQL utilisée pour récupérer les différentes valeurs dans la table et l'execute.
		Elle retourne le résultat de la requête.
	*/
	public function getDetails() {						
		$this->addresses = Address::select("`contact_id` = ".$this->id);
		$this->phones = Phone::select("`contact_id` = ".$this->id);
		$this->emails = Email::select("`contact_id` = ".$this->id);
	}
	
	/*
		Retourne le nombre de téléphones
	*/
	public function countPhones() {
		return count($this->phones);
	}
	
	/*
		Retourne le nombre d'emails
	*/
	public function countEmails() {
		return count($this->emails);
	}
	
	/*
		Retourne le nombre d'addresses
	*/
	public function countAddresses() {
		return count($this->addresses);
	}
	
	/*
		Retourne l'objet comme un tableau
	*/
	public function getAsArray() {
		return array(
			"id" => $this->id,
			"first_name" => $this->first_name,
			"last_name" => $this->last_name,
			"full_name" => $this->full_name,
			"initials" => $this->initials,
			"type" => $this->type,
			"addresses" => $this->addresses,
			"phones" => $this->phones,
			"emails" => $this->emails
		);
	}
	
	/*
		Retourne le tableau contenant les addresses
	*/
	public function getAddresses() {
		return $this->addresses;
	}
	
	/*
		Retourne le tableau contenant les téléphones
	*/
	public function getPhones() {
		return $this->phones;
	}
	
	/*
		Retourne le tableau contenant les emails
	*/
	public function getEmails() {
		return $this->emails;
	}
	
	
	/*
		Voir la classe Address pour une explication plus approfondie des deux classes getInsertValues et getUpdateValues.
	*/
	protected function getInsertValues($query = false) {
		if(!empty($this->first_name) || !empty($this->$last_name)) {
			$values = array(
				":id" => null,
				":first_name" => $this->first_name,
				":last_name" => $this->last_name,
				":type" => null
			);
			$tmp = array_key_replace($values, ':type', 'DEFAULT'); // On remplace :type par DEFAULT pour que le type récupère la valeur par défaut configurée dans la base de données
			if($query) {
				$query = implode(', ', array_keys($tmp));
				return $query;
			}
			unset($tmp['DEFAULT']); // on retire DEFAULT du tableau
			return $tmp;
		}
		return false;
	}
	
	protected function getUpdateValues($query = false) {
		if(!empty($this->first_name) || !empty($this->$last_name)) {
			$values = array(
				":id" => $this->id,
				":first_name" => $this->first_name,
				":last_name" => $this->last_name
			);
			if($query) {
				$a = preg_replace("/:(\w*)/", "`$1` = :$1", array_keys($values));
				$query = implode(', ', $a);
				return $query;
			}
			return $values;
		}
		return false;
	}
}