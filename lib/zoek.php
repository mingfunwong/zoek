<?php
use \Michelf\MarkdownExtra;

/**
 * Zoek - 简洁优雅的 CMS 系统
 * 
 * @link http://mingfunwong.com/zoek/
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfunw@gmail.com>
 */
class Zoek {

    private $plugins;

    public function __construct()
    {
        // 载入插件
        $this->load_plugins();
        $this->run_hooks('plugins_loaded');
        
        // 取得 Request URL 和 Script URL
        $url = '';
        $request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
        $script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

        // 取得域名/ 后面 URL
        if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
        $url = preg_replace('/\?.*/', '', $url); // Strip query string
        if (!empty($url)) { $url = '/' . $url; }
        $this->run_hooks('request_url', array(&$url));

        // 载入配置
        $settings = $this->get_config();
        $this->run_hooks('config_loaded', array(&$settings));
        
        // 取得所有页
        $pages = $this->get_pages($settings['base_url'], $settings['pages_order_by'], $settings['pages_order'], $settings['excerpt_length']);
        $category = $pages['category'];
        $pages    = $pages['pages'];
        $prev_page = array();
        $current_page = array();
        $next_page = array();
        while($current_page = current($pages)){
            if ($settings['base_url'] . $url == $current_page['meta']['url']) {
                break;
            }
            next($pages);
        }
        $this->run_hooks('get_pages', array(&$pages, $category, &$current_page));
        $content = $current_page['content'];
        $meta = $current_page['meta'];
        $file = $current_page['file'];
        
        if (empty($current_page))
        {
            $this->show_404($content, $meta, $file);
        }

        // 载入主题
        $this->run_hooks('before_twig_register');
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem(THEMES_DIR . $settings['theme']);
        $twig = new Twig_Environment($loader, $settings['twig_config']);
        $twig->addExtension(new Twig_Extension_Debug());
        $twig_vars = array(
            'config' => $settings,
            'base_dir' => rtrim(ROOT_DIR, '/'),
            'base_url' => $settings['base_url'],
            'theme_dir' => THEMES_DIR . $settings['theme'],
            'theme_url' => $settings['base_url'] .'/'. basename(THEMES_DIR) .'/'. $settings['theme'],
            'site_title' => $settings['site_title'],
            'meta' => $meta,
            'content' => $content,
            'pages' => $pages,
            'category' => $category,
            'current_page' => $current_page,
            'is_front_page' => $url ? false : true,
        );
        $this->run_hooks('before_render', array(&$twig_vars, &$twig, &$file));
        $twig_vars['content'] = $this->twig_content($file, $twig_vars);
        $output = $twig->render('index.html', $twig_vars);
        $this->run_hooks('after_render', array(&$output));
        echo $output;
    }
    
    /**
     * 展示 404 页面
     * 
     * @access public
     * @param mixed $content
     * @return void
     */
    public function show_404(&$content, &$meta, &$file)
    {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        $file = '404'. CONTENT_EXT;
        $content = $this->twig_content($file, array());
    }

    /**
     * Markdown 转 HTML
     * 
     * @access private
     * @param mixed $content
     * @return string
     */
    public function parse_content($content)
    {
        $content = preg_replace('#/\*.+?\*/#s', '', $content);
        $content = str_replace('%base_url%', $this->base_url(), $content);
        
        $content = MarkdownExtra::defaultTransform($content);

        return $content;
    }
    /**
     * twig 转 HTML
     * 
     * @access private
     * @param mixed $content
     * @return string
     */
    public function twig_content($file, $vars)
    {
        $loader = new Twig_Loader_Filesystem(CONTENT_DIR);
        $zoek = new Twig_Environment($loader, $vars);
        $output = $zoek->render($file, $vars);
        $content = $this->parse_content($output);
        return $content;
    }
    
