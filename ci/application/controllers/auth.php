<?php

require(dirname(__FILE__) . '/shared.php');

class Auth extends Shared {
	function index() {
		$this->_redirect('/auth/login/');
	}
	
	function login() {
		$this->_referrer('current');
		if ($this->authentication->isLoggedIn()) {
			$this->_redirect('referrer',true);
		} else {
			// we should use form_validation but it's too complicated 
			// to use in template so... we are doing it manually
			$login = trim($this->input->post('login',true));
			$password = trim($this->input->post('password',true));
			$remember = $this->input->post('remember')?true:false;
			$errors = null;

			if (empty($login)) {
				// hmm, do nothing
			} else {
				// process login information
				if ($this->authentication->login($login,$password,$remember)) {
					$this->_redirect('referrer',true);
				} else {
					$errors = $this->authentication->getErrors();
				}
			}
			
			$this->load->view('auth/login',array(
				'login' => $login,
				'remember' => $remember,
				'errors' => $errors
			));
		}
	}
	
	function logout() {
		$this->authentication->logout();
		$this->_redirect('referrer',true);
	}
	
	function register() {
		if ($this->authentication->isLoggedIn()) {
			$this->_redirect('referrer',true);
		} else {
			// manually validate as in login()
			$login = trim($this->input->post('login',true));
			$password = trim($this->input->post('password',true));
			$email = trim($this->input->post('email',true));
			$agree = $this->input->post('agree')?true:false;
			$errors = array();
			
			if (empty($login)) {
				// nothing here
			} else {
				if ($this->authentication->createUser($login,$password,$email,$agree)) {
					$this->authentication->flash($this->lang->line('auth_registration_completed'));
					$this->_redirect('/auth/login');
				} else {
					$errors = $this->authentication->getErrors();
				}
			}
			
			$this->load->view('auth/register',array(
				'login' => $login,
				'email' => $email,
				'agree' => $agree,
				'errors' => $errors,
			));
		}
	}
}

?>