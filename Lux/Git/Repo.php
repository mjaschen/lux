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
        
        // parse commits from line
        $commits = array();
        while ($commit = $this->_parseCommit($lines)) {
            $commits[] = $commit;
        }
        
        return $commits;
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
        // run git ls-tree. this will throw on error.
        $lines = $this->git->lsTree(null, array($tree, $path));
        
        $objects = array();
        foreach ($lines as &$line) {
            
            // take file name first
            list($data, $name) = explode("\t", $line);
            
            $data = explode(' ', $data);
            
            $objects[] = array(
                'mode' => $data[0],
                'type' => $data[1],
                'sha1' => $data[2],
                'name' => $name,
            );
        }
        
        // all ok!
        return $objects;
    }
    
    /**
     * 
     * Gets one commit as a commit object
     * 
     * @return void
     * 
     */
    public function commit($commit)
    {
        // options
        $opts = array(
            'pretty' => 'raw',
        );
        
        // make sure this is a commit
        if ($this->objectType($commit) != 'commit') {
            throw $this->_exception('ERR_NOT_COMMIT');
        }
        
        // run git command. this will throw on error.
        $lines = $this->git->show($opts, $commit);
        
        // parse one commit
        $commit = $this->_parseCommit($lines);
        
        // take the diff part
        $diff = array();
        $line = current($lines);
        while ($line !== false) {
            $diff[] = $line;
            $line = next($lines);
        }
        
        $commit['diff'] = $this->_parseDiff($diff);
        
        // return commit object
        return Solar::factory(
            'Lux_Git_Commit',
            array(
                'repo' => $this,
                'data' => $commit,
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
        $commit = array();
        
        // no more lines?
        if (($line = current($lines)) === false) {
            return false;
        }
        
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
            $info = explode(' ', $line);
            $commit[$key] = $info[1];
            $line = next($lines);
        }
        
        // parents: none or many
        while (substr($line, 0, 6) == 'parent') {
            $commit['parent'][] = ltrim($line, 'parent ');
            $line = next($lines);
        }
        
        // author and committer lines
        $list = array('author', 'committer');
        foreach ($list as $key) {
            // author
            $line = explode(' ', $line);
            
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
            
            $line = next($lines);
        }
        
        // skip empty line
        $line = next($lines);
        
        // first line is subject
        $commit['subj'] = trim($line);
        $line = next($lines);
        
        // look for message until an empty line
        $msg = array();
        while (in_array(substr($line, 0, 1), array('', ' '))) {
            $msg[] = trim($line);
            
            // if this was the last element, break out!
            if (($line = next($lines)) === false) {
                break;
            }
        }
        
        // the last line is always empty
        array_pop($msg);
        
        $commit['msg'] = implode("\n", $msg);
        
        // add to commits
        return $commit;
    }
    
    /**
     * 
     * Gets object type for a given object name
     * 
     * @param string $object Object name. I.e `HEAD`.
     * 
     * @return string Object type.
     * I.e `HEAD` would give you `commit`.
     * 
     */
    public function objectType($object)
    {
        $opts = array(
            't' => null,
        );
        
        try {
            // run git-cat-file -t
            $line = $this->git->catFile($opts, $object);
            
        } catch (Lux_Git_Exception_InvalidCommand $e) {
            // if we catch an error here it means the object
            // is not valid object name so we can just return
            // false
            return false;
        }
        
        return $line[0];
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function diff($spec)
    {
        $lines = $this->_repo->git->diff(null, $spec);
        
        return $this->_parseDiff($lines);
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    protected function _parseDiff(&$lines)
    {
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
}
