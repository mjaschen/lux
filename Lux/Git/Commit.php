<?php

class Lux_Git_Commit extends Solar_Struct {
    
    /**
     * 
     * Config keys
     * 
     * @var string
     * 
     */
    protected $_Lux_Git_Commit = array(
        'repo' => array('Lux_Git_Repo'),
        'data' => array(),
    );
    
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
        
        // set repo
        $this->_repo = Solar::dependency(
            'Lux_Git_Repo',
            $this->_config['repo']
        );
    }
    
    /**
     * 
     * Takes a diff between two commits
     * 
     * With empty first param takes diff between last
     * parent and this commit
     * 
     * @param string $commit If this is not empty
     * the diff will be between this commit and
     * the current
     * 
     * @return array Array of file diffs where one
     * element is an assoc array with keys `name` and`
     * `lines`.
     * 
     */
    public function diff($commit = null)
    {
        if (empty($commit)) {
            $arg = "{$this->commit}^!";
        } else {
            $arg = trim($commit) . ' ' . $this->commit;
        }
        
        $lines = $this->_repo->run('diff', null, $arg);
        
        $files = array();
        
        $count = count($lines);
        $i = 0;
        while ($i < $count) {
            $file = array(
                'mode'  => null,
                'bin'   => false,
                'name'  => null,
                'lines' => array(),
            );
            
            // take first line `diff --git ...`
            $file['lines'][] = $lines[$i];
            
            // file name
            $pos = strpos($lines[$i], 'b/');
            $file['name'] = substr($lines[$i], 13, $pos - 13);
            
            // mode: new, del
            $mode = substr($lines[$i+1], 0, 3);
            if ($mode == 'ind') {
                $mode = 'chg';
            }
            $file['mode'] = $mode;
            
            // binary file?
            $file['bin'] = substr($lines[$i+3], 0, 3) == 'Bin' ? true : false;
            
            // go to next line
            $i++;
            
            // go as long as next diff
            while ($i < $count && substr($lines[$i], 0, 4) != 'diff') {
                // add line
                $file['lines'][] = $lines[$i];
                
                // next line
                $i++;
            }
            
            // add this file
            $files[] = $file;
        }
        
        return $files;
    }
    
    /**
     * 
     * Returns parent commit object
     * 
     * @return void
     * 
     */
    public function parent($parent = 0)
    {
        return $this->_repo->commit($this->parent[(int) $parent]);
    }
}