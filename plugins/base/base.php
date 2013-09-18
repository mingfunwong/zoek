<?php
use \Michelf\MarkdownExtra;

/**
 * navigation plugin which generates a better configurable navigation with endless children navigations
 *
 * @author Ahmet Topal
 * @link http://ahmet-topal.com
 * @license http://opensource.org/licenses/MIT
 */
class Base {
    
    private $settings  = array();
    private $categorys = array();
    private $url       = '';
    private $post      = '';

    public function request_url(&$url)
    {
        $post = '';
        if ($url == '') { $post = 1; }
        if (strpos($url, 'post/') === 0) {
            $substr = substr($url, strlen('post/')); 
            if (is_numeric($substr) && $substr > 0) {
                $post = $substr;
                $url = '';
            }
        }
        
        $this->url  = $url;
        $this->post = $post;
    }
    
    public function before_read_file_meta(&$headers)
    {
        $headers = array_merge($headers, array('category' => 'Category'));
        $headers = array_merge($headers, array('naviname' => 'Naviname'));
    }
        
    public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
    {
        $categorys = array();
        foreach ($pages as $page) {
            if (!empty($page['category'])) {
                $categorys[$page['category']][] = $page;
            }
        }
        
        $this->categorys = $categorys;
    }
    
    
    public function get_page_data(&$data, $page_meta)
    {
        if (!empty($page_meta['category'])) { $data['category'] = $page_meta['category']; }
        if (!empty($page_meta['naviname'])) { $data['naviname'] = $page_meta['naviname']; }
    }
    
    public function before_render(&$twig_vars, &$twig)
    {

        $twig_vars['categorys'] = $this->categorys;
        if ($this->url == '') {
            
            // 分页
            $category = $this->categorys['log'];
            $twig_vars['contents'] = $this->page_slice($category);
            
            // 上下页
            $pages_length = $this->settings['pages_length'];
            $count = count($category);
            $pnums = @ceil($count / $pages_length);
            if ($this->post > $pnums) {
                header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            }
            $pagination = $this->pagination($count, $pages_length, $this->post, $this->settings['base_url'], '/post/');
            $twig_vars['pager'] = $pagination;
        }
        
        $file = $this->get_file($this->url);
        if ($file) {
            $loader = new Twig_Loader_Filesystem(CONTENT_DIR);
            $zoek = new Twig_Environment($loader, $twig_vars);
            $output = $zoek->render($this->get_file($this->url), $twig_vars);
            $twig_vars['content'] = $this->parse_content($output);
        }
    }
    
    public function config_loaded(&$settings)
    {
        $this->settings = $settings;
    }
    
    /**
     * 分页显示
     * 
     * @access private
     * @param mixed $category
     * @return void
     */
    private function page_slice($category)
    {
        $pages_length = $this->settings['pages_length'];
        $offset = ($this->post - 1) * $pages_length;
        
        $category = array_slice($category, $offset, $pages_length);
        return $category;
    }
    
    
    /**
     * 分页函数
     *
     * @param int $count 条目总数
     * @param int $perlogs 每页显示条数目
     * @param int $page 当前页码
     * @param string $home 主页地址
     * @param string $url 页码地址
     */
    private function pagination($count, $perlogs, $page, $home, $url, $anchor = '') {
        $pnums = @ceil($count / $perlogs);
        $re = array();
        
        if ($page < $pnums)
        {
            $previous = $page + 1;
            $re['old'] = $url . $previous . $anchor;
        }
        if ($page != 1)
        {
            $next = $page - 1;
            if ($page == 2)
            $re['new'] = $home . $anchor;
            
            if ($page > 2)
            $re['new'] = $url . $next . $anchor;
        }
        if ($pnums <= 1)
            $re = array();
        return $re;
    }

    
    /**
     * 获取原始文件名
     * 
     * @access private
     * @param mixed $url
     * @return void
     */
    private function get_file($url)
    {
        // Get the file path
        if($url) $file = $url;
        else $file = 'index';

        // Load the file
        if(is_dir(CONTENT_DIR . $file)) $file = $url .'/index'. CONTENT_EXT;
        else $file .= CONTENT_EXT;
        
        if (file_exists(CONTENT_DIR . $file)) {
            return $file; 
        } 
        else { 
            return false; 
        }
        
    }
    
    /**
     * 二维数组排序
     * 
     * @access private
     * @param mixed $arr
     * @param mixed $field
     * @param mixed $by
     * @return array
     */
    private function array_sort($arr, $field, $by = SORT_ASC)
    {
        foreach ($arr as $v) {
            $r[] = $v[$field];
        }
        array_multisort($r, $by, $arr);
        return $arr;
    }
    
    /**
     * Parses the content using Markdown
     *
     * @param string $content the raw txt content
     * @return string $content the Markdown formatted content
     */
    private function parse_content($content)
    {
        $content = preg_replace('#/\*.+?\*/#s', '', $content); // Remove comments and meta
        $content = str_replace('%base_url%', $this->settings['base_url'], $content);
        $content = MarkdownExtra::defaultTransform($content);

        return $content;
    }
    
}
?>
