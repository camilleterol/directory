<?php
/*
	Classe User : hérite de Model.
	Gère les communication entre le code PHP et la table `users` dans la base de données.
	La plupart des attributs correspondent à des colonnes dans la table.
*/
class User extends Model {
	
	private $username;
	private $password;
	
	private $hash;
	
	private $nickname;
	
	private static $table = "users";		// Nom de la table correspondante à cette classe.
	
	/*
		Le constructeur remplit simplement les attributs de la classe avec les arguments fournis.
	*/
	public function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	
	/*
		Cette méthode contrôle l'existance d'un utilisateur dans la base de données
	*/	
	private function exists() {
		$query = "SELECT 1 FROM `".self::$table."` WHERE `username` = :username;";
		$stmt = DB::getInstance()->getHandler()->prepare($query);
		$stmt->execute(array(':username' => $this->username));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if(isset($result[1]) && $result[1] == 1) { // Si notre retour est égal à un (on a choisis de faire une requête ou l'on fais un SELECT 1)
			return true;
		}
		return false;
	}
	
	/*
		La méthode suivante vérifie la correspondance entre le mot de passe et le hash stocké en base de données
	*/	
	private function verify() {
		$query = "SELECT `hash` FROM `".self::$table."` WHERE `username` = :username;";
		$stmt = DB::getInstance()->getHandler()->prepare($query);
		$stmt->execute(array(':username' => $this->username));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if(isset($result['hash']) && password_verify($this->password, $result['hash'])) {// Si le retour de la requête correspond au mot de passe
			return true;
		}
		return false;
	}
	
	/*
		Cette méthode retourne vrai si l'utilisateur existe et que son mot de passe est correct.
		Sinon elle retourne le message d'erreur approprié.
	*/	
	public function login() {
		if(!$this->exists()) {
			return array("error" => 'Nom d\'utilisateur invalide !', "error_type" => 'login-message-visible');
		}
		if(!$this->verify()) {
			return array("error" => 'Nom d\'utilisateur ou mot de passe invalide !', "error_type" => 'login-message-visible', "user" => $this->username);
		}
		return true;
	}
	
	/*
		La méthode suivante récupère le surnom stocké dans la base de données.	
	*/	
	public function getNickname() {
		if(!isset($this->nickname)) {				
			$query = "SELECT `nickname` FROM `".self::$table."` WHERE `username` = :username;";
			$stmt = DB::getInstance()->getHandler()->prepare($query);
			$stmt->execute(array(':username' => $this->username));
			if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$this->nickname = $result['nickname'];
			} else {
				return false;
			}
		}
		return $this->nickname;
	}
}