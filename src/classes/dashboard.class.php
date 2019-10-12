<?php
	
/*
	Classe Engine : hérite de Singleton.
	C'est le moteur de notre site.
	Cette classe instancies les autres classes et s'assure du rendu des pages HTML finales.
*/
class Dashboard extends Page {
	protected static $title = "Tableau de Bord";
	
	/*
		On initialise les différentes parties de notre tableau de données
	*/
	public function init() {
		$data = array(
			"nickname" => $this->getNickname(), // On récupère le surnom.
			"contacts" => $this->getContacts(), // On récupère la liste de contacts.
			"details" => $this->getDetails()	// On récupère le contenu de la colonne de droite (détails des contacts).
		);
		return $data;
	}
	
	/*
		S'il y a un surnom enregistré dans la session, on le retourne, sinon on retourne Anonyme
	*/
	private function getNickname() {
		if(isset($_SESSION['login']['nickname'])) {
			$nickname = $_SESSION['login']['nickname'];
		} else {
			$nickname = "Anonyme";
		}
		return $nickname;
	}
	
	/*
		Cette méthode récupère la liste des contacts et les retourne.	
	*/		
	private function getContacts() {
		$contacts = array();
		$contacts['html'] = "";
		
		$template = Engine::getInstance()->template("contact"); // On charge la template contact
		$parser = new Parser($template);
		
		/*
			On vérifie dans quelle sous-requête on se trouve.
			Dans tous les cas on affiche la liste des contacts inchangée avec le contact en cours selectionné.
		*/
		$request = new Request();
		$details = $request->matches("\/+dashboard\/+user\/+([0-9]+)", true); // On affiche les détails du contact
		$delete = $request->matches("\/+dashboard\/+delete\/+([0-9]+)", true); // On affiche la page de confirmation de la suppression
		$edit = $request->matches("\/+dashboard\/+edit\/+([0-9]+)", true); // On affiche la page de modification des contacts
		$add = $request->matches("\/+dashboard\/+add\/*", true); // On affiche la page d'ajout des contacts
		if(isset($add[0])) {
			$contacts['html'] .= $parser->compose(array("contact" => array("target" => " target")), true); // On ajoute un contact vide pour bien montrer qu'on ajoute un nouveau contact.
		}
		
		foreach(Contact::select() as $contact) {
			if((isset($details[1]) && $details[1] == $contact['id']) || (isset($delete[1]) && $delete[1] == $contact['id']) || (isset($edit[1]) && $edit[1] == $contact['id'])) {
				$contact['target'] = " target";	// Si on modifie ou affiche un contact existant, on lui donne la classe target, pour pouvoir plus tard l'afficher comme sélectionné en CSS.
			}
			$obj = new Contact($contact['id'], $contact['first_name'], $contact['last_name'], $contact['type'], true); // On crée un object contact, vrai demande à la classe d'aller chercher les coordonnées.
			// On récupère les coordonnées.
			$contact['phones'] = $obj->getPhones();
			$contact['emails'] = $obj->getEmails();
			$contact['addresses'] = $obj->getAddresses();
			
			array_push($contacts, $contact);
			$contacts['html'] .= $parser->compose(array("contact" => $contact), true); // On ajoute tout ça a notre rendu html.
		}
		return $contacts;
	}
	
