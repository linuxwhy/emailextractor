<?php

define('WWW_DIR', __DIR__);

define('APP_DIR', WWW_DIR . '/../app');
define('LIBS_DIR', WWW_DIR . '/../libs');
define('VENDOR_DIR', WWW_DIR . '/../vendor');
define('DATA_DIR', WWW_DIR . '/../data');

require VENDOR_DIR.'/nette/nette/Nette/loader.php';

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
	->addDirectory(VENDOR_DIR.'/dg/dibi/dibi')
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(APP_DIR . '/config.neon');

$container = $configurator->createContainer();


require WWW_DIR . '/UnitTestCase.php';

require WWW_DIR . '/IntegrationTestCase.php';
require WWW_DIR . '/SeleniumTestCase.php';
