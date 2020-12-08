<?php
namespace smet;
class Api {
    private $urls;
    private $requestType;


    public function addUrl($url, $requestType,$callBack){
        $this->urls[$url] = $callBack;
        $this->requestType[$url] = $requestType;
    }

    public function isBind($url, $requestType){
        return
            isset($this->urls[$url]) &&
            isset($this->requestType[$url]) &&
            strpos($this->requestType[$url], $requestType) !== false;
    }

    public function handle($url, $params){
        $callable = $this->urls[$url];
        return
            call_user_func($callable, $params);
    }

}