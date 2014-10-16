<?php

$_GET['controller'] = 'index';
$_GET['fuse'] = 'admin';
$_GET['action'] = 'executeservice';
define('RUNNING_SERVICE_SCRIPT', true);

chdir(dirname(__FILE__));
require 'library/front.php';

?>
