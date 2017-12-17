<?php
class ControllerExtensionModuleOcfeaturedcategorytab extends Controller 
{
    private $error = array();

    public function index() {
        $this->load->language('extension/module/ocfeaturedcategorytab');

        $this->document->setTitle($this->language->get('page_title'));

        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('ocfeaturedcategorytab', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->cache->delete('product');

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
                'href' => $this->url->link('extension/module/ocfeaturedcategorytab', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/ocfeaturedcategorytab', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/ocfeaturedcategorytab', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/ocfeaturedcategorytab', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

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

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($module_info) && isset($module_info['title'])) {
            $data['title'] = $module_info['title'];
        } else {
            $data['title'] = array();
        }

        if (isset($this->request->post['showlabel'])) {
            $data['showlabel'] = $this->request->post['showlabel'];
        } elseif (!empty($module_info)) {
            $data['showlabel'] = $module_info['showlabel'];
        } else {
            $data['showlabel'] = 1;
        }

        if (isset($this->request->post['limit'])) {
            $data['limit'] = $this->request->post['limit'];
        } elseif (!empty($module_info)) {
            $data['limit'] = $module_info['limit'];
        } else {
            $data['limit'] = 10;
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

        if (isset($this->request->post['item'])) {
            $data['item'] = $this->request->post['item'];
        } elseif (!empty($module_info)) {
            $data['item'] = $module_info['item'];
        } else {
            $data['item'] = 4;
        }

        if (isset($this->request->post['autoplay'])) {
            $data['autoplay'] = $this->request->post['autoplay'];
        } elseif (!empty($module_info)) {
            $data['autoplay'] = $module_info['autoplay'];
        } else {
            $data['autoplay'] = 1;
        }

        if (isset($this->request->post['rotator'])) {
            $data['rotator'] = $this->request->post['rotator'];
        } elseif (!empty($module_info)) {
            $data['rotator'] = $module_info['rotator'];
        } else {
            $data['rotator'] = 1;
        }

        if (isset($this->request->post['thumbnail'])) {
            $data['thumbnail'] = $this->request->post['thumbnail'];
        } elseif (!empty($module_info)) {
            $data['thumbnail'] = $module_info['thumbnail'];
        } else {
            $data['thumbnail'] = 1;
        }

        if (isset($this->request->post['speed'])) {
            $data['speed'] = $this->request->post['speed'];
        } elseif (!empty($module_info)) {
            $data['speed'] = $module_info['speed'];
        } else {
            $data['speed'] = 3000;
        }

        if (isset($this->request->post['showprice'])) {
            $data['showprice'] = $this->request->post['showprice'];
        } elseif (!empty($module_info)) {
            $data['showprice'] = $module_info['showprice'];
        } else {
            $data['showprice'] = 1;
        }

        if (isset($this->request->post['showdes'])) {
            $data['showdes'] = $this->request->post['showdes'];
        } elseif (!empty($module_info)) {
            $data['showdes'] = $module_info['showdes'];
        } else {
            $data['showdes'] = 0;
        }
        if (isset($this->request->post['showaddtocart'])) {
            $data['showaddtocart'] = $this->request->post['showaddtocart'];
        } elseif (!empty($module_info)) {
            $data['showaddtocart'] = $module_info['showaddtocart'];
        } else {
            $data['showaddtocart'] = 1;
        }
        if (isset($this->request->post['shownextback'])) {
            $data['shownextback'] = $this->request->post['shownextback'];
        } elseif (!empty($module_info)) {
            $data['shownextback'] = $module_info['shownextback'];
        } else {
            $data['shownextback'] = 1;
        }
        if (isset($this->request->post['shownav'])) {
            $data['shownav'] = $this->request->post['shownav'];
        } elseif (!empty($module_info)) {
            $data['shownav'] = $module_info['shownav'];
        } else {
            $data['shownav'] = 0;
        }

        if (isset($this->request->post['rows'])) {
            $data['rows'] = $this->request->post['rows'];
        } elseif (!empty($module_info)) {
            $data['rows'] = $module_info['rows'];
        } else {
            $data['rows'] = 1;
        }
		
        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = 1;
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ocfeaturedcategorytab', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ocfeaturedcategorytab')) {
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
}