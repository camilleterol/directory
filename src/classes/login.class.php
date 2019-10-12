<?php	
/*
	Classe Login : hérite de Page
	Cette classe gère l'authentification.	
*/
class Login extends Page {
	protected static $title = 'Connectez-vous !'; // titre de la page '/login/'
	
	/*
		La methode suivante reçois les données du POST en arguments.
		Retourne des messages d'erreurs si le nom d'utilisateur ou le mot de passe sont vides.
		Connecte l'utilisateur.
	*/
	protected function post($data) {
		if(!isset($data['username']) || empty($data['username'])) {
			return array("error" => 'Vous devez entrer un nom d\'utilisateur !', "error_type" => 'login-message-visible');
		} 
		
		if(!isset($data['password']) || empty($data['password'])) {
			return array("error" => 'Vous devez entrer un mot de passe !', "error_type" => 'login-message-visible', "user" => $data['username']);
		}
		
		$user = new User($data['username'], $data['password']); // Récupère l'instance de la classe User avec laquelle on va se connecter.
		if($data = $user->login()) { // Si l'on peut se connecter avec les données fournies
			if(isset($data["error"])) { // Et que login ne retourne pas de message d'erreur
				return $data;
			}
			if($nick = $user->getNickname()) { // Alors on récupère le surnom
				$session = array( // On crée le tableau que l'on va ensuite insérer dans la variable $_SESSION
					"status" => true,
					"nickname" => $nick
				);
				
				$_SESSION['login'] = $session; // On initialise la variable de session.
				$r = new Request();
				Router::getInstance()->refresh($r); // On rafraichis la page pour se retrouver dans le tableau de bord.
			}
		}
	}
}