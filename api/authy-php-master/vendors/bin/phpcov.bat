@ECHO OFF
SET BIN_TARGET=%~dp0/../eher/phpunit/bin/phpcov
php "%BIN_TARGET%" %*
