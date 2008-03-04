<?php
/**
 *
 * Filesystem manipulation utilities.
 *
 * @category Lux
 *
 * @package Lux_Filesystem
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */
class Lux_Filesystem extends Solar_Base
{
    /**
     *
     * User-provided configuration values.
     *
     * @var array
     *
     */
    protected $_Lux_Filesystem = array(
        'chmod_dir'  => 0755,
        'chmod_file' => 0644,
    );

    /**
     *
     * Copies a directory recursivelly.
     *
     * @param string $from From directory path.
     *
     * @param string $to To directory path.
     *
     * @param string $ignore Regular expression to ignore files or directories.
     *
     */
    public function copyDir($from, $to, $ignore = null)
    {
        if ($ignore && preg_match($ignore, $from)) {
            // Ignore the file or dir.
            return;
        }

        if (is_file($from)) {
            // Single file copy.
            $this->copyFile($from, $to);
            return;
        }

        if (! file_exists($to)) {
            // Create destination dir.
            $this->createDir($to);
        }

        $iter = new DirectoryIterator($from);

        foreach($iter as $info) {
            // Ignore dots and links.
            if ($info->isDot() || (!$info->isFile() && !$info->isDir())) {
                continue;
            }

            // Increment the destination with the file/dir name.
            $new_to = $to . DIRECTORY_SEPARATOR . basename($info->getPathname());

            // Copy recursivelly.
            $this->copyDir($info->getPathname(), $new_to, $ignore);
        }
    }

    /**
     *
     * Creates a directory.
     *
     * @param string $path Directory path.
     *
     * @param $chmod Directory permissions.
     *
     * @return bool True on success.
     *
     */
    public function createDir($path, $chmod = null)
    {
        if (! $chmod) {
            $chmod = $this->_config['chmod_dir'];
        }
        
        $base_path = dirname($path);

        if (! is_writable($base_path)) {
            throw $this->_exception('ERR_DIRECTORY_NOT_WRITABLE', array(
                'path' => $base_path,
            ));
        }

        $res = false;
        if (! file_exists($path)) {
            $umask = umask(0000);
            if (mkdir($path)) {
                $res = true;
            }
            umask($umask);
            chmod($path, $chmod);
        }
        return $res;
    }

    /**
     *
     * Copies a file.
     *
     * @param string $from Source directory path.
     *
     * @param string $to Destination directory path.
     *
     * @return bool True on success.
     *
     */
    public function copyFile($from, $to, $chmod = null)
    {
        if (! $chmod) {
            $chmod = $this->_config['chmod_file'];
        }
        
        $res = false;
        if (is_file($from)) {
            $umask = umask(0000);
            if (copy($from, $to)) {
                $res = true;
            }
            umask($umask);
            chmod($to, $chmod);
        }
        return $res;
    }

    /**
     *
     * Extracts a file extension.
     *
     * @param string $filename
     *
     * @return string Extension (without dot).
     *
     */
    public function getExtension($filename)
    {
        $pos = strrpos($filename, '.');
        $ext = substr($filename, $pos + 1);
        return $ext;
    }
}