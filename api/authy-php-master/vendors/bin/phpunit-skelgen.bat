@ECHO OFF
SET BIN_TARGET=%~dp0/../eher/phpunit/bin/phpunit-skelgen
php "%BIN_TARGET%" %*
