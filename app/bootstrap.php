<?php

/**
 * My NApplication bootstrap file.
 */
 


// Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';


// Enable NDebug for error visualisation & logging
NDebug::$strictMode = TRUE;
NEnvironment::setMode('production', FALSE);

$_ips = array(
	'95.103.8.133'
);

if ( in_array($_SERVER['REMOTE_ADDR'], $_ips) ) {
  	NEnvironment::setMode('production', FALSE);  	
	NDebug::enable(false); 
} else {
	NDebug::enable( NDebug::DETECT, LOG_DIR, 'form@vizion.sk');
}unset($_ips);

NDebug::enable();


// Load configuration from config.neon file
NEnvironment::loadConfig();


// Configure application
$application = NEnvironment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;
//var_dump(NEnvironment::getConfig()->database);exit;

	dibi::connect ( (array)NEnvironment::getConfig()->database );


// Setup router
{
	$router = $application->getRouter();

	$router[] = new NRoute('index.php', 'Homepage:default', NRoute::ONE_WAY);

	$router[] = new NRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
};


// Run the application!
$application->run();
