<?php
/*
  Plugin Name: Nginx Exact URL Purge
  Plugin URI: https://www.wpoets.com/
  Description: Import Export Awesome App's as package (XML) for transfering them from one site to another.
  Version: 1.0
  Author: WPoets Team
  Author URI: httpa://www.wpoets.com/
  License: GPLv3+
*/

if ( ! defined( 'NGINX_CACHE_PATH' ) ) {
    define( 'NGINX_CACHE_PATH', '/var/run/nginx-cache' );
}

add_action( 'admin_init', 'wpoets_nginx_purge::purge_cache' );
add_action( 'admin_menu', 'wpoets_nginx_purge::register_menus' );
add_action( 'admin_bar_menu', 'wpoets_nginx_purge::register_admin_bar_menus',3000 );

class wpoets_nginx_purge{
	static function register_menus(){
		add_management_page( "Nginx Exact URL Purge", "Nginx Exact URL Purge", 'manage_options', 'nginx-exact-url-purge', 'wpoets_nginx_purge::menu_page' );
		
	}
	
	static function menu_page(){
		
		echo '<div class="wrap" >'; 
			echo '<h3>Purge Nginx Cache</h3>';
			if(isset($_GET['msg'])){

				echo'<div class="notice notice-info"><p style="font-weight:700">'.htmlentities($_GET['msg']).'</p></div>';
				
			}
			echo '
			<div>
			<form method="post" action="'.wp_nonce_url(admin_url('tools.php?page=nginx-exact-url-purge&nginx_exact_cache_purge=yes'), 'redis_nonced-purge').'" method="post">
				<label for="nginx_url_to_purge" style="font-weight:bold">URL To Purge</label></br>
				</br>
				<input type="text" style="width:500px" id="nginx_url_to_purge" name="nginx_url_to_purge" />
				<input type="submit" class="button-secondary" value="Purge Cache"/>
			 </form>
			 </div>';
			echo '</div>
				';
				
			
		echo '</div>';		
				
	}
	
	static function register_admin_bar_menus(){
	  global $wp_admin_bar;
	  
	  if(!current_user_can( 'manage_options' ))
		return;

		$menu_id = 'wpoetsneup';
		$wp_admin_bar->add_menu(array('id' => $menu_id, 'title' => 'Nginx Exact Purge', 'href' => get_admin_url(null,'tools.php?page=nginx-exact-url-purge')));
		
	}
	
	
	static function purge_cache(){
		if ( !isset( $_REQUEST['nginx_exact_cache_purge'] ) )
				return;

			if ( !current_user_can( 'manage_options' ) )
				wp_die( 'Sorry, you do not have the necessary privileges to edit these options.' );

			$action = $_REQUEST['nginx_exact_cache_purge'];

			if ( $action == 'yes' ) {
				$url = $_REQUEST['nginx_url_to_purge'];
				$status = self::flush_cache($url);
				
			}

			wp_redirect(get_admin_url(null,'tools.php?page=nginx-exact-url-purge'). '&msg='.$status );
	}
		
	static function flush_cache( $page_url ) {
   
		$msg='x';
		$path = wp_parse_url( $page_url );
		
		$url= $path['scheme']. '://' . $path['host'] . '/purge' . $path['path'];
		//purge		
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {

			$_errors_str = implode( ' - ', $response->get_error_messages() );
			$msg =  'Error while purging URL. ' . $_errors_str ;

		} else {

			if ( $response['response']['code'] ) {

				switch ( $response['response']['code'] ) {

					case 200:
							$msg =   '- - ' . $url . ' *** PURGED ***' ;
						break;
					case 404:
							$msg =   '- - ' . $url . ' is currently not cached' ;
						break;
					default:
							$msg =   '- - ' . $url . ' not found ( ' . $response['response']['code'] . ' )' ;

				}
			}
    }  /**/
	
	return $msg;
	}
}