<?php
/*
* @Author 		pickplugins
* Copyright: 	pickplugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 
	
	
	
	
	
	
	
	
	function classified_maker_filter_widgets_company( $widget ) {	
	
		$ads_id = get_the_id();
		$company_id = get_post_meta($ads_id, 'classified_maker_ads_company_id', true);
		
		if(!empty($company_id)){
			
			$company_data = get_post($company_id);
			
			$company_title = $company_data->post_title;
			$company_logo = get_post_meta($company_id, 'classified_maker_com_logo', true);	
			$company_city = get_post_meta($company_id, 'classified_maker_com_city', true);
			$company_country = get_post_meta($company_id, 'classified_maker_com_country', true);				
			$company_link = get_permalink($company_id);		
			
			
			//var_dump($company_title);
			//var_dump($company_logo);		
		
			$html_company = '<div class="com-logo"><img src="'.$company_logo.'" /></div>
			<div class="com-name"><a href="'.$company_link.'">'.$company_title.'</a></div>
			<div class="com-city">'.$company_city.'</div>	
			<div class="com-country">'.$company_country.'</div>			
			';
		
			$widget[2] = array(
							
							'title'			=> __('Company', classified_maker_com_textdomain),
							'description'	=>'',	
							'html'			=>$html_company,														
							'css_class'		=>'company',
							);
			
			}
		

	
	
		return $widget;
	
	}
	
	add_filter( 'classified_maker_filter_widgets', 'classified_maker_filter_widgets_company', 0 );
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function filter_classified_maker_filter_post_ads_inputs( $input_fields ) {
		
		$new_input_fields =  array (
		
			'classified_maker_ads_company_id' => array(
			
				'meta_key'=>'classified_maker_ads_company_id',
				'css_class'=>'ads_company_id',
				'placeholder'=>'',
				'required'=>'yes',
				'title'=>__('Company Name', classified_maker_com_textdomain),
				'option_details'=>__('Company name of this ads', classified_maker_com_textdomain),					
				'input_type'=>'select2',
				'input_values'=> array(''),
				'input_args'=> array(), 
			)
		);
			
		$input_fields['meta_fields'] = 
		array_slice( $input_fields['meta_fields'], 0, 5, true) + 
		$new_input_fields + 
		array_slice($input_fields['meta_fields'], 5, count($input_fields['meta_fields']) - 1, true) ;

		// echo '<pre>'; print_r( $input_fields );  echo '</pre>';
	
		return $input_fields;
	}
	
	add_filter( 'classified_maker_filter_post_ads_inputs', 'filter_classified_maker_filter_post_ads_inputs', 30, 1 );
	
	
	
	
	
	
	
	
	
	
	
	
	function classified_maker_com_ajax_company_folowing(){
	

		$company_id = (int)$_POST['company_id'];
		$redirect = $_POST['redirect'];
		

		$html = array();
		
		
		if ( is_user_logged_in() ) 
			{
				$follower_id = get_current_user_id();
		
				global $wpdb;
				$table = $wpdb->prefix . "classified_maker_com_follow";
				$result = $wpdb->get_results("SELECT * FROM $table WHERE company_id = '$company_id' AND follower_id = '$follower_id'", ARRAY_A);
				$already_insert = $wpdb->num_rows;
			
				if($already_insert > 0 )
					{
							
						$wpdb->delete( $table, array( 'company_id' => $company_id, 'follower_id' => $follower_id), array( '%d','%d' ) );
						//$wpdb->query("UPDATE $table SET followed = '$followed' WHERE author_id = '$authorid' AND follower_id = '$follower_id'");

						$html['follower_id'] = $follower_id;
						$html['follow_status'] = 'unfollow';
						$html['follow_class'] = 'follow_no';
						$html['follow_text'] = 'Follow';						
					}
				else
					{
						$wpdb->query( $wpdb->prepare("INSERT INTO $table 
													( id, company_id, follower_id, follow)
											VALUES	( %d, %d, %d, %s )",
											array	( '', $company_id, $follower_id, 'yes')
													));
						
						$html['follower_id'] = $follower_id;
						$html['follow_status'] = 'following';
						$html['follow_class'] = 'follow_yes';
						$html['follow_text'] = 'Unfollow';	
						$html['follower_html'] = '<div class="follower follower-'.$follower_id.'">'.get_avatar( $follower_id, 32 ).'</div>';
	
						
					}

			}
		else
			{
				$html['login_error'] = __('Please <a href="'.wp_login_url($redirect).'">login</a> first.',classified_maker_com_textdomain);
			}
		
		
		echo json_encode($html);
		

		die();		

	}

add_action('wp_ajax_classified_maker_com_ajax_company_folowing', 'classified_maker_com_ajax_company_folowing');
add_action('wp_ajax_nopriv_classified_maker_com_ajax_company_folowing', 'classified_maker_com_ajax_company_folowing');


	
	add_filter('classified_maker_com_filter_company_single_header','classified_maker_com_filter_company_single_header');
	
	function classified_maker_com_filter_company_single_header($html){
	
		$company_id = get_the_ID();
		$follower_id = get_current_user_id();
		
		global $wpdb;
		$table = $wpdb->prefix . "classified_maker_com_follow";

		
		
		$html.= '<div class="follow">';
		
		$is_follow_query = $wpdb->get_results("SELECT * FROM $table WHERE company_id = '$company_id' AND follower_id = '$follower_id'", ARRAY_A);
		$is_follow = $wpdb->num_rows;
		if($is_follow > 0 ){
				
				$follow_text = __('Unfollow',classified_maker_com_textdomain);
			}
		else{
				$follow_text = __('Follow',classified_maker_com_textdomain);
			}
							
							
		$html.= '<span company_id="'.get_the_ID().'" redirect="'.$_SERVER['REQUEST_URI'].'" class="follow-button">'.$follow_text.'</span>';	
		
		
		
		$follower_query = $wpdb->get_results("SELECT * FROM $table WHERE company_id = '$company_id' ORDER BY id DESC LIMIT 10");

		$html.= '<div class="follower-list">';	
		
		foreach( $follower_query as $follower )
			{
				$follower_id = $follower->follower_id;
				$user = get_user_by( 'id', $follower_id );
				
				//var_dump($user);
				
				$html .= '<div title="'.$user->display_name.'" class="follower follower-'.$follower_id.'">';
				$html .= get_avatar( $follower_id, 50 );
				$html .= '</div>';
			}
		
		$html.= '</div>';
		
		$html.= '<div class="status"></div>';		
		$html.= '</div>';	
		$html.= '<div class="clear"></div>';
		
		return $html;
	
	}
	

	function classified_maker_com_ajax_submit_reviews(){
		
		$post_id = (int)$_POST['post_id'];
		$rate_value = (int)$_POST['rate_value'];	
		$rate_comment = sanitize_text_field($_POST['rate_comment']);	
		
		
		$current_user = wp_get_current_user();

		$comment_author_email = $current_user->user_email;
		$comment_author = $current_user->user_nicename;

		$data = array(
			'comment_post_ID' => $post_id,
			'comment_author_email' => $comment_author_email,	
			'comment_author_url' => '',	
			'comment_author' => $comment_author,						
			'comment_content' => $rate_comment,
			'comment_type' => '',
			'comment_parent' => 0,
			'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
			'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
			'comment_date' => current_time('mysql'),
			'comment_approved' => 1
		);
		
		
		$comments = get_comments( array( 'post_id' => $post_id, 'status' => 'all', 'author_email'=>$comment_author_email ) );
		
		
		if(!empty($comments)){
			
			echo '<i class="fa fa-times"></i> '.__('You already submitted a reviews',classified_maker_com_textdomain);

			
			}
		else{
			
			$comment_id = wp_insert_comment( $data );
			add_comment_meta( $comment_id, 'classified_maker_com_review_rate', $rate_value );
			
			echo '<i class="fa fa-check" aria-hidden="true"></i> '.__('Review submitted.',classified_maker_com_textdomain);
			}
		
		
		
		
		die();
		}

	add_action('wp_ajax_classified_maker_com_ajax_submit_reviews', 'classified_maker_com_ajax_submit_reviews');
	add_action('wp_ajax_nopriv_classified_maker_com_ajax_submit_reviews', 'classified_maker_com_ajax_submit_reviews');