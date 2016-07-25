<?php
/**
 * Template engine base on PHP 5.3+
 *
 * Usage
 * <code>
 * $tpl = new Template('/path/to/templates');
 * $tpl->set('variable', 'some value');
 * $tpl->display('template-tpl.php');
 * </code>
 *
 * @category  Template
 * @package   Template
 * @author    ahmachan
 */
class Template
{
    var $vars; /// Holds all the template variables
    var $path; /// Path to the templates

    /**
     * Constructor
     *
     * @param string $path the path to the templates
     *
     * @return void
     */
    function Template($path = '')
    {
        $this->path = $path;
        $this->vars = array();
    }

    /**
     * 获取一个实例
     * @param string $path 存放模板的目录
     * @return Template
     */
    static function create($path = '')
    {
        return new Template($path);
    }

    /**
     * Set the path to the template files.
     *
     * @param string $path path to template files
     *
     * @return Template
     */
    function setPath($path)
    {
        $this->path = $path;
        return $this;
    }


    /**
     * Set a template variable.
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Template
     */
    function assign($name,$value='') {
        if(is_array($name)) {
            $this->vars   =  array_merge($this->vars,$name);
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->vars[$key] = $val;
        }else {
            $this->vars[$name] = $value;
        }

        return $this;
    }


    /**
     * Set a bunch of variables at once using an associative array.
     *
     * @param array $vars  array of vars to set
     * @param bool  $clear whether to completely overwrite the existing vars
     *
     * @return Template
     */
    function setVars($vars, $clear = false)
    {
        if ($clear) {
            $this->vars = $vars;
        } else {
            if (is_array($vars)) $this->vars = array_merge($this->vars, $vars);
        }
        return $this;
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param string $file the template file name
     *
     * @return string
     */
    function fetch($file)
    {
        extract($this->vars);          // Extract the vars to local namespace
        //页面缓存
        ob_start();
        ob_implicit_flush(0);            // Start output buffering
        include $this->path . $file;  // Include the file
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        return $contents;              // Return the contents
    }

    /**
     * Displays the template directly
     *
     * @param string $file the template file name
     *
     * @return string
     */
    function display($file,$display=true,$charset='utf-8',$contentType='text/html')
    {
       // 网页字符编码
       header("Content-Type:".$contentType."; charset=".$charset);       
       header("Cache-control: private");  //支持页面回跳
       $content=$this->fetch($file);
       // 输出模板文件
       if($display){
        	echo $content;
       }else{
        	return $content;
       }
    }
}

/**
 * An extension to Template :Template_Cache
 *
 * Usage
 * <code>
 * $tpl = & new CachedTemplate('/path/to/templates/', '/path/to/cache/', $cache_identifier_for_page);
 * if (!($tpl->is_cached())) {
 *     $tpl->set('title', 'some value');
 * }
 * $tpl->display('main-tpl.php');
 * </code>
 *
 *
 * @category  Template
 * @package   Template
 * @author    ahmachan
 */
class Template_Cache extends Template
{
    var $cache_id;
    var $expire;
    var $cached;

    /**
     * Constructor
     *
     * @param string $path             path to template files
     * @param string $path_cache_files where to save the cache files
     * @param string $cache_id         unique cache identifier
     * @param int    $expire           number of seconds the cache will live
     *
     * @return void
     */
    function Template_Cache($path, $path_cache_files = 'cache/', $cache_id = null, $expire = 900)
    {
        $this->Template($path);
        $this->cache_id = $cache_id ? $path_cache_files . md5($cache_id) : $cache_id;
        $this->expire   = $expire;
    }

    /**
     * Test to see whether the currently loaded cache_id has a valid
     * corrosponding cache file.
     *
     * @return bool
     */
    function isCached()
    {
        if ($this->cached) return true;

        // Passed a cache_id?
        if (!$this->cache_id) return false;

        // Cache file exists?
        if (!file_exists($this->cache_id)) return false;

        // Can get the time of the file?
        if (!($mtime = filemtime($this->cache_id))) return false;

        // Cache expired?
        if (($mtime + $this->expire) < time()) {
            @unlink($this->cache_id);
            return false;
        } else {
            /**
             * Cache the results of this is_cached() call.  Why?  So
             * we don't have to double the overhead for each template.
             * If we didn't cache, it would be hitting the file system
             * twice as much (file_exists() & filemtime() [twice each]).
             */
            $this->cached = true;
            return true;
        }
    }

    /**
     * Returns a cached copy of a template (if it exists),
     * otherwise, it parses it as normal and caches the content.
     *
     * @param string $file string the template file
     *
     * @return string
     */
    function fetch($file)
    {
        if ($this->isCached()) {
            $fp = @fopen($this->cache_id, 'r');
            $contents = fread($fp, filesize($this->cache_id));
            fclose($fp);
            return $contents;
        } else {
            $contents = $this->fetch($file);
            // Write the cache
            if ($fp = @fopen($this->cache_id, 'w')) {
                fwrite($fp, $contents);
                fclose($fp);
            } else {
                die('Unable to write cache.');
            }
            return $contents;
        }
    }
}
