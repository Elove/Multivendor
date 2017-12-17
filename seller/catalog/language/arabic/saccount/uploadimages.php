<?php 
class ControllerSAccountUploadimages extends Controller { 
	private $error = array();
	public function index() {
	    if (!$this->seller->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('saccount/uploadimages', '', 'SSL');
	  		$this->redirect($this->url->link('saccount/login', '', 'SSL'));
    	}
		$this->load->language('saccount/folderimage');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('saccount/seller');
		$this->getList();
	}
	public function multiupload() {
		$this->load->language('saccount/folderimage');
		$this->load->model('saccount/seller');
		$this->document->setTitle($this->language->get('heading_title1'));
		$this->data['parent'] = $this->request->get['parent'];		
		$this->getForm();
	}
	private function getList() {
	if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$url = '';
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '' ,'SSL'),
      		'separator' => false
   		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Account',
			'href'      => $this->url->link('saccount/account', '', 'SSL'),       		
      		'separator' => ' :: '
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('saccount/uploadimages', '', 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['insert'] = $this->url->link('saccount/uploadimages/insert', $url, 'SSL');
		$this->data['delete'] = $this->url->link('saccount/uploadimages/delete', $url, 'SSL');
		$this->data['folders'] = array();
		$data = array(
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		$results = $this->model_saccount_seller->getfolders($this->seller->getId());
		$uploadimages_total = $this->model_saccount_seller->getTotalFolders($this->seller->getId());
		foreach ($results as $result) {
			$action = array();
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('saccount/uploadimages/multiupload', '' . '&parent=' . $result['folder_id']. $url, 'SSL')
			);
			$path_exp = str_replace("/",">",$result['path']);
			$this->data['folders'][] = array(
				'folder_id' => $result['folder_id'],
				'parent_folder' => $path_exp,
				'selected'    => isset($this->request->post['selected']) && in_array($result['folder_id'], $this->request->post['selected']),
				'action'      => $action
			);
		}
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['column_parent'] = $this->language->get('column_parent');
		$this->data['column_foldername'] = $this->language->get('column_foldername');
		$this->data['column_action'] = $this->language->get('column_action');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		$pagination = new Pagination();
		$pagination->total = $uploadimages_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('saccount/uploadimages', $url . '&page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/saccount/folderimage.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/saccount/folderimage.tpl';
		} else {
			$this->template = 'default/template/saccount/folderimage.tpl';
		}
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
		$this->response->setOutput($this->render());
	}
	private function getForm() {
		$this->data['heading_title1'] = $this->language->get('heading_title1');
		$this->data['entry_parentfolder'] = $this->language->get('entry_parentfolder');
		$this->data['entry_foldername'] = $this->language->get('entry_foldername');
		$this->data['button_continue'] = $this->language->get('button_continue');
    	$this->data['button_back'] = $this->language->get('button_back');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
    	$this->data['tab_general'] = $this->language->get('tab_general');
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = "";
		}
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Account',
			'href'      => $this->url->link('saccount/account', '', 'SSL'),       		
      		'separator' => ' :: '
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title1'),
			'href'      => $this->url->link('saccount/uploadimages', '', 'SSL'),
      		'separator' => ' :: '
   		);
		if (isset($this->request->get['parent'])) {
			$this->data['action'] = $this->url->link('saccount/uploadimages/insert', '' . '&parent=' . $this->request->get['parent'], 'SSL');
		} 
		$this->data['cancel'] = $this->url->link('saccount/uploadimages', '', 'SSL');
		$this->load->model('saccount/seller');
		$this->data['folderpath'] = $this->model_saccount_seller->addImages($this->request->get['parent']);
		$this->data['back'] = $this->url->link('saccount/uploadimages', '', 'SSL');
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/saccount/uploadimages.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/saccount/uploadimages.tpl';
		} else {
			$this->template = 'default/template/saccount/uploadimages.tpl';
		}
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
		$this->response->setOutput($this->render());
	}
	public function upload() {
		$this->language->load('saccount/extension');	
		$json = array();
		if (!empty($this->request->files['myfile']['name'])) {
			$filename = basename(html_entity_decode($this->request->files['myfile']['name'], ENT_QUOTES, 'UTF-8'));
			if ((strlen($filename) < 3) || (strlen($filename) > 128)) {
        		$json['error'] = $this->language->get('error_filename');
	  		}	  	
			$allowed = array();
			$filetypes = explode("\n", $this->config->get('config_file_extension_allowed'));
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
       		}	
			if ($this->request->files['myfile']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['myfile']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}
		if (!$json) {
			if (is_uploaded_file($this->request->files['myfile']['tmp_name']) && file_exists($this->request->files['myfile']['tmp_name'])) {
				//$file = basename($filename) . '.' . md5(rand());
				$file = basename($filename);
				// Hide the uploaded file name so people can not link to it directly.
				$json['myfile'] = $file;
				$this->load->model('saccount/seller');
				$foldername = $this->model_saccount_seller->addImages($this->request->get['parent']);
				//$json['foldername'] = 'image/surfaceone80/123456/';
				move_uploaded_file($this->request->files['myfile']['tmp_name'], DIR_IMAGE .$foldername.'/'. $file);
			}
			$json['success'] = $this->language->get('text_upload');
		}	
		$this->response->setOutput(json_encode($json));		
	}
}
?>