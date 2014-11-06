<?php
namespace app\frontend\controller;
use \lib\Entities\User;
use \lib\HTTPRequest;
use \lib\HTTPResponse;

class UserController extends \lib\Controller {

	/**
	*	POST user/register
	*/
	public function login() {

		if($this->isUser()){
			$json = '{"succeed":true,"token":""}';
		}else{
			$json = '{"succeed":false,"toast":"Wrong User."}';
		}

		$this->_app->_page->assign('json', $json);
		HTTPResponse::send($this->_app->_page->draw('JsonView.php'));
	}

	/**
	*	Used by $this->login() and Applicationfrontend
	*/
	public function isUser() {
		// return true; // Only for test

		if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))
			return false;

		$user_param = array();
		$user_param['username'] = $_SERVER['PHP_AUTH_USER'];
		$user_param['password'] = sha1($_SERVER['PHP_AUTH_PW']);

		$user = new User($user_param);
		$userManager = $this->getManagerof('User');

		if($userManager->exist($user->getUsername())) {				
			$userbdd = $userManager->get($user->getUsername());

			if($user->getPassword() === $userbdd->getPassword())
				return true;
		}
		return false;
	}

	/**
	*	POST user/register
	*/
	public function register() {

		$json = HTTPRequest::get('json');

		if($json==null) {
			$json = '{"succeed":false,"toast":"Wrong User."}';
			$this->_app->_page->assign('json', $json);
			HTTPResponse::send($this->_app->_page->draw('JsonView.php'));
			return;
		}

		if(!@array_key_exists('user', $json)) {
			$json = '{"succeed":false,"toast":"Wrong User."}';
			$this->_app->_page->assign('json', $json);
			HTTPResponse::send($this->_app->_page->draw('JsonView.php'));
			return;
		}

		if(!$this->_app->_config->get('registration_open')) {
			$json = '{"succeed":false,"toast":"Registration close."}';
			$this->_app->_page->assign('json', $json);
			HTTPResponse::send($this->_app->_page->draw('JsonView.php'));
			return;
		}
		
		$user = new User($json['user']);
		$user->setPassword(sha1($json['user']['password']));
		$userManager = $this->getManagerof('User');
		// Check if User exist
		if(!$userManager->exist($user->getUsername())) {
			$userManager->add($user);
			$json = '{"succeed":true}';
		}
		else {
			$this->_app->_page->assign('error', true);
			$json = '{"succeed":false,"toast":"Username already exists."}';
		}
		$this->_app->_page->assign('json', $json);
		HTTPResponse::send($this->_app->_page->draw('JsonView.php'));
	}
}