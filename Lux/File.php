<?php
/**
 * 
 * File operation class
 * 
 * @category Lux
 * 
 * @package Lux_File
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * File operation class
 * 
 * @category Lux
 * 
 * @package Lux_File
 * 
 */
class Lux_File extends Solar_Base {
    
    /**
     * 
     * Applies a user function for every row in a csv file
     * 
     * Applies a callback for every row in a file. File's values on the
     * first row are treated as array keys and other rows as array values
     * for the array that is passed to the callback function.
     * 
     * @param string $file Filename
     * 
     * @param callback $callback Callback to apply for every line
     * 
     * @param int @length Line length
     * 
     * @param string $delim Field delimiter, default is ',' (comma)
     * 
     * @param string $enclose Enclose every field with this char
     * 
     * @return int Line count with the first column row removed.
     * 
     */
    public function csvReadLineAssoc($file, $callback, $length = 1000, $delim = ',',
        $enclose = null) {
            
        if (! ($file = Solar::fileExists($file))) {
            throw $this->_exception('ERR_FILE_EXISTS');
        }
        
        $fp = @fopen($file, 'r');
        
        $line = 0;
        // read line-by-line
        while (($data = fgetcsv($fp, (int) $length, (string) $delim)) !== FALSE) {
            if ($line === 0) {
                $keys = $data;
                $line++;
                continue;
            }
            
            // make first line values the keys and data
            // as values
            if (! $data = array_combine($keys, $data)) {
                throw $this->_exception('ERR_COMBINE');
            }
            
            // call the callback
            call_user_func($callback, $data);
            
            // next line begins
            $line++;
        }
        
        // return real line count
        return --$line;
    }
}
