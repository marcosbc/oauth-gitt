<?php
	echo "service installation script\n";

	if(isset($argv[1]))
		echo "entered option: ${argv[1]}\n";

	// root credentials to create/drop db and users
	$user = 'root';
	$pass = 'bitnami1';
	$host = 'localhost';

	// new credentials for oauth project
	$newuser = 'oauth';
	$newpass = 'oauth';
	$newdb = 'oauth';

	// construct the query
	if(!isset($argv[1]) || $argv[1] != "remove") {
		echo "creating...\n";
		$query = "CREATE DATABASE IF NOT EXISTS `$newdb`;";
		$query .= "CREATE USER `$newuser`@`$host` IDENTIFIED BY '$newpass';";
		$query .= "GRANT ALL PRIVILEGES ON `$newdb`.* TO `$newuser`@`$host`;";
		$query .= "FLUSH PRIVILEGES;";

		// create the needed tables
		$structure = array(
			'users' => array(
				// the first one is the PRIMARY KEY
				'uid' => 'UNSIGNED INT NOT NULL',
				'password' => 'VARCHAR(20)',
				'username' => 'VARCHAR(20)',
				'name' => 'VARCHAR(20)',
				'lastname' => 'VARCHAR(40)',
				'iban' => 'VARCHAR(34)'), // *** should be unique ***
			'transactions' => array(
				// the first one is the PRIMARY KEY
				'tid' => 'UNSIGNED INT NOT NULL',
				'description' => 'VARCHAR(100)',
				'quantity' => 'DOUBLE(10,2)',
				'from_iban' => 'VARCHAR(34)',
				'to_iban' => 'VARCHAR(34)',
				'date' => 'UNSIGNED INT', // UNIX_TIMESTAMP()
				'status' => "ENUM('done', 'inprogress', 'failed')"),
			'apps' => array(
				// the first one is the PRIMARY KEY
				'aid' => 'UNSIGNED INT NOT NULL',
				'name' => 'VARCHAR(20)',
				'publickey' => 'VARCHAR(100)',
				'privatekey' => 'VARCHAR(100)',
				'permission_access_account' => array('BIT(1)', 1),
				'permission_modify_account' => array('BIT(1)', 0),
				'permission_read_transaction' => array('BIT(1)', 1),
				'permission_create_transaction' => array('BIT(1)', 0),
				'permission_read_credentials' => array('BIT(1)', 0)),
			'permissions' => array(
				'pid' => 'VARCHAR(40)',
				'description' => 'VARCHAR(60)',
				'default' => '0'
			));
		$sample_data = array(
			'users' => array(
				array(
					'uid' => 1,
					'password' => sha1('test'),
					'username' => 'marcos',
					'name' => 'Marcos',
					'lastname' => 'Bjorkelund',
					'iban' => 'ES8023100001180000012345'),
				array(
					'uid' => 2,
					'password' => sha1('test'),
					'username' => 'luis',
					'name' => 'Luis',
					'lastname' => 'Casabuena',
					'iban' => 'ES8023100001180000054321')),
			'transactions' => array(
				array(
					'tid' => 1,
					'description' => 'transferencia a favor de pablo',
					'quantity' => 504.25,
					'from_iban' => 'ES8023100001180000012345',
					'to_iban' => 'ES8023100001180000054321',
					'date' => time(),
					'status' => 'ok'),
				array(
					'tid' => 1,
					'description' => 'deuda pendiente',
					'quantity' => -104.5,
					'from_iban' => 'ES8023100001180000012345',
					'to_iban' => 'ES8023100001180000054321',
					'date' => time(),
					'status' => 'ok')),
			'permissions' => array(
				array(
					'pid' => 'permission_access_account',
					'description' => 'Read your account information'),
				array(
					'pid' => 'permission_modify_account',
					'description' => 'Modify your account information'),
				array(
					'pid' => 'permission_read_transaction',
					'description' => 'Read transactions information'),
				array(
					'pid' => 'permission_create_transaction',
					'description' => 'Create a new transaction'),
				array(
					'pid' => 'permission_read_credentials',
					'description' => 'Read your passwords')));
	} else {
		echo "removing...\n";
		$query = "DROP DATABASE `$newdb`;";
		$query .= "DROP USER `$newuser`@`$host`;";
		$query .= "FLUSH PRIVILEGES;";
	}

	echo "\nQuery to execute: \"$query\"\n";

	// create the db connection
	$db = new PDO("mysql:host=$host", $user, $pass);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
	
	try {
	    $db->exec($query);
	} catch (PDOException $e) {
		echo "PDO exception\n";
		die($e->getMessage());
	} catch (Exception $e) {
		echo "Exception\n";
		die($e->getMessage());
	}
