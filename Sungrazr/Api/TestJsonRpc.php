<?php
/**
 *
 * An example *client* for a JSON-RPC service.
 *
 */
class Sungrazr_Api_TestJsonRpc extends Solar_Controller_Page
{
    /**
     *
     * User-provided configuration.
     *
     * Config keys are ...
     *
     * `uri`
     * : (string) Service URI.
     *
     * @var array
     *
     */
    protected $_Sungrazr_Api_TestJsonRpc = array(
        'uri' => null,
    );

    /**
     *
     * The default page controller action.
     *
     * @var string
     *
     */
    protected $_action_default = 'test';

    public function actionTest()
    {
        $uri = $this->_config['uri'] . '/json-rpc';

        $call = array(
            'method' => 'stuff.create',
            'params' => array(
                array('apikey' => 'foo'),
                array('properties' => array(
                    'first_name' => 'John',
                    'last_name'  => 'Public',
                )),
            ),
            'id'     => 42,
        );

        $call = Solar::factory('Solar_Json')->encode($call);

        $request = Solar::factory('Solar_Http_Request');
        $response = $request->setUri($uri)
                    ->setMethod('post')
                    ->setContentType('application/json')
                    ->setContent($call)
                    ->fetch();

        $code = $response->getStatusCode();
        $msg = $response->content;

        Solar::dump($code);
        Solar::dump($msg);
        die();
    }
}
