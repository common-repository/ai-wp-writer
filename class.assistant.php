<?php

class AIASIST{
	
	private $info;
	
	private $steps;
	
	private $options;

	private $api = 'https://aipost.ru';
		
	function __construct(){
		$this->options = get_option('_ai_assistant');
		$this->info = $this->getInfo();
		$this->steps = get_option('_aiassist_generator');
		
		if( isset( $this->info->promts ) && ! is_array( $this->steps['promts'] ?? null ) )
			$this->steps['promts'] = (array) $this->info->promts;
	
		if( @$this->info->promts ){			
			foreach( $this->info->promts as $key => $promts ){
				foreach( $promts as $k => $promt ){
					if( ! isset( $this->steps['promts'][ $key ][ $k ] ) )
						$this->steps['promts'][ $key ][ $k ] = $promt;
					else
						$this->steps['promts'][ $key ][ $k ] = $this->steps['promts'][ $key ][ $k ];
				}
			}
		}
	
		if( ! isset( $this->options->token ) ){
			$this->options = new stdClass();
			$this->options->token = null;
		}
	
		add_filter('https_ssl_verify',					'__return_false');
		add_action('admin_menu',						[$this, 'menu']);
		add_action('wp_enqueue_scripts', 				[$this, 'front'] );
		add_action('admin_enqueue_scripts', 			[$this, 'scripts'] );
		add_filter('mce_external_plugins',				[$this, 'add_button']);
		add_filter('mce_buttons', 						[$this, 'button_init'], 999);
		add_action('edit_form_after_title',				[$this, 'metabox'] );
		
		add_action('wp_ajax_saveContent',				[$this, 'saveContent']);
		add_action('wp_ajax_clearContent',				[$this, 'clearContent']);
		add_action('wp_ajax_aiassist_sign',				[$this, 'sign']);
		add_action('wp_ajax_getStat',					[$this, 'getStat']);
		add_action('wp_ajax_aiassist_buy',				[$this, 'buy']);
		add_action('wp_ajax_saveStep',					[$this, 'saveStep']);
		add_action('wp_ajax_saveTranslateImagesPromts',	[$this, 'saveTranslateImagesPromts']);
		add_action('wp_ajax_loadImage',					[$this, 'loadImage']);
		add_action('wp_ajax_saveKey',					[$this, 'saveKey']);
		add_action('wp_ajax_getBonus',					[$this, 'getBonus']);
		
		add_action('wp_ajax_rewriteOptions',			[$this, 'rewriteOptions']);
		add_action('wp_ajax_autoGenOptions',			[$this, 'autoGenOptions']);
		
		add_action('wp_ajax_aiassist_cron',				[$this, 'cron']);
		add_action('wp_ajax_nopriv_aiassist_cron',		[$this, 'cron']);
		
		add_action('wp_ajax_initRewrite',				[$this, 'initRewrite']);
		add_action('wp_ajax_startRewrite',				[$this, 'startRewrite']);
		add_action('wp_ajax_clearRewrite',				[$this, 'clearRewrite']);
		add_action('wp_ajax_stopRewrite',				[$this, 'stopRewrite']);
		add_action('wp_ajax_postRestore',				[$this, 'postRestore']);
		
		add_action('wp_ajax_stopArticlesGen',			[$this, 'stopArticlesGen']);
		add_action('wp_ajax_clearArticlesGen',			[$this, 'clearArticlesGen']);
		add_action('wp_ajax_initArticlesGen',			[$this, 'initArticlesGen']);
		add_action('wp_ajax_startArticlesGen',			[$this, 'startArticlesGen']);
		
		add_action('wp_ajax_removeQueueArticle',		[$this, 'removeQueueArticle']);
	}
	
	public function menu(){
		add_menu_page('AI WP Writer', 'AI WP Writer', 'level_10', 'wpai-assistant', [$this, 'options'], 'dashicons-businessperson', 777);
	}
		
	public function cron(){
		global $wpdb;
		
		$data = [ 'rewrites' => [], 'articles' => [] ];
		$wpdb->query('SET innodb_lock_wait_timeout=1');
		$wpdb->query('START TRANSACTION');
		$hold = $wpdb->query('SELECT * FROM `'. $wpdb->options .'` WHERE `option_name`="aiWriterCronCheck" FOR UPDATE');
		
		if( $hold === false )
			return $data;
		
		$check = (int) get_option('aiWriterCronCheck');
		
		if( $check < time() - 60 ){
			$data['rewrites'] = $this->aiRewrite();
			$data['articles'] = $this->aiArticlesAutoGen();
			update_option('aiWriterCronCheck', time() );
		}
		
		$wpdb->query('COMMIT');
		wp_die( json_encode( $data ) );
	}
	
