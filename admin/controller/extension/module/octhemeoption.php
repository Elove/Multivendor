<?php
class ControllerExtensionModuleOcthemeoption extends Controller
{
    private $error = array();

    public function install() {
        $config = array(
            'module_octhemeoption_status' => 1,
            'module_octhemeoption_catalog' => 1,
            'module_octhemeoption_rotator' => 1,
            'module_octhemeoption_quickview' => 1
        );

        $this->load->model('setting/setting');
        $this->load->model('extension/module/octhemeoption');

        $this->model_extension_module_octhemeoption->createThemeTables();
        $this->model_setting_setting->editSetting('module_octhemeoption', $config);
    }

    public function index() {
        $this->load->language('extension/module/octhemeoption');

        $this->document->setTitle($this->language->get('page_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_octhemeoption', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/octhemeoption', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['database'] = array(
            DIR_APPLICATION . '../database/opencart_db1.sql' => 'Home Page 1',
            DIR_APPLICATION . '../database/opencart_db2.sql' => 'Home Page 2',
            DIR_APPLICATION . '../database/opencart_db3.sql' => 'Home Page 3',
            DIR_APPLICATION . '../database/opencart_db4.sql' => 'Home Page 4',
            DIR_APPLICATION . '../database/opencart_db5.sql' => 'Home Page 5',
            DIR_APPLICATION . '../database/opencart_db6.sql' => 'Home Page 6'
        );

        $this->load->model('setting/store');
        
        $data['stores'] = array();

        $data['stores'][] = array(
            'store_id' => 0,
            'name'     => $this->config->get('config_name') . $this->language->get('text_default')
        );

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $data['stores'][] = array(
                'store_id' => $store['store_id'],
                'name'     => $store['name']
            );
        }

        if(isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = false;
        }

        if(isset($this->session->data['error_load_file'])) {
            $data['error_load_file'] = $this->session->data['error_load_file'];

            unset($this->session->data['error_load_file']);
        } else {
            $data['error_load_file'] = false;
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
            'href' => $this->url->link('extension/module/octhemeoption', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['user_token'] = $this->session->data['user_token'];

        $data['action'] = $this->url->link('extension/module/octhemeoption', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_import'] = $this->url->link('extension/module/octhemeoption/import', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_octhemeoption_status'])) {
            $data['module_octhemeoption_status'] = $this->request->post['module_octhemeoption_status'];
        } else {
            $data['module_octhemeoption_status'] = $this->config->get('module_octhemeoption_status');
        }

        if (isset($this->request->post['module_octhemeoption_a_tag'])) {
            $data['module_octhemeoption_a_tag'] = $this->request->post['module_octhemeoption_a_tag'];
        } else {
            $data['module_octhemeoption_a_tag'] = $this->config->get('module_octhemeoption_a_tag');
        }

        if (isset($this->request->post['module_octhemeoption_header_tag'])) {
            $data['module_octhemeoption_header_tag'] = $this->request->post['module_octhemeoption_header_tag'];
        } else {
            $data['module_octhemeoption_header_tag'] = $this->config->get('module_octhemeoption_header_tag');
        }

        if (isset($this->request->post['module_octhemeoption_body'])) {
            $data['module_octhemeoption_body'] = $this->request->post['module_octhemeoption_body'];
        } else {
            $data['module_octhemeoption_body'] = $this->config->get('module_octhemeoption_body');
        }

        if (isset($this->request->post['module_octhemeoption_catalog'])) {
            $data['module_octhemeoption_catalog'] = $this->request->post['module_octhemeoption_catalog'];
        } else {
            $data['module_octhemeoption_catalog'] = $this->config->get('module_octhemeoption_catalog');
        }

        if (isset($this->request->post['module_octhemeoption_rotator'])) {
            $data['module_octhemeoption_rotator'] = $this->request->post['module_octhemeoption_rotator'];
        } else {
            $data['module_octhemeoption_rotator'] = $this->config->get('module_octhemeoption_rotator');
        }

        if (isset($this->request->post['module_octhemeoption_quickview'])) {
            $data['module_octhemeoption_quickview'] = $this->request->post['module_octhemeoption_quickview'];
        } else {
            $data['module_octhemeoption_quickview'] = $this->config->get('module_octhemeoption_quickview');
        }

        if (isset($this->request->post['module_octhemeoption_loader_img'])) {
            $data['module_octhemeoption_loader_img'] = $this->request->post['module_octhemeoption_loader_img'];
        } else {
            $data['module_octhemeoption_loader_img'] = $this->config->get('module_octhemeoption_loader_img');
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['module_octhemeoption_loader_img']) && is_file(DIR_IMAGE . $this->request->post['module_octhemeoption_loader_img'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['module_octhemeoption_loader_img'], 50, 50);
        } elseif (is_file(DIR_IMAGE . $this->config->get('module_octhemeoption_loader_img'))) {
            $data['thumb'] = $this->model_tool_image->resize($this->config->get('module_octhemeoption_loader_img'), 50, 50);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 50, 50);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 50, 50);

        $this->document->addScript('view/javascript/octhemeoption/jscolor.js');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/octhemeoption', $data));
    }

    public function import() {
        $this->load->language('extension/module/octhemeoption');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['file'])) {
            $file = $this->request->post['file'];
        } else {
            $file = '';
        }

        if (!file_exists($file)) {
            unset($this->session->data['success']);

            $this->session->data['error_load_file'] = sprintf($this->language->get('error_load_file'), $file);

            $this->response->redirect($this->url->link('extension/module/octhemeoption', 'user_token=' . $this->session->data['user_token'], true));
        } else {
            unset($this->session->data['error_load_file']);

            $lines = file($file);

            if($lines) {
                $sql = '';

                foreach($lines as $line) {
                    if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
                        $sql .= $line;

                        if (preg_match('/;\s*$/', $line)) {
                            $sql = str_replace("DROP TABLE IF EXISTS `oc_", "DROP TABLE IF EXISTS `" . DB_PREFIX, $sql);
                            $sql = str_replace("CREATE TABLE `oc_", "CREATE TABLE `" . DB_PREFIX, $sql);
                            $sql = str_replace("CREATE TABLE IF NOT EXISTS `oc_", "CREATE TABLE `" . DB_PREFIX, $sql);
                            $sql = str_replace("INSERT INTO `oc_", "INSERT INTO `" . DB_PREFIX, $sql);
                            $sql = str_replace("UPDATE `oc_", "UPDATE `" . DB_PREFIX, $sql);
                            $sql = str_replace("WHERE `oc_", "WHERE `" . DB_PREFIX, $sql);
                            $sql = str_replace("TRUNCATE TABLE `oc_", "TRUNCATE TABLE `" . DB_PREFIX, $sql);
                            $sql = str_replace("ALTER TABLE `oc_", "ALTER TABLE `" . DB_PREFIX, $sql);

                            $this->db->query($sql);

                            $sql = '';
                        }
                    }
                }
            }

            $this->session->data['success'] = $this->language->get('text_import_success');

            $this->response->redirect($this->url->link('extension/module/octhemeoption', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/octhemeoption')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}