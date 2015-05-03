<?php
	session_start();
	require_once('inc/functions.php');

	// initialize db and variables
	$title = "Urbank";
	try{
		$db = new PDO('mysql:host=localhost;dbname=oauth', 'oauth', 'oauth');
	} catch(Exception $e) {
		die($e->getMessage());
	}
	$user = array();
	$errormsg = "";
	$alertmsg = "";
	$logo = $title;
	global $db, $user;

	// logging in...
	if(isset($_POST['action']) && $_POST['action'] == 'login') {
		if(is_already_logged_in()) {
			$errormsg = generate_error('Ya ha iniciado sesi&oacute;n');
		} elseif(!login($_POST['dni'], $_POST['pass'])) {
			$errormsg = generate_error('Las credenciales no son correctas');
		}
	} elseif(isset($_GET['action']) && $_GET['action'] == 'logout') {
		if(!is_already_logged_in()) {
			$errormsg = generate_error('No ha iniciado sesi&oacute;n aun');
		} else {
			logout();
		}
	}

	// not logged in: redirect to login
	if(!isset($_SESSION['uid'])) {
		$viewfile = 'login';
	}
	else {
		$title = "Tu $title";
		// get the user data
		foreach($_SESSION as $key => $val) {
			$user[$key] = $val;
		}

		// generate the dashboard
		$dashboard = generate_dashboard();
		foreach($dashboard as $key => $value) {
			$$key = $value;
		}
		$viewfile = 'dashboard';
	}

	// parse the view and show
	$header = parse_template("header");
	eval("\$header = \"$header\";");
	$footer = parse_template("footer");
	eval("\$footer = \"$footer\";");
	$file = parse_template($viewfile);
	eval("\$file = \"$file\";");
	echo $file;
