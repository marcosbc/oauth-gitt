<?php
	session_start();
	require_once __DIR__.'/vendor/autoload.php';
	use Illuminate\Database\Capsule\Manager as Capsule;

	// set up the capsule
	$capsule = new Capsule;
	$capsule->addConnection([
		'driver'    => 'mysql',
		'host'      => 'localhost',
		'database'  => 'oauth',
		'username'  => 'oauth',
		'password'  => 'oauth',
		'charset'   => 'utf8',
		'collation' => 'utf8_unicode_ci',
		'prefix'    => '',
	]);

	// Set the event dispatcher used by Eloquent models... (optional)
	use Illuminate\Events\Dispatcher;
	use Illuminate\Container\Container;
	$capsule->setEventDispatcher(new Dispatcher(new Container));

	// Make this Capsule instance available globally via static methods... (optional)
	$capsule->setAsGlobal();

	// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
	$capsule->bootEloquent();
	
	// class autoloader
	spl_autoload_register('__autoload');
	function __autoload($nombre_clase) {
		$loc = strrpos($nombre_clase, '\\');
		$path = str_replace('\\', '/', substr($nombre_clase, 0, $loc + 1));
		$class = substr($nombre_clase, $loc + 1);
		include "$path$class.php";
	}

	$server = new \League\OAuth2\Server\ResourceServer(
		new Storage\SessionStorage(),
		new Storage\AccessTokenStorage(),
		new Storage\ClientStorage(),
		new Storage\ScopeStorage()
	);

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	// create the request
	$request = (new Request())->createFromGlobals();
	$router = new \League\Route\RouteCollection();

	/*
	 * Functions
	 */

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

	/*
	 * set up routes
	 */

	// get token information data
	$router->get('/tokeninfo', function(Request $request) use ($server) {
		$accessToken = $server->getAccessToken();
		$session = $server->getSessionStorage()->getByAccessToken($accessToken);
		$token = array(
			'owner_id' => $session->getOwnerId(),
			'owner_type' => $session->getOwnerType(),
			'access_token' => $accessToken,
			'client_id' => $session->getClient()->getId(),
			'scopes' => $accessToken->getScopes()
		);

		return new Response(json_encode($token));
	});

	// get list of transactions
	$router->get('/transaction', function(Request $request, Response $response) use ($server) {
		$accessToken = $server->getAccessToken();
		$session = $server->getSessionStorage()->getByAccessToken($accessToken);
		$body = get_transactions($session->getOwnerId());
		$response->setContent(json_encode($body));
		$response->setStatusCode(200);
		return $response;
	});

	// get a specific transaction
	$router->get('/transaction/{id:number}', function(Request $request, Response $response, array $args) use ($server) {
		$accessToken = $server->getAccessToken();
		$session = $server->getSessionStorage()->getByAccessToken($accessToken);
		$body = get_transactions($session->getOwnerId(), $args['id']);
		if(empty($body)) {
			// not found
			$response->setStatusCode(404);
		} else {
			$response->setStatusCode(200);
		}
		$response->setContent(json_encode($body));
		return $response;
	});

	// get information about the current user
	$router->get('/user', function(Request $request, Response $response, array $args) use ($server) {
		$accessToken = $server->getAccessToken();
		$session = $server->getSessionStorage()->getByAccessToken($accessToken);
		$body = get_user($session->getOwnerId());
		if(empty($body)) {
			// not found
			$response->setStatusCode(404);
		} else {
			$response->setStatusCode(200);
		}
		$response->setContent(json_encode($body));
		return $response;
	});

	// show the application, and error page if error
	try {
		// is access token present?
		$server->isValidRequest(false);

		// create the response (from the dispatcher)
		$dispatcher = $router->getDispatcher();
		$response = $dispatcher->dispatch(
			$request->getMethod(),
			$request->getPathInfo()
		);
	} catch(\Exception $e) {
		$response = new Response(json_encode(array(
			'status_code' => 500,
			'message' => $e->getMessage(),
		)), 500);
	}

	// return response
	$response->headers->set('Content-type', 'application/json');
	$response->send();

	
