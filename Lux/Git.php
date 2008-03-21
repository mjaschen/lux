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
     * Overload method calls to git commands
     * 
     * @return void
     * 
     */
    protected function __call($method, $args)
    {
        // gitLog => git-log
        $method = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $method));
        
        return $this->_run($method, $args[0], $args[1]);
    }
    
    /**
     * 
     * Runs specified git command and returns output
     * 
     * @return array Lines as array elements
     * 
     */
    protected function _run($command, $opts = array(), $args = array())
    {
        $cmd = $this->_binary
             . ' --git-dir=' . escapeshellarg($this->_git_dir)
             . " $command";
        
        // options
        foreach ((array) $opts as $opt => $val) {
            $val = escapeshellarg($val);
            
            // long option?
            if (strlen($opt) > 1) {
                
                // long option without value?
                if (empty($val)) {
                    $cmd .= " --$opt";
                } else {
                    $cmd .= " --$opt=$val";
                }
                
            } else {
                $cmd .= " -$opt$val";
            }
        }
        
        // args?
        if (! empty($args)) {
            $cmd .= ' ' . implode(' ', (array) $args);
        }
        
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
    
    /**
     * 
     * Sets `--git-dir`
     * 
     * Checks to see if dir exists
     * 
     * @return void
     * 
     */
    public function setDir($dir)
    {
        // "fix" dir
        $dir = Solar_Dir::fix($dir);
        
        // set dir
        $this->_git_dir = $dir;
        
        $opts = array(
            'git-dir' => null,
        );
        
        $out = $this->_run('rev-parse', $opts);
        
        if (is_int($out)) {
            throw $this->_exception(
                'ERR_REPO_NOT_FOUND',
                array('dir' => $dir)
            );
        }
        
    }
}