    /**
     * 取得 meta 头
     * 
     * @access private
     * @param mixed $content
     * @return array
     */
    public function read_file_meta($file, $base_url, $url)
    {
        global $config;
        
        $content = file_get_contents($file);
        
        $headers = array(
            'title'      => 'Title',
            'category'   => 'Category',
            'naviname'   => 'Naviname',
            'url'        => 'Url',
            'description' => 'Description',
            'author'     => 'Author',
            'date'       => 'Date',
            'robots'     => 'Robots',
            'order'      => 'Order'
        );

        $this->run_hooks('before_read_file_meta', array(&$headers));

        foreach ($headers as $field => $regex){
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $content, $match) && $match[1]){
                $headers[ $field ] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
            } else {
                $headers[ $field ] = '';
            }
        }
        
        if(isset($headers['date'])) $headers['date_formatted'] = date($config['date_format'], strtotime($headers['date']));
        if (empty($headers['url'])) {
            $headers['url'] = $base_url;
        }
        else {
            $headers['url'] = $base_url . '/' . $headers['url'];
        }
        return $headers;
    }

    /**
     * 载入插件
     * 
     * @access private
     * @return void
     */
    private function load_plugins()
    {
        $this->plugins = array();
        $plugins = $this->get_files(PLUGINS_DIR, '.php');
        if(!empty($plugins)){
            foreach($plugins as $plugin){
                include_once($plugin);
                $plugin_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($plugin));
                if(class_exists($plugin_name)){
                    $obj = new $plugin_name;
                    $this->plugins[] = $obj;
                }
            }
        }
    }
    
    /**
     * 取得配置
     * 
     * @access private
     * @return array
     */
    private function get_config()
    {
        global $config;
        @include_once(ROOT_DIR .'config.php');

        $defaults = array(
            'site_title' => 'Zoek',
            'base_url' => $this->base_url(),
            'theme' => 'default',
            'date_format' => 'jS M Y',
            'twig_config' => array('cache' => false, 'autoescape' => false, 'debug' => false),
            'pages_order_by' => 'alpha',
            'pages_order' => 'asc',
            'excerpt_length' => 50
        );

        if(is_array($config)) $config = array_merge($defaults, $config);
        else $config = $defaults;

        return $config;
    }
    
    /**
     * 取得所有页面
     * 
     * @access private
     * @param mixed $base_url
     * @param string $order_by
     * @param string $order
     * @param int $excerpt_length
     * @return array
     */
    private function get_pages($base_url, $order_by = 'alpha', $order = 'asc', $excerpt_length = 50)
    {
        global $config;
        
        $pages = $this->get_files(CONTENT_DIR, CONTENT_EXT);
        $sorted_pages = $category = array();
        $date_id = 0;
        foreach($pages as $key=>$page){
            if(basename($page) == '404'. CONTENT_EXT){
                unset($pages[$key]);
                continue;
            }
            if (in_array(substr($page, -1), array('~','#'))) {
                unset($pages[$key]);
                continue;
            }
            $url = str_replace(CONTENT_DIR, $base_url .'/', $page);
            $url = str_replace('index'. CONTENT_EXT, '', $url);
            $url = str_replace(CONTENT_EXT, '', $url);
            $page_meta = $this->read_file_meta($page, $base_url, $url);
            $this->run_hooks('page_meta', array(&$page_meta));
            
            $this->run_hooks('before_load_content', array(&$page));
            $content = file_get_contents($page);
            $this->run_hooks('after_load_content', array(&$page, &$content));
            $page_content = $this->parse_content($page);
            $this->run_hooks('content_parsed', array(&$page_content));
            $data['file'] = str_replace(CONTENT_DIR, '', $page);
            $data['meta'] = $page_meta;
            $data['content'] = $page_content;
            $data['excerpt'] = $this->limit_words(strip_tags($page_content), $excerpt_length);
            $this->run_hooks('get_page_data', array(&$data, $page_meta));

            if($order_by == 'date' && isset($page_meta['date'])){
                $sorted_pages[$page_meta['date'].$date_id] = $data;
                $date_id++;
            }
            else $sorted_pages[] = $data;
        }
        
        if($order == 'desc') krsort($sorted_pages);
        else ksort($sorted_pages);
        
        $categorys = $sorted_pages;
        foreach ($categorys as $value) {
            if (isset($value['meta']['category'])) {
                $category[$value['meta']['category']][] = $value;
            }
        }
        $return = array('pages' => $sorted_pages, 'category' => $category);
        return $return;
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
    public function array_sort($arr, $field, $by = SORT_ASC)
    {
        foreach ($arr as $v) {
            $r[] = $v[$field];
        }
        array_multisort($r, $by, $arr);
        return $arr;
    }
    
    /**
     * 执行 Hooks
     * 
     * @access private
     * @param mixed $hook_id
     * @param array $args
     * @return void
     */
    private function run_hooks($hook_id, $args = array())
    {
        if(!empty($this->plugins)){
            foreach($this->plugins as $plugin){
                if(is_callable(array($plugin, $hook_id))){
                    call_user_func_array(array($plugin, $hook_id), $args);
                }
            }
        }
    }

    /**
     * 取得基本 URL
     * 
     * @access private
     * @return string
     */
    private function base_url()
    {
        global $config;
        if(isset($config['base_url']) && $config['base_url']) return $config['base_url'];

        $url = '';
        $request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
        $script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';
        if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');

        $protocol = $this->get_protocol();
        return rtrim(str_replace($url, '', $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']), '/');
    }

    /**
     * 取得服务页面
     * 
     * @access private
     * @return string
     */
    private function get_protocol()
    {
        preg_match("|^HTTP[S]?|is",$_SERVER['SERVER_PROTOCOL'],$m);
        return strtolower($m[0]);
    }
         
    /**
     * 取得所有文件
     * 
     * @access private
     * @param mixed $directory
     * @param string $ext
     * @return array
     */
    private function get_files($directory, $ext = '')
    {
        $array_items = array();
        if($handle = opendir($directory)){
            while(false !== ($file = readdir($handle))){
                if(preg_match("/^(^\.)/", $file) === 0){
                    if(is_dir($directory. "/" . $file)){
                        $array_items = array_merge($array_items, $this->get_files($directory. "/" . $file, $ext));
                    } else {
                        $file = $directory . "/" . $file;
                        if(!$ext || strstr($file, $ext)) $array_items[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }
        return $array_items;
    }
    
    /**
     * 截取字符串
     * 
     * @access private
     * @param mixed $string
     * @param mixed $word_limit
     * @return string
     */
    private function limit_words($string, $word_limit)
    {
        $words = explode(' ',$string);
        return trim(implode(' ', array_splice($words, 0, $word_limit))) .'...';
    }


}
