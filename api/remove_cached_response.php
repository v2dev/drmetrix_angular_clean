<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/queries.php';

ignore_user_abort();
set_time_limit(0);

$sql = 'UPDATE cached_response SET clause = ""';
execute_sql($sql);

closeConnection();