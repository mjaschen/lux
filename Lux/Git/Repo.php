<?php
/**
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
class Lux_Git_Repo extends Solar_Base {
    
    /**
     * 
     * Config keys
     * 
     * @var array
     * 
     */
    protected $_Lux_Git_Repo = array(
        'dir' => null,
    );
    
    /**
     * 
     * Git command-line tool
     * 
     * @var Lux_Git
     * 
     */
    public $git;
    
    /**
     * 
     * Config keys
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // instantiate git commander
        $this->git = Solar::factory('Lux_Git');
        
        // set git-dir
        $this->git->setDir($this->_config['dir']);
    }
    
    /**
     * 
     * Examines log
     * 
     * @return void
     * 
     */
    public function log($ref, $n = 10, $page = 1)
    {
        // options
        $opts = array(
            'pretty' => 'raw',
            'n'      => (int) $n,
        );
        
        // run command
        $lines = $this->git->log($opts, $ref);
        
        if (is_int($lines)) {
            // failed for some reason
            return $lines;
        }
        
        return $this->_parseCommit($lines);
    }
    
    /**
     * 
     * Returns branches
     * 
     * @return void
     * 
     */
    public function branch()
    {
        $lines = $this->git->branch();
        
        $branches = array();
        foreach ($lines as $branch) {
            $branches[] = trim(ltrim($branch, '*'));
        }
        
        return $branches;
    }
    
    /**
     * 
     * Fetches and returns repo description
     * 
     * @return string Description
     * 
     */
    public function description()
    {
        $file = $this->git->getDir() . 'description';
        return file_get_contents($file);
    }
    
    /**
     * 
     * Fetches and returns array of trees and blobs
     * from a given tree object and optionally restricted
     * to a path
     * 
     * @param string $tree Tree object name
     * 
     * @param string $path Path name
     * 
     * @return void
     * 
     */
    public function tree($tree = 'HEAD', $path = null)
    {
        // run git ls-tree
        $lines = $this->git->lsTree(null, array($tree, $path));
        
        // was there an error?
        if (is_int($lines)) {
            return false;
        }
        
        $objects = array();
        foreach ($lines as &$line) {
            
            $data = explode(' ', $line);
            
            list($sha1, $name) = explode("\t", $data[2]);
            
            $objects[] = array(
                'mode' => $data[0],
                'type' => $data[1],
                'sha1' => $sha1,
                'name' => $name,
            );
        }
        
        // all done!
        return $objects;
    }
    
    /**
     * 
     * Gets one commit as a commit object
     * 
     * @return void
     * 
     */
    public function commit($spec)
    {
        // options
        $opts = array(
            'pretty' => 'raw',
            'n'      => 1,
        );
        
        $lines = $this->git->log($opts, $spec);
        
        if (is_int($lines)) {
            return false;
        }
        
        $commits = $this->_parseCommit($lines);
        
        // return commit object
        return Solar::factory(
            'Lux_Git_Commit',
            array(
                'repo' => $this,
                'data' => $commits[0],
            )
        );
    }
    
    /**
     * 
     * Parses commits from a set of lines
     * 
     * @return void
     * 
     */
    protected function _parseCommit(&$lines)
    {
        
        // list of commits
        $commits = array();
        
        // line count
        $count = count($lines);
        
        $i = 0;
        while ($i < $count) {
            
            // "raw" is this:
            // @todo any better formats with `format:`?
            
            // commit 457e9f562731a3d9e18c078fa3deb5e3ccced89d
            // tree e1de956a91fa756a321bdd3f96b703f8fd81042c
            // parent 81cae1604237ac48869374c76b869e84c933c0d6
            // author Antti Holvikari <anttih@gmail.com> 1205593549 +0200
            // committer Antti Holvikari <anttih@gmail.com> 1205593549 +0200
            // 
            //     <msg>
            // 
            
            
            // these are the keys we're looking for
            $commit = array(
                'commit'           => null,
                'tree'             => null,
                'parent'           => array(),
                'author'           => null,
                'author_email'     => null,
                'author_time'      => null,
                'author_offset'    => null,
                'committer'        => null,
                'committer_email'  => null,
                'committer_time'   => null,
                'committer_offset' => null,
                'subj'             => '',
                'msg'              => '',
            );
            
            // commit, tree and parent lines
            $list = array('commit', 'tree');
            foreach ($list as $key) {
                $info = explode(' ', $lines[$i]);
                $commit[$key] = $info[1];
                $i++;
            }
            
            // parents: none or many
            while (substr($lines[$i], 0, 6) == 'parent') {
                $commit['parent'][] = ltrim($lines[$i], 'parent ');
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
                $commit["{$key}_offset"] = array_pop($line);
                $commit["{$key}_time"]   = array_pop($line);
                
                // email
                $email = str_replace(array('<', '>'), '', array_pop($line));
                $commit["{$key}_email"] = $email;
                
                // the rest as the person name
                $commit[$key] = implode(' ', $line);
                
                $i++;
            }
            
            // skip empty line
            $i++;
            
            // first line is subject
            $commit['subj'] = trim($lines[$i]);
            $i++;
            
            $msg = array();
            
            // look for message until a new commit starts
            while ($i < $count && substr($lines[$i], 0, 6) != 'commit') {
                $msg[] = trim($lines[$i]);
                $i++;
            }
            
            $commit['msg'] = implode("\n", $msg);
            
            $commits[] = $commit;
        }
        
        // add to commits
        return $commits;
    }
}
