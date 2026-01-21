<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes // FIX LOOP Timeout Issue
set_time_limit(300); // FIX LOOP Timeout Issue
ini_set('memory_limit', '1024M'); // or you could use 1G
// date_default_timezone_set ( trim( shell_exec("timedatectl | grep 'Time zone:' | awk '{ print $3 }'")) );

require __DIR__ . '/../constants.php';

use Respect\Validation\Validator as v;
use Slim\Factory\AppFactory;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

session_start();

$_SESSION['error']=array();
$_SESSION['error']['status']=true;
$_SESSION['error']['authorized']=false;
$_SESSION['error']['message']='Unknown Error';

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use tgui\Controllers\APIHA\HAGeneral;
use Illuminate\Database\Capsule\Manager as Capsule;

// Create Container
$container = new Container();

// Database settings
$container->set('settings', function() {
	return [
		'displayErrorDetails' => true,
		'db' => [
			'default' => [
				'driver' => 'mysql',
				'host'	=> DB_HOST,
				'database' => DB_NAME,
				'username' => ( ! HAGeneral::isSlave() ) ? DB_USER : 'tgui_ro',
				'password' => ( ! HAGeneral::isSlave() ) ? DB_PASSWORD : HAGeneral::isSlave(),
				'charset' => DB_CHARSET,
				'collation' => DB_COLLATE,
				'prefix' => ''
			],
			'logging' => [
				'driver' => 'mysql',
				'host'	=> DB_HOST,
				'database' => DB_NAME_LOG,
				'username' => DB_USER,
				'password' => DB_PASSWORD,
				'charset' => DB_CHARSET,
				'collation' => DB_COLLATE,
				'prefix' => ''
			]
		]
	];
});

$capsule = new Capsule;
$capsule->addConnection($container->get('settings')['db']['default'], 'default');
$capsule->addConnection($container->get('settings')['db']['logging'], 'logging');
$capsule->setAsGlobal();
$capsule->schema();
$capsule->bootEloquent();

$container->set('db', function() use ($capsule) {
	return $capsule;
});

$container->set('validator', function($container) {
	return new \tgui\Validation\Validator;
});

$container->set('HomeController', function($container) {
	return new \tgui\Controllers\HomeController($container);
});

$container->set('AuthController', function($container) {
	return new \tgui\Controllers\Auth\AuthController($container);
});

$container->set('APIUsersCtrl', function($container) {
	return new \tgui\Controllers\API\APIUsers\APIUsersCtrl($container);
});

$container->set('APIUpdateCtrl', function($container) {
	return new \tgui\Controllers\APIUpdate\APIUpdateCtrl($container);
});

$container->set('APIUserGrpsCtrl', function($container) {
	return new \tgui\Controllers\API\APIUserGrps\APIUserGrpsCtrl($container);
});

$container->set('APISettingsCtrl', function($container) {
	return new \tgui\Controllers\APISettings\APISettingsCtrl($container);
});

$container->set('APIHACtrl', function($container) {
	return new \tgui\Controllers\APIHA\APIHACtrl($container);
});

$container->set('APINotificationCtrl', function($container) {
	return new \tgui\Controllers\APINotification\APINotificationCtrl($container);
});
$container->set('APIDevCtrl', function($container) {
	return new \tgui\Controllers\APIDev\APIDevCtrl($container);
});

$container->set('TACDevicesCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACDevices\TACDevicesCtrl($container);
});
$container->set('TACDeviceGrpsCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACDeviceGrps\TACDeviceGrpsCtrl($container);
});
$container->set('TACUsersCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACUsers\TACUsersCtrl($container);
});

$container->set('TACUserGrpsCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACUserGrps\TACUserGrpsCtrl($container);
});

$container->set('TACACLCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACACL\TACACLCtrl($container);
});

$container->set('TACServicesCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACServices\TACServicesCtrl($container);
});

$container->set('TACCMDCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACCMD\TACCMDCtrl($container);
});

$container->set('TACConfigCtrl', function($container) {
	return new \tgui\Controllers\TACConfig\TACConfigCtrl($container);
});

