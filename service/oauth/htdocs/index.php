<?php
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

	$server = new \League\OAuth2\Server\AuthorizationServer;
	$server->setSessionStorage(new Storage\SessionStorage);
	$server->setAccessTokenStorage(new Storage\AccessTokenStorage);
	$server->setClientStorage(new Storage\ClientStorage);
	$server->setScopeStorage(new Storage\ScopeStorage);
	$server->setAuthCodeStorage(new Storage\AuthCodeStorage);
	$authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant();
	$server->addGrantType($authCodeGrant);
	$refreshTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
	$server->addGrantType($refreshTokenGrant);

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	// create the request
	$request = (new Request())->createFromGlobals();
	$router = new \League\Route\RouteCollection();

	// set up routes
	$router->get('/authorize', function (Request $request) use ($server) {
		try {
			$authParams = $server->getGrantType('authorization_code')->checkAuthorizeParams();
		} catch(\Exception $e) {
			return new Response(
				json_encode(array(
					'error' => $e->errorType,
					'message' => $e->getMessage(),
				)),
				$e->httpStatusCode,
				$e->getHttpHeaders()
			);
		}
		$redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', 1, $authParams);

		$response = new Response(null, 303, array(
			'Location' => $redirectUri
		));

		return $response;
	});

	$router->post('/access_token', function(Request $request) use ($server) {
		try {
			$response = $server->issueAccessToken();
			echo "ture";
			return new Response(json_encode($response), 200);
		} catch(\Exception $e) {
			return new Response(
				json_encode(array(
					'error' => $e->errorType,
					'message' => $e->getMessage(),
				)),
				$e->httpStatusCode,
				$e->getHttpHeaders()
			);
		}
	});

	// show the application, and error page if error
	try {
		// create the response (from the dispatcher)
		$dispatcher = $router->getDispatcher();
		$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
	} catch(\League\Route\Http\Exception\NotFoundException $e) {
		$response = new Response(json_encode(array(
			'error' => 404,
			'message' => $e->getMessage(),
		)), 404);
	} catch(\Exception $e) {
		$response = new Response(json_encode(array(
			'status_code' => 500,
			'message' => $e->getMessage(),
		)), 500);
	}

	$response->headers->set('Content-type', 'application/json');
	$response->send();
	
