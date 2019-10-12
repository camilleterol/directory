<?php
	/*
		On utilise les variables de Session pour notre annuaire, donc il est nécessaire de dire à PHP que c'est le cas.
	*/
	session_start();
	
	/*
		On définit des constantes qui nous seront utiles pour la navigation dans l'arborescence des fichiers.
		WEBROOT récupère le chemin vers la racine de notre site internet.
		ROOT récupère le chemin vers la racine de tous nos fichiers, classes. ROOT contiens WEBROOT.
	*/
	
	define('WEBROOT', __DIR__.'/');
	define('ROOT', dirname(__DIR__).'/');
	
	/*
		Pour se faciliter la vie lors des chargements de classes, on utilise spl_autoload_register et une fonction anonyme 
		pour permettre à PHP de savoir où sont nos fichiers de classes et comment les appeler si on les instancies.
	*/
	
	spl_autoload_register(function ($class) {
		require_once(ROOT.'classes/'. strtolower($class) . '.class.php');
	});
	
	/*
		Ci-dessous, des fonctions utilse pour certaines parties du programme
	*/
	
	
	/*
		Cette fonction prends en paramètres un tableau, une clé de ce tableau, et une clé avec laquelle remplacer cette clé.
		Elle modifie la clé d'un tableau sans changer l'ordre du tableau.
		Retourne le tableau un fois la clé modifiée.
	*/
	function array_key_replace($array, $old_key, $new_key) {
	    $keys = array_keys($array);
	    if (false !== $index = array_search($old_key, $keys)) {
	    	$keys[$index] = $new_key;
	    }
	    return array_combine($keys, array_values($array));
	}
	
	/*
		Ici on récupère l'instance du singleton Engine, qui est la classe qui gère le moteur de notre annuaire.
		Puis on démarre le moteur.
	*/
	
	$engine = Engine::getInstance();
	$engine->run();
?>