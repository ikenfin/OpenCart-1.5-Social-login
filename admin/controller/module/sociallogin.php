<?php
class ControllerModuleSociallogin extends Controller {
	private $error = array();

	public function install(){
		$this->load->model('module/sociallogin');
		$this->model_module_sociallogin->install();
	}

	public function uninstall(){
		$this->load->model('module/sociallogin');
		$this->model_module_sociallogin->uninstall();
	}

	public function index() {
		$this->load->language('module/sociallogin');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('sociallogin', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if( !empty($this->request->post['stay']) ){
				$this->redirect($this->url->link('module/sociallogin', 'token=' . $this->session->data['token'], 'SSL'));
			}else{
				$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		if(isset($this->session->data['success'])){
			$this->data['success'] =  $this->session->data['success'];
			unset($this->session->data['success']);
		}
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_facebook'] = $this->language->get('tab_facebook');
		$this->data['tab_vkontakte'] = $this->language->get('tab_vkontakte');
		$this->data['tab_twitter'] = $this->language->get('tab_twitter');
		$this->data['tab_ok'] = $this->language->get('tab_ok');
		$this->data['tab_instagram'] = $this->language->get('tab_instagram');

		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_help'] = $this->language->get('text_help');

		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['entry_vkontakte_appid'] = $this->language->get('entry_vkontakte_appid');
		$this->data['entry_vkontakte_description'] = $this->language->get('entry_vkontakte_description');
		$this->data['entry_vkontakte_appsecret'] = $this->language->get('entry_vkontakte_appsecret');

		$this->data['entry_facebook_appid'] = $this->language->get('entry_facebook_appid');
		$this->data['entry_facebook_description'] = $this->language->get('entry_facebook_description');
		$this->data['entry_facebook_appsecret'] = $this->language->get('entry_facebook_appsecret');

		$this->data['entry_ok_appid'] = $this->language->get('entry_ok_appid');
		$this->data['entry_ok_description'] = $this->language->get('entry_ok_description');
		$this->data['entry_ok_appsecret'] = $this->language->get('entry_ok_appsecret');
		$this->data['entry_ok_apppublic'] = $this->language->get('entry_ok_apppublic');

		$this->data['entry_twitter_appid'] = $this->language->get('entry_twitter_appid');
		$this->data['entry_twitter_description'] = $this->language->get('entry_twitter_description');
		$this->data['entry_twitter_consumerkey'] = $this->language->get('entry_twitter_consumerkey');
		$this->data['entry_twitter_consumersecret'] = $this->language->get('entry_twitter_consumersecret');

		$this->data['entry_instagram'] = $this->language->get('entry_instagram');
		$this->data['entry_instagram_description'] = $this->language->get('entry_instagram_description');

		$this->data['button_save_go'] = $this->language->get('button_save_go');
		$this->data['button_save_stay'] = $this->language->get('button_save_stay');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		//============================================

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/sociallogin', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('module/sociallogin', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		// VKONTAKTE SETTINGS

		if (isset($this->request->post['sociallogin_vkontakte_status'])) {
			$this->data['sociallogin_vkontakte_status'] = $this->request->post['sociallogin_vkontakte_status'];
		} elseif ($this->config->get('sociallogin_vkontakte_status')) {
			$this->data['sociallogin_vkontakte_status'] = $this->config->get('sociallogin_vkontakte_status');
		} else {
			$this->data['sociallogin_vkontakte_status'] = 0;
		}

		if (isset($this->request->post['sociallogin_vkontakte_appid'])) {
			$this->data['sociallogin_vkontakte_appid'] = $this->request->post['sociallogin_vkontakte_appid'];
		} elseif ($this->config->get('sociallogin_vkontakte_appid')) {
			$this->data['sociallogin_vkontakte_appid'] = $this->config->get('sociallogin_vkontakte_appid');
		} else {
			$this->data['sociallogin_vkontakte_appid'] = '';
		}

		if (isset($this->request->post['sociallogin_vkontakte_appsecret'])) {
			$this->data['sociallogin_vkontakte_appsecret'] = $this->request->post['sociallogin_vkontakte_appsecret'];
		} elseif ($this->config->get('sociallogin_vkontakte_appsecret')) {
			$this->data['sociallogin_vkontakte_appsecret'] = $this->config->get('sociallogin_vkontakte_appsecret');
		} else {
			$this->data['sociallogin_vkontakte_appsecret'] = '';
		}

		// FACEBOOK SETTINGS

		if (isset($this->request->post['sociallogin_facebook_status'])) {
			$this->data['sociallogin_facebook_status'] = $this->request->post['sociallogin_facebook_status'];
		} elseif ($this->config->get('sociallogin_facebook_status')) {
			$this->data['sociallogin_facebook_status'] = $this->config->get('sociallogin_facebook_status');
		} else {
			$this->data['sociallogin_facebook_status'] = 0;
		}

		if (isset($this->request->post['sociallogin_facebook_appid'])) {
			$this->data['sociallogin_facebook_appid'] = $this->request->post['sociallogin_facebook_appid'];
		} elseif ($this->config->get('sociallogin_facebook_appid')) {
			$this->data['sociallogin_facebook_appid'] = $this->config->get('sociallogin_facebook_appid');
		} else {
			$this->data['sociallogin_facebook_appid'] = '';
		}

		if (isset($this->request->post['sociallogin_facebook_appsecret'])) {
			$this->data['sociallogin_facebook_appsecret'] = $this->request->post['sociallogin_facebook_appsecret'];
		} elseif ($this->config->get('sociallogin_facebook_appsecret')) {
			$this->data['sociallogin_facebook_appsecret'] = $this->config->get('sociallogin_facebook_appsecret');
		} else {
			$this->data['sociallogin_facebook_appsecret'] = '';
		}

		// ODNOKLASSNIKI

		if (isset($this->request->post['sociallogin_ok_status'])) {
			$this->data['sociallogin_ok_status'] = $this->request->post['sociallogin_ok_status'];
		} elseif ($this->config->get('sociallogin_ok_status')) {
			$this->data['sociallogin_ok_status'] = $this->config->get('sociallogin_ok_status');
		} else {
			$this->data['sociallogin_ok_status'] = 0;
		}

		if (isset($this->request->post['sociallogin_ok_appid'])) {
			$this->data['sociallogin_ok_appid'] = $this->request->post['sociallogin_ok_appid'];
		} elseif ($this->config->get('sociallogin_ok_appid')) {
			$this->data['sociallogin_ok_appid'] = $this->config->get('sociallogin_ok_appid');
		} else {
			$this->data['sociallogin_ok_appid'] = '';
		}

		if (isset($this->request->post['sociallogin_ok_appsecret'])) {
			$this->data['sociallogin_ok_appsecret'] = $this->request->post['sociallogin_ok_appsecret'];
		} elseif ($this->config->get('sociallogin_ok_appsecret')) {
			$this->data['sociallogin_ok_appsecret'] = $this->config->get('sociallogin_ok_appsecret');
		} else {
			$this->data['sociallogin_ok_appsecret'] = '';
		}

		if (isset($this->request->post['sociallogin_ok_apppublic'])) {
			$this->data['sociallogin_ok_apppublic'] = $this->request->post['sociallogin_ok_apppublic'];
		} elseif ($this->config->get('sociallogin_ok_apppublic')) {
			$this->data['sociallogin_ok_apppublic'] = $this->config->get('sociallogin_ok_apppublic');
		} else {
			$this->data['sociallogin_ok_apppublic'] = '';
		}

		// TWITTER SETTINGS
		if (isset($this->request->post['sociallogin_twitter_status'])) {
			$this->data['sociallogin_twitter_status'] = $this->request->post['sociallogin_twitter_status'];
		} elseif ($this->config->get('sociallogin_twitter_status')) {
			$this->data['sociallogin_twitter_status'] = $this->config->get('sociallogin_twitter_status');
		} else {
			$this->data['sociallogin_twitter_status'] = 0;
		}

		if (isset($this->request->post['sociallogin_twitter_appid'])) {
			$this->data['sociallogin_twitter_appid'] = $this->request->post['sociallogin_twitter_appid'];
		} elseif ($this->config->get('sociallogin_twitter_appid')) {
			$this->data['sociallogin_twitter_appid'] = $this->config->get('sociallogin_twitter_appid');
		} else {
			$this->data['sociallogin_twitter_appid'] = 0;
		}

		if (isset($this->request->post['sociallogin_twitter_consumerkey'])) {
			$this->data['sociallogin_twitter_consumerkey'] = $this->request->post['sociallogin_twitter_consumerkey'];
		} elseif ($this->config->get('sociallogin_twitter_consumerkey')) {
			$this->data['sociallogin_twitter_consumerkey'] = $this->config->get('sociallogin_twitter_consumerkey');
		} else {
			$this->data['sociallogin_twitter_consumerkey'] = '';
		}

		if (isset($this->request->post['sociallogin_twitter_consumersecret'])) {
			$this->data['sociallogin_twitter_consumersecret'] = $this->request->post['sociallogin_twitter_consumersecret'];
		} elseif ($this->config->get('sociallogin_twitter_consumersecret')) {
			$this->data['sociallogin_twitter_consumersecret'] = $this->config->get('sociallogin_twitter_consumersecret');
		} else {
			$this->data['sociallogin_twitter_consumersecret'] = '';
		}

		// INSTAGRAM
		if (isset($this->request->post['sociallogin_instagram_status'])) {
			$this->data['sociallogin_instagram_status'] = $this->request->post['sociallogin_instagram_status'];
		} elseif ($this->config->get('sociallogin_instagram_status')) {
			$this->data['sociallogin_instagram_status'] = $this->config->get('sociallogin_instagram_status');
		} else {
			$this->data['sociallogin_instagram_status'] = 0;
		}

		//========================================

		$this->template = 'module/sociallogin.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/sociallogin')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if( !empty( $this->request->post['sociallogin_vkontakte_appid'] ) )	{
			$this->request->post['sociallogin_vkontakte_appid'] = trim($this->request->post['sociallogin_vkontakte_appid']);
		}

		if( !empty( $this->request->post['sociallogin_vkontakte_appsecret'] ) ){
			$this->request->post['sociallogin_vkontakte_appsecret'] = trim($this->request->post['sociallogin_vkontakte_appsecret']);
		}

		if( !empty( $this->request->post['sociallogin_facebook_appid'] ) ){
			$this->request->post['sociallogin_facebook_appid'] = trim($this->request->post['sociallogin_facebook_appid']);
		}

		if( !empty( $this->request->post['sociallogin_facebook_appsecret'] ) ){
			$this->request->post['sociallogin_facebook_appsecret'] = trim($this->request->post['sociallogin_facebook_appsecret']);
		}

		if( !empty( $this->request->post['sociallogin_ok_appid'] ) ){
			$this->request->post['sociallogin_ok_appid'] = trim($this->request->post['sociallogin_ok_appid']);
		}

		// odnoklassniki settings
		if( !empty( $this->request->post['sociallogin_ok_appsecret'] ) ){
			$this->request->post['sociallogin_ok_appsecret'] = trim($this->request->post['sociallogin_ok_appsecret']);
		}

		if( !empty( $this->request->post['sociallogin_twitter_consumerkey'] ) ){
			$this->request->post['sociallogin_twitter_consumerkey'] = trim($this->request->post['sociallogin_twitter_consumerkey']);
		}

		if( !empty( $this->request->post['sociallogin_twitter_consumersecret'] ) ){
			$this->request->post['sociallogin_twitter_consumersecret'] = trim($this->request->post['sociallogin_twitter_consumersecret']);
		}

		if( !empty( $this->request->post['sociallogin_module'] ) ){
			$this->request->post['sociallogin_module'] = $this->request->post['sociallogin_module'];
		}else{
			$this->request->post['sociallogin_module'] = '';
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>