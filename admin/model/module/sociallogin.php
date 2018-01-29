<?php
class ModelModuleSociallogin extends Model {

	public function uninstall() {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE '%sociallogin%'");
		$this->cache->delete('seo_pro');
		$this->cache->delete('seo_url');
	}

	public function install() {
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "url_alias` WHERE Field = 'seo_mod'");
		if(count($query->rows) == 0 ) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "url_alias` ADD COLUMN seo_mod INT(1) DEFAULT 0;");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "url_alias` ADD INDEX (seo_mod);");
		}
		$sql = array();
		$sql[] = "INSERT INTO `" . DB_PREFIX . "url_alias` (query, keyword, seo_mod) VALUES ('module/sociallogin/vk', 'vk-login', 1);";
		$sql[] = "INSERT INTO `" . DB_PREFIX . "url_alias` (query, keyword, seo_mod) VALUES ('module/sociallogin/fb', 'fb-login', 1);";

		foreach ($sql as $_sql) {
			$this->db->query($_sql);
		}
		$this->cache->delete('seo_pro');
		$this->cache->delete('seo_url');
	}

}
?>