<?php
/**
 * 
 * Uses pecl_http for a standalone HTTP request.
 * 
 * @package Lux
 *
 * @subpackage Lux_Http
 *
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
class Lux_Http_Request_Adapter_Http extends Solar_Http_Request_Adapter {

    /**
     * 
     * Support method to make the request, then return headers and content.
     * 
     * @param string $uri The URI get a response from.
     * 
     * @param array $headers A sequential array of header lines for the request.
     * 
     * @param string $content A string of content for the request.
     * 
     * @return array A sequential array where element 0 is a sequential array of
     * header lines, and element 1 is the body content.
     * 
     * @todo Implement an exception for timeouts.
     * 
     */
    protected function _fetch($uri, $headers, $content)
    {
        $http = new HttpRequest;
        
        // set HTTP method
        $http->setMethod(constant("HTTP_METH_{$this->_method}"));
        
        // set specialized headers and retain all others
        $http_header = array();
        foreach ($headers as $header) {
            $pos = strpos($header, ':');
            $label = substr($header, 0, $pos);
            $value = substr($header, $pos + 2);
            
            $http_header[$label] = $value;
        }
        $http->setOptions(array('headers' => $http_header));
        
        $http->setUrl($uri);
        
        // decide what content to set
        if (! empty($content)) {
            if ($this->_method == 'POST') {
                $http->addRawPostData($content);
            } elseif ($this->_method == 'PUT') {
                $http->addPutData($content);
            }
        }
        
        // make the request
        $response = $http->send();
        
        $version = $response->getHttpVersion();
        $code    = $response->getResponseCode();
        $status  = $response->getResponseStatus();
        
        $headers = $response->getHeaders();
        
        // build status line. i.e. HTTP/1.1 200 OK
        $status_line = "HTTP/$version $code $status";
        
        // add status line as the first header
        array_unshift($headers, $status_line);
        
        return array($headers, $response->getBody());
    }
}