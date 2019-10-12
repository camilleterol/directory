<?php
/*
	Classe Logout : hérite de Page
	Cette classe détruit la session et nous ramène sur la page de login.	
*/	
class Logout extends Page {
	public function init() {
		$_SESSION = array();
		session_destroy();
		$r = new Request();
		Router::getInstance()->refresh($r);
	}
}