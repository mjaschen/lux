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
     * @param string $from Source directory path.
     *
     * @param string $to Destination directory path.
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

        // Fix dir name: always end with a slash.
        $to = Solar_Dir::fix($to);

        if (! file_exists($to)) {
            // Create destination dir.
            mkdir($to, $this->_config['chmod_dir'], true);
        }

        $iter = new DirectoryIterator($from);

        foreach ($iter as $file) {
            // Ignore dots and links.
            if ($file->isDot()) {
                continue;
            }

            // Increment the destination with the filename.
            $new_to = $to . $file->getFilename();

            // Copy recursivelly.
            $this->copyDir($file->getPathname(), $new_to, $ignore);
        }
    }

    /**
     *
     * Deletes a directory or directory files recursivelly.
     *
     * @param string $path Directory path.
     *
     * @param bool $remove_dir If false, keeps the directory structure, removing
     * only files.
     *
     */
    public function rmDir($path, $remove_dir = true)
    {
        if (is_file($path)) {
            // Single file deletion.
            unlink($path);
            return;
        }

        $iter = new DirectoryIterator($path);

        // Delete all files and dirs inside the directory.
        foreach ($iter as $file) {
            if ($file->isDot()) {
                continue;
            }
            $this->rmDir($file->getPathname(), $remove_dir);
        }

        // Delete the directory itself.
        if ($remove_dir) {
            rmdir($path);
        }
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

        $res = copy($from, $to);

        if ($res) {
            $umask = umask(0000);
            chmod($to, $chmod);
            umask($umask);
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