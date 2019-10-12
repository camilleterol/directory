<?php
/*
	Classe Singleton : Restreint l'instanciation d'une classe à un seul Objet (c.f. Wikipédia)
	Fonctionne à partir de PHP v5.3
*/
class Singleton {
	/*
		On crée un tableau d'instances pour éviter des problèmes en cas de multiples classes enfant.
		Elle disposeront ainsi chacune d'une instance propre.
	*/
	protected static $instances = array();
	/*
		Empêche l'instanciation d'objets à l'extérieur de la classe.
	*/
	protected function __construct() {
	}
	/*
		Empêche le clonage à l'extérieur de la classe.
	*/
	protected function __clone() {
	}
	/*
		Depuis 5.3 on peut créer un Singleton héritable.
		
		@return Singleton
	*/
	public static function getInstance() {
		// On récupère le nom de la classe dans laquelle on se trouve.
		$c = get_called_class();
		// On vérifie l'existance d'une instance de la classe
		if(!isset(self::$instances[$c])) {
			/* 
				Si aucune instance n'est trouvée, on en crée une.
				On utilise static au lieu de self, self retournerais Singleton, static retourne la classe appelée.
			*/
			self::$instances[$c] = new static;
		}
		// Puis on retourne cette instance.
		return self::$instances[$c];
	}
}
// On n'affiche pas de contenu, il est donc préférable de ne pas fermer le tag PHP