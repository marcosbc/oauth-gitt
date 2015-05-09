<?php
	use Illuminate\Database\Capsule\Manager as Capsule;

	function get_user($uid) {
		return Capsule::table('users')
					->select(Capsule::raw('uid, username, name, lastname, dni, iban, balance'))
					->where('uid', '=', $uid)->first();
	}

	function get_transactions($uid, $tid = false) {
		$escaped_uid = addslashes($uid);
		$res = Capsule::table('transactions')
					->select(Capsule::raw('transactions.*'))
					// ensure we are querying a user who we have permission to view, not everyone's query
					->leftJoin(Capsule::raw('users u1'), 'u1.iban', '=', 'to_iban')
					->leftJoin(Capsule::raw('users u2'), 'u2.iban', '=', 'from_iban')
					->where(function($query) use ($uid) {
						$query->where('u1.uid', '=', $uid)
						      ->orWhere('u2.uid', '=', $uid);
					});

		if($tid)
			$res = $res->where('tid', '=', $tid)->first();
		else
			$res = $res->get();

		if($res == null)
			$res = array();

		return $res;
	}

