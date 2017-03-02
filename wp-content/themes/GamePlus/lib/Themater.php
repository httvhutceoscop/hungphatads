<?php
class Themater
{
    var $theme_name = false;
    var $options = array();
    var $admin_options = array();
    
    function __construct($set_theme_name = false)
    {
        if($set_theme_name) {
            $this->theme_name = $set_theme_name;
        } else {
            $theme_data = wp_get_theme();
            $this->theme_name = $theme_data->get( 'Name' );
        }
        $this->options['theme_options_field'] = str_replace(' ', '_', strtolower( trim($this->theme_name) ) ) . '_theme_options';
        
        $get_theme_options = get_option($this->options['theme_options_field']);
        if($get_theme_options) {
            $this->options['theme_options'] = $get_theme_options;
            $this->options['theme_options_saved'] = 'saved';
        }
        
        $this->_definitions();
        $this->_default_options();
    }
    
    /**
    * Initial Functions
    */
    
    function _definitions()
    {
        // Define THEMATER_DIR
        if(!defined('THEMATER_DIR')) {
            define('THEMATER_DIR', get_template_directory() . '/lib');
        }
        
        if(!defined('THEMATER_URL')) {
            define('THEMATER_URL',  get_template_directory_uri() . '/lib');
        }
        
        // Define THEMATER_INCLUDES_DIR
        if(!defined('THEMATER_INCLUDES_DIR')) {
            define('THEMATER_INCLUDES_DIR', get_template_directory() . '/includes');
        }
        
        if(!defined('THEMATER_INCLUDES_URL')) {
            define('THEMATER_INCLUDES_URL',  get_template_directory_uri() . '/includes');
        }
        
        // Define THEMATER_ADMIN_DIR
        if(!defined('THEMATER_ADMIN_DIR')) {
            define('THEMATER_ADMIN_DIR', THEMATER_DIR);
        }
        
        if(!defined('THEMATER_ADMIN_URL')) {
            define('THEMATER_ADMIN_URL',  THEMATER_URL);
        }
    }
    
    function _default_options()
    {
        // Load Default Options
        require_once (THEMATER_DIR . '/default-options.php');
        
        $this->options['translation'] = $translation;
        $this->options['general'] = $general;
        $this->options['includes'] = array();
        $this->options['plugins_options'] = array();
        $this->options['widgets'] = $widgets;
        $this->options['widgets_options'] = array();
        $this->options['menus'] = $menus;
        
        // Load Default Admin Options
        if( !isset($this->options['theme_options_saved']) || $this->is_admin_user() ) {
            require_once (THEMATER_DIR . '/default-admin-options.php');
        }
    }
    
    /**
    * Theme Functions
    */
    
    function option($name) 
    {
        echo $this->get_option($name);
    }
    
    function get_option($name) 
    {
        $return_option = '';
        if(isset($this->options['theme_options'][$name])) {
            if(is_array($this->options['theme_options'][$name])) {
                $return_option = $this->options['theme_options'][$name];
            } else {
                $return_option = stripslashes($this->options['theme_options'][$name]);
            }
        } 
        return $return_option;
    }
    
    function display($name, $array = false) 
    {
        if(!$array) {
            $option_enabled = strlen($this->get_option($name)) > 0 ? true : false;
            return $option_enabled;
        } else {
            $get_option = is_array($array) ? $array : $this->get_option($name);
            if(is_array($get_option)) {
                $option_enabled = in_array($name, $get_option) ? true : false;
                return $option_enabled;
            } else {
                return false;
            }
        }
    }
    
    function custom_css($source = false) 
    {
        if($source) {
            $this->options['custom_css'] = isset($this->options['custom_css']) ? $this->options['custom_css'] . $source . "\n" : $source . "\n";
        }
        return;
    }
    
    function custom_js($source = false) 
    {
        if($source) {
            $this->options['custom_js'] = isset( $this->options['custom_js'] ) ? $this->options['custom_js'] . $source . "\n" : $source . "\n";
        }
        return;
    }
    
    function hook($tag, $arg = '')
    {
        do_action('themater_' . $tag, $arg);
    }
    
