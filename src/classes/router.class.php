<?php
/*
	Classe Router : hérite de Singleton.
	Gère les requêtes et les diriges vers les bonnes pages.
*/
class Router extends Singleton {
	private $routes;				// liste des routes
	private $login;					// chemin de la page d'authenfication
	private $logged_in_default;		// chemin de la page affichée si une requête sur la page d'authentification est faite alors qu'on est déja connecté
	
	/*
		Le contructeur charge les routes : un fichier JSON dans lequel sont contenu les correspondances entre les pages et les URI.
	*/
	protected function __construct() {
		$this->loadRoutes(ROOT.'config/routes');
	}
	
	/*
		La méthode route route les requêtes vers la bonne vue HTML.
	*/
	public function route($request) {
		foreach($this->routes as $key => $route) {
			if($request->matches($route['pattern'])) {
				if(isset($route['goto'])) { // Si la page vers laquelle on essaye d'acceder n'est qu'une redirection, on est redirigé.
					$this->redirect($route['goto']);
					return false;
				}
				if(isset($route['login']) && $route['login'] == true) { // Si on essaye d'accéder à la page d'authenfication alors que l'on est connecté, on est redirigé sur la page par défaut.
					if(Engine::getInstance()->getStatus()) {
						$this->redirect($this->getDefaultLoggedInRoute());
						return false;
					}
				}
				if(isset($route['need_login']) && $route['need_login'] == true) { // Si la page à laquelle on essaye d'accéder nécessite d'être authentifié, on est redirigé sur la page d'authenfication.
					if(!Engine::getInstance()->getStatus()) {
						$this->redirect($this->getLoginRoute());
						return false;
					}
				}
				if(isset($route['view'])) { // Si on est dans aucun des cas ci-dessus, mais que notre page à une vue html, on retourne le chemin de cette vue.
					return $route['view'];
				}
				if(isset($route['controller'])) { // Si il n'y a pas de vue mais qu'un 'controller' existe, on retourne une chaine qui spécifie l'abscence de vue.
					return "NO_VIEW";
				}
			}
		}
		return false; // Sinon on retourne faux.
	}
	
	/*
		Charge le fichier route, et stocke les routes localement.
	*/
	private function loadRoutes($path) {
		$this->routes = json_decode(file_get_contents($path.'.json'), true);
	}
	
	/*
		Retourne la route de la page d'authenfication.
	*/
	private function getLoginRoute() {
		if(!isset($this->login)) {
			foreach($this->routes as $key => $route) {
				if(isset($route['login']) && $route['login'] == true) {
					$this->login = $key;
				}
			}
		}
		return $this->login;
	}
	
	/*
		Retourne la route par défaut quand on est authentifié.
	*/
	private function getDefaultLoggedInRoute() {
		if(!isset($this->logged_in_default)) {
			foreach($this->routes as $key => $route) {
				if(isset($route['logged_in_default']) && $route['logged_in_default'] == true) {
					$this->logged_in_default = $key;
				}
			}
		}
		return $this->logged_in_default;
	}
	
	/*
		Retourne le nom de la classe 'controller'(i.e. Classe hérité de Page) qui correspond à une page.
	*/
	public function getController($request) {
		foreach($this->routes as $key => $route) {
			if($request->matches($route['pattern'])) {
				if(isset($route['controller'])) {
					return $route['controller'];
				}
			}
		}
		return false;
	}
	
	/*
		Redirige la page et renvoie un code 301.
	*/
	private function redirect($uri) {
		header('HTTP/1.1 301 Moved Permanently'); 
		header('Location: '.$uri);
	}
	
	/*
		Redirige la page et renvoie un code 307.
	*/
	public function tempRedirect($uri) {
		header('HTTP/1.1 307 Temporary Redirect'); 
		header('Location: '.$uri);
	}
	
	/*
		'Rafraichis' la page.
	*/
	public function refresh($request) {
		header('Location: '.$request->getURI());
	}
}