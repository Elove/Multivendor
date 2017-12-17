<?php
class ModelVermegamenuMenu extends Model {
	private $spacer_size = '5';
	private $_html = '';
	private $pattern = '/^([A-Z_]*)[0-9]+/';

	public function getCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}
	
	public  function getCategoryOption($id_category = 0, $id_lang = false, $id_shop = false, $recursive = true) {
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');
			$cateActives = explode(',',$this->config->get('h_active_cate'));
			$category = $this->model_catalog_category->getCategory($id_category);
			
			if ($recursive)
			{ 
				$children = $this->getCategories($id_category);		
				//echo "<pre>"; print_r($category); 
				if(isset($category['top'])) {
				if($category['top'] ==0) $category['top'] = 2;
					$spacer = str_repeat('&nbsp;', $this->spacer_size * (int) $category['top']);
				}
			}	
			if(isset($category['category_id']) )
				$this->_html .= '<option value="CAT'.(int)$category["category_id"].'">'.(isset($spacer) ? $spacer : '').$category["name"].' </option>';

		  if (isset($children) && count($children))
				foreach ($children as $child)
					$this->getCategoryOption((int)$child['category_id']);
					
			return $this->_html;		
		
	}
	
	public function getMenuOptions() {
		$data = $this->getMenuItems(); 
		$lang_id = (int)$this->config->get('config_language_id');
		$this->load->model('catalog/category');
		$this->_html = "";
		foreach ($data as $item)
		{
			if (!$item)
				continue;

			preg_match($this->pattern, $item, $values);
			$id = (int)substr($item, strlen($values[1]), strlen($item));

			switch (substr($item, 0, strlen($values[1])))
			{
				case 'CAT':
			
					$category = $this->model_catalog_category->getCategory($id);
//					echo "<pre>"; print_r($category); die;
					if ($category)
						$this->_html .= '<option value="CAT'.$id.'">'.$category['name'].'</option>'.PHP_EOL;
					break;
				case 'LINK':	
					$this->load->model('cmsblock/info');
					
					$info = $this->model_cmsblock_info->getcmsblockDescriptions($id);
					if($info) 
					$this->_html .= '<option value="LINK'.$id.'">'.$info[$lang_id]['title'].'</option>'.PHP_EOL;
					
				break;
				case 'CMS':	
					$this->load->model('catalog/information');
					
					$info = $this->model_catalog_information->getInformationDescriptions($id);
					//echo "<pre>"; print_r($info); die;  
					if($info) 
					$this->_html .= '<option value="CMS'.$id.'">'.$info[$lang_id]['title'].'</option>'.PHP_EOL;
					
				break;
			}
		}
		return $this->_html ;

	}
	
	private function getMenuItems(){
	//	$this->getInfomatinOptions();
		$module_info = array();
		if (isset($this->request->get['module_id'])) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}else {
			
			$module_info['h_active_cate'] = 'CMS3,CAT20,CAT18,CAT25,CAT57';
		}
	
		
		return explode(',',$module_info['h_active_cate']);
	}
	
	
	public function getInfomatinOptions()
	{
		$this->_html = ""; 
		$this->load->model('catalog/information');
		$data = array(
			'sort'=> 'asc'
		);
		$results = $this->model_catalog_information->getInformations($data);
		$infomations = array();
		$url = null;
    	foreach ($results as $result) {
			$action = array();						
			$this->_html .= '<option value="CMS'.$result['information_id'].'">'. $result['title'].'</option>'.PHP_EOL;	
		}
		//echo "<pre>"; print_r($infomations); die;
		return $this->_html; 
				
	
	}
	
	public function getTopLinks() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cmsblock i LEFT JOIN " . DB_PREFIX . "cmsblock_description id ON (i.cmsblock_id = id.cmsblock_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.type = 0  ORDER BY i.sort_order, LCASE(id.title) ASC");
		return $query->rows;
	}

	public function getLinkOptions()
	{
		$this->_html = "";
		$data = array(
			'sort'=> 'asc'
		);
		$results = $this->getTopLinks();
		$url = null;
		foreach ($results as $result) {
			$action = array();
			$this->_html .= '<option value="LINK'.$result['cmsblock_id'].'">'. $result['title'].'</option>'.PHP_EOL;
		}

		return $this->_html;


	}
}