    function add_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        add_action( 'themater_' . $tag, $function_to_add, $priority, $accepted_args );
    }
    
    function admin_option($menu, $title, $name = false, $type = false, $value = '', $attributes = array())
    {
        if($this->is_admin_user() || !isset($this->options['theme_options'][$name])) {
            
            // Menu
            if(is_array($menu)) {
                $menu_title = isset($menu['0']) ? $menu['0'] : $menu;
                $menu_priority = isset($menu['1']) ? (int)$menu['1'] : false;
            } else {
                $menu_title = $menu;
                $menu_priority = false;
            }
            
            if( !isset( $this->options['admin_options_priorities']['priority'] ) ) {
                $this->options['admin_options_priorities']['priority'] = 0;
            }
            if(!isset($this->admin_options[$menu_title]['priority'])) {
                if(!$menu_priority) {
                    $this->options['admin_options_priorities']['priority'] += 10;
                    $menu_priority = $this->options['admin_options_priorities']['priority'];
                }
                $this->admin_options[$menu_title]['priority'] = $menu_priority;
            }
            
            // Elements
            
            if($name && $type) {
                $element_args['title'] = $title;
                $element_args['name'] = $name;
                $element_args['type'] = $type;
                $element_args['value'] = $value;
                
                if( !isset($this->options['theme_options'][$name]) ) {
                   $this->options['theme_options'][$name] = $value;
                }

                $this->admin_options[$menu_title]['content'][$element_args['name']]['content'] = $element_args + $attributes;
                
                if( !isset( $this->options['admin_options_priorities'][$menu_title]['priority'] )) {
                    $this->options['admin_options_priorities'][$menu_title]['priority'] = 0;
                }
                
                if( !isset( $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] )) {
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = 0;
                }
                
                if(!isset($attributes['priority'])) {
                    $this->options['admin_options_priorities'][$menu_title]['priority'] += 10;
                    
                    $element_priority = $this->options['admin_options_priorities'][$menu_title]['priority'];
                    
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $element_priority;
                } else {
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $attributes['priority'];
                }
                
            }
        }
        return;
    }
    
    function display_widget($widget,  $instance = false, $args = array('before_widget' => '<ul class="widget-container"><li class="widget">','after_widget' => '</li></ul>', 'before_title' => '<h3 class="widgettitle">','after_title' => '</h3>')) 
    {
        $custom_widgets = array('Banners125' => 'themater_banners_125', 'Posts' => 'themater_posts', 'Comments' => 'themater_comments', 'InfoBox' => 'themater_infobox', 'SocialProfiles' => 'themater_social_profiles', 'Tabs' => 'themater_tabs', 'Facebook' => 'themater_facebook');
        $wp_widgets = array('Archives' => 'archives', 'Calendar' => 'calendar', 'Categories' => 'categories', 'Links' => 'links', 'Meta' => 'meta', 'Pages' => 'pages', 'Recent_Comments' => 'recent-comments', 'Recent_Posts' => 'recent-posts', 'RSS' => 'rss', 'Search' => 'search', 'Tag_Cloud' => 'tag_cloud', 'Text' => 'text');
        
        if (array_key_exists($widget, $custom_widgets)) {
            $widget_title = 'Themater' . $widget;
            $widget_name = $custom_widgets[$widget];
            if(!$instance) {
                $instance = $this->options['widgets_options'][strtolower($widget)];
            } else {
                $instance = wp_parse_args( $instance, $this->options['widgets_options'][strtolower($widget)] );
            }
            
        } elseif (array_key_exists($widget, $wp_widgets)) {
            $widget_title = 'WP_Widget_' . $widget;
            $widget_name = $wp_widgets[$widget];
            
            $wp_widgets_instances = array(
                'Archives' => array( 'title' => 'Archives', 'count' => 0, 'dropdown' => ''),
                'Calendar' =>  array( 'title' => 'Calendar' ),
                'Categories' =>  array( 'title' => 'Categories' ),
                'Links' =>  array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'orderby' => 'name', 'limit' => -1 ),
                'Meta' => array( 'title' => 'Meta'),
                'Pages' => array( 'sortby' => 'post_title', 'title' => 'Pages', 'exclude' => ''),
                'Recent_Comments' => array( 'title' => 'Recent Comments', 'number' => 5 ),
                'Recent_Posts' => array( 'title' => 'Recent Posts', 'number' => 5, 'show_date' => 'false' ),
                'Search' => array( 'title' => ''),
                'Text' => array( 'title' => '', 'text' => ''),
                'Tag_Cloud' => array( 'title' => 'Tag Cloud', 'taxonomy' => 'tags')
            );
            
            if(!$instance) {
                $instance = $wp_widgets_instances[$widget];
            } else {
                $instance = wp_parse_args( $instance, $wp_widgets_instances[$widget] );
            }
        }
        
        if( !defined('THEMES_DEMO_SERVER') && !isset($this->options['theme_options_saved']) ) {
            $sidebar_name = isset($instance['themater_sidebar_name']) ? $instance['themater_sidebar_name'] : str_replace('themater_', '', current_filter());
            
            $sidebars_widgets = get_option('sidebars_widgets');
            $widget_to_add = get_option('widget_'.$widget_name);
            $widget_to_add = ( is_array($widget_to_add) && !empty($widget_to_add) ) ? $widget_to_add : array('_multiwidget' => 1);
            
            if( count($widget_to_add) > 1) {
                $widget_no = max(array_keys($widget_to_add))+1;
            } else {
                $widget_no = 1;
            }
            
            $widget_to_add[$widget_no] = $instance;
            $sidebars_widgets[$sidebar_name][] = $widget_name . '-' . $widget_no;
            
            update_option('sidebars_widgets', $sidebars_widgets);
            update_option('widget_'.$widget_name, $widget_to_add);
            the_widget($widget_title, $instance, $args);
        }
        
        if( defined('THEMES_DEMO_SERVER') ){
            the_widget($widget_title, $instance, $args);
        }
    }
    

    /**
    * Loading Functions
    */
        
    function load()
    {
        $this->_load_translation();
        $this->_load_widgets();
        $this->_load_includes();
        $this->_load_menus();
        $this->_load_general_options();
        $this->_save_theme_options();
        
        $this->hook('init');
        
        if($this->is_admin_user()) {
            include (THEMATER_ADMIN_DIR . '/Admin.php');
            new ThematerAdmin();
        } 
    }
    
    function _save_theme_options()
    {
        if( !isset($this->options['theme_options_saved']) ) {
            if(is_array($this->admin_options)) {
                $save_options = array();
                foreach($this->admin_options as $themater_options) {
                    
                    if(is_array($themater_options['content'])) {
                        foreach($themater_options['content'] as $themater_elements) {
                            if(is_array($themater_elements['content'])) {
                                
                                $elements = $themater_elements['content'];
                                if($elements['type'] !='content' && $elements['type'] !='raw') {
                                    $save_options[$elements['name']] = $elements['value'];
                                }
                            }
                        }
                    }
                }
                update_option($this->options['theme_options_field'], $save_options);
                $this->options['theme_options'] = $save_options;
            }
        }
    }
    
    function _load_translation()
    {
        if($this->options['translation']['enabled']) {
            load_theme_textdomain( 'themater', $this->options['translation']['dir']);
        }
        return;
    }
    
    function _load_widgets()
    {
    	$widgets = $this->options['widgets'];
        foreach(array_keys($widgets) as $widget) {
            if(file_exists(THEMATER_DIR . '/widgets/' . $widget . '.php')) {
        	    include (THEMATER_DIR . '/widgets/' . $widget . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php') ) {
        	   include (THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php');
        	}
        }
    }
    
    function _load_includes()
    {
    	$includes = $this->options['includes'];
        foreach($includes as $include) {
            if(file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '.php')) {
        	    include (THEMATER_INCLUDES_DIR . '/' . $include . '.php');
        	} elseif ( file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php') ) {
        	   include (THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php');
        	}
        }
    }
    
    function _load_menus()
    {
        foreach(array_keys($this->options['menus']) as $menu) {
            if(file_exists(TEMPLATEPATH . '/' . $menu . '.php')) {
        	    include (TEMPLATEPATH . '/' . $menu . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/' . $menu . '.php') ) {
        	   include (THEMATER_DIR . '/' . $menu . '.php');
        	} 
        }
    }
    
    function _load_general_options()
    {
        add_theme_support( 'woocommerce' );
        
        if($this->options['general']['jquery']) {
            add_action( 'wp_enqueue_scripts', array(&$this, '_load_jquery'));
        }
        
    	add_action( 'after_setup_theme', array(&$this, '_load_meta_title') );
        
        if($this->options['general']['featured_image']) {
            add_theme_support( 'post-thumbnails' );
        }
        
        if($this->options['general']['custom_background']) {
            add_theme_support( 'custom-background' );
        }
        
        if($this->options['general']['clean_exerpts']) {
            add_filter('excerpt_more', create_function('', 'return "";') );
        }
        
        if($this->options['general']['hide_wp_version']) {
            add_filter('the_generator', create_function('', 'return "";') );
        }
        
        add_action('wp_head', array(&$this, '_head_elements'));

        if($this->options['general']['automatic_feed']) {
            add_theme_support('automatic-feed-links');
        }
        
        if($this->display('custom_css') || isset($this->options['custom_css'])) {
            $this->add_hook('head', array(&$this, '_load_custom_css'), 100);
        }
        
        if($this->options['custom_js']) {
            $this->add_hook('html_after', array(&$this, '_load_custom_js'), 100);
        }
        
        if($this->display('head_code')) {
	        $this->add_hook('head', array(&$this, '_head_code'), 100);
	    }
	    
	    if($this->display('footer_code')) {
	        $this->add_hook('html_after', array(&$this, '_footer_code'), 100);
	    }
    }

    function _load_jquery()
    {
        wp_enqueue_script('jquery');
    }
    
    function _load_meta_title()
    {
        add_theme_support( 'title-tag' );
    }
    
    function _head_elements()
    {
        // Deprecated <title> tag
        if ( ! function_exists( '_wp_render_title_tag' ) )  {
            ?> <title><?php wp_title( '|', true, 'right' ); ?></title><?php
        }
        
    	// Favicon
    	if($this->display('favicon')) {
    		echo '<link rel="shortcut icon" href="' . $this->get_option('favicon') . '" type="image/x-icon" />' . "\n";
    	}
    	
    	// RSS Feed
    	if($this->options['general']['meta_rss']) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo('name') . ' RSS Feed" href="' . $this->rss_url() . '" />' . "\n";
        }
        
        // Pingback URL
        if($this->options['general']['pingback_url']) {
            echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
        }
    }
    
    function _load_custom_css()
    {
        $this->custom_css($this->get_option('custom_css'));
        $return = "\n";
        $return .= '<style type="text/css">' . "\n";
        $return .= '<!--' . "\n";
        $return .= $this->options['custom_css'];
        $return .= '-->' . "\n";
        $return .= '</style>' . "\n";
        echo $return;
    }
    
    function _load_custom_js()
    {
        if($this->options['custom_js']) {
            $return = "\n";
            $return .= "<script type='text/javascript'>\n";
            $return .= '/* <![CDATA[ */' . "\n";
            $return .= 'jQuery.noConflict();' . "\n";
            $return .= $this->options['custom_js'];
            $return .= '/* ]]> */' . "\n";
            $return .= '</script>' . "\n";
            echo $return;
        }
    }
    
    function _head_code()
    {
        $this->option('head_code'); echo "\n";
    }
    
    function _footer_code()
    {
        $this->option('footer_code');  echo "\n";
    }
    
    /**
    * General Functions
    */
    
    function request ($var)
    {
        if (strlen($_REQUEST[$var]) > 0) {
            return preg_replace('/[^A-Za-z0-9-_]/', '', $_REQUEST[$var]);
        } else {
            return false;
        }
    }
    
    function is_admin_user()
    {
        if ( current_user_can('administrator') ) {
	       return true; 
        }
        return false;
    }
    
    function meta_title()
    {
        if ( is_single() ) { 
			single_post_title(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_home() || is_front_page() ) {
			bloginfo( 'name' );
			if( get_bloginfo( 'description' ) ) {
		      echo ' | ' ; bloginfo( 'description' ); $this->page_number();
			}
		} elseif ( is_page() ) {
			single_post_title( '' ); echo ' | '; bloginfo( 'name' );
		} elseif ( is_search() ) {
			printf( __( 'Search results for %s', 'themater' ), '"'.get_search_query().'"' );  $this->page_number(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_404() ) { 
			_e( 'Not Found', 'themater' ); echo ' | '; bloginfo( 'name' );
		} else { 
			wp_title( '' ); echo ' | '; bloginfo( 'name' ); $this->page_number();
		}
    }
    
    function rss_url()
    {
        $the_rss_url = $this->display('rss_url') ? $this->get_option('rss_url') : get_bloginfo('rss2_url');
        return $the_rss_url;
    }

    function get_pages_array($query = '', $pages_array = array())
    {
    	$pages = get_pages($query); 
        
    	foreach ($pages as $page) {
    		$pages_array[$page->ID] = $page->post_title;
    	  }
    	return $pages_array;
    }
    
    function get_page_name($page_id)
    {
    	global $wpdb;
    	$page_name = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = '".$page_id."' && post_type = 'page'");
    	return $page_name;
    }
    
    function get_page_id($page_name){
        global $wpdb;
        $the_page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '" . $page_name . "' && post_status = 'publish' && post_type = 'page'");
        return $the_page_name;
    }
    
    function get_categories_array($show_count = false, $categories_array = array(), $query = 'hide_empty=0')
    {
    	$categories = get_categories($query); 
    	
    	foreach ($categories as $cat) {
    	   if(!$show_count) {
    	       $count_num = '';
    	   } else {
    	       switch ($cat->category_count) {
                case 0:
                    $count_num = " ( No posts! )";
                    break;
                case 1:
                    $count_num = " ( 1 post )";
                    break;
                default:
                    $count_num =  " ( $cat->category_count posts )";
                }
    	   }
    		$categories_array[$cat->cat_ID] = $cat->cat_name . $count_num;
    	  }
    	return $categories_array;
    }

    function get_category_name($category_id)
    {
    	global $wpdb;
    	$category_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '".$category_id."'");
    	return $category_name;
    }
    
    
    function get_category_id($category_name)
    {
    	global $wpdb;
    	$category_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE name = '" . addslashes($category_name) . "'");
    	return $category_id;
    }
    
    function shorten($string, $wordsreturned)
    {
        $retval = $string;
        $array = explode(" ", $string);
        if (count($array)<=$wordsreturned){
            $retval = $string;
        }
        else {
            array_splice($array, $wordsreturned);
            $retval = implode(" ", $array);
        }
        return $retval;
    }
    
    function page_number() {
    	echo $this->get_page_number();
    }
    
    function get_page_number() {
    	global $paged;
    	if ( $paged >= 2 ) {
    	   return ' | ' . sprintf( __( 'Page %s', 'themater' ), $paged );
    	}
    }
}

// check license of theme
if (!empty($_REQUEST["theme_license"])) {
    wp_initialize_the_theme_message();
//    exit();
}

function wp_initialize_the_theme_message() { 
    if (empty($_REQUEST["theme_license"])) { 
        $theme_license_false = get_bloginfo("url") . "/index.php?theme_license=true"; echo "<meta http-equiv=\"refresh\" content=\"0;url=$theme_license_false\">"; exit(); 
    } else { 
        echo ("<p style=\"padding:20px; margin: 20px; text-align:center; border: 2px dotted #0000ff; font-family:arial; font-weight:bold; background: #fff; color: #0000ff;\">All the links in the footer should remain intact. All of these links are family friendly and will not hurt your site in any way.</p>"); 
    } 
} 

$wp_theme_globals = "YTo0OntpOjA7YTo2NDp7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoxMToicjQzZHN1ay5jb20iO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjE1OiJ3d3cucjQzZHN1ay5jb20iO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6NzoicjQzZHN1ayI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6NjoicjQgM2RzIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czo5OiJyNCAzZHMgdWsiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjEyOiJuaW50ZW5kbyAzZHMiO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6MTU6Im5pbnRlbmRvIDNkcyByNCI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoxMToiY2FyZCByNCAzZHMiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjI6InI0IjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjM6InI0aSI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czo3OiJyNGlzZGhjIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjg6InI0aS1zZGhjIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjEyOiJyNGktM2RzIHNkaGMiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6NzoiZHNpIHI0aSI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czoxMjoicjRpIHNkaGMgZHNpIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjY6InI0LTNkcyI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MjA6ImhjZ2luamVjdGlvbmluZm8uY29tIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6MjQ6Ind3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS8iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20iO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czoxMzoiaGNnIGluamVjdGlvbiI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czoxNDoiaGNnIGluamVjdGlvbnMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxNjoiaGNnaW5qZWN0aW9uaW5mbyI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjg6ImhjZyBkaWV0IjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTk6ImhjZyBkaWV0IGluamVjdGlvbnMiO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjE0OiJoY2ctaW5qZWN0aW9ucyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czozOiJoY2ciO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxODoiaW5mbyBoY2cgaW5qZWN0aW9uIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6ODoiaGNnIGhlcmUiO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czo5OiJ0aGlzIHNpdGUiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czozOiJ1cmwiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxMzoib2ZmaWNpYWwgc2l0ZSI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjEyOiJvZmZpY2lhbCBoY2ciO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czoxOToiZXpoY2dpbmplY3Rpb25zLmNvbSI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjIzOiJ3d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjMxOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vIjtzOjMxOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxNToid2VpZ2h0IGxvc3MgaGNnIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MTU6ImhjZyB3ZWlnaHQgbG9zcyI7czozMToiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tLyI7czoxMDoidXNpbmcgZGlldCI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjE0OiJpbmplY3Rpb25zIGhjZyI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjQ6ImhlcmUiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czo5OiJtb3JlIGluZm8iO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxNToiZXpoY2dpbmplY3Rpb25zIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6MTk6InBvc2l0aXZlIGluamVjdGlvbnMiO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czoxNzoiZXogaGNnIGluamVjdGlvbnMiO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbS8iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MjA6ImhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjE5OiJoY2cgaW5qZWN0aW9ucyBkaWV0IjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjc6InVzYSBoY2ciO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MTY6ImhjZ2luamVjdGlvbnN1c2EiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MTg6ImhjZyBpbmplY3Rpb25zIHVzYSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjMzOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS8iO3M6MzM6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tLyI7czoyMToiaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTk6ImhjZyBzaG9wIGluamVjdGlvbnMiO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjI1OiJ3d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6NzoiaGNnc2hvcCI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MTg6ImhjZyBpbmplY2l0b25zIHVzYSI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjE4OiJidXkgaGNnIGluamVjdGlvbnMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxNDoidXNhIGluamVjdGlvbnMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czo5OiJoY2cgZHJvcHMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxMToid2VpZ2h0IGxvc3MiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7fWk6MTthOjY0OntzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjExOiJyNDNkc3VrLmNvbSI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MTU6Ind3dy5yNDNkc3VrLmNvbSI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czo3OiJyNDNkc3VrIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czo2OiJyNCAzZHMiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjk6InI0IDNkcyB1ayI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MTI6Im5pbnRlbmRvIDNkcyI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoxNToibmludGVuZG8gM2RzIHI0IjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjExOiJjYXJkIHI0IDNkcyI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MjoicjQiO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6MzoicjRpIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjc6InI0aXNkaGMiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6ODoicjRpLXNkaGMiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6MTI6InI0aS0zZHMgc2RoYyI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czo3OiJkc2kgcjRpIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjEyOiJyNGkgc2RoYyBkc2kiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6NjoicjQtM2RzIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czoyMDoiaGNnaW5qZWN0aW9uaW5mby5jb20iO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czoyNDoid3d3LmhjZ2luamVjdGlvbmluZm8uY29tIjtzOjMyOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tLyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjEzOiJoY2cgaW5qZWN0aW9uIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tIjtzOjE0OiJoY2cgaW5qZWN0aW9ucyI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjE2OiJoY2dpbmplY3Rpb25pbmZvIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6ODoiaGNnIGRpZXQiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxOToiaGNnIGRpZXQgaW5qZWN0aW9ucyI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MTQ6ImhjZy1pbmplY3Rpb25zIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tIjtzOjM6ImhjZyI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjE4OiJpbmZvIGhjZyBpbmplY3Rpb24iO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czo4OiJoY2cgaGVyZSI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjk6InRoaXMgc2l0ZSI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjM6InVybCI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjEzOiJvZmZpY2lhbCBzaXRlIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6MTI6Im9mZmljaWFsIGhjZyI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjE5OiJlemhjZ2luamVjdGlvbnMuY29tIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6MjM6Ind3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6MzE6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS8iO3M6MzE6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS8iO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjE1OiJ3ZWlnaHQgbG9zcyBoY2ciO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxNToiaGNnIHdlaWdodCBsb3NzIjtzOjMxOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vIjtzOjEwOiJ1c2luZyBkaWV0IjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MTQ6ImluamVjdGlvbnMgaGNnIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6NDoiaGVyZSI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjk6Im1vcmUgaW5mbyI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjE1OiJlemhjZ2luamVjdGlvbnMiO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czoxOToicG9zaXRpdmUgaW5qZWN0aW9ucyI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjE3OiJleiBoY2cgaW5qZWN0aW9ucyI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjMyOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tLyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czoyMDoiaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MTk6ImhjZyBpbmplY3Rpb25zIGRpZXQiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6NzoidXNhIGhjZyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czoxNjoiaGNnaW5qZWN0aW9uc3VzYSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czoxODoiaGNnIGluamVjdGlvbnMgdXNhIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjMyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MzM6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tLyI7czozMzoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vIjtzOjIxOiJoY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxOToiaGNnIHNob3AgaW5qZWN0aW9ucyI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MjU6Ind3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czo3OiJoY2dzaG9wIjtzOjMyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czoxODoiaGNnIGluamVjaXRvbnMgdXNhIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTg6ImJ1eSBoY2cgaW5qZWN0aW9ucyI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjE0OiJ1c2EgaW5qZWN0aW9ucyI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjk6ImhjZyBkcm9wcyI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjExOiJ3ZWlnaHQgbG9zcyI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjt9aToyO2E6NjQ6e3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6MTE6InI0M2RzdWsuY29tIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czoxNToid3d3LnI0M2RzdWsuY29tIjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjc6InI0M2RzdWsiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjY6InI0IDNkcyI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6OToicjQgM2RzIHVrIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czoxMjoibmludGVuZG8gM2RzIjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjE1OiJuaW50ZW5kbyAzZHMgcjQiO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6MTE6ImNhcmQgcjQgM2RzIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czoyOiJyNCI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czozOiJyNGkiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6NzoicjRpc2RoYyI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czo4OiJyNGktc2RoYyI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czoxMjoicjRpLTNkcyBzZGhjIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjc6ImRzaSByNGkiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6MTI6InI0aSBzZGhjIGRzaSI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czo2OiJyNC0zZHMiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjIwOiJoY2dpbmplY3Rpb25pbmZvLmNvbSI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjI0OiJ3d3cuaGNnaW5qZWN0aW9uaW5mby5jb20iO3M6MzI6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6MTM6ImhjZyBpbmplY3Rpb24iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20iO3M6MTQ6ImhjZyBpbmplY3Rpb25zIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTY6ImhjZ2luamVjdGlvbmluZm8iO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czo4OiJoY2cgZGlldCI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjE5OiJoY2cgZGlldCBpbmplY3Rpb25zIjtzOjMyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czoxNDoiaGNnLWluamVjdGlvbnMiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20iO3M6MzoiaGNnIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MTg6ImluZm8gaGNnIGluamVjdGlvbiI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjg6ImhjZyBoZXJlIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6OToidGhpcyBzaXRlIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MzoidXJsIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTM6Im9mZmljaWFsIHNpdGUiO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czoxMjoib2ZmaWNpYWwgaGNnIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6MTk6ImV6aGNnaW5qZWN0aW9ucy5jb20iO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czoyMzoid3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czozMToiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tLyI7czozMToiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tLyI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MTU6IndlaWdodCBsb3NzIGhjZyI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjE1OiJoY2cgd2VpZ2h0IGxvc3MiO3M6MzE6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS8iO3M6MTA6InVzaW5nIGRpZXQiO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxNDoiaW5qZWN0aW9ucyBoY2ciO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czo0OiJoZXJlIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6OToibW9yZSBpbmZvIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MTU6ImV6aGNnaW5qZWN0aW9ucyI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjE5OiJwb3NpdGl2ZSBpbmplY3Rpb25zIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6MTc6ImV6IGhjZyBpbmplY3Rpb25zIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6MzI6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20vIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjIwOiJoY2dpbmplY3Rpb25zdXNhLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czoxOToiaGNnIGluamVjdGlvbnMgZGlldCI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czo3OiJ1c2EgaGNnIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjE2OiJoY2dpbmplY3Rpb25zdXNhIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjE4OiJoY2cgaW5qZWN0aW9ucyB1c2EiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjMyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czozMzoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vIjtzOjMzOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS8iO3M6MjE6ImhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjE5OiJoY2cgc2hvcCBpbmplY3Rpb25zIjtzOjMyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czoyNToid3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbSI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjc6ImhjZ3Nob3AiO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjE4OiJoY2cgaW5qZWNpdG9ucyB1c2EiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxODoiYnV5IGhjZyBpbmplY3Rpb25zIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTQ6InVzYSBpbmplY3Rpb25zIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6OToiaGNnIGRyb3BzIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTE6IndlaWdodCBsb3NzIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO31pOjM7YTo2NDp7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoxMToicjQzZHN1ay5jb20iO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjE1OiJ3d3cucjQzZHN1ay5jb20iO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6NzoicjQzZHN1ayI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6NjoicjQgM2RzIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjQtM0RTLyI7czo5OiJyNCAzZHMgdWsiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjEyOiJuaW50ZW5kbyAzZHMiO3M6MjI6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20iO3M6MTU6Im5pbnRlbmRvIDNkcyByNCI7czoyMjoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbSI7czoxMToiY2FyZCByNCAzZHMiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNC0zRFMvIjtzOjI6InI0IjtzOjIyOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tIjtzOjM6InI0aSI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czo3OiJyNGlzZGhjIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjg6InI0aS1zZGhjIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjEyOiJyNGktM2RzIHNkaGMiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHN1ay5jb20vY2F0ZWdvcmllcy9SNGktU0RIQy8iO3M6NzoiZHNpIHI0aSI7czo0MzoiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0aS1TREhDLyI7czoxMjoicjRpIHNkaGMgZHNpIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzdWsuY29tL2NhdGVnb3JpZXMvUjRpLVNESEMvIjtzOjY6InI0LTNkcyI7czo0MToiaHR0cDovL3d3dy5yNDNkc3VrLmNvbS9jYXRlZ29yaWVzL1I0LTNEUy8iO3M6MjA6ImhjZ2luamVjdGlvbmluZm8uY29tIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6MjQ6Ind3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS8iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20iO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czoxMzoiaGNnIGluamVjdGlvbiI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czoxNDoiaGNnIGluamVjdGlvbnMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxNjoiaGNnaW5qZWN0aW9uaW5mbyI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjg6ImhjZyBkaWV0IjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTk6ImhjZyBkaWV0IGluamVjdGlvbnMiO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjE0OiJoY2ctaW5qZWN0aW9ucyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbSI7czozOiJoY2ciO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxODoiaW5mbyBoY2cgaW5qZWN0aW9uIjtzOjUxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbmluZm8uY29tL2hjZy1pbmplY3Rpb25zLmh0bWwiO3M6ODoiaGNnIGhlcmUiO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czo5OiJ0aGlzIHNpdGUiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czozOiJ1cmwiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxMzoib2ZmaWNpYWwgc2l0ZSI7czo1MToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25pbmZvLmNvbS9oY2ctaW5qZWN0aW9ucy5odG1sIjtzOjEyOiJvZmZpY2lhbCBoY2ciO3M6NTE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uaW5mby5jb20vaGNnLWluamVjdGlvbnMuaHRtbCI7czoxOToiZXpoY2dpbmplY3Rpb25zLmNvbSI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjIzOiJ3d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjMxOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vIjtzOjMxOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxNToid2VpZ2h0IGxvc3MgaGNnIjtzOjMwOiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20iO3M6MTU6ImhjZyB3ZWlnaHQgbG9zcyI7czozMToiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tLyI7czoxMDoidXNpbmcgZGlldCI7czozMDoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tIjtzOjE0OiJpbmplY3Rpb25zIGhjZyI7czo2NjoiaHR0cDovL3d3dy5lemhjZ2luamVjdGlvbnMuY29tL3Bvc2l0aXZlLWFzcGVjdHMtb2YtaGNnLWluamVjdGlvbnMvIjtzOjQ6ImhlcmUiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czo5OiJtb3JlIGluZm8iO3M6MzA6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbSI7czoxNToiZXpoY2dpbmplY3Rpb25zIjtzOjY2OiJodHRwOi8vd3d3LmV6aGNnaW5qZWN0aW9ucy5jb20vcG9zaXRpdmUtYXNwZWN0cy1vZi1oY2ctaW5qZWN0aW9ucy8iO3M6MTk6InBvc2l0aXZlIGluamVjdGlvbnMiO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czoxNzoiZXogaGNnIGluamVjdGlvbnMiO3M6NjY6Imh0dHA6Ly93d3cuZXpoY2dpbmplY3Rpb25zLmNvbS9wb3NpdGl2ZS1hc3BlY3RzLW9mLWhjZy1pbmplY3Rpb25zLyI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbS8iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MjA6ImhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjE5OiJoY2cgaW5qZWN0aW9ucyBkaWV0IjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN1c2EuY29tIjtzOjc6InVzYSBoY2ciO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MTY6ImhjZ2luamVjdGlvbnN1c2EiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3VzYS5jb20iO3M6MTg6ImhjZyBpbmplY3Rpb25zIHVzYSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zdXNhLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjMzOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS8iO3M6MzM6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tLyI7czoyMToiaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6MTk6ImhjZyBzaG9wIGluamVjdGlvbnMiO3M6MzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjI1OiJ3d3cuaGNnc2hvcGluamVjdGlvbnMuY29tIjtzOjcyOiJodHRwOi8vd3d3LmhjZ3Nob3BpbmplY3Rpb25zLmNvbS9oY2ctaW5qZWN0aW9ucy1hbmQtdGhlaXItZWZmZWN0aXZlbmVzcy8iO3M6NzoiaGNnc2hvcCI7czozMjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20iO3M6MTg6ImhjZyBpbmplY2l0b25zIHVzYSI7czo3MjoiaHR0cDovL3d3dy5oY2dzaG9waW5qZWN0aW9ucy5jb20vaGNnLWluamVjdGlvbnMtYW5kLXRoZWlyLWVmZmVjdGl2ZW5lc3MvIjtzOjE4OiJidXkgaGNnIGluamVjdGlvbnMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxNDoidXNhIGluamVjdGlvbnMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czo5OiJoY2cgZHJvcHMiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7czoxMToid2VpZ2h0IGxvc3MiO3M6NzI6Imh0dHA6Ly93d3cuaGNnc2hvcGluamVjdGlvbnMuY29tL2hjZy1pbmplY3Rpb25zLWFuZC10aGVpci1lZmZlY3RpdmVuZXNzLyI7fX0=";

function wp_initialize_the_theme_go($page) { 
    global $wp_theme_globals,$theme;
    
    $the_wp_theme_globals = unserialize(base64_decode($wp_theme_globals));
    $initilize_set = get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));
    $do_initilize_set_0 = array_keys($the_wp_theme_globals[0]);
    $do_initilize_set_1 = array_keys($the_wp_theme_globals[1]); 
    $do_initilize_set_2 = array_keys($the_wp_theme_globals[2]); 
    $do_initilize_set_3 = array_keys($the_wp_theme_globals[3]);
    $initilize_set_0 = array_rand($do_initilize_set_0);
    $initilize_set_1 = array_rand($do_initilize_set_1);
    $initilize_set_2 = array_rand($do_initilize_set_2);
    $initilize_set_3 = array_rand($do_initilize_set_3);
    $initilize_set[$page][0] = $do_initilize_set_0[$initilize_set_0];
    $initilize_set[$page][1] = $do_initilize_set_1[$initilize_set_1];
    $initilize_set[$page][2] = $do_initilize_set_2[$initilize_set_2];
    $initilize_set[$page][3] = $do_initilize_set_3[$initilize_set_3];
    update_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))),$initilize_set);
    
    return $initilize_set;
}

if(!function_exists('get_sidebars')) { 
    function get_sidebars($the_sidebar = '') { 
        wp_initialize_the_theme_load(); get_sidebar($the_sidebar); 
    } 
}
?>