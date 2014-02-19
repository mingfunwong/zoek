<?php

/**
 * Zoek - 极简博客
 * 
 * @link http://mingfunwong.com/zoek
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */

// 载入框架
$base = require('lib/base.php');

// 系统配置
$base->config('config.ini');

// 应用配置
$base->set('base', $base->get('SCHEME').'://'.$base->get('HOST').$base->get('BASE').'/');
$base->set('theme', $base->get('base').$base->get('UI'));

// 欢迎页面

// 列表页面和文章页面
$base->route(array('GET /', 'GET /@log'),
    function($base, $get) {
        $zoek = new Zoek;
        if ($page = $zoek->log($zoek->_from($get, 'log', 'index')))
        {
            $base->mset(
                array(
                    'title' => $page['Title'],
                    'page' => $page,
                    'content' => 'page.htm',
                    'pages' => $zoek->pages('log/*.md')
                )
            );
            exit(View::instance()->render('layout.htm'));
        } else {
            $zoek->show_not_found();
        }
    }
);

$base->run();



/**
 * Zoek - 极简博客
 * 
 * @link http://mingfunwong.com/zoek
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */
class Zoek {
    
    private $_pages = array(); 
    
    /**
     * 展示 404 页面
     * 
     * @access public
     * @return void
     */
    public function show_not_found()
    {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        exit("<h1>Not Found</h1>");
    }
    
    /**
     * 取得所有页面
     * 
     * @access public
     * @param string $pattern
     * @return string
     */
    public function pages($pattern = 'log/*.md')
    {
        $pages = array();
        if (empty($this->_pages))
        {
            $files = $this->_files($pattern);
            foreach($files as $file) {
                $info = $this->_info($file);
                if (!$info['Url']) continue;
                $pages[$info['Url']] = $info;
            }
            $pages = $this->_array_sort($pages, 'Date', SORT_DESC);
            $this->_pages = $pages;
        } else {
            $pages = $this->_pages;
        }
        return $pages;
    }
    
    /**
     * 取得请求页面
     * 
     * @access public
     * @param mixed $log
     * @return array
     */
    public function log($log)
    {
        $pages = $this->pages();
        if (isset($pages[$log]) && $log = $pages[$log])
        {
            $log['contents'] = $this->_Markdown($log['_contents']);
            return $log;
        }
        return FALSE;
    }

    /**
     * 取得信息
     * 
     * @access public
     * @param mixed $file
     * @return string
     */
    public function _info($file)
    {
        $info = array();
        $contents = file_get_contents($file);
        $headers = array(
            'Title',
            'Url',
            'Date',
        );
        foreach ($headers as $val){
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($val, '/') . ':(.*)$/mi', $contents, $match) && $match[1]){
                $info[ $val ] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
            } else {
                $info[ $val ] = '';
            }
        }
        $contents = preg_replace('#/\*.+?\*/#s', '', $contents, 1);
        $info['_file'] = $file;
        $info['_contents'] = $contents;
        return $info;
    }
    
    /**
     * Markdown 转 HTML
     * 
     * @access public
     * @param mixed $contents
     * @return string
     */
    public function _Markdown($contents)
    {
        return \Michelf\MarkdownExtra::defaultTransform($contents);
    }
    
    /**
     * 取得所有文件
     * 
     * @access public
     * @param mixed $pattern
     * @param int $flags
     * @return array
     */
    public function _files($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, $this->_files($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }
    
    /**
     * 二维数组排序
     * 
     * @access public
     * @param mixed $arr
     * @param mixed $field
     * @param mixed $by
     * @return array
     */
    public function _array_sort($arr, $field, $by = SORT_ASC)
    {
        foreach ($arr as $v) {
            $r[] = $v[$field];
        }
        array_multisort($r, $by, $arr);
        return $arr;
    }

    /**
    * 获得数组指定键的值
    *
    * @access public
    * @param array $array
    * @param string $key
    * @param mixed $default
    * @param bool $check_empty
    * @return mixed
    */
    public function _from($array, $key, $default = FALSE, $check_empty = FALSE)
    {
        return (isset($array[$key]) === FALSE OR ($check_empty === TRUE && empty($array[$key])) === TRUE) ? $default : $array[$key];
    }
}