$container->set('TACExportCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACExport\TACExportCtrl($container);
});
$container->set('TACImportCtrl', function($container) {
	return new \tgui\Controllers\TAC\TACImport\TACImportCtrl($container);
});

$container->set('ObjAddress', function($container) {
	return new \tgui\Controllers\Obj\ObjAddress\ObjAddress($container);
});
$container->set('APICheckerCtrl', function($container) {
	return new \tgui\Controllers\APIChecker\APICheckerCtrl($container);
});
$container->set('TACReportsCtrl', function($container) {
	return new \tgui\Controllers\TACReports\TACReportsCtrl($container);
});
$container->set('APILoggingCtrl', function($container) {
	return new \tgui\Controllers\APILogging\APILoggingCtrl($container);
});
$container->set('APIBackupCtrl', function($container) {
	return new \tgui\Controllers\APIBackup\APIBackupCtrl($container);
});
$container->set('APIDownloadCtrl', function($container) {
	return new \tgui\Controllers\APIDownload\APIDownloadCtrl($container);
});
$container->set('MAVISLDAP', function($container) {
	return new \tgui\Controllers\MAVIS\MAVISLDAP\MAVISLDAPCtrl($container);
});
$container->set('MAVISLocal', function($container) {
	return new \tgui\Controllers\MAVIS\MAVISLocal\MAVISLocalCtrl($container);
});
$container->set('MAVISOTP', function($container) {
	return new \tgui\Controllers\MAVIS\MAVISOTP\MAVISOTPCtrl($container);
});
$container->set('MAVISSMS', function($container) {
	return new \tgui\Controllers\MAVIS\MAVISSMS\MAVISSMSCtrl($container);
});

$container->set('ConfManager', function($container) {
	return new \tgui\Controllers\ConfManager\ConfManager($container);
});
$container->set('ConfModels', function($container) {
	return new \tgui\Controllers\ConfManager\ConfModels($container);
});
$container->set('ConfDevices', function($container) {
	return new \tgui\Controllers\ConfManager\ConfDevices($container);
});
$container->set('ConfGroups', function($container) {
	return new \tgui\Controllers\ConfManager\ConfGroups($container);
});
$container->set('ConfigCredentials', function($container) {
	return new \tgui\Controllers\ConfManager\ConfigCredentials($container);
});
$container->set('ConfQueries', function($container) {
	return new \tgui\Controllers\ConfManager\ConfQueries($container);
});

$container->set('HAGeneral', function($container) {
	return new \tgui\Controllers\APIHA\HAGeneral($container);
});
$container->set('HAMaster', function($container) {
	return new \tgui\Controllers\APIHA\HAMaster($container);
});
$container->set('HASlave', function($container) {
	return new \tgui\Controllers\APIHA\HASlave($container);
});

/*$container->set('csrf', function($container) {
	return new \Slim\Csrf\Guard;
});*/

$container->set('auth', function($container) {
	return new \tgui\Auth\Auth;
});

// Create App with container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

//$app->add(new \tgui\Middleware\ValidationErrorsMiddleware($container));
//$app->add(new \tgui\Middleware\OldInputMiddleware($container));
$app->add(new \tgui\Middleware\ChangeHeaderMiddleware($container));

$app->add(new Tuupola\Middleware\JwtAuthentication([
		//"path" => "/api/auth/123",
		"ignore" => ["/auth", "/tacacs/user/change_passwd/change/", "/backup/download/", "/backup/upload/", '/ha/', '/export/', '/import/'],
		"attribute" => "decoded_token_data",
    "secret" => DB_PASSWORD,
		"algorithm" => ["HS256"],
		"secure" => false,
		"error" => function ($response, $arguments) {
				$data["status"] = "error";
				$data["message"] = $arguments["message"];
				return $response
						->withHeader("Content-Type", "application/json")
						->withBody(\Slim\Psr7\Factory\StreamFactory::create(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
		}
]));

//$app->add($container->get('csrf')); //Turn on CSRF for all project//

v::with('tgui\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';
