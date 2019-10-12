<?php
/*
	Classe Page.
	Cette classe gère les données de la page et tout ce qui doit être calculé avant l'affichage.
	Reçois les requêtes POST, GET
*/
class Page {
	protected static $title; 						// Titre de notre page
	protected static $title_separator = "  |  "; 	// Séparateur entre le titre global et le titre des pages
	protected $dependencies; 						// Différents fichiers dont à besoin la page pour s'afficher correctement (CSS, JS ...)
	protected $request;								// La requete contenant l'URL qui à mené à cette page.
	protected $data;								// Les données de la page qui seront ensuite utilisées par le parseur.
	
	/*
		Le contructeur de notre classe initialise le titre de la page s'il est défini.
		Si des données en POST ou en GET ont été reçues, on les envoies aux méthodes correspondantes.
		Si la méthode init existe alors on l'execute.	
	*/
	public function __construct() {
		$this->request = new Request();
		$this->data = array();
		if($title = $this->getTitle()) {
			$this->data["title"] = $title;
		}
		if($data = $this->request->get()) {
			$this->data = array_merge($this->data, $this->get($data));
		}
		if($data = $this->request->post()) {
			$this->data = array_merge($this->data, $this->post($data));
		}
		if(method_exists($this, "init")) {
			$data = $this->init();
			if($data != NULL && is_array($data)) {	
				$this->data = array_merge($this->data, $data);
			}
		}
	}
	/*
		Cette méthode retourne le titre de la page, s'il existe, concaténé au séparateur de titre.
	*/
	private function getTitle() {
		if(isset(static::$title)) {
			return self::$title_separator.static::$title;
		}
		return false;
	}
	
	/*
		Cette méthode retourne les données de la page.
	*/
	public function getData() {
		return $this->data;
	}
	
	/*
		Les deux méthodes suivantes sont définies dans les classes filles si la page est censée recevoir des données POST ou GET.
	*/
	
	protected function get($data) {}
	
	protected function post($data) {}
}