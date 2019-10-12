<?php
/*
	Classe Email : hérite de Model.
	Gère les communication entre le code PHP et la table `emails` dans la base de données.
	La plupart des attributs correspondent à des colonnes dans la table.
*/
class Email extends Model {
	
	private $type;
	
	private $email;
	
	private $contact_id;
	
	protected static $table = "emails";		// Nom de la table correspondante à cette classe.
	
	protected static $select = "SELECT `id`, `type`, `email`, `contact_id` FROM"; // requête SELECT correspondant à cette table
	protected static $update_condition = ' AND `contact_id` = :contact_id'; // update condition utilisé par la méthode parente update()
	
	/*
		Le constructeur remplit simplement les attributs de la classe avec les arguments fournis.
	*/
	public function __construct($id, $type, $email, $contact_id) {
		if(!empty($email)) { // si l'email fourni est vide on ne crée pas l'objet.
			$this->id = $id;
			$this->type = $type;
			$this->email = $email;
			$this->contact_id = $contact_id;
		} else { // sinon on lève une erreur pour empêcher la création de l'objet
			//throw new Exception('Email vide');
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
		$select = self::$select." `".self::$table."` WHERE `email` LIKE :keyword;";
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
			":email" => $this->email,
			":contact_id" => $this->contact_id
		);
		if($query) {
			$query = implode(', ', array_keys($values));
			return $query;
		}
		return $values;
	}
	
	protected function getUpdateValues($query = false) {
		$values = array(
			":id" => $this->id,
			":type" => $this->type,
			":email" => $this->email,
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