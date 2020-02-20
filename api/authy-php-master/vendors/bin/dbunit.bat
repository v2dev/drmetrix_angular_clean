@ECHO OFF
SET BIN_TARGET=%~dp0/../eher/phpunit/bin/dbunit
php "%BIN_TARGET%" %*
