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

	// create the needed tables
	$structure = array(
		'users' => array(
			// the first one is the PRIMARY KEY
			'uid' => 'INT UNSIGNED NOT NULL',
			'password' => 'VARCHAR(20)',
			'username' => 'VARCHAR(20)',
			'name' => 'VARCHAR(20)',
			'lastname' => 'VARCHAR(40)',
			'iban' => 'VARCHAR(34)'), // *** should be unique ***
		'transactions' => array(
			// the first one is the PRIMARY KEY
			'tid' => 'INT UNSIGNED NOT NULL',
			'description' => 'VARCHAR(100)',
			'quantity' => 'DOUBLE(10,2)',
			'from_iban' => 'VARCHAR(34)',
			'to_iban' => 'VARCHAR(34)',
			'date' => 'INT UNSIGNED', // UNIX_TIMESTAMP()
			'status' => "ENUM('done', 'inprogress', 'failed')"),
		'apps' => array(
			// the first one is the PRIMARY KEY
			'aid' => 'INT UNSIGNED NOT NULL',
			'name' => 'VARCHAR(20)',
			'publickey' => 'VARCHAR(100)',
			'privatekey' => 'VARCHAR(100)',
			'permission_access_account' => 'BIT(1) DEFAULT 1',
			'permission_modify_account' => 'BIT(1) DEFAULT 0',
			'permission_read_transaction' => 'BIT(1) DEFAULT 1',
			'permission_create_transaction' => 'BIT(1) DEFAULT 0',
			'permission_read_credentials' => 'BIT(1) DEFAULT 0'),
		'permissions' => array(
			'pid' => 'VARCHAR(40)',
			'description' => 'VARCHAR(60)'),
	);

	// construct the query
	if(!isset($argv[1]) || $argv[1] != "remove") {
		echo "creating...\n";
		$query = "CREATE DATABASE IF NOT EXISTS `$newdb`;\n";
		$query .= "CREATE USER `$newuser`@`$host` IDENTIFIED BY '$newpass';\n";
		$query .= "GRANT ALL PRIVILEGES ON `$newdb`.* TO `$newuser`@`$host`;\n";
		$query .= "FLUSH PRIVILEGES;\n";

		// we will start inserting data now
		$query .= "USE `$newdb`;\n";

		$sample_data = array(
			'users' => array(
				array(
					'uid' => 1,
					'password' => sha1('test'),
					'username' => 'marcos',
					'name' => 'Marcos',
					'lastname' => 'Bjorkelund',
					'iban' => 'ES8023100001180000012345'
				),
				array(
					'uid' => 2,
					'password' => sha1('test'),
					'username' => 'luis',
					'name' => 'Luis',
					'lastname' => 'Casabuena',
					'iban' => 'ES8023100001180000054321'
				),
			),
			'transactions' => array(
				array(
					'tid' => 1,
					'description' => 'TRANSFER to LUIS CASABUENA',
					'quantity' => 504.25,
					'from_iban' => 'ES8023100001180000012345',
					'to_iban' => 'ES8023100001180000054321',
					'date' => time(),
					'status' => 'ok'),
				array(
					'tid' => 2,
					'description' => 'PURCHASE at MERCADONA',
					'quantity' => -104.5,
					'from_iban' => 'ES8023100001180000012345',
					'to_iban' => 'ES8023100001180000099998',
					'date' => time(),
					'status' => 'ok'),
				array(
					'tid' => 3,
					'description' => 'PURCHASE at CARREFOUR',
					'quantity' => 41.34,
					'from_iban' => 'ES8023100001180000012345',
					'to_iban' => 'ES8023100001180000099999',
					'date' => time(),
					'status' => 'ok'),
				array(
					'tid' => 4,
					'description' => 'TOLL at AUTOPISTA AP-4',
					'quantity' => 41.34,
					'from_iban' => 'ES8023100001180000012345',
					'to_iban' => 'ES8023100001180000099999',
					'date' => time(),
					'status' => 'ok'),
			),
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
					'description' => 'Read your passwords'),
			),
			'apps' => array(
				array(
					'aid' => '1',
					'name' => 'oauth-gitt',
					'publickey' => '123456789abcdefghijklmnopqrstuvwxyz',
					'privatekey' => 'zyxwvutsrqponmlkjihgfedcba987654321',
					'permission_access_account' => '1',
					'permission_modify_account' => '0',
					'permission_read_transaction' => '1',
					'permission_create_transaction' => '0',
					'permission_read_credentials' => '0'),
			)
		);

		foreach($structure as $table => $cols) {
			$query .= "CREATE TABLE $table (";
			$count = 0;
			foreach($cols as $col => $type) {
				$query .= "$col $type";
				if($count == 0) {
					$query .= ",PRIMARY KEY($col)";
				}
				if($count != count($cols) - 1) {
					$query .= ",";
				}
				$count++;
			}
			$query .= ");\n";
		}

		// run through each table
		foreach($sample_data as $table => $rows) {
			// run through each row
			for($i = 0; $i < count($rows); $i++) {
				$cols = "";
				$vals = "";
				// run through each key
				foreach($rows[$i] as $col => $val) {
					if($cols != "")
						$cols .= ", ";
					if($vals != "")
						$vals .= ", ";
					$cols .= "`$col`";
					$vals .= "\"$val\"";
				}
				$query .= "INSERT INTO `$table` ($cols) VALUES ($vals);\n";
			}
		}
	} else {
		echo "removing...\n";
		$query = "DROP DATABASE `$newdb`;\n";
		$query .= "DROP USER `$newuser`@`$host`;\n";
		$query .= "FLUSH PRIVILEGES;\n";

		// we will start dropping data now
		$query .= "USE `$newdb`;\n";
		foreach($structure as $key => $val) {
			$query .= "DROP TABLE $key;\n";
		}
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
