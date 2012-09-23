<?php
/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;



ini_set('error_reporting',E_ALL^E_NOTICE);
ini_set('display_errors',1);



// Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require VENDOR_DIR . '/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->enableDebugger(WWW_DIR . '/../log');

// Enable RobotLoader - this will load all classes automatically

$configurator->setTempDirectory(WWW_DIR . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(APP_DIR . '/config.neon');

$container = $configurator->createContainer();

Addons\Panels\Callback::register($container);
//Kdyby\Extension\Diagnostics\HtmlValidator\DI\ValidatorExtension::register($configurator);
try {
	dibi::setConnection ( $container->dibi );
} catch ( Exception $e ) {
	echo "Nepodarilo sa pripojit";
	exit ();
}

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


// Configure and run the application!
//$container->application->run();
