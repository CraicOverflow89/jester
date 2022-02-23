<?php

// Include Dependencies
require_once '../vendor/autoload.php';

// Include Framework
require_once 'jester/app.php';

// Use Classes
use \Jester\JesterFramework;

// Framework Settings
JesterFramework::createTwig();

// Invoke Framework
JesterFramework::invoke();