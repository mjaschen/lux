<?php
/**
 * 
 * OO-wrapper around git command-line tools
 * 
 * @category Lux
 * 
 * @package Lux_Git
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
class Lux_Git extends Solar_Base {
    
    /**
     * 
     * Config keys
     * 
     * `binary`
     * : (string) Path to git binary
     * 
     * @var array
     * 
     */
    protected $_Lux_Git = array(
        'binary' => '/usr/bin/env git',
    );
    
    /**
     * 
     * Path to git binary
     * 
     * @var string
     * 
     */
    protected $_binary;
    
    /**
     * 
     * Path to git repo
     * 
     * @var string
     * 
     */
    protected $_git_dir;
    
    /**
     * 
     * List of run shell commands
     * 
     * @var array
     * 
     */
    protected $_debug = array();
    
    /**
     * 
     * Constructor
     * 
     * @param array $config Configuration keys
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        $this->_binary = $this->_config['binary'];
    }
    
    /**
     * 
     * Overloads method calls to git commands
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
     * @param string $command Git sub-command
     * 
     * @param array $opts Options
     * 
     * @param array $args Additional arguments
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
        
        $this->_debug[] = $cmd;
        
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
     * @param string $dir Path to git-dir
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
    
    /**
     * 
     * Returns path to --git-dir
     * 
     * @return string Path with trailing slash
     * 
     */
    public function getDir()
    {
        return $this->_git_dir;
    }
    
    /**
     * 
     * Get list of git commands than have been run
     * 
     * @return array List of shell commands
     * 
     */
    public function getDebug()
    {
        return $this->_debug;
    }
}