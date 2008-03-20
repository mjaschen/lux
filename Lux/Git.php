<?php

class Lux_Git extends Solar_Base {
    
    /**
     * undocumented class variable
     *
     * @var string
     */
    protected $_Lux_Git = array(
        'binary' => '/usr/bin/env git',
    );
    
    /**
     * undocumented class variable
     *
     * @var string
     */
    protected $_binary;
    
    /**
     * undocumented class variable
     *
     * @var string
     */
    protected $_git_dir;
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        $this->_binary = $this->_config['binary'];
    }
    
    /**
     * 
     * Runs specified git command and returns output
     * 
     * @return array Lines as array elements
     * 
     */
    public function run($command, $opts = array(), $args = array())
    {
        $cmd = $this->_binary
             . ' --git-dir=' . escapeshellarg($this->_git_dir)
             . " $command";
        
        // options
        foreach ((array) $opts as $opt => $val) {
            $val = escapeshellarg($val);
            if (strlen($opt) > 1) {
                $cmd .= " --$opt=$val";
            } else {
                $cmd .= " -$opt$val";
            }
        }
        
        // args
        $cmd .= ' ' . implode(' ', (array) $args);
        
        $cmd = escapeshellcmd($cmd);
        
        $lines = array();
        
        // execute command
        exec($cmd, $lines, $exit);
        
        // error exit code?
        if ($exit > 0) {
            return $exit;
        }
        
        // done, return lines
        return $lines;
    }
}