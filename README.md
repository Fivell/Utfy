### About

Utfy is library for grabbing web content and encode it to utf-8.

### Requirements

* PHP >= 5.3
* PHP cURL
* PHP mbstring

### Usage

    use Utfy\Grabber;
    $grabber = new Grabber();
    //add array of urls
    $grabber->addUrl(array('http://koi8.pp.ru/', 'http://google.com'));
    //add url with custom curl options
    $grabber->addUrl('https://trust-host.ru/', array( CURLOPT_SSL_VERIFYPEER =>0,  CURLOPT_SSL_VERIFYHOST => 0));
    //grab!   
    $responses = $grabber->execute();
    foreach ($responses as $url => $value) {
        echo $url, PHP_EOL;
        if($value->hasError()){
            echo $value->getError();
            
        }else{
            echo $value->getEncoding(), PHP_EOL, $value->getEncodedBody();
        }
        
    }
