<?php

class Archives {
    
    private $settings  = array();
    private $category  = array();
    private $archives  = False;

    public function request_url(&$url)
    {
        if ($url == '/archives') {
            $this->archives = TRUE;
        }
    }
    
        
    public function get_pages(&$pages, $category, &$current_page)
    {
        $this->category = $category;
    }
    
    
    public function before_render(&$twig_vars, &$twig)
    {
        if ($this->archives) {
            $content = $this->twig_content($twig_vars['content'], $twig_vars);
            $twig_vars['content'] = $content;
        }
    }
    
    public function config_loaded(&$settings)
    {
        $this->settings = $settings;
    }
    
    /**
     * 编译 twig
     * 
     * @access private
     * @param mixed $content
     * @return string
     */
    public function twig_content($content, $vars)
    {
        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);
        $output = $twig->render($content, $vars);
        return $output;
    }
    
}