	/*
		Récupère les différentes données d'un contact.
		Utilise request pour savoir dans quelle URI on se trouve.
		TODO: Modifier pour rendre plus global la detection de l'URI et simplifier le code.
	*/
	private function getDetails() {
		// Voir la méthode getContacts() pour savoir à quoi correspondent les expression régulières ci-dessous.
		$request = new Request();
		$details = $request->matches("\/+dashboard\/+user\/+([0-9]+)", true);
		$delete = $request->matches("\/+dashboard\/+delete\/+([0-9]+)", true);
		$edit = $request->matches("\/+dashboard\/+edit\/+([0-9]+)", true);
		$add = $request->matches("\/+dashboard\/+add\/*", true);
		if(isset($delete[1])) { // Si l'utilisateur souhaite supprimer un client
			$contact = Contact::selectObject('`id` = '.$delete[1])->getAsArray(); // On récupère les infos du contact relié à l'id passé dans l'URI.
			
			$template = Engine::getInstance()->template("delete"); // On charge la template correspondante.
			$parser = new Parser($template);
			$html = $parser->compose(array("contact" => $contact), true); // on génère le code html
			
			return array("html" => $html); // et on le retourne.
		}
		// On affiche l'interface de création/modification d'utilisateur si ça nous est demandé.
		if(isset($add[0]) || isset($edit[1])) {
			$contact = array();
			// Si l'on est sur la page de modification des contact
			if(isset($edit[1])) {
				// On récupère les données du contact, et on les insère dans notre ensemble de données.
				$contact = array_merge($contact, Contact::selectObject('`id` = '.$edit[1])->getAsArray());
				$contact['phones']['count'] = count($contact['phones']);
				$contact['emails']['count'] = count($contact['emails']);
				$contact['addresses']['count'] = count($contact['addresses']);
			}
			// Si des données d'un contact ont été POSTées alors on les ajoutes à nos données locales.
			if(isset($this->data['posted_contact'])) {
				$contact = array_merge($contact, $this->data['posted_contact']);
			}
			// On récupère aussi les varaibles de retour POST (nombre de téléphones, emails, adresses notamment) 
			if(isset($this->data['return'])) {
				$contact = array_merge_recursive($contact, $this->data['return']);
			} else { // Sinon on affecte 1 a chaque compteur pour que dans le cas d'une création on affiche au moins un champs pour chaque donnée.
				$contact['phones']['count'] = 1;
				$contact['emails']['count'] = 1;
				$contact['addresses']['count'] = 1;
			}
			$data = $this->prepareEditData($contact); // On prends nos données locales ($contact) et on les envoie à la méthode prepareEditData() pour les préparer 
			$contact = array_merge($contact, $data); // Puis on récupère le retour et l'intègre à nos données locales.
			
			$template = Engine::getInstance()->template("edit"); // On récupère notre template globale.
			$parser = new Parser($template);
			$html = $parser->compose(array("contact" => $contact), true); // On génère l'HTML
			return array("html" => $html); // Et on le retourne pour pouvoir l'afficher avec le moteur.
		}
		// Si on souhaite afficher la page qui afficher les données de notre contact
		if(isset($details[1])) {
			$contact = Contact::selectObject('`id` = '.$details[1], true); // On récupère les infos du contact relié à l'id passé dans l'URI.
			
			// Ces trois lignes génèrent les titres de la vue détails et les accordent au pluriel si c'est nécessaire.
			$contact['phones']['title'] = "Téléphone".((count($contact['phones']) > 1) ? 's' : '');
			$contact['emails']['title'] = "Email".((count($contact['emails']) > 1) ? 's' : '');
			$contact['addresses']['title'] = "Adresse".((count($contact['addresses']) > 1) ? 's' : '');
			
			// Les trois tructures suivantes sont les même pour chaque adresse, email, téléphone, si l'index de la valeur est numérique (!= html), alors on charge la template et la remplis des données puis retourne le code html dans notre tableau $contact. 
			
			$contact['addresses']['html'] = "";
			foreach($contact['addresses'] as $k=> $address) {
				if(is_numeric($k)) {
					$address['street'] = nl2br($address['street']); // On échappe les retour de chariot/saut de ligne en <br /> pour afficher correctement les données.
					$template = Engine::getInstance()->template("address");
					$parser = new Parser($template);
					$contact['addresses']['html'] .= $parser->compose(array("address" => $address), true);
				}
			}
			
			$contact['phones']['html'] = "";
			foreach($contact['phones'] as $l => $phone) {
				if(is_numeric($l)) {
					$template = Engine::getInstance()->template("phone");
					$parser = new Parser($template);
					$contact['phones']['html'] .= $parser->compose(array("phone" => $phone), true);
				}
			}
			
			$contact['emails']['html'] = "";
			foreach($contact['emails'] as $m => $email) {
				if(is_numeric($m)) {
					$template = Engine::getInstance()->template("email");
					$parser = new Parser($template);
					$contact['emails']['html'] .= $parser->compose(array("email" => $email), true);
				}
			}
			$template = Engine::getInstance()->template("details"); // On récupère la template globale des coordonnées.
			$parser = new Parser($template);
			$html = $parser->compose(array("contact" => $contact), true); // On récupère le code html
			return array("html" => $html); // et on le retourne.
		}
	}
	
