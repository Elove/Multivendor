<?php
class ControllerExtensionModuleOcajaxlogin extends Controller {
    private $error = array();

    public function install() {
        $config = array(
            'module_ocajaxlogin_status' => 1,
            'module_ocajaxlogin_loader_img' => 'catalog/AjaxLoader.gif'
        );
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_ocajaxlogin', $config);
    }

    public function index() {
        $this->load->language('extension/module/ocajaxlogin');

        $this->document->setTitle($this->language->get('page_title'));

        $this->load->model('setting/setting');
        $this->load->model('tool/image');
        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_ocajaxlogin', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ocajaxlogin', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/ocajaxlogin', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_ocajaxlogin_status'])) {
            $data['module_ocajaxlogin_status'] = $this->request->post['module_ocajaxlogin_status'];
        } else {
            $data['module_ocajaxlogin_status'] = $this->config->get('module_ocajaxlogin_status');
        }

        if (isset($this->request->post['module_ocajaxlogin_redirect_status'])) {
            $data['module_ocajaxlogin_redirect_status'] = $this->request->post['module_ocajaxlogin_redirect_status'];
        } else {
            $data['module_ocajaxlogin_redirect_status'] = $this->config->get('module_ocajaxlogin_redirect_status');
        }

        if (isset($this->request->post['module_ocajaxlogin_loader_img'])) {
            $data['module_ocajaxlogin_loader_img'] = $this->request->post['module_ocajaxlogin_loader_img'];
        } else {
            $data['module_ocajaxlogin_loader_img'] = $this->config->get('module_ocajaxlogin_loader_img');
        }

        if (isset($this->request->post['module_ocajaxlogin_loader_img']) && is_file(DIR_IMAGE . $this->request->post['module_ocajaxlogin_loader_img'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['module_ocajaxlogin_loader_img'], 50, 50);
        } elseif (is_file(DIR_IMAGE . $this->config->get('module_ocajaxlogin_loader_img'))) {
            $data['thumb'] = $this->model_tool_image->resize($this->config->get('module_ocajaxlogin_loader_img'), 50, 50);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 50, 50);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 50, 50);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ocajaxlogin', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ocajaxlogin')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}