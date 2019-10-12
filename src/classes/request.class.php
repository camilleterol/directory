<?php
/*
	Classe Request.
	Récupère l'URI de la requête en cours.
	Peut être crée avec une valeur d'URI arbitraire.
*/
class Request {
	private $uri;
	
	/*
		Le constructeur prends une URI en arguments.
		Si aucune URI n'es fournie lors de l'instanciation on récupère celle de la requête en cours.
	*/
	public function __construct($uri = null) {
		if($uri != null) {
			$this->uri = $uri;
		} else {
			$this->uri = $_SERVER['REQUEST_URI'];
		}
	}
	
	/*
		Retourne l'URI.
	*/
	public function getURI() {
		return $this->uri;
	}
	
	/*
		Vérifie si le motif fourni corresponds à l'adresse.
		Retourne soit le retour de la fonction de preg_match ou retourne les valeurs trouvées par l'expression régulière en fonction de la valeur de $return_matches
	*/
	public function matches($pattern, $return_matches = false) {
		$return = preg_match('#^'.$pattern.'#', $this->uri, $matches);
		if($return_matches) {
			return $matches;
		}
		return $return;
	}
	
	/*
		Retourne les données GET.
	*/
	public function get() {
		if($_GET) {
			return $_GET;
		}
		return false;
	}
	
	/*
		Retourne les données POST.
	*/
	public function post() {
		if($_POST) {
			return $_POST;
		}
		return false;
	}
}