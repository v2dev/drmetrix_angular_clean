@ECHO OFF
SET BIN_TARGET=%~dp0/../eher/phpunit/bin/phpcpd
php "%BIN_TARGET%" %*
