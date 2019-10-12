<?php
class Result extends Page {
	protected static $title = 'Recherchez un nom, une adresse, un téléphone, ...';
	private $contacts;
	
	protected function post($data) {
		$start = microtime(true); // On lance un timer pour savoir combien de temps prends la recherche.
		$return = array();
		if(isset($data['q'])) { // Si un terme a été posté (on s'assure qu'on est pas arrivé sur cette page en postant n'importe quoi).
			$return['search']['query'] = $data['q']; // On met le terme recherché dans le tableau de valeurs retournées
			$return = array_merge($return, $this->search($data['q'])); // On lance la recherche.
		}
		$end = microtime(true); // On arrête le timer
		$return['search']['time'] = number_format($end - $start, 5, ',', ' ') . ' secondes'; // On formate le temps de façon à le rendre lisible
		if(isset($return['results']) && $return['results-nb'] > 0) { // Si il y a des résultats à notre recherche.
			/*
				On crée une chaine de caractères qui contient le terme recherché, le nombre de résultats et le temps nécessaire à la recherche.
			*/
			$nb = $return['results-nb'];
			$return['search']['string'] = 'Résultats de la recherche "'.$return['search']['query'].'" ('.$nb.' résultat'.(($nb > 1) ? 's' : '').' trouvé'.(($nb > 1) ? 's' : '').' en '.$return['search']['time'].')';
		} else { // Sinon on retourne une chaine de caractère qui nous dit qu'il n'y a pas de résultats.
			$return['search']['string'] = 'Aucun résultat trouvé (requête terminée en '.$return['search']['time'].')';
		}
		return $return;
	}
	
	/*
		Cette méthode recherche successivement dans la table des contacts, adresses, téléphones et emails le terme recherché.
		Pour chaque recherche on récupère un objet contact.
	*/
	private function search($q) {
		if($q === "") { // Si le terme de recherche n'est pas une chaine vide
			return false;
		}
		$this->contacts = []; // On initialise le tableau des contacts
		// Cherche des résultats dans les contacts
		foreach(Contact::search($q) as $contact) {
			array_push($this->contacts, $contact); // Si on trouve quelque chose alors on l'ajoute au tableau des contacts
		}
		// Cherche des résultats dans les adresses
		foreach(Address::search($q) as $address) {
			$this->getContact($address['contact_id']); // Si on trouve quelque chose alors on récupère le contact relié et on l'ajoute au tableau des contacts
		}
		// Cherche des résultats dans les téléphones
		foreach(Phone::search($q) as $phone) {
			$this->getContact($phone['contact_id']); // Si on trouve quelque chose alors on récupère le contact relié et on l'ajoute au tableau des contacts
		}
		// Cherche des résultats dans les emails
		foreach(Email::search($q) as $email) {
			$this->getContact($email['contact_id']); // Si on trouve quelque chose alors on récupère le contact relié et on l'ajoute au tableau des contacts
		}
		return $this->processResult();
	}
	
	/*
		Trie les résultats du tableau contact par nom, puis prénom.
	*/
	private function processResult() {
		foreach($this->contacts as $key => $contact) { // Pour chaque contacts dans la liste.
			$c = new Contact($contact['id'], $contact['first_name'], $contact['last_name'], $contact['type'], true); // On crée un objet Contact avec les données récupérées par la fonction search, ainsi que les coordonnées
			$data = $c->getAsArray(); // On récupère le tout sous forme de tableau
			$data['addresses']['html'] = "";
			foreach($data['addresses'] as $k => $address) { // Pour chaque addresse récupérée
				if(is_numeric($k)) { // Si la clé est un chiffre (donc pas 'html')
					$address['street'] = nl2br($address['street']); // on utilise nl2br pour remplacer les retours chariots et les nouvelles lignes par des <br />
					$template = Engine::getInstance()->template("address"); // on récupère la template des adresses
					$parser = new Parser($template);
					$data['addresses']['html'] .= $parser->compose(array("address" => $address), true); // et on la remplie avec les données récupérées
				}
			}
			$data['phones']['html'] = "";
			foreach($data['phones'] as $l => $phone) { // Pour chaque téléphone récupérée
				if(is_numeric($l)) { // Si la clé est un chiffre (donc pas 'html')
					$template = Engine::getInstance()->template("phone"); // on récupère la template
					$parser = new Parser($template);
					$data['phones']['html'] .= $parser->compose(array("phone" => $phone), true); // et on la remplie avec les données récupérées
				}
			}
			$data['emails']['html'] = "";
			foreach($data['emails'] as $m => $email) { // Pour chaque email récupérée
				if(is_numeric($m)) { // Si la clé est un chiffre (donc pas 'html')
					$template = Engine::getInstance()->template("email"); // on récupère la template
					$parser = new Parser($template);
					$data['emails']['html'] .= $parser->compose(array("email" => $email), true); // et on la remplie avec les données récupérées
				}
			}
			$data['html'] = "";
			
			$template = Engine::getInstance()->template("result"); // on récupère la template d'un résultat
			$parser = new Parser($template);
			$data['html'] .= $parser->compose(array("contact" => $data), true);  // et on la remplie avec les données récupérées et calculée ci dessus.
			
			$this->contacts[$key] = $data; // on renvoie toutes les données dans notre liste de contacts
		}
		$results = $this->sortResults(); // puis on trie les données avant de les retourner.
		return $results;
	}
	
	/*
		Trie les résultats du tableau contact par nom, puis prénom.
	*/
	private function sortResults() {
		if(count($this->contacts) <= 0) {
			return $this->contacts;
		}

		$sort = array(); // crée un tableau qui nous servira avec la fonction array_multisort
		foreach($this->contacts as $key => $contact) {
			$sort['first_name'][$key] = $contact['first_name']; // On crée un tableau dans lequel on stoque uniquement la variable avec laquelle on veut trier notre tableau de valeurs
			$sort['last_name'][$key] = $contact['last_name'];
		}
		array_multisort($sort['last_name'], SORT_ASC, $sort['first_name'], SORT_ASC, $this->contacts); // on trie de façon croissante en fonction des nom, puis prénom.
		$results = array("results" => "", "results-nb" => 0); // on initialise le tableau dans lequel on stocke les résultats.
		foreach($this->contacts as $key => $contact) { // On utilise le contenu html généré dans la méthode processResult et on le met dans l'ordres de nos contacts.
			if(is_numeric($key)) {
				$results['results'] .= $contact['html'];
				$results['results-nb']++; // On compte aussi le nombre de résultats;
			}
		}
		return $results;
	}
	
	/*
		Cette méthode vérifie si un contact avec l'id passé en arguments existe dans le tableau des contacts
		Retourne vrai si c'est le cas, sinon retourne faux.
	*/
	private function contact_search($id) {
		foreach($this->contacts as $contact) {
			if($contact['id'] === $id) {
				return true;
			}
		}
		return false;
	}
	
	/*
		La méthode suivante récupère un contact dans la base de données avec son id.
		Si il n'est pas déjà dans le tableau, elle l'ajoute à celui-ci.
	*/
	private function getContact($id) {
		if(!$this->contact_search($id)) { // Si le contact n'existe pas dans le tableau
			$contact = Contact::select('`id` = '.$id); // Alors on récupère le contact dans la base de données
			array_push($this->contacts, $contact[0]); // On l'ajoute au tableau
		}
	}
}