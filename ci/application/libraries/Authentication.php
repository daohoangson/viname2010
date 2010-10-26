<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

define('AUTH_COOKIE_NAME','auth');
define('AUTH_COOKIE_LIFE',24*60*60*30);

class Authentication {
	private $errors = array();
	
	function __construct() {
		$this->ci =& get_instance();
		$this->ci->load->library('session');
		$this->ci->load->database();
		$this->ci->load->model('auth/User');
		$this->ci->load->helper('cookie');

		// try to auto login
		$this->autologin();
	}
	
	function getUser($field) {
		if ($this->isLoggedIn()) {
			$value = $this->ci->session->userdata($field);
			return $value;
		} else {
			return false;
		}
	}
	
	function createUser($login,$password,$email,$agree) {
		$this->errors = array();
		
		// input validation
		if (empty($login)) {
			$this->errors[] = $this->ci->lang->line('auth_error_login_missing');
		}
		if (empty($password)) {
			$this->errors[] = $this->ci->lang->line('auth_error_password_missing');
		}
		if (empty($email)) {
			$this->errors[] = $this->ci->lang->line('auth_error_email_missing');
		}
		if (empty($agree)) {
			$this->errors[] = $this->ci->lang->line('auth_error_must_agree');
		}
		if (!empty($this->errors)) return false;
		
		// check database
		$samelogin = $this->ci->User->get(array('username' => $login));
		if (!empty($samelogin)) {
			$this->errors[] = $this->ci->lang->line('auth_error_login_taken') 
				. ' <a href="' . site_url('/auth/forgot/') . '">' . $this->ci->lang->line('auth_forgot_password_question') . '</a>';
			return false;
		}
		$sameemail = $this->ci->User->get(array('email' => $email));
		if (!empty($sameemail)) {
			$this->errors[] = $this->ci->lang->line('auth_error_email_registered')
				. ' <a href="' . site_url('/auth/forgot/') . '">' . $this->ci->lang->line('auth_forgot_password_question') . '</a>';
			return false;
		}
		
		// update database
		$user = array();
		$user['username'] = $login;
		$user['salt'] = $this->ci->User->generateSalt();
		$user['password'] = $this->ci->User->hashPassword($password,$user['salt']);
		$user['email'] = $email;
		$user['joined'] = time();
		
		if ($this->ci->User->save($user)) {
			return true;
		} else {
			$this->errors[] = $this->ci->lang->line('auth_error_unknown');
			return false;
		}
	}
	
	function login($login,$password,$remember) {
		$this->errors = array();
		
		// input validation
		if (empty($login)) {
			$this->errors[] = $this->ci->lang->line('auth_error_login_missing');
		}
		if (empty($password)) {
			$this->errors[] = $this->ci->lang->line('auth_error_password_missing');
		}
		if (!empty($this->errors)) return false;
		
		// check database
		$user = $this->ci->User->only(array('username' => $login));
		if (!empty($user) AND $user->password == $this->ci->User->hashPassword($password,$user->salt)) {
			$this->ci->session->set_userdata(get_object_vars($user));
			if ($remember) {
				// set cookie
				set_cookie(array(
					'name' => AUTH_COOKIE_NAME,
					'value' => serialize(array(
						'user_id' => $user->user_id,
						'password' => md5($user->password . $this->cookieHash()),
					)),
					'expire' => AUTH_COOKIE_LIFE
				));
			}
			$this->ci->User->loggedIn($user);
			return true;
		} else {
			$this->errors[] = $this->ci->lang->line('auth_error_login_failed');
		}
		return false;
	}
	
	function logout() {
		$this->ci->session->set_userdata(array('user_id' => 0));
		$this->ci->session->sess_destroy();
		delete_cookie(AUTH_COOKIE_NAME);
	}
	
	function isLoggedIn() {
		return $this->ci->session->userdata('user_id') > 0;
	}
	
	private function autologin() {
		if (!$this->isLoggedIn()) {
			if ($cookie = get_cookie(AUTH_COOKIE_NAME, TRUE)) {
				$data = unserialize($cookie);
				if (isset($data['user_id'])) {
					$user = $this->ci->User->only(array('user_id' => $data['user_id']));
					if (!empty($user)) {
						if ($data['password'] != md5($user->password . $this->cookieHash())) {
							delete_cookie(AUTH_COOKIE_NAME);
							return false;
						}
						// save user info
						$this->ci->session->set_userdata(get_object_vars($user));
						// renew cookie
						set_cookie(array(
							'name' => AUTH_COOKIE_NAME,
							'value' => $cookie,
							'expire' => AUTH_COOKIE_LIFE
						));
						$this->ci->User->loggedIn($user);
						return true;
					}
				}
			}
		}
		return false;
	}
	
	function getErrors() {
		$errors = $this->errors;
		$this->errors = array();
		return $errors;
	}
	
	function flash($message = null) {
		if ($message === null) {
			// getting
			return $this->ci->session->flashdata('auth_message');
		} else {
			// setting
			$this->ci->session->set_flashdata('auth_message',$message);
		}
	}
	
	private function cookieHash() {
		return 'something about browser should be placed here (useragent?)';
	}
}

?>