	/*
		Cette méthode prépare la vue de modification des données d'un contact.
	*/
	private function prepareEditData($data) {
		// On récupère la liste des types de données de nos contacts.
		$types = array();
		$types['phone'] = DB::getInstance()->getEnumValues('phones', 'type');
		$types['email'] = DB::getInstance()->getEnumValues('emails', 'type');
		$types['address'] = DB::getInstance()->getEnumValues('addresses', 'type');
		
		$return = array();
		// Pour chaque colonne qui possède plusieurs types, on 'crée' une boucle
		foreach($types as $key => $set) {
			// On initialise ce qui sera plus tard notre code html
			$types[$key]['html'] = "";
			// On récupère notre fichier de template
			$template = Engine::getInstance()->template("edit/type");
			// On la charge dans le parseur
			$parser = new Parser($template);
			// Pour chaque type de donnée, on 'crée' une boucle
			foreach($set as $type) {
				// On crée un tableau contenant la valeur de notre type, et son nom (la valeur en minuscule)
				$type = array(
					'value' => $type,
					'name' => strtolower($type)
				);
				// On génère le code html à partir de la template.
				$types[$key]['html'] .= $parser->compose(array("type" => $type));
			}
		}
		$values = array('phone' => 'phones', 'email' => 'emails', 'address' => 'addresses'); // On crée un tableau qui nous permettra d'écrire une seule fois le code pour tous nos types de données.
		foreach($values as $key => $value) { // On crée un boucle pour chaque type de données
			if(isset($data[$value]['count'])) {
				// Compte uniquement les clés numériques.
				$length = count(array_filter(array_keys($data[$value]), 'is_numeric'));
				$return[$value]['count'] = max($data[$value]['count'], $length); // On vérifie qui entre la variable postée 'count' et la longueur de notre tableau est la plus grande. Cela nous permet de créer un nombre adéquats de champs dans notre vue. La variable postée 'count' contient le nombre de champs pour un type de données (i.e. téléphone, email, adresse)
			}
			$template = Engine::getInstance()->template("edit/".$key); // On récupère la sous-template correspondant à notre type de donnée
			$parser = new Parser($template);
			$return[$value]['html'] = "";
			for($i = 0; $i < $return[$value]['count']; $i++) { // Pour chaque élément qu'on doit créer
				if(isset($data[$value][$i])) { // On vérifie si il y a une valeur associée dans le tableau POST
					$data[$value][$i] = array_merge_recursive($data[$value][$i], array('nb' => $i)); // On rajoute le numéro du champs dans notre tableau de données
					$element = $data[$value][$i];
					// Pour chaque champs, on selectionne la bonne valeur dans les listes déroulantes.
					$string = preg_replace_callback("/option value=\"([\w ]*)\"/", function($matches) use ($element) { // On utilise une expression régulière qui modifie notre code HTML
						if($matches[1] == $element['type']) { // Si le type contenu dans notre balise option de notre liste déroulante correspond au type de notre champs (Mobile, domicile, professionnel ...)
							return $matches[0].' selected'; // on ajoute selected uniquement à cet élement.
						} else {
							return $matches[0]; // Sinon on ne change rien.
						}
					}, $types[$key]['html']);
					$return[$value]['html'] .= $parser->compose(array("types" => $string, $key => $data[$value][$i])); // On retourne notre élément et on y insère nos données.
				} else {
					$return[$value]['html'] .= $parser->compose(array("types" => $types[$key]['html'], $key => array('nb' => $i))); // on retourne un élément vide.
				}
			}
		}
		return $return;
	} 
	
	/*
		Réponds à la requete POST.
		Passe les données en arguments dans la variable $data.
	*/
	protected function post($data = null) {
		// Voir la méthode getContacts() pour savoir à quoi correspondent les expression régulières ci-dessous.
		$request = new Request();
		$delete = $request->matches("\/+dashboard\/+delete\/+([0-9]+)", true);
		$edit = $request->matches("\/+dashboard\/+edit\/+([0-9]+)", true);
		$add = $request->matches("\/+dashboard\/+add\/*", true);
		
		// Si l'URI est celle qui pointe vers la suppression de contact, et que le paramètre $contact_id est présent dans nos données. (L'utilisateur à confirmé la suppression)
		if(isset($delete[1]) && $delete[1] && isset($data['contact_id']) && $data['contact_id'] == $delete[1]) {
			Contact::delete($data['contact_id']); // On supprime notre contact.
			$r = new Request('/dashboard/'); // On retourne sur le tableau de bord.
			Router::getInstance()->refresh($r);
			return array("deleted", true);
		}
		
		if($data['sb'] == 'add-phone' || $data['sb'] == 'add-email' || $data['sb'] == 'add-address') { // Si un des boutons "ajouter un champs" a été pressé.
			if($data['sb'] == 'add-phone') { // Si c'est celui pour ajouter un téléphone
				$data['phone-count']++; // On incrémente le nombre de champs de un.
			}
			if($data['sb'] == 'add-email') { // Pareil pour les emails
				$data['email-count']++;
			}
			if($data['sb'] == 'add-address') { // et pour les addresses
				$data['address-count']++;
			}
			$posted = array( // On récupère les données postées au cas où l'utilisateur aurait déjà entré des données. On s'en servira pour reremplir le formulaire et qu'il n'ai pas à tout retaper.
					"first_name" => $data['first_name'],	
					"last_name" => $data['last_name'],
					"type" => $data['type'],
					"addresses" => $data['addresses'],
					"emails" => $data['emails'],
					"phones" => $data['phones']
			);
			
			$return = array('addresses' => array('count' => $data['address-count']), 'emails' => array('count' => $data['email-count']), 'phones' => array('count' => $data['phone-count']));
			return array('return' => $return, 'posted_contact' => $posted);
		}
		
		if($data['sb'] == "Annuler") { // Si c'est le bouton annuler qui a été pressé
			$url = '/dashboard/';
			if(isset($edit[1])) {
				$url = $url . 'user/' . $edit[1]; // On trouve l'URI correspondant à notre utilisateur
			}
			$r = new Request($url);
			Router::getInstance()->refresh($r); // On rafraichis vers cette page.
		}
		
		if(isset($add[0]) && $data != null) { // Si on est sur la page ajouter et que des données ont été postées
			$this->addContact($data); // On va dans la méthode qui s'occupe d'ajouter les données en base.
		}
		
		if(isset($edit[1]) && $data != null) { // Pareil pour la modification.
			$this->updateContact($data, $edit[1]);
		}
	}
	
