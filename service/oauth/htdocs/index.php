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
	$server->setRefreshTokenStorage(new Storage\RefreshTokenStorage);
	$authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant();
	$server->addGrantType($authCodeGrant);
	$refreshTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
	$server->addGrantType($refreshTokenGrant);

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	// create the request
	$request = (new Request())->createFromGlobals();
	$router = new \League\Route\RouteCollection();

	/*
	 * set up routes
	 */

	// step 1: get auth code (and login) <- from user to server
	$router->get('/oauth', function (Request $request) use ($server) {
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

		// login page
		return new Response('', 302, array(
			'Location' => "/authorize?client_id={$_GET['client_id']}&redirect_uri={$_GET['redirect_uri']}&response_type={$_GET['response_type']}"
		));
	});

	$router->get('/authorize', function(Request $request) use ($server) {
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

		$title = "Vincular app con Urbank";
		$logo = "Urbank";
		$header = parse_template('header');
		eval("\$header = \"$header\";");
		$footer = parse_template('footer');
		eval("\$footer = \"$footer\";");

		$loginform = "";
		$userinfo = "";
		// the table must exist for the code to come to this point
		$app = Capsule::table('oauth_clients')->where('id', '=', $_GET['client_id'])->first();

		// to improve
		if(!isset($_SESSION['uid'])) {
			$loginform = parse_template('loginform');
			eval("\$loginform = \"$loginform\";");
		} else {
			$user = Capsule::table('users')->where('uid', '=', $_SESSION['uid'])->first();
			$name_formatted = strtoupper("{$user['name']} {$user['lastname']}");
			$userinfo = parse_template('userinfo');
			eval("\$userinfo = \"$userinfo\";");
		}

		$view = parse_template('login');
		eval("\$view = \"$view\";");

		$response = new Response($view, 200, array(
			'Content-type' => 'text/html'
		));
		return $response;
	});

	// step 2: login and authorize
	$router->post('/authorize', function(Request $request) use ($server) {
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

		// if authorize isn't set, redirect to login
		if(!isset($_POST['authorize'])) {
			$response = new Response(null, 302, array(
				'Location' => '/authorize'
			));
		}

		// The user denied the request so redirect back with a message
		elseif($_POST['authorize'] !== 'Autorizar') {
			$error = new \League\OAuth2\Server\Exception\AccessDeniedException;
			$redirectUriClass = new \League\OAuth2\Server\Util\RedirectUri;
			$redirectUri = $redirectUriClass->make(
				$authParams['redirect_uri'],
				array(
					'error' => $error->errorType,
					'message' => $error->getMessage()
				)
			);
			$response = new Response('', 302, array(
				'Location' => $redirectUri
			));
		}

		// The user accepted
		else {
			if(!isset($_SESSION['uid'])) {
				// verify login
				if(verify_login($_POST['dni'], $_POST['pass'])) {
					$user = Capsule::table('users')->where('dni', '=', $_POST['dni'])->first();

					// set the session
					$_SESSION = array();
					$_SESSION['uid'] = $user['uid'];

					// if login ok, redirect to redirect-uri
					// note the owner type is user, since we dont store app-specific information
					$redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', $user['uid'], $authParams);

					$response = new Response(null, 302, array(
						/*'Location' => $redirectUri*/
					));
					
					// TODO: update
					echo "$redirectUri";
				} else {
					// if login not ok, redirect to login form again
					return new Response(null, 302, array(
						'Location' => "/authorize?client_id={$_GET['client_id']}&redirect_uri={$_GET['redirect_uri']}&response_type={$_GET['response_type']}"
					));
				}
			} else {
				$user = Capsule::table('users')->where('uid', '=', $_SESSION['uid'])->first();
				if($user == null) {
					return new Response(null, 302, array(
						'Location' => "/authorize?client_id={$_GET['client_id']}&redirect_uri={$_GET['redirect_uri']}&response_type={$_GET['response_type']}"
					));
				} else {
					// if login ok, redirect to redirect-uri
					$redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', $user['uid'], $authParams);
					$response = new Response(null, 302, array(
						/*'Location' => $redirectUri*/
					));

					// TODO: update
					echo "$redirectUri";
				}
			}
		}

		$response->headers->set('Content-type', 'application/json');
		return $response;
	});

	// step 2: from auth code, get token <- from client to server
	$router->post('/access_token', function(Request $request) use ($server) {
		try {
			$response = $server->issueAccessToken();
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
		$response->headers->set('Content-type', 'application/json');
		return $response;
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
		$response->headers->set('Content-type', 'application/json');
	} catch(\Exception $e) {
		$response = new Response(json_encode(array(
			'status_code' => 500,
			'message' => $e->getMessage(),
		)), 500);
		$response->headers->set('Content-type', 'application/json');
	}

	$response->send();
	
