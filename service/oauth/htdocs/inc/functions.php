<?php
    function parse_template($name) {
        $file = file_get_contents("views/$name.html");
        return str_replace(array('\\', '"'), array('\\\\', '\\"'), $file);
    }

	function verify_login($dni, $pass) {
		$user = Capsule::table('users')->where('dni', '=', $dni)->first();
		if($user != null) {
			return $user['hash'] == hash_password($user['salt'], $pass);
		}
		return false;
	}

    function hash_password($salt, $pass) {
        return sha1(md5($salt).hash('sha256', $pass));
    }
