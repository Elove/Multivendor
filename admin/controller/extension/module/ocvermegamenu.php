<?php
class ControllerExtensionModuleOCvermegamenu extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/ocvermegamenu');

		$this->document->setTitle($this->language->get('page_title'));
		
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('ocvermegamenu', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
						
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_homepage'] = $this->language->get('text_homepage');
		$data['text_depth'] = $this->language->get('text_depth');
		$data['text_item_number'] = $this->language->get('text_item_number');
        $data['text_level'] = $this->language->get('text_level');
		$data['text_link_cate'] = $this->language->get('text_link_cate');
		
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_product'] = $this->language->get('entry_product');
		$data['entry_limit'] = $this->language->get('entry_limit');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['text_item_limit'] = $this->language->get('text_item_limit');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

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
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ocvermegamenu', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ocvermegamenu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);			
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ocvermegamenu', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ocvermegamenu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
		
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['h_show_homepage'])) {
			$data['h_show_home'] = $this->request->post['h_show_homepage'];
		} elseif (!empty($module_info)) {
			$data['h_show_home'] = $module_info['h_show_homepage'];
		} else {
			$data['h_show_home'] = 1;
		}
		if (isset($this->request->post['hdepth'])) {
			$data['hdepth'] = $this->request->post['hdepth'];
		} elseif (!empty($module_info) && isset($module_info['hdepth'])) {
			$data['hdepth'] = $module_info['hdepth'];
		} else {
			$data['hdepth'] = 1;
		}
		
        if (isset($this->request->post['h_item_number'])) {
			$data['h_item_number'] = $this->request->post['h_item_number'];
		} elseif (!empty($module_info) && isset($module_info['h_item_number'])) {
			$data['h_item_number'] = $module_info['h_item_number'];
		} else {
			$data['h_item_number'] = 1;
		}
	
	   	if (isset($this->request->post['hlevel'])) {
			$data['hlevel'] = $this->request->post['hlevel'];
		} elseif (!empty($module_info) && isset($module_info['hlevel'])) {
			$data['hlevel'] = $module_info['hlevel'];
		} else {
			$data['hlevel'] = 1;
		}
		
		if (isset($this->request->post['hitem_limit'])) {
			$data['hitem_limit'] = $this->request->post['hitem_limit'];
		} elseif (!empty($module_info) && isset($module_info['hitem_limit'])) {
			$data['hitem_limit'] = $module_info['hitem_limit'];
		} else {
			$data['hitem_limit'] = 10;
		}


		if (isset($this->request->post['h_active_cate'])) {
			$data['h_active_cate'] = $this->request->post['h_active_cate'];
		} elseif (!empty($module_info) && isset($module_info['h_active_cate'])) {
			$data['h_active_cate'] = $module_info['h_active_cate'];
		} else {
			$data['h_active_cate'] = 'CAT20,CAT18,CAT25';
		}

		$this->load->model('vermegamenu/menu');
		$data['cate_option'] = $this->model_vermegamenu_menu->getCategoryOption(0,1,1,1); 
		$data['option_active'] = $this->model_vermegamenu_menu->getMenuOptions();
		$data['cms_option'] = $this->model_vermegamenu_menu->getInfomatinOptions(); 
		$data['link_option'] = $this->model_vermegamenu_menu->getLinkOptions();	
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ocvermegamenu', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ocvermegamenu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		return !$this->error;
	}
}