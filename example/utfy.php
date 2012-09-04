<?php

require '../lib/Utfy/Grabber.php';

use Utfy\Grabber;

$grabber = new Grabber();

$grabber->addUrl('https://trust-host.ru/', array( CURLOPT_SSL_VERIFYPEER =>0,  CURLOPT_SSL_VERIFYHOST => 0));

$grabber->addUrl(array('http://koi8.pp.ru/', 'http://google.com'));

$responses = $grabber->execute();
foreach ($responses as $url => $value) {
    
    echo $url, "=========>", PHP_EOL;
    if(!$value->hasError()){
        echo $value->getEncoding(), PHP_EOL, $value->getEncodedBody();
        
    }else{
        echo $value->getError();
    }
        
}
