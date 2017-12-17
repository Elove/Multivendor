<?php

class ControllerExtensionModuleOcblog extends Controller
{
    private $error = array();

    public function index() {
        $this->load->language('extension/module/ocblog');

        $this->document->setTitle($this->language->get('page_title'));

        $this->load->model('blog/articlelist');
        $this->load->model('setting/setting');
		$this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('ocblog', $this->request->post);
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

        $data['article_lists'] = array();

        $article_lists = $this->model_blog_articlelist->getAllArticlesList();
        foreach($article_lists as $list) {
            $data['article_lists'][] = array(
                'article_list_id' => $list['article_list_id'],
                'name'  => $list['name']
            );
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

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/module/ocblog', 'user_token=' . $this->session->data['user_token'], true),
        );

        if (isset($this->request->get['module_id'])) {
            $data['list_action'] = $this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        } else {
            $data['list_action'] = $this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'], true);
        }

		$data['action'] = $this->url->link('extension/module/ocblog', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
            $data['action'] = $this->url->link('extension/module/ocblog', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
        
        if (isset($this->request->post['list'])) {
            $data['list'] = $this->request->post['list'];
        } elseif (!empty($module_info)) {
            $data['list'] = $module_info['list'];
        } else {
            $data['list'] = '';
        }

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

        if (isset($this->request->post['width'])) {
            $data['width'] = $this->request->post['width'];
        } elseif (!empty($module_info)) {
            $data['width'] = $module_info['width'];
        } else {
            $data['width'] = '';
        }

        if (isset($this->request->post['height'])) {
            $data['height'] = $this->request->post['height'];
        } elseif (!empty($module_info)) {
            $data['height'] = $module_info['height'];
        } else {
            $data['height'] = '';
        }

        if (isset($this->request->post['rows'])) {
            $data['rows'] = $this->request->post['rows'];
        } elseif (!empty($module_info)) {
            $data['rows'] = $module_info['rows'];
        } else {
            $data['rows'] = 1;
        }

        if (isset($this->request->post['items'])) {
            $data['items'] = $this->request->post['items'];
        } elseif (!empty($module_info)) {
            $data['items'] = $module_info['items'];
        } else {
            $data['items'] = 4;
        }

        if (isset($this->request->post['auto'])) {
            $data['auto'] = $this->request->post['auto'];
        } elseif (!empty($module_info)) {
            $data['auto'] = $module_info['auto'];
        } else {
            $data['auto'] = 1;
        }

        if (isset($this->request->post['speed'])) {
            $data['speed'] = $this->request->post['speed'];
        } elseif (!empty($module_info)) {
            $data['speed'] = $module_info['speed'];
        } else {
            $data['speed'] = 3000;
        }

        if (isset($this->request->post['navigation'])) {
            $data['navigation'] = $this->request->post['navigation'];
        } elseif (!empty($module_info)) {
            $data['navigation'] = $module_info['navigation'];
        } else {
            $data['navigation'] = 1;
        }

        if (isset($this->request->post['pagination'])) {
            $data['pagination'] = $this->request->post['pagination'];
        } elseif (!empty($module_info)) {
            $data['pagination'] = $module_info['pagination'];
        } else {
            $data['pagination'] = 0;
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

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ocblog', $data));
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ocblog')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['width']) {
            $this->error['width'] = $this->language->get('error_width');
        }

        if (!$this->request->post['height']) {
            $this->error['height'] = $this->language->get('error_height');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('blog/ocblog');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/article');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/article');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/articlelist');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/articlelist');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'blog/config');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'blog/config');

        $this->model_blog_ocblog->install();

        $data = array(
            'module_ocblog_article_limit' => '10',
            'module_ocblog_meta_title'    => 'Blog',
            'module_ocblog_meta_description' => 'Blog Description',
            'module_ocblog_meta_keyword'    => 'Blog Keyword'
        );

        $this->model_setting_setting->editSetting('module_ocblog', $data, 0);
    }

    public function uninstall() {
        $this->load->model('blog/ocblog');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');

        $this->model_blog_ocblog->uninstall();
        $this->model_setting_extension->uninstall('module_ocblog', $this->request->get['extension']);
        $this->model_setting_setting->deleteSetting($this->request->get['extension']);
    }
}
