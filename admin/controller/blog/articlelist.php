<?php

class ControllerBlogArticlelist extends Controller
{
    private $error = array();

    public function index() {
        $this->load->language('blog/articlelist');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('blog/articlelist');

        $this->getList();
    }

    public function add() {
        $this->load->language('blog/articlelist');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('blog/articlelist');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $article_list_last_id = $this->model_blog_articlelist->addArticlesList($this->request->post);
            $this->model_blog_articlelist->addArticleToList($article_list_last_id, $this->request->post['article']);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('blog/articlelist');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('blog/articlelist');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_blog_articlelist->editArticleList($this->request->get['article_list_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('blog/articlelist');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('blog/articlelist');

        if (isset($this->request->post['selected']) && $this->validateCopy()) {
            foreach ($this->request->post['selected'] as $article_list_id) {
                $this->model_blog_articlelist->deleteArticlesList($article_list_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    public function copy() {
        $this->load->language('blog/articlelist');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('blog/articlelist');

        if (isset($this->request->post['selected']) && $this->validateCopy()) {
            foreach ($this->request->post['selected'] as $article_list_id) {
                $this->model_blog_articlelist->copyArticlesList($article_list_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    public function getList() {
        $data = array();

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['module_id'])) {
            $url .= '&module_id=' . $this->request->get['module_id'];
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
            'text'      => $this->language->get('text_blog'),
            'href'      => $this->url->link('extension/module/ocblog', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('blog/articlelist/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['copy'] = $this->url->link('blog/articlelist/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('blog/articlelist/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['articles_list'] = array();

        $filter_data = array(
            'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'           => $this->config->get('config_limit_admin')
        );

        $article_list_total = $this->model_blog_articlelist->getTotalArticlesList();

        $results = $this->model_blog_articlelist->getAllArticlesList($filter_data);

        foreach ($results as $result) {
            $data['articles_list'][] = array(
                'article_list_id' => $result['article_list_id'],
                'name'       => $result['name'],
                'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit'       => $this->url->link('blog/articlelist/edit', 'user_token=' . $this->session->data['user_token'] . '&article_list_id=' . $result['article_list_id'] . $url, true)
            );
        }

        $data['user_token'] = $this->session->data['user_token'];

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

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $pagination = new Pagination();
        $pagination->total = $article_list_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($article_list_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($article_list_total - $this->config->get('config_limit_admin'))) ? $article_list_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $article_list_total, ceil($article_list_total / $this->config->get('config_limit_admin')));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('blog/articlelist_list', $data));
    }

    public function getForm() {
        $data['text_form'] = !isset($this->request->get['article_list_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['module_id'])) {
            $url .= '&module_id=' . $this->request->get['module_id'];
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
            'text'      => $this->language->get('text_blog'),
            'href'      => $this->url->link('extension/module/ocblog', 'user_token=' . $this->session->data['user_token'] . $url, true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['article_list_id'])) {
            $data['action'] = $this->url->link('blog/articlelist/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('blog/articlelist/edit', 'user_token=' . $this->session->data['user_token'] . '&article_list_id=' . $this->request->get['article_list_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('blog/articlelist', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['article_list_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $article_list_info = $this->model_blog_articlelist->getArticleList($this->request->get['article_list_id']);
            $article_list_info['article'] = $this->model_blog_articlelist->getArticleToList($this->request->get['article_list_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($article_list_info)) {
            $data['status'] = $article_list_info['status'];
        } else {
            $data['status'] = true;
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($article_list_info)) {
            $data['name'] = $article_list_info['name'];
        } else {
            $data['name'] = '';
        }

        $this->load->model('blog/article');

        $data['articles'] = array();

        if (isset($this->request->post['article'])) {
            $articles = $this->request->post['article'];
        } elseif (!empty($article_list_info)) {
            $articles = $article_list_info['article'];
        } else {
            $articles = array();
        }

        foreach ($articles as $article) {
            $article_info = $this->model_blog_article->getArticle($article['article_id']);

            if ($article_info) {
                $data['articles'][] = array(
                    'article_id' => $article_info['article_id'],
                    'name'       => $article_info['name']
                );
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('blog/articlelist_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'blog/articlelist')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'blog/articlelist')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function validateCopy() {
        if (!$this->user->hasPermission('modify', 'blog/articlelist')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}