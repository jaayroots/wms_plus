<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('generate_token')):

	function generate_token() {
		$sToken = md5(uniqid(mt_rand(), true));	
		
		return $sToken;
	}

endif;

if (!function_exists('register_token')):

	function register_token($flow_id, $present_state, $process_id) {
		
		$token = generate_token();

		$data = array(
			"token" => $token
			, "flow_id" => $flow_id
			, "present_state" => $present_state
			, "process_id" => $process_id
			, "expire" => time() + (60 * 60 * 6)
		);
				
		$CI =& get_instance();
		
		$CI->load->model('token');
		
		$CI->token->register($data);
		
		return $token;
				
	}

endif;

if (!function_exists('validate_token')):

	function validate_token($token, $flow_id, $present_state, $process_id) {
	
		$CI =& get_instance();
	
		$CI->load->model('token');
	
		$result = $CI->token->validate($token);
	
		if ($result['0']->flow_id == $flow_id && $result['0']->present_state == $present_state && $result['0']->process_id == $process_id) :
			return TRUE;
		else :
			return FALSE;	
		endif;
	
	
	}

endif;

if (!function_exists('validate_state')):

function validate_state($module) {

	$CI =& get_instance();

	$CI->load->model('workflow_model', 'flow');
	
	$path = $CI->uri->segment(1);
			
	if ($path != $module) {
		log_message('DEBUG', 'State not correct -> redirect');
		$result = $CI->flow->get_return_path($path);
		return $result['0']->NavigationUri;
	} else {
		return FALSE;
	}

}

endif;

/* End of file token_helper.php */
/* Location: ./application/helper/token_helper.php */
