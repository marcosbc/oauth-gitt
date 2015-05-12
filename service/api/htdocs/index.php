<?php
	require_once __DIR__.'/../../vendor/autoload.php';
	require_once __DIR__.'/inc/functions.php';
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
			'status_code' => 400,
			'message' => $e->getMessage(),
		)), 400);
	}

	// return response
	$response->headers->set('Content-type', 'application/json');
	$response->send();

	
