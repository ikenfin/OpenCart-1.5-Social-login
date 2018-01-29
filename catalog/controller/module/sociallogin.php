<?php
/*
	Вход и регистрация через соцсети
*/
class ControllerModuleSociallogin extends Controller {
	private $error = array();

	/*
		Выаполняет запрос по url
	*/
	protected function makeRequest ($url, $post = false, $postfields = array()) {
		$response = null;
		
		if(extension_loaded('curl') ){
			$c = curl_init($url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_POST, $post);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postfields);
			$response = curl_exec($c);
			curl_close($c);
		}
		else {
			die('No curl installed!');
		}

		return $response;
	}

	/*
		Строит url
	*/
	protected function createUrl ($base_url, $params = array()) {
		return $base_url . '?' . http_build_query($params);
	}

	/*
		Устанавливает в куках страницу с которой юзер пришёл
	*/
	protected function setReferer () {
		if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
			setcookie("soclogin_ref", $_SERVER['HTTP_REFERER']);
		}
		else {
			setcookie("soclogin_ref", $this->url->link('account/account', '', 'SSL'));
		}
	}

	protected function getReferer () {
		if (isset($_COOKIE['soclogin_ref'])) {
			return $_COOKIE['soclogin_ref'];
		}
		else {
			return $this->url->link('account/account', '', 'SSL');
		}
	}

	/*
		Сохраняет пользователя и логинится
	*/
	private function saveAndLogin ($userdata) {
		$userdata['newsletter'] = 1;
		$userdata['telephone'] = $userdata['fax'] = $userdata['company_id'] = $userdata['address_1'] = $userdata['city'] = $userdata['country_id'] = '';
		$userdata['company'] = $userdata['tax_id'] = $userdata['address_2'] = $userdata['postcode'] = $userdata['zone_id'] = $userdata['country_id'] = '';
		$userdata['password'] = $this->generatePassword();
		$this->model_account_customer->addCustomer($userdata);
		$this->customer->login($userdata['email'], $userdata['password']);

		return $userdata;
	}

	/*
		Генерирует случайный пароль
	*/
	private function generatePassword ($length = 8) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);
			for ($i = 0, $result = ''; $i < $length; $i++) {
				$index = rand(0, $count - 1);
				$result .= mb_substr($chars, $index, 1);
			}
		return $result;
	}

	/*
		Отправляет регистрационную инфу пользователю
	*/
	private function mailPassword ($userdata){
		$userdata = array_replace(array(
			'firstname' => '',
			'lastname' => '',
			'password' => '',
			'email' => ''
		), $userdata);

		// если email отсутствует, то и пытаться отправлять письмо не стоит
		if(trim($userdata['email']) == '')
			return;

		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
		$message = sprintf($this->language->get('text_help'), $userdata['lastname'], $userdata['firstname'], $this->config->get('config_name'), $this->config->get('config_url'), $this->config->get('config_name'), $userdata['password']);

		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');
		$mail->setTo($userdata['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setText(strip_tags($message));//html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($message);
		$mail->send();
	}

	/*
		*******************
		Методы авторизации:
		*******************
	*/


	/*
		Авторизация Вконтакте
	*/
	public function vk () {
		$this->language->load('module/sociallogin');
		// Check if module is on
		if (!$this->config->get('sociallogin_vkontakte_status', false)) {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$redirect_uri = $this->url->link('module/sociallogin/vk');

		// первый заход
		if (!isset($this->request->get['code']) || empty($this->request->get['code'])) {
			$this->setReferer();

			$client_id = $this->config->get('sociallogin_vkontakte_appid');
			$api_url = $this->createUrl('https://oauth.vk.com/authorize', array(
				'client_id' => $client_id,
				'scope' => 'SETTINGS,email',
				'redirect_uri' => $redirect_uri,
				'display' => 'page',
				'response_type' => 'code'
			));

			$this->redirect($api_url);
		}
		else {
			// у нас есть CODE
			// if it is request from vk server already
			$code = $this->request->get['code'];

			$client_id = $this->config->get('sociallogin_vkontakte_appid');
			$client_secret = $this->config->get('sociallogin_vkontakte_appsecret');

			$url = $this->createUrl('https://oauth.vk.com/access_token', array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'code' => $code,
				'redirect_uri' => $redirect_uri
			));

			$response = $this->makeRequest($url);
			$data = json_decode($response, true);

			if (!empty($data['access_token'])) {
				$graph_url = $this->createUrl('https://api.vk.com/method/users.get', array(
					'uids' => $data['user_id'],
					'fields' => 'uid,first_name,last_name',
					'access_token' => $data['access_token']
				));

				$json = $this->makeRequest($graph_url);

				$json_data = json_decode($json, true);
				$userdata = array();

				if (count($json_data) > 0) {
					// $json_data['response'][0]['email'] = $data['email'];
					foreach ($json_data['response'][0] as $key => $value) {
						switch ($key) {
							case "first_name":
								$userdata["firstname"] = $value;
							case "last_name":
								$userdata['lastname'] = $value;
							default:
								$userdata[$key] = $value;
						}
					}
				}

				$userdata['email'] = $data['email'];

				$this->load->model('account/customer');

				if ($this->model_account_customer->getTotalCustomersByEmail($userdata['email'])) {
					// login without password
					$this->customer->login($userdata['email'], "", true);
					$this->redirect($this->getReferer());
				}
				else {
					$userdata = $this->saveAndLogin($userdata);
					$this->mailPassword($userdata);
					$this->redirect($this->getReferer());
				}
			}
		}
	}

	/*
		Авторизация одноклассники
	*/
	public function ok () {
		$this->language->load('module/sociallogin');

		if (!$this->config->get('sociallogin_ok_status')) {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$client_id = $this->config->get('sociallogin_ok_appid');

		$redirect_uri = $this->url->link('module/sociallogin/ok');

		if (!isset($this->request->get['code']) || empty($this->request->get['code'])) {
			$this->setReferer();

			$url = $this->createUrl('https://connect.ok.ru/oauth/authorize', array(
				'client_id' => $client_id,
				'response_type' => 'code',
				'scope' => 'VALUABLE_ACCESS;GET_EMAIL;LONG_ACCESS_TOKEN',
				'redirect_uri' => $redirect_uri
			));

			$this->redirect($url);
		}
		else {
			$code = $_GET['code'];
			$client_secret = $this->config->get('sociallogin_ok_appsecret');

			$url = $this->createUrl('https://api.ok.ru/oauth/token.do', array(
				'code' => $code,
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri' => $redirect_uri,
				'grant_type' => 'authorization_code'
			));

			$response = $this->makeRequest($url, true);
			$data = json_decode($response, true);

			if (!empty($data['access_token'])) {
				$app_public_key = $this->config->get('sociallogin_ok_apppublic');

				$__secretKey = strtolower(md5($data['access_token'] . $client_secret));
				$CURRENT_URI = $_COOKIE['soclogin_ref'];

				$__signature = strtolower(md5('application_key='.$app_public_key . 'format=jsonmethod=users.getCurrentUser' . $__secretKey));

				$url = $this->createUrl('https://api.ok.ru/fb.do', array(
					'method' => 'users.getCurrentUser',
					'application_key' => $app_public_key,
					'format' => 'json',
					'sig' => $__signature,
					'access_token' => $data['access_token']
				));

				$response = $this->makeRequest($url);
				$data = json_decode($response, true);

				$userdata = array();

				foreach ($data as $key => $val) {
					switch ($key) {
						case 'first_name' : 
							$userdata['firstname'] = $val;
							break;
						case 'last_name' :
							$userdata['lastname'] = $val;
							break;
						default:
							$userdata[$key] = $data[$key];
							break;
					}
				}

				$this->load->model('account/customer');

				if($this->model_account_customer->getTotalCustomersByEmail($userdata['email'])){
					// login without password
					$this->customer->login($userdata['email'], "", true);
					$this->redirect($CURRENT_URI);
				}else{
					$userdata = $this->saveAndLogin($userdata);
					$this->mailPassword($userdata);
					$this->redirect($CURRENT_URI);
				}
			}
		}
	}

	/*
		Авторизация facebook
	*/
	public function fb () {
		$this->language->load('module/sociallogin');
		// Check if module is on
		if (!$this->config->get('sociallogin_facebook_status')) {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$redirect_uri = $this->url->link('module/sociallogin/fb');
		$client_id = $this->config->get('sociallogin_facebook_appid');

		if (!isset($this->request->get['code']) || empty($this->request->get['code'])) {
			$this->setReferer();

			$url = $this->createUrl('https://www.facebook.com/dialog/oauth', array(
				'client_id' => $client_id,
				'redirect_uri' => $redirect_uri,
				'scope' => 'email'
			));

			$this->redirect($url);
		}
		else {
			$code = $this->request->get['code'];
			$CURRENT_URI = $_COOKIE['soclogin_ref'];

			$client_secret = $this->config->get('sociallogin_facebook_appsecret');

			$url = $this->createUrl('https://graph.facebook.com/oauth/access_token', array(
				'client_id' => $client_id,
				'redirect_uri' => $redirect_uri,
				'client_secret' => $client_secret,
				'code' => $code
			));

		    $response = $this->makeRequest($url);

			$data = json_decode($response, true);

			if (!empty($data['access_token'])) {
				$graph_url = $this->createUrl('https://graph.facebook.com/me', array(
					'access_token' => $data['access_token'],
					'scope' => 'user_about_me',
					'fields' => 'name,email'
				));

				$response = $this->makeRequest($graph_url);
				$json_data = json_decode($response, TRUE);

				$userdata = array();
				foreach ($json_data as $key => $value) {
					switch ($key) {
						case "name":
							$name_parts = explode(" ", $value);
							$userdata["firstname"] = $name_parts[0];
							if(count($name_parts) > 1) {
								$userdata['lastname'] = $name_parts[1];
							}
						default:
							$userdata[$key] = $value;
					}
				}

				$this->load->model('account/customer');

				if ($this->model_account_customer->getTotalCustomersByEmail($userdata['email'])) {
					// login without password
					$this->customer->login($userdata['email'], "", true);
					$this->redirect($this->getReferer());
				}
				else {
					$userdata = $this->saveAndLogin($userdata);
					$this->mailPassword($userdata);
					$this->redirect($this->getReferer());
				}
			}
		}

	}

	/*
		Twitter auth through twitter-async lib
		почему-то пока не удалось заставить твитер корректно работать
	*/
	public function twitter() {

		$cb_url = $this->url->link('module/sociallogin/twitter');

		$this->language->load('module/sociallogin');
		// Check if module is on
		if(!$this->config->get('sociallogin_twitter_status') ){
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->load->library('twitter-async/EpiCurl');
		$this->load->library('twitter-async/EpiOAuth');
		$this->load->library('twitter-async/EpiSequence');
		$this->load->library('twitter-async/EpiTwitter');
		
		$twitter = new EpiTwitter(
			$this->config->get('sociallogin_twitter_consumerkey'),
			$this->config->get('sociallogin_twitter_consumersecret')
		);

		$twitter->useAsynchronous(false);

		$twitter->setCallback($cb_url);

		$oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : null;

		if (is_null($oauth_token) || $oauth_token == '') {
			$this->setReferer();
			// $twitter->setCallback($this->url->link('auth/twitter'));
			try {
				$url = $twitter->getAuthorizationUrl(null, array('oauth_callback' => $cb_url));
				$this->redirect($url);
			}
			catch(EpiOAuthException $e) {
				var_dump($e->getMessage());
				die;
			}
			catch(EpiTwitterException $e) {
				var_dump($e->getMessage());
				die;
			}
		}
		else {
			$twitter->setToken($oauth_token);
			$token = $twitter->getAccessToken();

			try {
				$twitter->setToken($token->oauth_token, $token->oauth_token_secret);
			}
			catch(EpiOAuthException $e) {
				var_dump($e->getMessage());
				die;
			}

			$twitterInfo = null;

			try {
				$twitterInfo = $twitter->get_accountVerify_credentials(
					array(
						'include_email' => 'true',
						'include_entities' => 'false',
						'skip_status' => 'true'
					)
				);
			}
			catch(EpiOAuthException $e) {
				var_dump($e->getMessage());
				die;
			}
			catch(EpiTwitterException $e) {
				var_dump($e->getMessage());
				die;
			}

			$userdata = array();

			foreach($twitterInfo->response as $key => $usrdata){
				switch($key){
					case "name":
						$name_parts = explode(" ", $usrdata);
						$userdata["firstname"] = $name_parts[0];
						if(count($name_parts) > 1) {
							$userdata['lastname'] = $name_parts[1];
						}
					default:
						$userdata[$key] = $usrdata;
				}
			}

			if (!isset($userdata['lastname'])) {
				$userdata['lastname'] = '';
			}

			$this->load->model('account/customer');

			if($this->model_account_customer->getTotalCustomersByEmail($userdata['email'])){
				// login without password
				$this->customer->login($userdata['email'], "", true);
				$this->redirect($this->getReferer());
			}else{
				$userdata = $this->saveAndLogin($userdata);
				$this->mailPassword($userdata);
				$this->redirect($this->getReferer());
			}
		}
	}
}
?>