	public function options(){
		if( isset( $_POST['save'] ) ){
			if( isset( $_POST['token'] ) )
				$this->activation( sanitize_text_field( $_POST['token'] ) );
		
			$this->options = new stdClass();
			
			if( isset( $_POST['token'] ) && preg_match('/^[A-Za-z0-9]{64}$/i', $_POST['token']) ){
				$this->options->token = sanitize_text_field( $_POST['token'] );
				$this->info = $this->getInfo();
			}
			
			update_option('_ai_assistant', $this->options );
		}
		$rewrites	= get_option('aiRewritesData');
		$autoGen	= get_option('aiArticlesAutoGenData');
		$cats		= get_categories( [ 'hide_empty' => 0 ] );
		
		include dirname(__FILE__) . '/tpl/options.php';
	}
	
	public function removeQueueArticle(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') || ! isset( $_POST['id'] ) )
			return;
		
		if( ! $autoGen = get_option('aiArticlesAutoGenData') )
			$autoGen = [];
		
		$id = (int) $_POST['id'];
		
		if( isset( $autoGen['articles'][ $id ] ) )
			unset( $autoGen['articles'][ $id ] );
		
		update_option('aiArticlesAutoGenData', $autoGen);
	}
	
	public function sign(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		if( ! empty( $_POST ) ){
			@$_POST['action'] = sanitize_text_field( @$_POST['act'] );
			wp_die( $this->wpCurl( $this->api, $_POST ) );
		}
		
		wp_die('{"success":"false"}');
	}
	
	public function buy(){
		if( ! $this->checkNonce()  || ! current_user_can('manage_options') )
			return;
	
		wp_die( $this->wpcurl( $this->api, [ 'token' => sanitize_text_field( $this->options->token ), 'action' => 'getPayUrl', 'promocode' => $_POST['promocode'], 'type' => sanitize_text_field( $_POST['type'] ), 'crypto' => sanitize_text_field( $_POST['crypto'] ), 'out_summ' => sanitize_text_field( $_POST['out_summ'] ), 'gptkey' => sanitize_text_field( get_option('aiassist_gpt_key') ) ] ) );
	}
	
	private function activation( $token ){
		$this->wpcurl( $this->api, [ 'host' => $this->getHost(), 'action' => 'activation', 'token' => sanitize_text_field( $token ) ] );
	}
	
	public function getBonus(){
		if( ! $this->checkNonce()  || ! current_user_can('manage_options') )
			return;
	
		wp_die( $this->wpcurl( $this->api, [ 'method' => sanitize_text_field( $_POST['method'] ), 'wallet' => sanitize_text_field( $_POST['wallet'] ), 'info' => sanitize_text_field( $_POST['info'] ), 'token' => sanitize_text_field( $this->options->token ), 'action' => 'requestBonus' ] ) );
	}
	
	private function getInfo(){
		$args = [ 'action' => 'getInfo', 'token' => sanitize_text_field( @$this->options->token ) ];
		
		if( isset( $_POST['promocode'] ) )
			$args['promocode'] = sanitize_text_field( $_POST['promocode'] );
	
		if( $info = json_decode( $this->wpcurl( $this->api, $args ) ) )
			return $info;
		
		return false;
	}
	
	public function saveKey(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		if( isset( $_POST['key'] ) )
			update_option('aiassist_gpt_key', sanitize_text_field( $_POST['key'] ) );
	}
	
	public function getStat(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
	
		wp_die( $this->wpcurl( $this->api, [ 'token' => sanitize_text_field( $this->options->token ), 'action' => 'getStat', 'host' => sanitize_text_field( $this->getHost() ), 'dateStart' => sanitize_text_field( $_REQUEST['dateStart'] ), 'dateEnd' => sanitize_text_field( $_REQUEST['dateEnd'] ) ] ) );
	}
	
	private function getHost(){
		$host = get_option('siteurl');
		
		if( $_SERVER['HTTPS'] == 'on' )
			$host = str_replace('http://', 'https://', $host);
			
		return $host;
	}