	/*
		Cette méthode ajoute un contact et prends en arguments les données POSTées dans le formulaire.
	*/
	private function addContact($data) {	
		array_walk_recursive($data, function($value, $key) use ($data) {
			$data[$key] = Engine::escapeData($value); // On 'echape' nos données avec la fonction présente dans le moteur.
		});
		$c = new Contact(NULL, $data['first_name'], $data['last_name'], NULL); // On crée un contact
		$contact_id = $c->create(); // On le sauvegarde en base.
		
		foreach($data['addresses'] as $address) { // Pareil pour les addresses, peu importe le nombre
			$a = new Address(NULL, $address['type'], $address['street'], $address['zip'], $address['city'], $contact_id);
			$a->create();
		}
		
		foreach($data['emails'] as $email) { // Idem Email
			$e = new Email(NULL, $email['type'], $email['email'], $contact_id);
			$e->create();
		}
		foreach($data['phones'] as $phone) { // Idem Téléphones
			$p = new Phone(NULL, $phone['type'], $phone['number'], NULL, $contact_id);
			$p->create();
		}
		$url = '/dashboard/';
		if(isset($contact_id)) {			
			$url = $url . '/user/' . $contact_id; // On recharge vers l'URI pointant sur la page de l'utilisateur juste créé.
		}
		$r = new Request($url);
		Router::getInstance()->refresh($r);
	}
	
	/*
		Cette méthode met à jour un contact et prends en arguments les données POSTées dans le formulaire.
	*/
	private function updateContact($data, $id) {
		array_walk_recursive($data, function($value, $key) use ($data) {
			$data[$key] = Engine::escapeData($value); // On 'echape' nos données avec la fonction présente dans le moteur.
		});
		$c = new Contact($id, $data['first_name'], $data['last_name'], NULL); // On crée un objet contact
		$c->update(); // On mets à jour le contact avec les données fournies
		
		foreach($data['addresses'] as $address) { // Pour chaque addresse
			if(!empty($address['id'])) { // On détermine si c'est une nouvelle adresse ou s'il faut simplemeent mettre à jour les données.
				$a = new Address($address['id'], $address['type'], $address['street'], $address['zip'], $address['city'], $id); // On crée l'objet
				$a->update(); // met à jour la base
			} else {
				$a = new Address(NULL, $address['type'], $address['street'], $address['zip'], $address['city'], $id);
				$a->create(); // sauvegarde dans la base une nouvelle entrée
			}
		}
		
		foreach($data['emails'] as $email) { // Idem email
			if(!empty($email['id'])) {	// On détermine si c'est une nouvelle adresse email ou s'il faut simplemeent mettre à jour les données.
				$e = new Email($email['id'], $email['type'], $email['email'], $id);
				$e->update();
			} else {	
				$e = new Email(NULL, $email['type'], $email['email'], $id);
				$e->create();
			}
		}
		
		foreach($data['phones'] as $phone) { // Idem Téléphone
			if(!empty($phone['id'])) {
				$p = new Phone($phone['id'], $phone['type'], $phone['number'], NULL, $id);
				$p->update();
			} else {
				$p = new Phone(NULL, $phone['type'], $phone['number'], NULL, $id);
				$p->create();
			}
		}
		
		$url = '/dashboard/';
		if(isset($id)) {
			$url = $url . '/user/' . $id; // On recharge vers la page qui affiche les données de notre utilisateur.
		}
		$r = new Request($url);
		Router::getInstance()->refresh($r);
	}
}