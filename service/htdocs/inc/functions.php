<?php
	function load_view($name) {
		$file = addslashes(file_get_contents("views/$name.html"));
		eval("\$file = \"$file\";");
		echo $file;
	}

	function check_user($idval, $pass) {
		global $db, $user;

		$sentence = $db->prepare("SELECT * FROM users WHERE dni = ? LIMIT 1");

		if($sentence->execute(array($idval))) {
			$user = $sentence->fetch();
			return $user['hash'] == hash_password($user['salt'], $pass);
		}
		return false;
	}

	function login($idval, $pass) {
		global $db, $user;

		if(check_user($idval, $pass)) {
			// set session data
			foreach($user as $key => $val) {
                $_SESSION[$key] = $val;
			}
			return true;
		}
		return false;
	}

	function logout() {
		// can be improved
		$_SESSION = array();
		session_destroy();
	}

	function is_already_logged_in() {
		if(isset($_SESSION['uid'])) {
			// for the future, we could improve this (a lot)
			return true;
		}

		return false;
	}

	function get_user_data($idval) {
		global $db;

		$sentence = $db->prepare("SELECT * FROM users WHERE dni = '?' LIMIT 1");

		if($sentence->execute(array($idval))) {
			$user = $sentence->fetch();
		}

		return $user;
	}

	function generate_dashboard() {
		global $user, $db;
		$dashboard = array();

		// name
		$dashboard['name'] = strtoupper("{$user['name']} {$user['lastname']}");

		// iban
		// http://es.wikipedia.org/wiki/International_Bank_Account_Number#En_Espa.C3.B1a
		$dashboard['iban'] = format_iban($user['iban']);

		// balance
		$dashboard['balance'] = format_quantity($user['balance']);

		// generate the transactions
		$transactions = "";
		$sentence = $db->prepare("SELECT * FROM transactions WHERE from_iban = :iban OR to_iban = :iban");
		if($sentence->execute(array(':iban' => $user['iban']))) {
			$trans = $sentence->fetchAll();
			foreach($trans as $t) {
				$t_quantity = format_quantity($t['quantity']);
				if($user['iban'] == $t['to_iban']) {
					$t_iban = format_iban($t['from_iban']);
				} else {
					$t_iban = format_iban($t['to_iban']);
					$t_quantity = '-'.$t_quantity;
				}
				$t_description = strtoupper($t['description']);
				$transactions .= "<tr><td class=\"description left\"><strong>$t_description</strong><br/><span>$t_iban</span><td class=\"center\">";
				$transactions .= "Ayer"; // TODO
				$transactions .= "</td><td class=\"right\"><strong>$t_quantity</strong></td></tr>";
			}
		}
		$dashboard['transactions'] = $transactions;

		return $dashboard;
	}

	function generate_error($msg) {
		return "<div id=\"error\"><strong>Error:</strong> $msg</div>";
	}

	function hash_password($salt, $pass) {
		return sha1(md5($salt).hash('sha256', $pass));
	}

	function format_quantity($number) {
		return number_format($number, 2, ',', '.')."&euro;";
	}

	function format_iban($user_iban) {
		$iban = "";
		$iban .= substr($user_iban, 0, 4)." ";
		$iban .= substr($user_iban, 4, 4)." ";
		$iban .= substr($user_iban, 8, 4)." ";
		$iban .= substr($user_iban, 12, 4)." ";
		$iban .= substr($user_iban, 16, 4)." ";
		$iban .= substr($user_iban, 20, 4)." ";
		$iban .= substr($user_iban, 24, 4);

		return $iban;
	}
