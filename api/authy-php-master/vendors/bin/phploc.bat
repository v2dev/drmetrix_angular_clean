@ECHO OFF
SET BIN_TARGET=%~dp0/../eher/phpunit/bin/phploc
php "%BIN_TARGET%" %*
