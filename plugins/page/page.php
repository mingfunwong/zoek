<?php

class Page {
    
    private $settings  = array();
    private $category = array();
    private $url       = '';
    private $post      = '';

    public function request_url(&$url)
    {
        $post = '';
        if ($url == '') { $post = 1; }
        if (strpos($url, '/post/') === 0) {
            $substr = substr($url, strlen('/post/')); 
            if (is_numeric($substr) && $substr > 0) {
                $post = $substr;
                $url = '';
            }
        }
        
        $this->url  = $url;
        $this->post = $post;
    }
    
        
    public function get_pages(&$pages, $category, &$current_page)
    {
        $this->category = $category;
    }
    
    
    public function before_render(&$twig_vars, &$twig)
    {
        if ($this->url == '' && isset($this->category['log'])) {
            
            // 分页
            $category = $this->category['log'];
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
            $re['old'] = $home . $url . $previous . $anchor;
        }
        if ($page != 1)
        {
            $next = $page - 1;
            if ($page == 2)
            $re['new'] = $home . $anchor;
            
            if ($page > 2)
            $re['new'] = $home . $url . $next . $anchor;
        }
        if ($pnums <= 1)
            $re = array();
        return $re;
    }
    
}
