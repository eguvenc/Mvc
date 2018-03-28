<?php

namespace Obullo\Mvc\Config\Cache;

/**
 * File handler
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class FileHandler implements CacheInterface
{
    protected $path;

    /**
     * Set save path
     * 
     * @param string $path path
     */
    public function __construct(string $path = '/var/cache/config')
    {
        $this->path = rtrim($path, '/');
    }

    /**
     * Checks the file has cached
     * 
     * @param  string $file filename
     * @return boolean
     */
    public function has(string $file) : bool
    {
        $id = $this->getFile($file);

        return file_exists($id);
    }

    /**
     * Read file
     * 
     * @param  string $file file
     * @return string
     */
    public function read(string $file) : array
    {
        $id = $this->getFile($file);
        $mtime = filemtime($file);
        $serializedData = file_get_contents($id);
        $data = unserialize($serializedData);
        $time = (int)$data['__mtime__'];
        if ($mtime > $time) {
            unlink($id);
        }
        unset($data['__mtime__']);
        return $data;
    }

    /**
     * Write to cache
     * 
     * @param  string $file  file
     * @param  data   $data  array
     * @return void
     */
    public function write(string $file, array $data)
    {
        if (! is_dir(ROOT.$this->path)) {
            mkdir(ROOT.$this->path, 0777);
        }
        $id = $this->getFile($file);
        $data['__mtime__'] = filemtime($file);
        $serializedData = serialize($data);
        file_put_contents($id, $serializedData);
    }

    /**
     * Returns to normalized key
     * 
     * @param  string $file file
     * @return string
     */
    protected function getFile(string $file)
    {
        $filestr  = str_replace(array(ROOT, '/'), array('',':'), $file);
        $filename = strstr($filestr, '.', true);

        return ROOT.$this->path.'/'.ltrim($filename, ':');
    }
}