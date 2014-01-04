<?php

/**
 * Zoek - 极简博客
 * 
 * @link http://mingfunwong.com/zoek
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */

// 载入框架
$base=require('lib/base.php');

// 系统配置
$base->config('config.ini');

// 应用配置
$base->set('base', $base->get('SCHEME').'://'.$base->get('HOST').$base->get('BASE').'/');
$base->set('theme', $base->get('base').$base->get('UI'));

// 欢迎页面

// 列表页面
$base->route('GET /',
    function($base) {
        $base->mset(
            array(
                'id'=>'list',
                'title'=>$base->get('site_name'),
                'pages'=>Zoek::pages(),
                'content'=>'list.htm'
            )
        );
        echo View::instance()->render('layout.htm');
    }
);

// 文章页面
$base->route('GET /@log',
    function($base, $get) {
        if ($page = Zoek::log($get['log']))
        {
            $base->mset(
                array(
                    'id'=>'page',
                    'title'=>$page['Title'],
                    'page'=>$page,
                    'content'=>'page.htm'
                )
            );
            echo View::instance()->render('layout.htm');
        } else {
            Zoek::show_404();
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
    
    /**
     * 展示 404 页面
     */
    static function show_404()
    {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        exit("<h1>Not Found</h1>");
    }
    
    /**
     * 取得所有页面
     */
    static function pages($pattern = 'log/*.md')
    {
        $pages = array();
        $files =  Zoek::_files($pattern);
        foreach($files as $file) {
            $info = Zoek::_info($file);
            if (!$info['Url']) continue;
            $pages[$info['Url']] = $info;
        }
        $pages = Zoek::_array_sort($pages, 'Date', SORT_DESC);
        return $pages;
    }
    
    /**
     * 取得请求页面
     */
    static function log($log)
    {
        $pages = Zoek::pages();
        if (isset($pages[$log]) && $log = $pages[$log])
        {
            $log['contents'] = Zoek::_Markdown($log['_contents']);
            return $log;
        }
        return FALSE;
    }

    /**
     * 取得信息
     */
    static function _info($file)
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
     */
    static function _Markdown($contents)
    {
        // return Markdown::instance()->convert($contents);
        return \Michelf\MarkdownExtra::defaultTransform($contents);
    }
    
    /**
     * 取得所有文件
     */
    static function _files($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, Zoek::_files($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }
    
    /**
     * 二维数组排序
     */
    static function _array_sort($arr, $field, $by = SORT_ASC)
    {
        foreach ($arr as $v) {
            $r[] = $v[$field];
        }
        array_multisort($r, $by, $arr);
        return $arr;
    }

}
