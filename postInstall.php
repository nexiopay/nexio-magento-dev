<?php

echo "THIS IS A TEST\n";
error_log("**** THIS IS A TEST\n",3,"/tmp/composer.debug");

echo "-->". print_r($argv,true). "\n";

error_log(print_r($argv,true)."\n",3,"/tmp/composer.debug");

