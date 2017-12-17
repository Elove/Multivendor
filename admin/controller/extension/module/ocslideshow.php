<?php 
class ControllerExtensionModuleOcslideshow extends Controller {
	private $error = array();
	
	public function install() { 
	  	$this->load->model('ocslideshow/slide');
		$this->model_ocslideshow_slide->createTable();
      	$this->model_ocslideshow_slide->alterTable();
	}
	
	public function uninstall() { 
		$this->load->model('ocslideshow/slide');
      //  $this->model_ocslideshow_slide->deleteTable();
    }
	
	public function index() {
		$this->load->language('extension/module/ocslideshow');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('ocslideshow', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}
		
		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ocslideshow', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ocslideshow', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);			
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ocslideshow', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ocslideshow', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('model_setting', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
		
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['effect'])) {
			$data['effect'] = $this->request->post['effect'];
		} elseif (!empty($banner_info)) {
			$data['effect'] = $banner_info['effect'];
		} else {
			$data['effect'] = '';
		}
		
		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} elseif (!empty($module_info) && isset($module_info['banner'])) {
			$data['banner'] = $module_info['banner'];
		} else {
			$data['banner'] = 1;
		}
						
		

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 200;
		}	
			
		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 200;
		}		
		
		// if (isset($this->request->post['small_width'])) {
			// $data['small_width'] = $this->request->post['small_width'];
		// } elseif (!empty($module_info)) {
			// $data['small_width'] = $module_info['small_width'];
		// } else {
			// $data['small_width'] = 200;
		// }	
			
		// if (isset($this->request->post['small_height'])) {
			// $data['small_height'] = $this->request->post['small_height'];
		// } elseif (!empty($module_info)) {
			// $data['small_height'] = $module_info['small_height'];
		// } else {
			// $data['small_height'] = 200;
		// }
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		$data['link_to_module'] = $this->url->link('extension/module/ocslideshow', 'user_token=' . $this->session->data['user_token'], true);
		$data['link_to_banner'] = $this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'], true);
		
		$this->load->model('localisation/language');
		$this->load->model('ocslideshow/slide');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['ocslideshows'] = $this->model_ocslideshow_slide->getocslideshows();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ocslideshow/config', $data));
	}
	
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ocslideshow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}
		
		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}

		return !$this->error;
	}
 
	public function form() {
		$this->language->load('extension/module/ocslideshow');

		$this->document->setTitle($this->language->get('page_title'));
		
		$this->load->model('ocslideshow/slide');
		
		
		$this->getList();
	}

	public function insert() {
		$this->language->load('extension/module/ocslideshow');

		$this->document->setTitle($this->language->get('page_title'));
		
		$this->load->model('ocslideshow/slide');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_ocslideshow_slide->addocslideshow($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

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
			
			$this->response->redirect($this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('extension/module/ocslideshow');

		$this->document->setTitle($this->language->get('page_title'));
		
		$this->load->model('ocslideshow/slide');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
		
			$this->model_ocslideshow_slide->editocslideshow($this->request->get['ocslideshow_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

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
			$this->response->redirect($this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function copy() {
		$this->language->load('extension/module/ocslideshow');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('ocslideshow/slide');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $ocslideshow_id) {
				$this->model_ocslideshow_slide->copySlideshow($ocslideshow_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

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

			$this->response->redirect($this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function delete() { 

		$this->language->load('extension/module/ocslideshow');
 
		$this->document->setTitle($this->language->get('page_title'));
		
		$this->load->model('ocslideshow/slide');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $ocslideshow_id) {
				$this->model_ocslideshow_slide->deleteocslideshow($ocslideshow_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

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

			$this->response->redirect($this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
			
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

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_banner_slider'),
			'href' => $this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['insert'] = $this->url->link('extension/module/ocslideshow/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/ocslideshow/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('extension/module/ocslideshow/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['ocslideshows']= array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$banner_total = $this->model_ocslideshow_slide->getTotalocslideshows();
		
		$results = $this->model_ocslideshow_slide->getocslideshows($filter_data);
		
		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('extension/module/ocslideshow/update', 'user_token=' . $this->session->data['user_token'] . '&ocslideshow_id=' . $result['ocslideshow_id'] . $url, true)
			);

			$data['ocslideshows'][] = array(
				'ocslideshow_id' => $result['ocslideshow_id'],
				'name'      => $result['name'],	
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),				
				'selected'  => isset($this->request->post['selected']) && in_array($result['ocslideshow_id'], $this->request->post['selected']),				
				'action'    => $action
			);
		}
 
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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['sort_name'] = $this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $banner_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($banner_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($banner_total - $this->config->get('config_limit_admin'))) ? $banner_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $banner_total, ceil($banner_total / $this->config->get('config_limit_admin')));
		$data['sort'] = $sort;
		$data['order'] = $order;
	

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/ocslideshow/ocslideshow_list', $data));
		
	}

	protected function getForm() {
		if (isset($this->request->post['ocslideshow_module'])) {
			$modules = $this->request->post['ocslideshow_module'];
		} elseif ($this->config->has('ocslideshow_module')) {
			$modules = $this->config->get('ocslideshow_module');
		} else {
			$modules = array();
		}

		$data['ocslideshow_modules'] = array();
		
		foreach ($modules as $key => $module) {
			$data['ocslideshow_modules'][] = array(
				'key'       => $key,
				//'banner_id' => $module['ocslideshow_id'],
				'width'     => $module['width'],
				'height'    => $module['height']
			);
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['delay'])) {
			$data['error_delay'] = $this->error['delay'];
		} else {
			$data['error_delay'] = '';
		}

 		if (isset($this->error['ocslideshow_image'])) {
			$data['error_ocslideshow_image'] = $this->error['ocslideshow_image'];
		} else {
			$data['error_ocslideshow_image'] = array();
		}	
						
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

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_banner_slider'),
			'href' => $this->url->link('extension/module/ocslideshow/form', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['ocslideshow_id'])) {
            $data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('extension/module/ocslideshow/insert', 'user_token=' . $this->session->data['user_token'] . $url, true)
            );

			$data['action'] = $this->url->link('extension/module/ocslideshow/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
            $data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('extension/module/ocslideshow/update', 'user_token=' . $this->session->data['user_token'] . '&ocslideshow_id=' . $this->request->get['ocslideshow_id'] . $url, true)
            );

			$data['action'] = $this->url->link('extension/module/ocslideshow/update', 'user_token=' . $this->session->data['user_token'] . '&ocslideshow_id=' . $this->request->get['ocslideshow_id'] . $url, true);
		}
		
		$data['cancel'] = $this->url->link('extension/module/ocslideshow', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		if (isset($this->request->get['ocslideshow_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$banner_info = $this->model_ocslideshow_slide->getocslideshow($this->request->get['ocslideshow_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($banner_info)) {
			$data['name'] = $banner_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['effect'])) {
			$data['effect'] = $this->request->post['effect'];
		} elseif (!empty($banner_info)) {
			$data['effect'] = $banner_info['effect'];
		} else {
			$data['effect'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($banner_info)) {
			$data['status'] = $banner_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if (isset($this->request->post['auto'])) {
			$data['auto'] = $this->request->post['auto'];
		} elseif (!empty($banner_info)) {
			$data['auto'] = $banner_info['auto'];
		} else {
			$data['auto'] = true;
		}
		
		if (isset($this->request->post['effect'])) {
			$data['effect'] = $this->request->post['effect'];
		} elseif (!empty($banner_info)) {
			$data['effect'] = $banner_info['effect'];
		} else {
			$data['effect'] = 'random';
		}
		
		if (isset($this->request->post['delay'])) {
			$data['delay'] = $this->request->post['delay'];
		} elseif (!empty($banner_info)) {
			$data['delay'] = $banner_info['delay'];
		} else {
			$data['delay'] = '3000';
		}
		
		if (isset($this->request->post['hover'])) {
			$data['hover'] = $this->request->post['hover'];
		} elseif (!empty($banner_info)) {
			$data['hover'] = $banner_info['hover'];
		} else {
			$data['hover'] = true;
		}
		
		if (isset($this->request->post['nextback'])) {
			$data['nextback'] = $this->request->post['nextback'];
		} elseif (!empty($banner_info)) {
			$data['nextback'] = $banner_info['nextback'];
		} else {
			$data['nextback'] = true;
		}
		
		if (isset($this->request->post['contrl'])) {
			$data['contrl'] = $this->request->post['contrl'];
		} elseif (!empty($banner_info)) {
			$data['contrl'] = $banner_info['contrl'];
		} else {
			$data['contrl'] = true;
		}

		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('tool/image');

		if (isset($this->request->post['ocslideshow_image'])) {
			$ocslideshow_images = $this->request->post['ocslideshow_image'];
		} elseif (isset($this->request->get['ocslideshow_id'])) {
		  
			$ocslideshow_images = $this->model_ocslideshow_slide->getocslideshowImages($this->request->get['ocslideshow_id']);	
		} else {
			$ocslideshow_images = array();
		}
		
		$data['ocslideshow_images'] = array();
	
		foreach ($ocslideshow_images as $key =>  $ocslideshow_image) {
			if ($ocslideshow_image['image'] && file_exists(DIR_IMAGE . $ocslideshow_image['image'])) {
				$image = $ocslideshow_image['image'];
				
			} else {
				$image = 'no_image.png';
			}

			if($ocslideshow_image['small_image'] && file_exists(DIR_IMAGE . $ocslideshow_image['small_image'])) {
				$small_image = $ocslideshow_image['small_image'];
			} else {
				$small_image = 'no_image.png';
			}
				
			if(isset($ocslideshow_image['banner_store'])) {
					
				
				$data['ocslideshow_images'][] = array(
					'key' => $key,
					'ocslideshow_image_description' => $ocslideshow_image['ocslideshow_image_description'],
					'link'                     => $ocslideshow_image['link'],
					'type'                     => $ocslideshow_image['type'],
					'banner_store'             => explode(',',$ocslideshow_image['banner_store']),
					'image'                    => $image,
					'thumb'                    => $this->model_tool_image->resize($image, 100, 100),
					'small_image'			   => $small_image,
					'small_thumb'			   => $this->model_tool_image->resize($small_image, 100, 100)
				);	
			} else {
				$data['ocslideshow_images'][] = array(
					'key' => $key,
					'ocslideshow_image_description' => $ocslideshow_image['ocslideshow_image_description'],
					'link'                     => $ocslideshow_image['link'],
					'type'                     => $ocslideshow_image['type'],
					'image'                    => $image,
					'thumb'                    => $this->model_tool_image->resize($image, 100, 100),
					'small_image'			   => $small_image,
					'small_thumb'			   => $this->model_tool_image->resize($small_image, 100, 100)
				);	
			}
		} 
		

        $effect_options =  array(
				array('value'=>'random', 'label'=>'random'),
				array('value'=>'fade', 'label'=>'fade'),
				array('value'=>'slide', 'label'=>'slide'),
				array('value'=>'sliceDown', 'label'=>'sliceDown'),
				array('value'=>'sliceDownLeft', 'label'=>'sliceDownLeft'),
				array('value'=>'sliceUp', 'label'=>'sliceUp'),
				array('value'=>'sliceUpLeft', 'label'=>'sliceUpLeft'),
				array('value'=>'sliceUpDown', 'label'=>'sliceUpDown'),
				array('value'=>'sliceUpDownLeft', 'label'=>'sliceUpDownLeft'),
				array('value'=>'fold', 'label'=>'fold'),
				array('value'=>'slideInRight', 'label'=>'slideInRight'),
				array('value'=>'slideInLeft', 'label'=>'slideInLeft'),
				array('value'=>'boxRandom', 'label'=>'boxRandom'),
				array('value'=>'boxRain', 'label'=>'boxRain'),
				array('value'=>'boxRainReverse', 'label'=>'boxRainReverse'),
				array('value'=>'boxRainGrow', 'label'=>'boxRainGrow'),
				array('value'=>'boxRainGrowReverse', 'label'=>'boxRainGrowReverse')
        );
    
		$data['effect_option']  = $effect_options ;

		$this->load->model('setting/store');
		
		$data['stores'] = $this->model_setting_store->getStores();
		$data['stores'][] = array('store_id'=>0,'name'=>'Default Store');
		$data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);		

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('extension/module/ocslideshow/ocslideshow_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/ocslideshow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ($this->request->post['delay'] == '') {
			$this->error['delay'] = $this->language->get('error_delay');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/ocslideshow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}