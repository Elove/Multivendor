<?php
class ModelExtensionModuleOcthemeoption extends Model
{
    public function createThemeTables() {
        $this->createRotatorImage();
        $this->createBlogTable();
        $this->createCategoryThumbnailTable();
        $this->createCMSTable();
        $this->createSlideShowTable();
        $this->createTestimonialTable();
    }

    public function createBlogTable() {
        $this->load->model('blog/ocblog');

        $this->model_blog_ocblog->install();
    }

    public function createCMSTable() {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cmsblock` (
			  `cmsblock_id` int(11) NOT NULL AUTO_INCREMENT,
			  `status` tinyint(1) NOT NULL,
			  `sort_order` tinyint(1) DEFAULT NULL,
			  `identify` varchar(255) DEFAULT NULL,
			  `link` varchar(255) DEFAULT NULL,
			  `type` tinyint(1) DEFAULT NULL,
			  `banner_store` varchar(255) DEFAULT '0',
			  PRIMARY KEY (`cmsblock_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19;
		");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cmsblock_description` (
			  `cmsblock_des_id` int(11) NOT NULL AUTO_INCREMENT,
			  `language_id` int(11) NOT NULL,
			  `cmsblock_id` int(11) NOT NULL,
			  `title` varchar(64) NOT NULL,
			  `sub_title` varchar(64) DEFAULT NULL,
			  `description` text,
			  PRIMARY KEY (`cmsblock_des_id`,`language_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=129;
		");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cmsblock_to_store` (
			  `cmsblock_id` int(11) DEFAULT NULL,
			  `store_id` tinyint(4) DEFAULT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		");
    }

    public function createSlideShowTable() {
        $sql = array();
        $sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ocslideshow` (
					  `ocslideshow_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(64) NOT NULL,
					  `status` tinyint(1) NOT NULL,
					  `auto` tinyint(1) DEFAULT NULL,
					  `delay` int(11) DEFAULT NULL,
					  `hover` tinyint(1) DEFAULT NULL,
					  `nextback` tinyint(1) DEFAULT NULL,
					  `contrl` tinyint(1) DEFAULT NULL,
					  `effect` varchar(64) NOT NULL,
					  PRIMARY KEY (`ocslideshow_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;";

        $sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ocslideshow_image` (
					  `ocslideshow_image_id` int(11) NOT NULL AUTO_INCREMENT,
					  `ocslideshow_id` int(11) NOT NULL,
					  `link` varchar(255) NOT NULL,
					  `type` int(11) NOT NULL,
					  `banner_store` varchar(110) DEFAULT '0',
					  `image` varchar(255) NOT NULL,
					  `small_image` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`ocslideshow_image_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=173 ;";

        $sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ocslideshow_image_description` (
					  `ocslideshow_image_id` int(11) NOT NULL,
					  `language_id` int(11) NOT NULL,
					  `ocslideshow_id` int(11) NOT NULL,
					  `title` varchar(64) NOT NULL,
					  `sub_title` varchar(64) DEFAULT NULL,
					  `description` text,
					  PRIMARY KEY (`ocslideshow_image_id`,`language_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

        foreach( $sql as $q ){
            $this->db->query($q);
        }
    }

    public function createTestimonialTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `".DB_PREFIX."testimonial` (
            `testimonial_id` int(11) NOT NULL AUTO_INCREMENT,
            `status` int(1) NOT NULL default 0,
            `sort_order` int(11) NOT NULL default 0,
            PRIMARY KEY (`testimonial_id`)
            )DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."testimonial_description`(
                `testimonial_id` int(10) unsigned NOT NULL,
                `language_id` int(10) unsigned NOT NULL,
                `image` varchar(255) collate utf8_bin ,
                `customer_name` varchar(255) collate utf8_bin NOT NULL,
                `content` text collate utf8_bin NOT NULL,
                PRIMARY KEY (`testimonial_id`,`language_id`)
                )
                DEFAULT CHARSET=utf8;");
    }

    public function createCategoryThumbnailTable() {
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'catalog/occategorythumbnail');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'catalog/occategorythumbnail');

        $this->load->model('catalog/occategorythumbnail');

        $this->model_catalog_occategorythumbnail->installCategoryThumbnail();
    }

    public function createRotatorImage() {
        $this->load->model('catalog/ocproductrotator');
        $this->model_catalog_ocproductrotator->installProductRotator();
    }
}