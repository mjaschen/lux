<?php
/**
 * 
 * Provides an interface to Amazon's Simple Storage System (S3).
 * 
 * @package Lux
 * 
 * @subpackage Lux_Service
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
class Lux_Service_Amazon_S3 extends Solar_Base
{
    /**
     * 
     * Provides the default values for internal configuration
     * 
     * Keys are:
     *  `access_key`
     * : (string) The Amazon provided S3 Access Key
     * 
     * `secret_key`
     * : (string) The Amazon provided S3 Secret Key
     * 
     * @var array
     * 
     */
    protected $_Lux_Service_Amazon_S3 = array(
        'access_key' => null,
        'secret_key' => null,
        'endpoint'   => 's3.amazonaws.com',
    );
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_endpoint;
    
    /**
     * 
     * Constructor
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_endpoint = $this->_config['endpoint'];
    }
    
    /**
     * 
     * Gets a bucket object
     * 
     * @param string $name Name of the bucket
     * 
     * @return Lux_Service_Amazon_S3_Resource_Bucket
     * 
     */
    public function getBucket($name)
    {
        $bucket = Solar::factory(
            'Lux_Service_Amazon_S3_Resource_Bucket',
            array('s3' => $this)
        );
        $bucket->name = $name;
        return $bucket;
    }
    
    /**
     * 
     * Fetches all buckets
     * 
     * @return array
     * 
     */
    public function fetchBuckets()
    {
        $service = Solar::factory(
            'Lux_Service_Amazon_S3_Resource_Service',
            array('s3' => $this)
        );
        
        return $service->fetchBuckets();
    }
    
    /**
     * 
     * Makes an HTTP request to Amazon
     * 
     * @param string $method HTTP method
     * 
     * @param object $resource Resource object
     * 
     * @param int|array $expect Expect the HTTP status
     * code to be one of these, if not, will throw an
     * exception. 
     * 
     * @param array $params A list of query params
     * 
     * @return Solar_Http_Response
     * 
     */
    public function fetch($method, Lux_Service_Amazon_S3_Resource $resource,
        $expect = 200, $params = array())
    {
        // new HTTP request
        $request = Solar::factory('Solar_Http_Request');
        
        // create Solar_Uri object based on resource and params
        $uri = $this->_buildUri($resource, $params);
        
        // build URI from the uri object
        $request->setUri($uri)
                ->setMethod($method)
                ->setContent($resource->getBody());
        
        // make sure these headers are sent
        $headers = array(
            'Host'           => $uri->host,
            'Date'           => gmdate('r'),
            'Content-Length' => strlen($resource->getBody()),
        );
        
        // merge headers from resource with these
        $headers = array_merge(
            $resource->getHeaders($method),
            $headers
        );
        
        // add all headers
        foreach ($headers as $name => $val) {
            $request->setHeader($name, $val);
        }
        
        // generate auth cert
        $this->_sign($request, $uri, $resource);
        
        // make the request
        $response = $request->fetch();
        
        // HTTP status code from response
        $code = $response->getStatusCode();
        
        // if code is not one of expected, throw an exception
        if (! in_array($code, (array) $expect)) {
            throw $this->_error($response);
        }
        
        // all seems ok!
        return $response;
    }
    
    /**
     * 
     * Signs a request by adding Authorization header
     * 
     * @param object $request Request object
     * 
     * @param object $uri URI object
     * 
     * @param object $resource Resource object
     * 
     * @return void
     * 
     */
    private function _sign(Solar_Http_Request_Adapter $request, Solar_Uri $uri,
        Lux_Service_Amazon_S3_Resource $resource)
    {
        // get all request options
        $opts = $request->getOptions();
        
        $method  = $opts['method'];
        $headers = $opts['headers'];
        
        ksort($headers);
        $amz_headers = array();
        foreach ($headers as $header => $value) {
            $header = strtolower($header);
            if (substr($header, 0, 6) == 'x-amz-') {
                $amz_headers[] = "$header:$value";
            }
        }
        
        $canon_resource = '/';
        if ($resource instanceof Lux_Service_Amazon_S3_Resource_Bucket
        || $resource instanceof Lux_Service_Amazon_S3_Resource_Object) {
            $canon_resource .= $resource->getBucketName() . '/';
        }
        
        if (! empty($uri->path)) {
            $canon_resource .= implode('/', $uri->path);
            
            // add format?
            $canon_resource .= empty($uri->format) ? '' : ".{$uri->format}";
        }
        
        $content_md5 = '';
        if (isset($headers['Content-MD5'])) {
            $content_md5 = $headers['Content-MD5'];
        }
        
        $content_type = '';
        if (isset($headers['Content-Type'])) {
            $content_type = $headers['Content-Type'];
        }
        
        // build "stringToSign"
        $string_to_sign = $method                     . "\n"
                        . $content_md5                . "\n"
                        . $content_type               . "\n"
                        . $headers['Date']            . "\n"
                        . implode("\n", $amz_headers)
                        . (!empty($amz_headers) ? "\n" : '')
                        . $canon_resource;
        
        $hash = hash_hmac(
            'sha1',
            $string_to_sign,
            $this->_config['secret_key']
        );
        
        $signature = base64_encode(pack('H*', $hash));
        
        // set auth header
        $request->setHeader(
            'Authorization',
            "AWS {$this->_config['access_key']}:$signature"
        );
    }
    
    /**
     * 
     * Builds a URI object based
     * 
     * @param object $resource Amazon resource object
     * 
     * @param array $params Query parameters as an assoc array
     * 
     * @return Solar_Uri
     * 
     */
    protected function _buildUri(Lux_Service_Amazon_S3_Resource $resource,
        $params = array())
    {
        $uri = Solar::factory('Solar_Uri');
        
        // only http for now
        $uri->scheme = 'http';
        
        $uri->host = $this->_endpoint;
        
        if (! $resource instanceof Lux_Service_Amazon_S3_Resource_Service) {
            $bucket = $resource->getBucketName();
            $uri->host = "$bucket.{$this->_endpoint}";
        }
        
        // requests on objects set the object key in the path
        if ($resource instanceof Lux_Service_Amazon_S3_Resource_Object) {
            $uri->setPath($resource->key);
        }
        
        // i.e /?location
        foreach ($params as $key => $val) {
            $uri->query[$key] = $val;
        }
        
        return $uri;
    }
    
    /**
     * 
     * Parses a response for error info and returns
     * a correct exception class
     * 
     * @return Lux_Service_Amazon_S3_Exception
     * 
     */
    protected function _error(Solar_Http_Response $response)
    {
        $status = $response->getStatusCode() . ' ' . $response->getStatusText();
        
        // add headers to info
        $info = array(
            'status'  => $status,
            'headers' => $response->getHeaders(),
            'extra'   => array(),
        );
        
        $content = $response->getContent();
        
        // default error code
        $code = 'ERR_UNEXPECTED_STATUS';
        
        $status_code = $response->getStatusCode();
        
        // if this was an actual error with error content
        // we'll parse the content
        if ($status_code >= 400 && ! empty($content)) {
            // parse error message
            $xml = new SimpleXMLElement($content);
            
            foreach ($xml->children() as $node) {
                $name = strtolower($node->getName());
                $info['extra'][$name] = (string) $node;
            }
            
            $code = $info['extra']['code'];
            unset($info['extra']['code']);
        }
        
        // look up an exception class for this error code
        return $this->_exception(
            $code,
            $info
        );
    }
}
