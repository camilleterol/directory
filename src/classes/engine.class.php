<?php
/*
	Classe Engine : hérite de Singleton.
	C'est le moteur de notre site.
	Cette classe instancies les autres classes et s'assure du rendu des pages HTML finales.
*/
class Engine extends Singleton {
	/*
		On défini les diférentes variables statiques utilisé par la classe.
		
		$views_folder contiendra après l'instanciation le chemin vers le dossier contenant nos pages HTML.
		$templates_folder contiendra après l'instanciation le chemin vers le dossier contenant nos templates HTML.
		
		$default_template contient le nom de la page qui sera utilisée comme template par défaut.
		$_404 contient le nom de la page qui sera utilisée comme page d'erreur en cas de page non trouvé. Cela nous permet de créer une page d'erreur qui sera cohérente avec le thème de notre annuaire.
		
		Et les varaibles dynamiques.
		
		$dbh contiendra après l'instanciation le handler PDO connecté à la base de donnée contenant les données de notre annuaire.
		
		$dbh contiendra l'instance de notre parseur qui s'occupera de remplir nos templates avec nos données.
	*/
	private static $views_folder;
	private static $templates_folder;
	private static $default_template = "template.html";
	private static $_404 = "404.html";
	private $dbh;
	private $parser;
	
	/*
		Le constructeur initialise les différents attributs de notre classe.
	*/
	protected function __construct() {
		self::$views_folder = ROOT.'views/';
		self::$templates_folder = ROOT.'templates/';
		$this->dbh = DB::getInstance()->getHandler();
		$this->parser = new Parser();
	}
	
	/*
		Ceci est la méthode principale de la classe Engine.
		Elle récupère la vue correspondante à la requête en cours.
		Puis si un controlleur existe pour notre requête, on l'instancie.
	*/
	public function run() {
		$request = new Request();
		if($view = Router::getInstance()->route($request)) {
			$data = array();
			if($controller = Router::getInstance()->getController($request)) {
				try {
					$obj = new $controller();
					$data = $obj->getData();
				} catch(Exception $e) { // Si il y a un problème un récupère l'exception
				}
			}
			if($view != "NO_VIEW") { // Si notre vue existe
				$this->render(self::$views_folder.$view, $data); // On l'envoie à la méthode render.
			}
		} else {
			$this->render(self::$views_folder.self::$_404); // Sinon en envoie la vue de l'erreur 404.
		}
	}
	
	/*
		Cette méthode combine la page en cours avec la template et les fichiers de dépendence pour composer la page html finale.
	*/
	private function render($page, $data = array()) { // $page est le chemin vers la vue de notre page, $data est optionnel
		$content = file_get_contents($page); // On récupère le fichier de notre page.
		$template = file_get_contents(self::$templates_folder.self::$default_template); // Et le fichier template
		
		$this->parser->loadTemplate($template); // On charge notre template dans le Parseur
		$dependencies = $this->getDependencies($page); // On récupères les dépendances de notre page.
		
		$data["content"] = $content; // On insère le code html dans notre tableau de données
		$data = array_merge($data, $dependencies); // Pareil pour les dépendences
		
		echo $this->parser->compose($data, true); // On intègre tout ça dans notre template. Et on echo pour afficher le code HTML.
	}
	
	/*
		Cette méthode récupère les 'dépendences' (i.e. Fichiers CSS, JS ...) d'une vue à partir de son nom passé en argument.
	*/
	private function getDependencies($view) {
		$dependencies = array(
			"css" => preg_replace("/^".preg_quote(self::$views_folder, '/')."(.*)\.html$/", 'css/$1.css', $view), //On récupère le nom du fichier CSS
			"js" => preg_replace("/^".preg_quote(self::$views_folder, '/')."(.*)\.html$/", 'js/$1.js', $view) //On récupère le nom du fichier JS
		);
		
		foreach($dependencies as $key => $dependency) { // Pour chaques dépendences.
			if(!file_exists(WEBROOT.$dependency)) { // Si le fichier n'existe pas
				unset($dependencies[$key]); // On enlève l'élément du tableau
			} else {
				if($key == "css") { // Si c'est un fichier CSS
					$dependencies[$key] = $this->generateCSSLink("/".$dependencies[$key]); // On récupère le code HTML qui ira dans le head
				} elseif($key == "js") { // Pareil si c'est un JS
					$dependencies[$key] = $this->generateJSLink("/".$dependencies[$key]);
				}
			}
		}
		
		return $dependencies;
	}
	/*
		Les deux fonctions suivantes sont des raccourcis. On leur donne le chemin de nos fichiers CSS/JS et les convertissent en code HTML qui sera placé dans la page affichée afin que le navigateur puisse appeler les fichiers nécessaires.
		TO DO: Combiner ces deux fonctions en une.
	*/
	private function generateCSSLink($path) {
		return '<link rel="stylesheet" media="screen" type="text/css" href="'. $path .'"/>';
	}
	private function generateJSLink($path) {
		return '<script type="text/javascript" src="'. $path .'"></script>';
	}
	
	/*
		La fonction suivante vérifies si l'on est authentifié.
		Elle retourne un booléen qui correspond au statut d'authentification.
		TO DO: Gérer l'authentification 'continue' par cookie.
		TO DO: Peut être intégrer ce code dans l'objet Login. Pour l'instant comme cette fonction doit être accessible depuis deux pages, il est plus simple de la laisser dans cette classe.
	*/
	public static function getStatus() {
		if(isset($_SESSION['login']['status']) && $_SESSION['login']['status'] == true) {
			return true;
		}
		return false;
	}
	
	/*
		Retourne le chemin d'une template, si le fichier existe, en fonction de son nom passé en arguments.
	*/
	public function templatePath($name) {
		$path = self::$templates_folder.$name.".html";
		if(file_exists($path)) {
			return $path;
		}
		return false;
	}
	
	/*
		Retourne le contenu d'une template en fonction de son nom passé en arguments.
	*/
	public function template($name) {
		if($path = $this->templatePath($name)) {
			return file_get_contents($path);
		}
		return false;
	}
	
	/*
		Retourne les données passées en argument échappés.
	*/
	public static function escapeData($data) {
		return nl2br(htmlentities($data, ENT_QUOTES, 'UTF-8'));
	}
}