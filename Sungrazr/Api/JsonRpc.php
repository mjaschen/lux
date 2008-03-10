<?php
/**
 *
 * An example app for a JSON-RPC service.
 *
 */
class Sungrazr_Api_JsonRpc extends Sungrazr_Controller_Service
{
    /**
     *
     * User-provided configuration.
     *
     * Config keys are ...
     *
     * `server`
     * : (dependency) Server config
     *
     * @var array
     *
     */
    protected $_Sungrazr_Api_JsonRpc = array(
        'server' => array(
            'adapter' => 'Sungrazr_Controller_Server_Adapter_JsonRpc',
        ),
    );

    /**
     *
     * Description test.  Narrative test.
     *
     * @param int $apikey The authentication key.
     *
     * @param array $properties Properties for the new record.
     *
     */
    public function create($apikey, $properties)
    {
    }
}