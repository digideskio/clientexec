<?php

/************************************************************************
*
* TO CUSTOMIZE SUPPORT PIPING DON'T MODIFY THIS FILE,
* INSTEAD MODIFY:
*                 modules/admin/actions/PipeEmail.php
*                 modules/support/actions/Support_Event_PipedEmail.php
*
*************************************************************************/

define('NE_ADMIN', true);

$_GET['action'] = 'pipeemail';
$_GET['fuse'] = 'support';
$_GET['controller'] = 'email';

chdir(dirname(__FILE__));
require_once 'library/front.php';
