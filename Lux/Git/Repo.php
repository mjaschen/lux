<?php

class Lux_Git_Repo extends Lux_Git {
    
    /**
     * undocumented class variable
     *
     * @var string
     */
    protected $_Lux_Git_Repo = array(
        'git_dir'   => null,
    );
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // "fix" and set --git-dir
        $this->_git_dir = Solar_Dir::fix($this->_config['git_dir']);
        
        if (file_exists($this->_git_dir . DIRECTORY_SEPARATOR . '.git')) {
            throw $this->_exception(
                'ERR_REPO_NOT_BARE',
                array('git-dir' => $this->_git_dir)
            );
        }
    }
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function log($ref, $n = 10, $page = 1)
    {
        // options
        $opts = array(
            'pretty' => 'raw',
            'n'      => (int) $n,
        );
        
        // run command
        $lines = $this->run('log', $opts, $ref);
        
        return $lines;
        // parse...
    }
}