	private function aiArticlesAutoGen(){	
		if( $data = get_option('aiArticlesAutoGenData') ){
			
			if( ! @$data['start'] || ! @$data['articles'] )
				return $data;
			
			$key = date('Ymd');
			
			if( ! isset( $data['counter'][ $key ] ) )
				$data['counter'] = [ $key => 0 ];
				
			if( ! @$data['publishInDay'] )
				$data['publishInDay'] = +100500;
				
			if( $data['counter'][ $key ] > $data['publishInDay'] )
				return $data;
			
			$lang_id = 0;
			$break = false;
			$data['publish'] = 0;
			
			if( isset( $this->steps['promts']['multi_lang'] ) )
				$lang_id = (int) $this->steps['promts']['multi_lang'];
			
			foreach( $data['articles'] as $k => $article ){
			
				if( $article['post_id'] )
					$data['publish']++;
				
				if( $break )
					continue;
			
				if( ! isset( $article['task_id'] ) && $data['publishInDay'] > $data['counter'][ $key ] ){
					$break = true;
					
					$args = [
								'imageFormat'	=> 'jpg', 
								'thumb'			=> (bool) $data['thumb'], 
								'images'		=> (bool) $data['images'], 
								'textModel'		=> $data['textModel'], 
								'imageModel'	=> $data['imageModel'], 
								'lang_id'		=> $lang_id, 
								'promt'			=> isset( $this->steps['promts']['multi'][ $lang_id ] ) ? $this->steps['promts']['multi'][ $lang_id ] : $this->info->promts->multi[ $lang_id ],
								'title'			=> isset( $this->steps['promts']['multi_title'][ $lang_id ] ) ? $this->steps['promts']['multi_title'][ $lang_id ] : $this->info->promts->multi_title[ $lang_id ],
								'description'	=> isset( $this->steps['promts']['multi_desc'][ $lang_id ] ) ? $this->steps['promts']['multi_desc'][ $lang_id ] : $this->info->promts->multi_desc[ $lang_id ],
								'keywords'		=> $article['keywords'], 
								'action'		=> 'addAutoTask', 
								'token'			=> $this->options->token, 
								'gptkey'		=> get_option('aiassist_gpt_key'),
							];
							
					$task = json_decode( $this->wpCurl($this->api, $args ) );
					
					if( $task->task_id ){
						
						if( $revision_id = wp_insert_post( [ 'post_type' => 'wpai', 'post_title' => $article['keywords'] ] ) ){
							$data['counter'][ $key ]++;
							$data['articles'][ $k ]['task_id'] = $task->task_id;
							$data['articles'][ $k ]['revision_id'] = $revision_id;							
						}
					
					}
				
				}
				
				if( isset( $article['task_id'] ) && isset( $article['revision_id'] ) && ! isset( $article['post_id'] ) ){
					$task = json_decode( $this->wpCurl( $this->api, [ 'action' => 'getTask', 'id' => $article['task_id'], 'host' => $this->getHost(), 'token' => $this->options->token ] ) );
					
					if( ! @$data['articles'][ $k ]['check'] )
						$data['articles'][ $k ]['check'] = 0;
					
					if( ! isset( $task->content ) )
						$data['articles'][ $k ]['check']++;
						
					if( $data['articles'][ $k ]['check'] > 120 )
						$data['articles'][ $k ]['check'] = 0;
					
					if( $data['articles'][ $k ]['check'] > 60 )
						continue;
					
					$break = true;
					
					if( isset( $task->content ) ){
						
						$args = [
							'ID'			=> $article['revision_id'], 
							'post_type'		=> 'post', 
							'post_status'	=> $data['draft'] ? 'draft' : 'publish', 
							'post_title'	=> sanitize_text_field( wp_unslash( $task->header ) ), 
							'post_content'	=> wp_kses_post( wp_unslash( $task->content ) ),
							'post_category'	=> [ (int) $article['cat_id'] ],
						];
					
						if( $post_id = wp_update_post( $args ) ){
							$data['publish']++;
							$data['articles'][ $k ]['post_id'] = $post_id;
							
							update_post_meta($post_id, '_title', sanitize_text_field( wp_unslash( $task->title ) ) );
							update_post_meta($post_id, '_description', sanitize_text_field( wp_unslash( $task->description ) ) );
							update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field( wp_unslash( $task->title ) ) );
							update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field( wp_unslash( $task->description ) ) );
							update_post_meta($post_id, 'rank_math_title', sanitize_text_field( wp_unslash( $task->title ) ) );
							update_post_meta($post_id, 'rank_math_description', sanitize_text_field( wp_unslash( $task->description ) ) );
						
							if( $task->thumb ){
								if( $thumb_id = (int) $this->loadFile( $this->api .'/?action=getImage&image='. $task->thumb, $post_id ) )
									set_post_thumbnail( $post_id, $thumb_id );
							}
							
							if( $task->images ){
								$task->alts = (array) $task->alts;
								
								foreach( $task->images as $k => $src ){
									
									if( strpos($task->content, $src) === false )
										continue;
								
									if( $img_id = (int) $this->loadFile( $this->api .'/?action=getImage&image='. $src, $post_id ) ){
										$image = wp_get_attachment_image( $img_id, 'full', false, [ 'class' => 'size-full wp-image-'. $img_id .' aligncenter', 'alt' => $this->clearTitle( $task->alts[ $k ] ) .' фото', 'title' => $this->clearTitle( $task->alts[ $k ] ) ] );		
										$task->content = str_replace($src, $image, $task->content);
									}
								}
								wp_update_post( [ 'ID' => $post_id, 'post_content' => $task->content ] );
							}
						}
						
					}
				}
			}
			
