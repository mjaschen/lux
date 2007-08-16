<?php
/**
 *
 * Handy filesystem manipulation utilities.
 *
 * @category Lux
 *
 * @package Lux_Filesystem
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Handy filesystem manipulation utilities.
 *
 * @category Lux
 *
 * @package Lux_Filesystem
 *
 */
class Lux_Filesystem extends Solar_Base {

    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `suppress_warnings`
     * : (bool) Whether or not to suppress warnings during filesystem
     * operations.
     *
     * @var array
     *
     */
    protected $_Lux_Filesystem = array(
        'suppress_warnings' => true,
    );

    /**
     *
     * Copies a directory recursivelly.
     *
     * @param string $from From directory path.
     *
     * @param string $to To directory path.
     *
     * @param RecursiveDirectoryIterator $iter.
     *
     */
    public function copyR($from, $to, $iter = null)
    {
        if (is_file($from)) {
            return $this->copyFile($from, $to);
        } elseif (!is_dir($to)) {
            $this->createDir($to);
        }

        if(is_null($iter)) {
            $iter = new RecursiveDirectoryIterator($from);
        }

        for ($iter->rewind(); $iter->valid(); $iter->next()) {
            $file = basename($iter->current()->getPathname());

            if ($iter->isDot()) {
                // Skip dot-files.
                continue;
            } else {
                $new_from = $from . DIRECTORY_SEPARATOR . $file;
                $new_to = $to . DIRECTORY_SEPARATOR . $file;
                $new_iter = ($iter->isDir() && $iter->hasChildren())
                    ? $iter->getChildren()
                    : null;

                if ($to !== $new_from) {
                    $this->copyR($new_from, $new_to, $new_iter);
                }
            }
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
    public function createDir($path, $chmod = null) {
        $res = false;
        if (!is_dir($path)) {
            $umask = umask(0000);
            if (mkdir($path)) {
                $res = true;
            }
            umask($umask);

            if($chmod) {
                chmod($path, $chmod);
            }
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
    public function copyFile($from, $to) {
        $res = false;
        if (is_file($from)) {
            $oldumask = umask(0000);
            if (copy($from, $to)) {
                $res = true;
            }
            umask($oldumask);
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