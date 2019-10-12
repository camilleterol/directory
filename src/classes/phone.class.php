<?php
/*
	Classe Phone : hérite de Model.
	Gère les communication entre le code PHP et la table `phones` dans la base de données.
	La plupart des attributs correspondent à des colonnes dans la table.
*/
class Phone extends Model {
	
	private $type;
	
	private $number;
	private $country_code;
	
	private $contact_id;
	
	protected static $table = "phones";		// Nom de la table correspondante à cette classe.
	
	protected static $select = "SELECT `id`, `type`, `number`, `country_code`, `contact_id` FROM"; // requête SELECT correspondant à cette table
	protected static $update_condition = ' AND `contact_id` = :contact_id'; // update condition utilisé par la méthode parente update()
	
	/*
		Le constructeur remplit simplement les attributs de la classe avec les arguments fournis.
	*/
	public function __construct($id, $type, $number, $country_code, $contact_id) {
		if(!empty($number)) { // si le numéro fourni est vide on ne crée pas l'objet.
			$this->id = $id;
			$this->type = $type;
			$this->number = $number;
			$this->country_code = $country_code;
			$this->contact_id = $contact_id;
		} else { // sinon on lève une erreur pour empêcher la création de l'objet
			//throw new Exception('Numéro vide');
		}
	}
	
	/*
		Cette méthode construit la requête SQL utilisée pour récupérer les différentes valeurs dans la table et l'execute.
		Elle retourne le résultat de la requête.
		Voir la classe Address pour une explication plus approfondie.
	*/
	public static function select($where = NULL) {
		$select = self::$select." `".self::$table."` ".(($where != NULL) ? "WHERE $where;" : ";");
		$result = DB::getInstance()->query($select);
		return $result;
	}
	
	/*
		Cette méthode construit la requête SQL utilisé par la classe Result pour faire les recherches et l'execute.
		Elle retourne le résultat de la requête.
		Voir la classe Address pour une explication plus approfondie.
	*/
	public static function search($query) {
		$select = self::$select." `".self::$table."` WHERE `number` LIKE :keyword;";
		$data = array(":keyword" => '%'.$query.'%');
		$result = DB::getInstance()->query($select, $data);
		return $result;
	}
	
	/*
		Voir la classe Address pour une explication plus approfondie des deux classes getInsertValues et getUpdateValues.
	*/
	protected function getInsertValues($query = false) {
		$values = array(
			":id" => null,
			":type" => $this->type,
			":number" => $this->number,
			":country_code" => null,
			":contact_id" => $this->contact_id
		);
		$tmp = array_key_replace($values, ':country_code', 'DEFAULT'); // On remplace :type par DEFAULT pour que le type récupère la valeur par défaut configurée dans la base de données
		if($query) {
			$query = implode(', ', array_keys($tmp));
			return $query;
		}
		unset($tmp['DEFAULT']);
		return $tmp;
	}
	
	protected function getUpdateValues($query = false) {
		$values = array(
			":id" => $this->id,
			":type" => $this->type,
			":number" => $this->number,
			":contact_id" => $this->contact_id
		);
		if($query) {
			$a = preg_replace("/:(\w*)/", "`$1` = :$1", array_keys($values));
			$query = implode(', ', $a);
			return $query;
		}
		return $values;
	}
}