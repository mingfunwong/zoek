<?php
use \Michelf\MarkdownExtra;

/**
 * Zoek - ������ŵ� CMS ϵͳ
 * 
 * @link http://mingfunwong.com/zoek/
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfunw@gmail.com>
 */
class Zoek {

    private $plugins;

    public function __construct()
    {
        // ������
        $this->load_plugins();
        $this->run_hooks('plugins_loaded');
        
        // ȡ�� Request URL �� Script URL
        $url = '';
        $request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
        $script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

        // ȡ������/ ���� URL
        if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
        $url = preg_replace('/\?.*/', '', $url); // Strip query string
        $this->run_hooks('request_url', array(&$url));

        // ȡ���ļ�·��
        if($url) $file = CONTENT_DIR . $url;
        else $file = CONTENT_DIR .'index';

        // �����ļ�
        if(is_dir($file)) $file = CONTENT_DIR . $url .'/index'. CONTENT_EXT;
        else $file .= CONTENT_EXT;

        $this->run_hooks('before_load_content', array(&$file));
        if(file_exists($file)){
            $content = file_get_contents($file);
        } else {
            $this->run_hooks('before_404_load_content', array(&$file));
            $content = file_get_contents(CONTENT_DIR .'404'. CONTENT_EXT);
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            $this->run_hooks('after_404_load_content', array(&$file, &$content));
        }
        $this->run_hooks('after_load_content', array(&$file, &$content));
        
        // ��������
        $settings = $this->get_config();
        $this->run_hooks('config_loaded', array(&$settings));

        $meta = $this->read_file_meta($content);
        $this->run_hooks('file_meta', array(&$meta));
        $content = $this->parse_content($content);
        $this->run_hooks('content_parsed', array(&$content));
        
        // ȡ������ҳ
        $pages = $this->get_pages($settings['base_url'], $settings['pages_order_by'], $settings['pages_order'], $settings['excerpt_length']);
        $prev_page = array();
        $current_page = array();
        $next_page = array();
        while($current_page = current($pages)){
            if((isset($meta['title'])) && ($meta['title'] == $current_page['title'])){
                break;
            }
            next($pages);
        }
        $prev_page = next($pages);
        prev($pages);
        $next_page = prev($pages);
        $this->run_hooks('get_pages', array(&$pages, &$current_page, &$prev_page, &$next_page));

        // ��������
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
            'prev_page' => $prev_page,
            'current_page' => $current_page,
            'next_page' => $next_page,
            'is_front_page' => $url ? false : true,
        );
        $this->run_hooks('before_render', array(&$twig_vars, &$twig));
        $output = $twig->render('index.html', $twig_vars);
        $this->run_hooks('after_render', array(&$output));
        echo $output;
    }
    
    /**
     * ������
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
     * Markdown ת HTML
     * 
     * @access private
     * @param mixed $content
     * @return string
     */
    private function parse_content($content)
    {
        $content = preg_replace('#/\*.+?\*/#s', '', $content); // Remove comments and meta
        $content = str_replace('%base_url%', $this->base_url(), $content);
        $content = MarkdownExtra::defaultTransform($content);

        return $content;
    }

    /**
     * ȡ�� meta ͷ
     * 
     * @access private
     * @param mixed $content
     * @return array
     */
    private function read_file_meta($content)
    {
        global $config;
        
        $headers = array(
            'title'       	=> 'Title',
            'description' 	=> 'Description',
            'author' 		=> 'Author',
            'date' 			=> 'Date',
            'robots'     	=> 'Robots'
        );

        // Add support for custom headers by hooking into the headers array
        $this->run_hooks('before_read_file_meta', array(&$headers));

        foreach ($headers as $field => $regex){
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $content, $match) && $match[1]){
                $headers[ $field ] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
            } else {
                $headers[ $field ] = '';
            }
        }
        
        if(isset($headers['date'])) $headers['date_formatted'] = date($config['date_format'], strtotime($headers['date']));

        return $headers;
    }

    /**
     * ȡ������
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
     * ȡ������ҳ��
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
        $sorted_pages = array();
        $date_id = 0;
        foreach($pages as $key=>$page){
            // Skip 404
            if(basename($page) == '404'. CONTENT_EXT){
                unset($pages[$key]);
                continue;
            }

            // Ignore Emacs (and Nano) temp files
            if (in_array(substr($page, -1), array('~','#'))) {
                unset($pages[$key]);
                continue;
            }			
            // Get title and format $page
            $page_content = file_get_contents($page);
            $page_meta = $this->read_file_meta($page_content);
            $page_content = $this->parse_content($page_content);
            $url = str_replace(CONTENT_DIR, $base_url .'/', $page);
            $url = str_replace('index'. CONTENT_EXT, '', $url);
            $url = str_replace(CONTENT_EXT, '', $url);
            $data = array(
                'title' => isset($page_meta['title']) ? $page_meta['title'] : '',
                'url' => $url,
                'author' => isset($page_meta['author']) ? $page_meta['author'] : '',
                'date' => isset($page_meta['date']) ? $page_meta['date'] : '',
                'date_formatted' => isset($page_meta['date']) ? date($config['date_format'], strtotime($page_meta['date'])) : '',
                'content' => $page_content,
                'excerpt' => $this->limit_words(strip_tags($page_content), $excerpt_length)
            );

            // Extend the data provided with each page by hooking into the data array
            $this->run_hooks('get_page_data', array(&$data, $page_meta));

            if($order_by == 'date' && isset($page_meta['date'])){
                $sorted_pages[$page_meta['date'].$date_id] = $data;
                $date_id++;
            }
            else $sorted_pages[] = $data;
        }
        
        if($order == 'desc') krsort($sorted_pages);
        else ksort($sorted_pages);
        
        return $sorted_pages;
    }
    
    /**
     * ִ�� Hooks
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
     * ȡ�û��� URL
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
     * ȡ�÷���ҳ��
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
     * ȡ�������ļ�
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
     * ��ȡ�ַ���
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
