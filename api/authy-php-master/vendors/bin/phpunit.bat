@ECHO OFF
SET BIN_TARGET=%~dp0/../eher/phpunit/bin/phpunit
php "%BIN_TARGET%" %*
