<?php
/*
	Classe Model.
	C'est la classe qui sert de modèle pour les différentes classes qui communiquent avec des tables de la base de données.
*/
class Model {
	protected $id;
	
	/*
		Cette méthode crée une nouvelle entrée dans la base. Si tout se passe bien elle retourne l'id de la dernière requete.
		Sinon elle retourne faux.
	*/
	public function create() {
		if($values = $this->getInsertValues(true)) {
			$query = 'INSERT INTO `'.static::$table.'` VALUES('.$values.');';
			$data = $this->getInsertValues();
			$result = DB::getInstance()->query($query, $data);
			if($result !== false) {
				$result = DB::getInstance()->lastInsertId();
			}
			return $result;
		}
		return false;
	}
	
	public static function read($id = null) {}
	/*
		Cette méthode mets à jour les données d'une entrée dans la base.
	*/
	public function update() {
		if($values = $this->getUpdateValues(true)) {
			$query = 'UPDATE `'.static::$table.'` SET '.$values.' WHERE `id` = :id'.((isset(static::$update_condition)) ? static::$update_condition : '' ).';'; // Ici on ajoute une condition au WHERE si elle est définie dans la classe fille.
			$data = $this->getUpdateValues();
			$result = DB::getInstance()->query($query, $data, false);
			return $result;
		}
	}
	/*
		Cette méthode supprime une entrée dans la base correspondant à l'id
	*/
	public static function delete($id = null) {
		$query = "DELETE FROM `".static::$table."` WHERE `id` = :id;";
		$data = array(":id" => $id);
		$result = DB::getInstance()->query($query, $data, false);
		return $result;
	}
}