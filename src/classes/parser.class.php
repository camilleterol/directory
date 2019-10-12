<?php
/*
	Classe Parser.
	Gère le rempllissage des templates avec nos données.
	Cette classe instancies les autres classes et s'assure du rendu des pages HTML finales.
	TODO: gérer les balises blocks.
*/
class Parser {
	private $template;			// La template à remplir
	private $composited_page;	// La page HTML finale
	private static $ks = ".";	// Le séparateur entre deux niveaux de profondeur des clés des balises (i.e. {{address.street}})
	
	/*
		Le constructeur de notre classe prends une template en argument, et initialise le tableau qui contiendra nos balises.
	*/
	public function __construct($template = NULL) {
		$this->tags = array();
		if($template != NULL) {
			$this->loadTemplate($template);
		}
	}
	
	/*
		La méthode loadTemplate vide le tableau des balises, et charge la template dans l'attribut correspondant.
	*/
	public function loadTemplate($template = NULL) {
		if($template != NULL) {
			if(isset($this->tags)) {
				$this->tags = array();
			}
			$this->template = $template;
		}
	}
	
	/*
		La méthode replace est la méthode principale de cette classe.
		Elle prends en arguments le code html à remplir, et un tableau de données.
		Cette méthode fait la correspondance entre les valeurs à remplir et celle du tableau.
	*/
	private function replace($page, $data) {
		$page = preg_replace_callback("/([ \t]*)({{([a-zA-Z0-9_\.]*)}})(\n)?/", function($matches) use ($data) { // Cette expression régulière conserve le niveau d'indentation de la balise remplacée.
			//$matches[1] = niveau d'indentation
			//$matches[2] = balise
			//$matches[3] = nom de la balise
			//$matches[4] = retour de chariot après tag
			$tag_name = $matches[3];
			if(false !== $value = $this->getValue($tag_name, $data)) { // get value s'occupe de récupérer la valeur de notre balise même dans un tableu multidimensionnel.
				if($tag_name == "address.street") { // dans le cas particulier de address.street (qui se retrouve dans un textarea) on remplace \r\n par un retour chariot \r.
					$value = str_replace("\r\n", "\r", $value); // cela permet que les données s'affichent correctement dans le textarea - TODO: trouver une solution plus élégante.
				}
				$str = $matches[1].str_replace("\n", "\n".$matches[1], $value).((isset($matches[4])) ? $matches[4] : ""); // remplace les sauts de lignes par un saut de ligne et notre niveau d'indentation pour que le code conserve le niveau d'indentation même une fois passé à travers le parseur.
				return $this->replace($str, $data);
			}
			return $matches[0];
		}, $page);
		return $page;
	}
	
	/*
		Cette méthode est un raccourci vers replace.
		L'attibut $strip nous permet de choisir si l'on souhaite conserver ou pas les balises qui n'ont pas de données définies dans le tableau de données.
	*/
	public function compose($data, $strip = false) {
		$this->composited_page = $this->replace($this->template, $data);
		if($strip == true) {
			return $this->stripTags();
		} else {
			return $this->composited_page;
		}
		return false;
	}
	
	/*
		Cette méthode retire les balises de la template. 
	*/
	public function stripTags() {
		$this->composited_page = preg_replace("/([ \t]*)({{([a-zA-Z0-9_\.]*)}})(\n)?/", '', $this->composited_page);
		return $this->composited_page;
	}
	
	/*
		Méthode récursive qui récupère la valeur de notre balise dans un tableau multidimensionnel en utilisant le nom de la balise (i.e. contact.addresses.count)
	*/
	private function getValue($key, $data) {
		if(strpos($key, self::$ks) !== false) { // si notre nom de balise contient le séparateur de niveau (i.e. '.')
			$key_array = explode(self::$ks, $key); // on éclate notre nom de balise dans un tableau
			$key = array_shift($key_array); // on récupère la première valeur et on la sort du tableau
			if(isset($data[$key])) { // si des données correspondent au nom de notre balise
				return $this->getValue(implode(".", $key_array), $data[$key]); //on appelle de nouveau la méthode, avec le nom de balise raccourci d'un element, et le tableau avec un niveau en moins.
			}
		}
		if(isset($data[$key])) { // si notre nom de balise ne contient pas de séparateur, alors c'est qu'il n'est qu'a un seul niveau. On vérifie la correspondance nom/donnée
			return $data[$key];	// on retourne la valeur correspondante
		}
		return false; // si il n'y a pas correspondance on retourne faux.
	}
}