			if( $data['publishInDay'] == 100500 )
				unset( $data['publishInDay'] );
			
			update_option('aiArticlesAutoGenData', $data);
		}
		return $data;
	}
	
	public function autoGenOptions(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$data = get_option('aiArticlesAutoGenData');
		$data['draft'] = (bool) $_POST['draft'];
		$data['thumb'] = (bool) $_POST['thumb'];
		$data['images'] = (bool) $_POST['images'];
		$data['publishInDay'] = (int) $_POST['publishInDay'];
		$data['imageModel'] = sanitize_text_field( $_POST['imageModel'] );
		$data['textModel'] = sanitize_text_field( $_POST['textModel'] );
		update_option('aiArticlesAutoGenData', $data);
	}
	
	public function stopArticlesGen(){	
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$data = get_option('aiArticlesAutoGenData');
		$data['start'] = false;
		update_option('aiArticlesAutoGenData', $data);
	}
	
	public function startArticlesGen(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$data = get_option('aiArticlesAutoGenData');
		$data['start'] = true;
		update_option('aiArticlesAutoGenData', $data);
	}
	
	public function clearArticlesGen(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$args = get_option('aiArticlesAutoGenData');
		$data = [ 'start' => true, 'count' => 0, 'publish' => 0, 'articles' => [] ];
			
		if( isset( $args['publishInDay'] ) )
			$data['publishInDay'] = (int) $args['publishInDay'];
			
		if( isset( $args['draft'] ) )
			$data['draft'] = (bool) $args['draft'];
			
		if( isset( $args['thumb'] ) )
			$data['thumb'] = (bool) $args['thumb'];
			
		if( isset( $args['images'] ) )
			$data['images'] = (bool) $args['images'];
			
		if( isset( $args['imageModel'] ) )
			$data['imageModel'] = sanitize_text_field( $args['imageModel'] );
			
		if( isset( $args['textModel'] ) )
			$data['textModel'] = sanitize_text_field( $args['textModel'] );
		
		update_option('aiArticlesAutoGenData', $data);
	}
	
	public function initArticlesGen(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		if( $articles = $_POST['articles'] ){
			$args = get_option('aiArticlesAutoGenData');
			$data = [ 'start' => true, 'count' => 0, 'publish' => 0, 'articles' => [] ];
				
			if( isset( $args['publishInDay'] ) )
				$data['publishInDay'] = (int) $args['publishInDay'];
				
			if( isset( $args['draft'] ) )
				$data['draft'] = (int) $args['draft'];
				
			if( isset( $args['thumb'] ) )
				$data['thumb'] = (int) $args['thumb'];
				
			if( isset( $args['images'] ) )
				$data['images'] = (int) $args['images'];
			
			if( ! empty( $args['articles'] ) )
				$data['articles'] = $args['articles'];
				
			if( ! empty( $args['imageModel'] ) )
				$data['imageModel'] = $args['imageModel'];
				
			if( ! empty( $args['textModel'] ) )
				$data['textModel'] = $args['textModel'];
			
			foreach( $articles as $cat_id => $article ){
				if( $items = explode("\n", sanitize_textarea_field( $article ) ) ){
					foreach( $items as $item )
						$data['articles'][] = [ 'keywords' => $item, 'cat_id' => $cat_id ];
				}
			}
			$data['count'] = count( $data['articles'] );
			update_option('aiArticlesAutoGenData', $data);
			
			wp_die( json_encode( $data ) );
		}
		wp_die('{"success":"false"}');
	}
	
	public function rewriteOptions(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$data = get_option('aiRewritesData');
		$data['thumb']		= (bool) $_POST['thumb'];
		$data['images']		= (bool) $_POST['images'];
		$data['split']		= (int) $_POST['split'];
		$data['draft'] 		= (bool) $_POST['draft'];
		$data['imageModel']	= sanitize_text_field( $_POST['imageModel'] );
		$data['textModel']	= sanitize_text_field( $_POST['textModel'] );
		update_option('aiRewritesData', $data);
	}

	public function aiRewrite(){
		if( $data = get_option('aiRewritesData') ){
			
			if( ! @$data['start'] || ! @$data['posts'] )
				return $data;
			
			$break = false;
			
			if( $data['posts'] ){
				$data['counter'] = 0;
				
				$lang_id = 0;
				
				if( isset( $this->steps['promts']['rewrite_lang'] ) )
					$lang_id = (int) $this->steps['promts']['rewrite_lang'];
				
				foreach( $data['posts'] as $k => $item ){
					
					if( @$item['post_id'] )
						$data['counter']++;
					
					if( $break )
						continue;
					
					if( ! isset( $item['task_id'] ) ){
						$break = true;
						
						$args = [
									'imageFormat'		=> 'jpg', 
									'split'				=> $data['split'], 
									'thumb'				=> (bool) $data['thumb'], 
									'images'			=> (bool) $data['images'], 
									'textModel'			=> $data['textModel'], 
									'imageModel'		=> $data['imageModel'], 
									'lang_id'			=> $lang_id,
									'promt'				=> isset( $this->steps['promts']['rewrite'][ $lang_id ] ) ? $this->steps['promts']['rewrite'][ $lang_id ] : $this->info->promts->rewrite[ $lang_id ],
									'action'			=> 'addRewrite', 
									'token'				=> $this->options->token, 
									// 'gptkey'			=> get_option('aiassist_gpt_key'),
								]; 
						
						if( (int) $item['id'] ){
						
							if( $post = get_post( $item['id'] ) ){
								$args['post_title'] = $post->post_title;
								$args['post_content'] = $post->post_content;
							}
							
							$meta = $this->getPostMeta( $item['id'] );
							
							if( isset( $meta->title ) )
								$args['meta_title'] = $meta->title;
							
							if( isset( $meta->description ) )
								$args['meta_description'] = $meta->description;
						}
						
						if( isset( $item['url'] ) )
							$args['url'] = $item['url'];
							
						$task = json_decode( $this->wpCurl( $this->api, $args ) );
						
						if( $task->task_id )
							$data['posts'][ $k ]['task_id'] = $task->task_id;
					}
					
					
					if( isset( $item['task_id'] ) && ! isset( $item['post_id'] ) ){
						$task = json_decode( $this->wpCurl( $this->api, [ 'action' => 'getTask', 'id' => $item['task_id'], 'host' => $this->getHost(), 'token' => $this->options->token ] ) );
						
						if( ! @$data['posts'][ $k ]['check'] )
							$data['posts'][ $k ]['check'] = 0;
						
						if( ! isset( $task->content ) )
							$data['posts'][ $k ]['check']++;
							
						if( $data['posts'][ $k ]['check'] > 120 )
							$data['posts'][ $k ]['check'] = 0;
						
						if( $data['posts'][ $k ]['check'] > 60 )
							continue;
						
						$break = true;
							
						if( isset( $task->content ) ){
							
							if( (int) $item['id'] ){
								$post_id = $item['id'];
								$revision = get_post( $post_id );
								
								$args = [
									'post_title'    => $revision->post_title,
									'post_content'  => $revision->post_content,
									'post_status'   => 'inherit',
									'post_type'     => 'revision',
									'post_parent'   => $post_id,
								];

								$revision_id = wp_insert_post( $args );
								$data['posts'][ $k ]['revision_id'] = $revision_id;
								
								$meta = $this->getPostMeta( $post_id );
								
								if( $meta->title )
									update_post_meta( $revision_id, '_aiassist_meta_title', $meta->title );
								
								if( $meta->description )
									update_post_meta( $revision_id, '_aiassist_meta_description', $meta->description );
								
								wp_update_post( [ 'ID' => $post_id, 'post_title' => $task->post_title, 'post_content' => $task->content ] );
								
							} elseif( (int) $item['revision_id'] ){
								
								$args = [
									'ID'			=> (int) $item['revision_id'],
									'post_type'		=> 'post', 
									'post_status'	=> (bool) $data['draft'] ? 'draft' : 'publish', 
									'post_title'	=> sanitize_text_field( wp_unslash( $task->post_title ) ), 
									'post_content'	=> wp_kses_post( wp_unslash( $task->content ) ),
									'post_category'	=> [ (int) $item['cat_id'] ],
								];
								
								$post_id = wp_update_post( $args );
								
							} else {
							
								$args = [
									'post_status'	=> (bool) $data['draft'] ? 'draft' : 'publish', 
									'post_title'	=> sanitize_text_field( wp_unslash( $task->post_title ) ), 
									'post_content'	=> wp_kses_post( wp_unslash( $task->content ) ),
									'post_category'	=> [ (int) $item['cat_id'] ],
								];
								
								$post_id = wp_insert_post( $args );
							}
							
							if( $post_id ){
								
								$data['counter']++;
								$data['posts'][ $k ]['post_id'] = $post_id;
								
								update_post_meta($post_id, '_title', sanitize_text_field( wp_unslash( $task->meta_title ) ) );
								update_post_meta($post_id, '_description', sanitize_text_field( wp_unslash( $task->meta_description ) ) );
								
								update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field( wp_unslash( $task->meta_title ) ) );
								update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field( wp_unslash( $task->meta_description ) ) );
								
								update_post_meta($post_id, 'rank_math_title', sanitize_text_field( wp_unslash( $task->meta_title ) ) );
								update_post_meta($post_id, 'rank_math_description', sanitize_text_field( wp_unslash( $task->meta_description ) ) );
								
								
								if( $task->thumb ){
									if( $thumb_id = (int) $this->loadFile( $this->api .'/?action=getImage&image='. $task->thumb, $post_id ) )
										set_post_thumbnail( $post_id, $thumb_id );
								}
								
								if( $task->images ){
									$task->alts = (array) $task->alts;
									
									foreach( $task->images as $k => $src ){
										
										if( strpos($task->content, $src) === false )
											continue;
									
										if( $img_id = (int) $this->loadFile( $this->api .'/?action=getImage&image='. $src, $post_id ) ){
											$image = wp_get_attachment_image( $img_id, 'full', false, [ 'class' => 'size-full wp-image-'. $img_id .' aligncenter', 'alt' => $this->clearTitle( $task->alts[ $k ] ) .' фото', 'title' => $this->clearTitle( $task->alts[ $k ] ) ] );		
											$task->content = str_replace($src, $image, $task->content);
										}
									}
								}
								wp_update_post( [ 'ID' => $post_id, 'post_title' => $task->post_title, 'post_content' => $task->content ] );
							}
							
						}
					}
					
				
				}
			}
			update_option('aiRewritesData', $data);
		}
		return $data;
	}

	public function initRewrite(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;	
			
		$args = get_option('aiRewritesData');
		
		$args['start'] = true;
		
		if( $_POST['cats'] ){
			if( $posts = get_posts( [ 'category__in' => $_POST['cats'], 'post_status' => 'any', 'posts_per_page' => -1 ] ) ){
				foreach( $posts as $post )
					$args['posts'][] = [ 'id' => $post->ID, 'title' => $post->post_title ];
			}
		}

		if( $_POST['types'] ){
			if( $posts = get_posts( [ 'post_type' => $_POST['types'], 'post_status' => 'any', 'posts_per_page' => -1 ] ) ){
				foreach( $posts as $post ){
					if( trim( $post->post_title ) && trim( $post->post_content ) )
						$args['posts'][] = [ 'id' => $post->ID, 'title' => $post->post_title ];
				}
			}
		}
		
		if( $_POST['links'] ){
			$posts_ids = [];
			
			foreach( $_POST['links'] as $cat_id => $links ){
				foreach( $links as $link ){
					if( stripos( $link, $_SERVER['HTTP_HOST'] ) !== false ){
						if( $id = url_to_postid( $link ) )
							$posts_ids[] = $id;
					} else {
						
						$args['posts'][] = [ 'url' => $link, 'cat_id' => $cat_id, 'revision_id' => wp_insert_post( [ 'post_type' => 'wpai', 'post_title' => $link ] ) ];
						
					}
				}
			}
			
			if( $posts_ids ){
				if( $posts = get_posts( [ 'post__in' => $posts_ids, 'post_status' => 'any', 'posts_per_page' => -1 ] ) ){
					foreach( $posts as $post )
						$args['posts'][] = [ 'id' => $post->ID, 'title' => $post->post_title ];
				}
			}
		}
			
		update_option('aiRewritesData', $args);
		
		wp_die( json_encode( $args ) );
	}
	
	public function postRestore(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
		
		if( ! ( $revision_id = (int) $_POST['revision_id'] ) || ! ( $post_id = (int) $_POST['post_id'] ) )
			return;
	
		wp_restore_post_revision( $revision_id, $post_id );
		
		$meta_title = get_post_meta( $revision_id, '_aiassist_meta_title', true );
		$meta_description = get_post_meta( $revision_id, '_aiassist_meta_description', true );
		
		update_post_meta( $post_id, '_title', sanitize_text_field( wp_unslash( $meta_title ) ) );
		update_post_meta( $post_id, '_description', sanitize_text_field( wp_unslash( $meta_description ) ) );
		
		update_post_meta( $post_id, '_yoast_wpseo_title', sanitize_text_field( wp_unslash( $meta_title ) ) );
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', sanitize_text_field( wp_unslash( $meta_description ) ) );
		
		update_post_meta( $post_id, 'rank_math_title', sanitize_text_field( wp_unslash( $meta_title ) ) );
		update_post_meta( $post_id, 'rank_math_description', sanitize_text_field( wp_unslash( $meta_description ) ) );
		
		if( $args = get_option('aiRewritesData') ){
			foreach( $args['posts'] as $k => $post ){
				if( $post['post_id'] == $post_id )
					$args['posts'][ $k ]['restore'] = true;
			}
			update_option('aiRewritesData', $args);
		}
	}
	
	public function startRewrite(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$data = get_option('aiRewritesData');
		$data['start'] = true;
		update_option('aiRewritesData', $data);
	}
	
	public function stopRewrite(){	
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$data = get_option('aiRewritesData');
		$data['start'] = false;
		update_option('aiRewritesData', $data);
	}
	
	public function clearRewrite(){
		if( ! $this->checkNonce() || ! current_user_can('manage_options') )
			return;
			
		$args = get_option('aiRewritesData');
		$data = [ 'start' => true, 'posts' => [] ];
			
		if( isset( $args['thumb'] ) )
			$data['thumb'] = (bool) $args['thumb'];
			
		if( isset( $args['images'] ) )
			$data['images'] = (bool) $args['images'];
			
		if( isset( $args['imageModel'] ) )
			$data['imageModel'] = sanitize_text_field( $args['imageModel'] );
			
		if( isset( $args['textModel'] ) )
			$data['textModel'] = sanitize_text_field( $args['textModel'] );
		
		update_option('aiRewritesData', $data);
	}
		
	private function getPostMeta( $post_id ){
		if( defined('WPSEO_VERSION') )
			return (object) [ 'title' => get_post_meta( $post_id, '_yoast_wpseo_title', true ), 'description' => get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ) ];
	
		if( class_exists('All_in_One_SEO_Pack') )
			return (object) [ 'title' => get_post_meta( $post_id, '_title', true ), 'description' => get_post_meta( $post_id, '_description', true ) ];

		if( function_exists('rank_math') )
			return (object) [ 'title' => get_post_meta( $post_id, 'rank_math_title', true ), 'description' => get_post_meta( $post_id, 'rank_math_description', true ) ];
		
		return (object) [ 'title' => null, 'description' => null ];
	}
	
	public function saveTranslateImagesPromts(){
		if( ! $this->checkNonce() || ! current_user_can('edit_posts') )
			return;
		
		if( isset( $_POST['promts'] ) ){
			if( ! ( $data = $this->steps ) )
				$data = [];
			
			foreach( $_POST['promts'] as $item )				
				$data[ sanitize_text_field( $item['act'] ) ] = wp_kses_post( wp_unslash( $item['val'] ) );
			
			if( update_option('_aiassist_generator', $data) )
				wp_die('{"success":"true"}');
		}
		wp_die('{"success":"false"}');
	}
	
	public function saveStep(){
		if( ! $this->checkNonce() || ! current_user_can('edit_posts') )
			return;
			
		if( isset( $_POST['val'] ) && isset( $_POST['act'] ) ){
			
			if( is_array( $_POST['val'] ) )
				$val = array_map(function( $v ){
					return wp_unslash( $v );
				}, $_POST['val']);
			else
				$val = wp_kses_post( wp_unslash( $_POST['val'] ) );
			
			$act = sanitize_text_field( $_POST['act'] );
			
			if( ! ( $data = $this->steps ) )
				$data = [];
				
			$data[ $act ] = $val;
			
			update_option('_aiassist_generator', $data);
			wp_die('{"success":"true"}');
		}
		wp_die('{"success":"false"}');
	}

	public function resetStep(){
		update_option('_aiassist_generator', [ 'promts' => ( @$this->steps['promts'] ? $this->steps['promts'] : $this->info->promts ) ] );
	}

	public function loadImage(){
		if( ! $this->checkNonce()  || ! current_user_can('edit_posts') )
			return;
			
		if( $post_id = (int) $_POST['post_id'] ){
			
			$url = sanitize_url( $_POST['image']['src'] );
			$title = sanitize_text_field( $_POST['image']['title'] );
		
			if( $url && $title ){
				if( $img_id = (int) $this->loadFile( $url, $post_id ) ){
					$image = wp_get_attachment_image( $img_id, 'full', false, [ 'class' => 'size-full wp-image-'. $img_id .' aligncenter', 'alt' => $this->clearTitle( $title ) .' фото', 'title' => $this->clearTitle( $title ) ] );		
					wp_die( json_encode( [ 'id' => $img_id, 'url' => wp_get_attachment_url( $img_id ), 'image' => $image ] ) );
				}
			}
			
		}
		wp_die('{"success":"false"}');
	}

	public function clearContent(){
		if( ! $this->checkNonce() || ! current_user_can('edit_posts') )
			return;
			
		$this->resetStep();
	}
	
	public function saveContent(){
	
		if( ! $this->checkNonce() || ! current_user_can('edit_posts') )
			return;
	
		if( $post_id = (int) $_POST['post_id'] ){
			
			if( ! trim( $_POST['header'] ) ){
				if( preg_match('/<h1[^>]*>([^<]+)<\/h1>/isu', $_POST['content'], $header) ){
					$_POST['header'] = $header[1];
					$_POST['content'] = str_replace($header[0], '', $_POST['content']);
				} else {			
					if( preg_match('/\# ([а-яА-Яa-zA-Z0-9 :_-]+)/isu', $_POST['content'], $header) ){
						$_POST['header'] = $header[1];
						$_POST['content'] = str_replace($header[0], '', $_POST['content']);
					}
				}
			}
			
			if( $_POST['thumbnail'] ){
				if( $thumb_id = (int) $this->loadFile( $_POST['thumbnail'], $post_id ) )
					set_post_thumbnail( $post_id, $thumb_id );
			}

			$args = [
						'ID' => (int) $post_id, 
						'post_status' => 'draft', 
						'post_title' => sanitize_text_field( wp_unslash( $_POST['header'] ) ), 
						'post_content' => wp_kses_post( wp_unslash( $_POST['content'] ) ) 
					];
		
			if( $post_id = wp_update_post( $args ) ){
				update_post_meta($post_id, '_title', sanitize_text_field( wp_unslash( $_POST['title'] ) ) );
				update_post_meta($post_id, '_description', sanitize_text_field( wp_unslash( $_POST['desc'] ) ) );
				
				update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field( wp_unslash( $_POST['title'] ) ) );
				update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field( wp_unslash( $_POST['desc'] ) ) );
				
				update_post_meta($post_id, 'rank_math_title', sanitize_text_field( wp_unslash( $_POST['title'] ) ) );
				update_post_meta($post_id, 'rank_math_description', sanitize_text_field( wp_unslash( $_POST['desc'] ) ) );
				
				$this->resetStep();
				wp_die('{"id":"'. $post_id .'"}');
			}
		}
		wp_die('{"success":"false"}');
	}

	private function clearTitle( $title ){
		return preg_replace('/^[0-9]*.? ?/i', '', $title);
	}
	
	private function loadFile( $url, $post_id ){
		$tmp = download_url( $url, 300 );
		
		if( preg_match('/image=([^\.]+).(png|jpg)/i', $url, $matches ) ){
			$file = [ 'name' => $matches[1] .'.'. $matches[2], 'tmp_name' => $tmp ];
			$id = media_handle_sideload( $file, $post_id );
			
			if( is_wp_error( $id ) ){
				@unlink($file['tmp_name']);
				return false;
			}
			@unlink( $file['tmp_name'] );
		
			return $id;
		}
		return false;
	}

	public function front(){
		wp_enqueue_script('aiassist-cron', plugin_dir_url( __FILE__ ) .'assets/js/cron.js?t='. time(), false, false, false );
		wp_localize_script('aiassist-cron', 'aiassist', [ 'ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('aiassist') ] );
	}
	
	public function scripts(){
		wp_enqueue_style('aiassist', plugin_dir_url( __FILE__ ) .'assets/css/style.css?t='. time(), false, '1.0', 'all');
		
		wp_enqueue_script('google-charts', plugin_dir_url( __FILE__ ) .'assets/libs/charts.js', [ 'jquery' ], false, false );
		wp_enqueue_script('aiassist', plugin_dir_url( __FILE__ ) .'assets/js/app.js?t='. time(), [ 'jquery', 'wp-data' ], false, false );
		wp_enqueue_script('aiassist-image-block', plugin_dir_url( __FILE__ ) .'assets/js/image-block.js?t='. time(), [ 'wp-blocks', 'wp-element', 'jquery', 'aiassist' ], false, false );
		
		wp_localize_script('aiassist', 'aiassist', [ 
			'nonce'		=> wp_create_nonce('aiassist'), 
			'ajaxurl'	=> admin_url('admin-ajax.php'), 
			'apiurl'	=> $this->api,
			'token'		=> $this->options->token,
			'gptkey'	=> get_option('aiassist_gpt_key'),
			'info'		=> $this->info,
			'promts'	=> @$this->steps['promts'],
		] );
	}
	
	private function checkNonce(){
		return wp_verify_nonce( $_POST['nonce'], 'aiassist' );
	}
	
	public function button_init( $buttons ){
		$buttons[] = 'AIASSIST';
		$buttons[] = 'ai_image';
		return $buttons;
	}
	
	public function add_button( $data ){
		if( current_user_can('edit_posts') && current_user_can('edit_pages') ){
			$data['button'] = plugin_dir_url( __FILE__ ) .'assets/js/button.js?t='. time();
			$data['button2'] = plugin_dir_url( __FILE__ ) .'assets/js/image-tiny-mce.js?t='. time();
		}
		
		return $data;
	}
	
	public function metabox(){
		add_meta_box('ai_assistant', 'AI WP Writer', function(){
			include __DIR__ .'/tpl/workspace.php';
		}, array_values( get_post_types() ), 'normal', 'high' );
	}
	
	private function wpCurl( $url, $args = [] ){
		if( ! empty( $args ) )
			$args = [ 'body' => $args, 'timeout' => 300, 'method' => 'POST' ];
		
		$data = (array) wp_remote_request( $url, $args );
		
		return @$data['body'];
	}
	
}

?>