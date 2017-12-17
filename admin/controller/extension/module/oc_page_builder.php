<?php
class ControllerExtensionModuleOcPageBuilder extends Controller
{
    private $error = array();

    public function index() {
        $this->load->language('extension/module/oc_page_builder');

        $this->load->model('setting/module');

        $this->document->setTitle($this->language->get('page_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('oc_page_builder', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->cache->delete('product');

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
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
                'href' => $this->url->link('extension/module/oc_page_builder', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/oc_page_builder', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/oc_page_builder', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/oc_page_builder', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
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

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = 1;
        }

        if (isset($this->request->post['widget'])) {
            $data['widgets'] = $this->request->post['widget'];
        } elseif (!empty($module_info['widget'])) {
            $data['widgets'] = $module_info['widget'];
        } else {
            $data['widgets'] = array();
        }

        //echo "<pre/ >"; var_dump($data['widgets']);die;

        $this->load->model('setting/extension');

        $this->load->model('setting/module');

        $data['extensions'] = array();

        // Get a list of installed modules
        $extensions = $this->model_setting_extension->getInstalled('module');

        // Add all the modules which have multiple settings for each module
        foreach ($extensions as $code) {
            if($code == "oc_page_builder") continue;

            $this->language->load('extension/module/' . $code);

            $module_data = array();

            $modules = $this->model_setting_module->getModulesByCode($code);

            foreach ($modules as $module) {
                $module_data[] = array(
                    'name' => strip_tags($this->language->get('heading_title') . ' &gt; ' . $module['name']),
                    'code' => $code . '.' .  $module['module_id'],
                    'url'	=> $this->url->link('extension/module/' . $code, 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'], true)
                );
            }

            if ($this->config->has('module_' . $code . '_status') || $module_data) {
                $data['extensions'][] = array(
                    'name'   => strip_tags($this->language->get('heading_title')),
                    'code'   => $code,
                    'url'	 => $this->url->link('extension/module/' . $code, 'user_token=' . $this->session->data['user_token'], true),
                    'modules' => $module_data
                );
            }
        }
		
        $this->document->addStyle('view/javascript/jquery/css/jquery-ui.css');
        $this->document->addStyle('view/stylesheet/builder.css');
        $this->document->addScript('view/javascript/jquery/jquery-ui.js');
        $this->document->addScript('view/javascript/oc_page_builder/builder.js');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ocpagebuilder/oc_page_builder', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/oc_page_builder')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }
}