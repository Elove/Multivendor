<?php 
class ControllerSellerUploadimages extends Controller { 
	private $error = array();
	public function index() {
	    if (!$this->seller->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('seller/uploadimages', '', 'SSL');
	  $this->response->redirect($this->url->link('seller/login', '', 'SSL'));
    	}
		$this->load->language('seller/folderimage');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('seller/seller');
		$this->getList();
	}
	public function multiupload() {
		$this->load->language('seller/folderimage');
		$this->load->model('seller/seller');
		$this->document->setTitle($this->language->get('heading_title1'));
		$data['imageshref'] = $this->url->link('seller/filemanager','', '');
		$data['parent'] = $this->request->get['parent'];		
		$this->getForm();
	}
	public function delete() {
		$this->language->load('seller/folderimage');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('seller/seller');
		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $folder_id) {
				$this->model_seller_seller->deleteImages($folder_id);
			}
			$this->session->data['success'] = $this->language->get('text_deleted');
			$url = '';
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('seller/uploadimages', $url, 'SSL'));
		}
		$this->getList();
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
   		$data['breadcrumbs'] = array();
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '' ,'SSL'),
      		'separator' => false
   		);
		$data['breadcrumbs'][] = array(
       		'text'      => 'Account',
			'href'      => $this->url->link('seller/account', '', 'SSL'),       		
      		'separator' => ' :: '
   		);
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('seller/uploadimages', '', 'SSL'),
      		'separator' => ' :: '
   		);
		$data['insert'] = $this->url->link('seller/uploadimages/insert', $url, 'SSL');
		$data['delete'] = $this->url->link('seller/uploadimages/delete', $url, 'SSL');
		$data['folders'] = array();
		$data1 = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		$results = $this->model_seller_seller->getfolders($data1,$this->seller->getId());
		$uploadimages_total = $this->model_seller_seller->getTotalFolders($data1,$this->seller->getId());
		foreach ($results as $result) {
			$this->response->redirect($this->url->link('seller/uploadimages/multiupload', '&parent=' . $result['seller_id'], 'SSL'));
			$path_exp = str_replace("/"," ->",$result['path']);
			$data['folders'][] = array(
				'folder_id' => $result['seller_id'],
				'parent_folder' => $path_exp
			);
		}
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['column_parent'] = $this->language->get('column_parent');
		$data['column_foldername'] = $this->language->get('column_foldername');
		$data['column_action'] = $this->language->get('column_action');
		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_delete'] = $this->language->get('button_delete');
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}
		$pagination = new Pagination();
		$pagination->total = $uploadimages_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('seller/uploadimages', $url . '&page={page}', 'SSL');
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($uploadimages_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($uploadimages_total - $this->config->get('config_limit_admin'))) ? $uploadimages_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $uploadimages_total, ceil($uploadimages_total / $this->config->get('config_limit_admin')));
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = 
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/seller/folderimage.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/seller/folderimage.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/seller/folderimage.tpl', $data));
		}
	}
	private function getForm() {
		$data['heading_title'] = $this->language->get('heading_title1');
		$data['entry_parentfolder'] = $this->language->get('entry_parentfolder');
		$data['entry_foldername'] = $this->language->get('entry_foldername');
		$data['button_continue'] = $this->language->get('button_continue');
    	$data['button_back'] = $this->language->get('button_back');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
    	$data['tab_general'] = $this->language->get('tab_general');
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
 		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = "";
		}
  		$data['breadcrumbs'] = array();
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);
		$data['breadcrumbs'][] = array(
       		'text'      => 'Account',
			'href'      => $this->url->link('seller/account', '', 'SSL'),       		
      		'separator' => ' :: '
   		);
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title1'),
			'href'      => $this->url->link('seller/uploadimages', '', 'SSL'),
      		'separator' => ' :: '
   		);
		if (isset($this->request->get['parent'])) {
			$data['action'] = $this->url->link('seller/uploadimages/insert', '' . '&parent=' . $this->request->get['parent'], 'SSL');
		} 
		$data['cancel'] = $this->url->link('seller/account', '', 'SSL');
       	$data['imageshref'] = $this->url->link('seller/filemanager', '', 'SSL');			
		$this->load->model('seller/seller');
		$data['folderpath'] = $this->model_seller_seller->addImages($this->request->get['parent']);
		 $data['parent'] =  $this->request->get['parent'];
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['back'] = $this->url->link('seller/account', '', 'SSL');
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/seller/uploadimages.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/seller/uploadimages.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/seller/uploadimages.tpl', $data));
		}
		$data['back'] = $this->url->link('seller/uploadimages', '', 'SSL');
	}
	public function upload() {
		$this->language->load('seller/extension');	
		$json = array();
		if (!empty($this->request->files['myfile']['name'])) {
			$filename = basename(html_entity_decode($this->request->files['myfile']['name'], ENT_QUOTES, 'UTF-8'));
			if ((strlen($filename) < 3) || (strlen($filename) > 128)) {
        		$json['error'] = $this->language->get('error_filename');
	  		}	  	
			$allowed = array();
			$filetypes = explode("\n", $this->config->get('config_file_ext_allowed'));
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
				$this->load->model('seller/seller');
				$foldername = $this->model_seller_seller->addImages($this->request->get['parent']);
				//$json['foldername'] = 'image/surfaceone80/123456/';
				move_uploaded_file($this->request->files['myfile']['tmp_name'], DIR_IMAGE .$foldername.'/'. $file);
				/*$old = DIR_IMAGE .$foldername.'/'. $file;
				$new = DIR_IMAGE1 .$foldername.'/'. $file;
				copy($old, $new);*/
			}
			$json['success'] = $this->language->get('text_upload');
		}	
		$this->response->setOutput(json_encode($json));		
	}
}
?>