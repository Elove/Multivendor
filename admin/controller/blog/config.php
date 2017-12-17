<?php
class ControllerBlogConfig extends Controller
{
    private $error = array();

    public function index() {
        $this->load->language('blog/config');

        $this->document->setTitle($this->language->get('page_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_ocblog', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('blog/config', 'user_token=' . $this->session->data['user_token'], true));
        }

        if(isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['article_limit'])) {
            $data['error_article_limit'] = $this->error['article_limit'];
        } else {
            $data['error_article_limit'] = '';
        }

        if (isset($this->error['meta_title'])) {
            $data['error_meta_title'] = $this->error['meta_title'];
        } else {
            $data['error_meta_title'] = '';
        }

        if (isset($this->error['meta_description'])) {
            $data['error_meta_description'] = $this->error['meta_description'];
        } else {
            $data['error_meta_description'] = '';
        }

        if (isset($this->error['meta_keyword'])) {
            $data['error_meta_keyword'] = $this->error['meta_keyword'];
        } else {
            $data['error_meta_keyword'] = '';
        }

        if (isset($this->error['blog_width'])) {
            $data['error_image_blog'] = $this->error['blog_width'];
        } else {
            $data['error_image_blog'] = '';
        }

        if (isset($this->error['blog_height'])) {
            $data['error_image_blog'] = $this->error['blog_height'];
        } else {
            $data['error_image_blog'] = '';
        }

        if (isset($this->error['article_width'])) {
            $data['error_image_article'] = $this->error['article_width'];
        } else {
            $data['error_image_article'] = '';
        }

        if (isset($this->error['article_height'])) {
            $data['error_image_article'] = $this->error['article_height'];
        } else {
            $data['error_image_article'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_edit'),
            'href' => $this->url->link('blog/config', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('blog/config', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['module_ocblog_article_limit'])) {
            $data['module_ocblog_article_limit'] = $this->request->post['module_ocblog_article_limit'];
        } else {
            $data['module_ocblog_article_limit'] = $this->config->get('module_ocblog_article_limit');
        }

        if (isset($this->request->post['module_ocblog_meta_title'])) {
            $data['module_ocblog_meta_title'] = $this->request->post['module_ocblog_meta_title'];
        } else {
            $data['module_ocblog_meta_title'] = $this->config->get('module_ocblog_meta_title');
        }

        if (isset($this->request->post['module_ocblog_meta_description'])) {
            $data['module_ocblog_meta_description'] = $this->request->post['module_ocblog_meta_description'];
        } else {
            $data['module_ocblog_meta_description'] = $this->config->get('module_ocblog_meta_description');
        }

        if (isset($this->request->post['module_ocblog_meta_keyword'])) {
            $data['module_ocblog_meta_keyword'] = $this->request->post['module_ocblog_meta_keyword'];
        } else {
            $data['module_ocblog_meta_keyword'] = $this->config->get('module_ocblog_meta_keyword');
        }

        if (isset($this->request->post['module_ocblog_blog_width'])) {
            $data['module_ocblog_blog_width'] = $this->request->post['module_ocblog_blog_width'];
        } else {
            $data['module_ocblog_blog_width'] = $this->config->get('module_ocblog_blog_width');
        }

        if (isset($this->request->post['module_ocblog_blog_height'])) {
            $data['module_ocblog_blog_height'] = $this->request->post['module_ocblog_blog_height'];
        } else {
            $data['module_ocblog_blog_height'] = $this->config->get('module_ocblog_blog_height');
        }

        if (isset($this->request->post['module_ocblog_article_width'])) {
            $data['module_ocblog_article_width'] = $this->request->post['module_ocblog_article_width'];
        } else {
            $data['module_ocblog_article_width'] = $this->config->get('module_ocblog_article_width');
        }

        if (isset($this->request->post['module_ocblog_article_height'])) {
            $data['module_ocblog_article_height'] = $this->request->post['module_ocblog_article_height'];
        } else {
            $data['module_ocblog_article_height'] = $this->config->get('module_ocblog_article_height');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('blog/config', $data));
    }

    public function validate() {
        if (!$this->user->hasPermission('modify', 'blog/config')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['module_ocblog_article_limit']) {
            $this->error['article_limit'] = $this->language->get('error_article_limit');
        }

        if (!$this->request->post['module_ocblog_meta_title']) {
            $this->error['meta_title'] = $this->language->get('error_meta_title');
        }

        if (!$this->request->post['module_ocblog_meta_description']) {
            $this->error['meta_description'] = $this->language->get('error_meta_description');
        }

        if (!$this->request->post['module_ocblog_meta_keyword']) {
            $this->error['meta_keyword'] = $this->language->get('error_meta_keyword');
        }

        if (!$this->request->post['module_ocblog_blog_width']) {
            $this->error['blog_width'] = $this->language->get('error_image_blog');
        }

        if (!$this->request->post['module_ocblog_blog_height']) {
            $this->error['blog_height'] = $this->language->get('error_image_blog');
        }

        if (!$this->request->post['module_ocblog_article_width']) {
            $this->error['article_width'] = $this->language->get('error_image_article');
        }

        if (!$this->request->post['module_ocblog_article_height']) {
            $this->error['article_height'] = $this->language->get('error_image_article');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}