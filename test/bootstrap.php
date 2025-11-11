<?php

require './vendor/autoload.php';

if(php_sapi_name()!="cli") {
    die("no cli");
}

const _JEXEC = 1;

\Hamcrest\Util::registerGlobalFunctions();
