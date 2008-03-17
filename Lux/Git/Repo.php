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
        
        // add one empty line so the loop ends cleanly
        $lines[] = '';
        
        // parse...
        
        // commit 457e9f562731a3d9e18c078fa3deb5e3ccced89d
        // tree e1de956a91fa756a321bdd3f96b703f8fd81042c
        // parent 81cae1604237ac48869374c76b869e84c933c0d6
        // author Antti Holvikari <anttih@gmail.com> 1205593549 +0200
        // committer Antti Holvikari <anttih@gmail.com> 1205593549 +0200
        // 
        //     <msg>
        // 
        
        // list of commits
        $commits = array();
        
        // line count
        $count = count($lines);
        
        $i = 0;
        while ($i < $count) {
            
            // these are the keys we're looking for
            $commit = array(
                'commit'          => null,
                'tree'            => null,
                'parent'          => null,
                'author'          => null,
                'author_email'    => null,
                'author_time'     => null,
                'committer'       => null,
                'committer_email' => null,
                'committer_time'  => null,
                'msg'             => array(),
            );
            
            // commit, tree and parent lines
            $list = array('commit', 'tree', 'parent');
            foreach ($list as $key) {
                $info = explode(' ', $lines[$i]);
                $commit[$key] = $info[1];
                $i++;
            }
            
            // author and committer lines
            $list = array('author', 'committer');
            foreach ($list as $key) {
                // author
                $line = explode(' ', $lines[$i]);
                
                // take off the literal "author"
                array_shift($line);
                
                // timezone and unix timestamp
                $offset = array_pop($line);
                $time   = array_pop($line);
                
                // email
                $email = str_replace(array('<', '>'), '', array_pop($line));
                
                $commit["{$key}_time"]  = "$time $offset";
                $commit["{$key}_email"] = $email;
                
                // the rest as the person name
                $commit[$key] = implode(' ', $line);
                
                $i++;
            }
            
            // skip empty line
            $i++;
            
            // look for message until there's
            // a new line or lines run out
            while ($lines[$i] != '') {
                $commit['msg'][] = trim($lines[$i]);
                $i++;
            }
            
            // add to commits
            $commits[] = $commit;
            
            // skip empty line
            $i++;
        }
        
        return $commits;
    }
}
