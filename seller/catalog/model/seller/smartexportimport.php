<?php
static $registry = NULL;
class ModelSellerSmartExportImport extends Model {
	private $error = array();
	private $keyids = array(
			'product' => array(),
			'filtergroup' => array() 
	);
	function clean( &$str, $allowBlanks=FALSE ) {
		$result = "";
		$n = strlen( $str );
		for ($m=0; $m<$n; $m++) {
			$ch = substr( $str, $m, 1 );
			if (($ch==" ") && (!$allowBlanks) || ($ch=="\n") || ($ch=="\r") || ($ch=="\t") || ($ch=="\0") || ($ch=="\x0B")) {
				continue;
			}
			$result .= $ch;
		}
		return $result;
	}
	function multiquery( &$database, $sql ) {
		foreach (explode(";\n", $sql) as $sql) {
			$sql = trim($sql);
			if ($sql) {
				$database->query($sql);
			}
		}
	}
	protected function getDefaultLanguageId( &$database ) {
		$code = $this->config->get('config_language');
		$sql = "SELECT language_id FROM `".DB_PREFIX."language` WHERE code = '$code'";
		$result = $database->query( $sql );
		$languageId = 1;
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$languageId = $row['language_id'];
				break;
			}
		}
		return $languageId;
	}
	protected function getDefaultWeightUnit() {
		$weightUnit = $this->config->get( 'config_weight_class' );
		return $weightUnit;
	}
	protected function getDefaultMeasurementUnit() {
		$measurementUnit = $this->config->get( 'config_length_class' );
		return $measurementUnit;
	}
	function storeManufacturersIntoDatabase(&$database) {
		$sql = "SELECT `manufacturer_id`, `name` FROM `".DB_PREFIX."manufacturer`;";
		$result = $database->query( $sql );
		$manufacturerIds = array();
		if($result->rows){
			foreach($result->rows as $row) {
				$manufacturerId = $row['manufacturer_id'];
				$name = $row['name'];
				$manufacturerIds[$name] = $manufacturerId;
			}
		}
		return $manufacturerIds;
	}
	function getWeightClassIds( &$database ) {
		// find the default language id
		$languageId = $this->getDefaultLanguageId($database);
		// find all weight classes already stored in the database
		$weightClassIds = array();
		$sql = "SELECT `weight_class_id`, `unit` FROM `".DB_PREFIX."weight_class_description` WHERE `language_id`=$languageId;";
		$result = $database->query( $sql );
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$weightClassId = $row['weight_class_id'];
				$unit = $row['unit'];
				if (!isset($weightClassIds[$unit])) {
					$weightClassIds[$unit] = $weightClassId;
				}
			}
		}
		return $weightClassIds;
	}
	function getLengthClassIds( &$database ) {
		// find the default language id
		$languageId = $this->getDefaultLanguageId($database);
		// find all length classes already stored in the database
		$lengthClassIds = array();
		$sql = "SELECT `length_class_id`, `unit` FROM `".DB_PREFIX."length_class_description` WHERE `language_id`=$languageId;";
		$result = $database->query( $sql );
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$lengthClassId = $row['length_class_id'];
				$unit = $row['unit'];
				if (!isset($lengthClassIds[$unit])) {
					$lengthClassIds[$unit] = $lengthClassId;
				}
			}
		}
		return $lengthClassIds;
	}
	function getLayoutIds( &$database ) {
		$result = $database->query( "SELECT * FROM `".DB_PREFIX."layout`" );
		$layoutIds = array();
		foreach ($result->rows as $row) {
			$layoutIds[$row['name']] = $row['layout_id'];
		}
		return $layoutIds;
	}
	protected function getAvailableStoreIds( &$database ) {
		$sql = "SELECT store_id FROM `".DB_PREFIX."store`;";
		$result = $database->query( $sql );
		$storeIds = array(0);
		foreach ($result->rows as $row) {
			if (!in_array((int)$row['store_id'],$storeIds)) {
				$storeIds[] = (int)$row['store_id'];
			}
		}
		return $storeIds;
	}
	function getAvailableProductIds( &$database,$pname,$model ) {
	   $seller_id = $this->seller->getId();
		$sql = "SELECT p.product_id,p.model FROM `".DB_PREFIX."product` p";		
		$sql .= " WHERE p.model = '" . $model . "' "; 
		$result = $database->query( $sql );
		$productIds = array();
		foreach ($result->rows as $row) {
			$productIds[$row['product_id']] = $row['model'];
		}
		return $productIds;
	}
	function getAvailableCategoryIds( &$database ) {
		$sql = "SELECT `category_id` FROM `".DB_PREFIX."category`;";
		$result = $database->query( $sql );
		$categoryIds = array();
		foreach ($result->rows as $row) {
			$categoryIds[$row['category_id']] = $row['category_id'];
		}
		return $categoryIds;
	}
	function getAvailableFilterGroupIds( &$database ) {
		$sql = "SELECT `filter_group_id` FROM `".DB_PREFIX."filter_group`;";
		$result = $database->query( $sql );
		$filterGroupIds = array();
		foreach ($result->rows as $row) {
			$filterGroupIds[$row['filter_group_id']] = $row['filter_group_id'];
		}
		return $filterGroupIds;
	}
	function storeProductsIntoDatabase( &$database, &$products ) 
	{
		$languageId = 1;
		$layoutIds = $this->getLayoutIds( $database );
		$availableStoreIds = $this->getAvailableStoreIds( $database );
		$manufacturerIds = $this->storeManufacturersIntoDatabase($database);
		$weightClassIds = $this->getWeightClassIds( $database );
		$lengthClassIds = $this->getLengthClassIds( $database );
		$result = $this->db->query("SELECT p.product_id FROM `".DB_PREFIX."product` p");		
		$allproductIds = array();
		foreach ($result->rows as $row) {
			$allproductIds[$row['product_id']] = $row['product_id'];
		}
		$wrongmodels = array();
		$wrongmanufact = array();
		$newproducts = array();
		foreach ($products as $product) {
			if($product['product_id']>0){
			$productId = $product['product_id'];
			}else{$productId = 0;}
			$productName = $product['name'];
			$categories = $product['categories'];
			$quantity = $product['quantity'];
		    $model = $product['model'];
			$manufacturerName = $product['manufacturer'];
			$manufacturerId = 0;
			if(isset($manufacturerIds[$manufacturerName])){
				$manufacturerId = $manufacturerIds[$manufacturerName];
			}
			$shipping = 1;
			$price = trim($product['price']);
			$points = $product['points'];
			$weight = ($product['weight']=="") ? 0 : $product['weight'];
			$unit = $product['unit'];
			$weightClassId = (isset($weightClassIds[$unit])) ? $weightClassIds[$unit] : 0;
			$status = $product['status'];
			$status = ((strtoupper($status)=="TRUE") || (strtoupper($status)=="YES") || (strtoupper($status)=="ENABLED")) ? 1 : 0;
			$taxClassId = $product['tax_class_id'];
			$productDescription = $product['description'];
			$meta_title = $product['meta_title'];
			$meta_description = $product['meta_description'];
			$length = $product['length'];
			$width = $product['width'];
			$height = $product['height'];
			$keyword = $product['seo_keyword'];
			$lengthUnit = $product['measurement_unit'];
			$lengthClassId = (isset($lengthClassIds[$lengthUnit])) ? $lengthClassIds[$lengthUnit] : 0;
			$sku = $product['sku'];
			$upc = $product['upc'];
			$ean = $product['ean'];
			$jan = $product['jan'];
			$isbn = $product['isbn'];
			$mpn = $product['mpn'];
			$date_available = date('Y-m-d');
			$meta_keywords = $product['meta_keywords'];
			$tags = $product['tags'];
			$sqlm = $this->db->query("SELECT * FROM `".DB_PREFIX."product` where lower(model) = '".$this->db->escape($model)."'");
			$last_id = 0;
			if($sqlm->num_rows) {				   	   
				   $wrongmodels[]=$model;  
			}else{
				$seller_id = $this->seller->getId();
				$sqlp = $this->db->query("SELECT * FROM `".DB_PREFIX."product` where product_id = '".(int)$productId."'");
				if($sqlp->num_rows>0 || $productId==0) {
					$query = $this->db->query("SELECT foldername FROM " . DB_PREFIX . "sellers WHERE seller_id = '" . (int)$this->seller->getId() . "'");
					$foldername = $query->row['foldername'];
					$imageName	= $foldername.'/' .$product['image'];
					$this->db->query("INSERT INTO " . DB_PREFIX . "product SET quantity = '" . (int)$quantity . "',model='".$this->db->escape($model)."',sku = '" . $this->db->escape($sku) . "',minimum = '1', subtract = '1', upc = '" . $this->db->escape($upc) . "', ean = '" . $this->db->escape($ean) . "', jan = '" . $this->db->escape($jan) . "', isbn = '" . $this->db->escape($isbn) . "', mpn = '" . $this->db->escape($mpn) . "',shipping = '1', tax_class_id = '" . (int)$taxClassId . "',stock_status_id = '" . (int)$this->config->get('config_stock_status_id'). "',price='".(float)$price."',date_available = '" . $this->db->escape($date_available) . "',manufacturer_id = '" . (int)$manufacturerId . "',weight = '" . (float)$weight . "', weight_class_id = '" . (int)$weightClassId . "', points = '" . (int)$points . "',length = '" . (float)$length . "',width = '" . (float)$width . "',height = '" . (float)$height . "',length_class_id = '" . (int)$lengthClassId . "',date_added = NOW(),status = '" . (int)$status . "',image = '".$this->db->escape(html_entity_decode($imageName, ENT_QUOTES, 'UTF-8')) . "',seller_id='".(int)$this->seller->getId()."'");
					$last_id = $this->db->getLastId();
					$newproducts[$productId] = $last_id;
					$productId = $last_id;
				}else{
					$query = $this->db->query("SELECT foldername FROM " . DB_PREFIX . "sellers WHERE seller_id = '" . (int)$this->seller->getId() . "'");
					$foldername = $query->row['foldername'];
					$imageName = $foldername.'/' .$product['image'];
					$this->db->query("INSERT INTO " . DB_PREFIX . "product SET product_id = '" . (int)$productId . "',quantity = '" . (int)$quantity . "',model='".$this->db->escape($model)."',sku = '" . $this->db->escape($sku) . "',minimum = '1', subtract = '1', upc = '" . $this->db->escape($upc) . "', ean = '" . $this->db->escape($ean) . "', jan = '" . $this->db->escape($jan) . "', isbn = '" . $this->db->escape($isbn) . "', mpn = '" . $this->db->escape($mpn) . "',shipping = '1', tax_class_id = '" . (int)$taxClassId . "',stock_status_id = '" . (int)$this->config->get('config_stock_status_id'). "',price='".(float)$price."',date_available = '" . $this->db->escape($date_available) . "',manufacturer_id = '" . (int)$manufacturerId . "',weight = '" . (float)$weight . "', weight_class_id = '" . (int)$weightClassId . "', points = '" . (int)$points . "',length = '" . (float)$length . "',width = '" . (float)$width . "',height = '" . (float)$height . "',length_class_id = '" . (int)$lengthClassId . "',date_added = NOW(),status = '" . (int)$status . "',image = '".$this->db->escape(html_entity_decode($imageName, ENT_QUOTES, 'UTF-8')) . "',seller_id='".(int)$this->seller->getId()."'");			
				}
				$this->keyids['product'][] = $productId;
				if ($this->config->get('config_product_autoapprove')) {
				$this->db->query("UPDATE " . DB_PREFIX . "product SET approve = 1 WHERE product_id = '" . (int)$productId . "'");
				}
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$productId . "', language_id = '1', name = '".$this->db->escape($productName)."', description = '" . $this->db->escape($productDescription) . "', meta_title = '" . $this->db->escape($meta_title) . "', meta_description = '" . $this->db->escape($meta_description) . "', meta_keyword = '" . $this->db->escape($meta_keywords) . "', tag = '" . $this->db->escape($tags) . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "sellers_products SET price='".(float)$price."',seller_id='".(int)$this->seller->getId()."',product_id = '" . (int)$productId. "',quantity ='" . (int)$quantity. "',date_added = NOW()");
				$this->db->query("INSERT INTO " . DB_PREFIX . "seller SET vproduct_id = '" . (int)$productId . "', seller_id = '" . (int)$this->seller->getId() . "'");		
				if (count($categories) > 0) {
					foreach ($categories as $categoryId) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$productId . "', category_id = '" . (int)$categoryId . "'");
					}
				}
				// if($keyword){
				// 	$sqlk = $this->db->query("SELECT * FROM `".DB_PREFIX."url_alias` where lower(keyword) = '".$this->db->escape($keyword)."'");
				// 	if($sqlk->num_rows){}else{
				// 		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$productId . "', keyword = '".$this->db->escape($keyword)."'");
				// 	}
				// }
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$productId . "',store_id = '0'");
			}
		}
		if(count($newproducts)>0){$this->session->data['wrongproducts'] = $newproducts;}else{unset($this->session->data['wrongproducts']);}
		if(count($wrongmodels)>0){$this->session->data['wrongmodels'] = $wrongmodels;}else{unset($this->session->data['wrongmodels']);}
		return TRUE;
	}
	protected function detect_encoding( $str ) {
		// auto detect the character encoding of a string
		return mb_detect_encoding( $str, 'UTF-8,ISO-8859-15,ISO-8859-1,cp1251,KOI8-R' );
	}
	function uploadProducts( &$reader, &$database ) {
		$languageId = $this->getDefaultLanguageId($database);
		$defaultWeightUnit = $this->getDefaultWeightUnit();
		$defaultMeasurementUnit = $this->getDefaultMeasurementUnit();
		$defaultStockStatusId = $this->config->get('config_stock_status_id');
		$data = $reader->getSheet(0);
		$products = array();
		$product = array();
		$isFirstRow = TRUE;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			if ($productId=="") {
				continue;
			}
			$name = $this->getCell($data,$i,$j++);
			$categories = $this->getCell($data,$i,$j++);
			$sku = $this->getCell($data,$i,$j++,'');
			$upc = $this->getCell($data,$i,$j++,'');
			$ean = $this->getCell($data,$i,$j++,'');
			$jan = $this->getCell($data,$i,$j++,'');
			$isbn = $this->getCell($data,$i,$j++,'');
			$mpn = $this->getCell($data,$i,$j++,'');
			$quantity = $this->getCell($data,$i,$j++,'0');
			$model = $this->getCell($data,$i,$j++,'   ');
			$manufacturer = $this->getCell($data,$i,$j++);
			$imageName = $this->getCell($data,$i,$j++);
			$price = $this->getCell($data,$i,$j++,'0.00');
			$points = $this->getCell($data,$i,$j++,'0');
			$weight = $this->getCell($data,$i,$j++,'0');
			$unit = $this->getCell($data,$i,$j++,$defaultWeightUnit);
			$length = $this->getCell($data,$i,$j++,'0');
			$width = $this->getCell($data,$i,$j++,'0');
			$height = $this->getCell($data,$i,$j++,'0');
			$measurementUnit = $this->getCell($data,$i,$j++,$defaultMeasurementUnit);
			$status = $this->getCell($data,$i,$j++,'true');
			$taxClassId = $this->getCell($data,$i,$j++,'0');
			$keyword = $this->getCell($data,$i,$j++);
			$description = $this->getCell($data,$i,$j++);
			$meta_title = $this->getCell($data,$i,$j++);
			$meta_description = $this->getCell($data,$i,$j++);
			$meta_keywords = $this->getCell($data,$i,$j++);
			$tags = $this->getCell($data,$i,$j++);
			$product = array();
			$product['product_id'] = $productId;
			$product['name'] = $name;
			$product['categories'] = array();	
			if($categories !=""){
				 $abc = explode( ",", $categories);
			     foreach($abc as $res){	 
				    $name = htmlspecialchars($res);
					$query = $this->db->query("SELECT DISTINCT *  FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE cd2.name = '" . $this->db->escape($name) . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");	
				    if($query->num_rows){
					      $product['categories'][]=$query->row['category_id'];
					}
				 }
			}
			$product['quantity'] = $quantity;
			$product['model'] = $model;
			$product['manufacturer'] = $manufacturer;
			$product['image'] = $imageName;
			$product['price'] = $price;
			$product['points'] = $points;
			$product['weight'] = $weight;
			$product['unit'] = $unit;
			$product['status'] = $status;
			$product['tax_class_id'] = $taxClassId;
			$product['description'] = $description;
			$product['meta_title'] = $meta_title;
			$product['stock_status_id'] = $defaultStockStatusId;
			$product['meta_description'] = $meta_description;
			$product['length'] = $length;
			$product['width'] = $width;
			$product['height'] = $height;
			$product['seo_keyword'] = $keyword;
			$product['measurement_unit'] = $measurementUnit;
			$product['sku'] = $sku;
			$product['upc'] = $upc;
			$product['ean'] = $ean;
			$product['jan'] = $jan;
			$product['isbn'] = $isbn;
			$product['mpn'] = $mpn;
			$product['meta_keywords'] = $meta_keywords;
			$product['tags'] = $tags;
			$products[$productId] = $product;
		}
		return $this->storeProductsIntoDatabase( $database, $products );
	}
	function storeCategoriesIntoDatabase( &$database, &$categories ) 
	{
		// find the default language id
		$languageId = $this->getDefaultLanguageId($database);
		// start transaction, remove categories
		$sql = "START TRANSACTION;\n";
		//$sql .= "DELETE FROM `".DB_PREFIX."category`;\n";
		//$sql .= "DELETE FROM `".DB_PREFIX."category_description` WHERE language_id=$languageId;\n";
		//$sql .= "DELETE FROM `".DB_PREFIX."category_to_store`;\n";
		//$sql .= "DELETE FROM `".DB_PREFIX."url_alias` WHERE `query` LIKE 'category_id=%';\n";
		//$sql .= "DELETE FROM `".DB_PREFIX."category_to_layout`;\n";
		//$sql .= "DELETE FROM `".DB_PREFIX."category_path`;\n";
		$this->multiquery( $database, $sql );
		// get pre-defined layouts
		$layoutIds = $this->getLayoutIds( $database );
		// get pre-defined store_ids
		$availableStoreIds = $this->getAvailableStoreIds( $database );
		// generate and execute SQL for inserting the categories
		foreach ($categories as $category) {
			$categoryId = $category['category_id'];
			$imageName = $category['image'];
			$parentId = $category['parent_id'];
			$filters = $category['filters'];
			$top = $category['top'];
			$top = ((strtoupper($top)=="TRUE") || (strtoupper($top)=="YES") || (strtoupper($top)=="ENABLED")) ? 1 : 0;
			$columns = $category['columns'];
			$sortOrder = $category['sort_order'];
			$dateAdded = $category['date_added'];
			$dateModified = $category['date_modified'];
			$languageId = $category['language_id'];
			$name = $database->escape($category['name']);
			$description = $database->escape($category['description']);
			$meta_title = $database->escape($category['meta_title']);
			$meta_description = $database->escape($category['meta_description']);
			$meta_keywords = $database->escape($category['meta_keywords']);
			$keyword = $database->escape($category['seo_keyword']);
			$storeIds = $category['store_ids'];
			$layout = $category['layout'];
			$status = $category['status'];
			$status = ((strtoupper($status)=="TRUE") || (strtoupper($status)=="YES") || (strtoupper($status)=="ENABLED")) ? 1 : 0;
			if (count($filters) > 0) {
				$sql = "INSERT INTO `".DB_PREFIX."category_filter` (`category_id`,`filter_id`) VALUES ";
				$first = TRUE;
				foreach ($filters as $filterId) {
					$sql .= ($first) ? "\n" : ",\n";
					$first = FALSE;
					$sql .= "($categoryId,$filterId)";
				}
				$sql .= ";";
				$database->query($sql);
			}
			$sql2 = "INSERT INTO `".DB_PREFIX."category` (`category_id`, `image`, `parent_id`, `top`, `column`, `sort_order`, `date_added`, `date_modified`, `status`) VALUES ";
			$sql2 .= "( $categoryId, '$imageName', $parentId, $top, $columns, $sortOrder, ";
			$sql2 .= ($dateAdded=='NOW()') ? "$dateAdded," : "'$dateAdded',";
			$sql2 .= ($dateModified=='NOW()') ? "$dateModified," : "'$dateModified',";
			$sql2 .= " $status);";
			$database->query( $sql2 );
			$sql3 = "INSERT INTO `".DB_PREFIX."category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES ";
			$sql3 .= "( $categoryId, $languageId, '$name', '$description', '$meta_title', '$meta_description', '$meta_keywords' );";
			$database->query( $sql3 );
			// if ($keyword) {
			// 	$sql5 = "INSERT INTO `".DB_PREFIX."url_alias` (`query`,`keyword`) VALUES ('category_id=$categoryId','$keyword');";
			// 	$database->query($sql5);
			// }
			foreach ($storeIds as $storeId) {
				if (in_array((int)$storeId,$availableStoreIds)) {
					$sql6 = "INSERT INTO `".DB_PREFIX."category_to_store` (`category_id`,`store_id`) VALUES ($categoryId,$storeId);";
					$database->query($sql6);
				}
			}
			$layouts = array();
			foreach ($layout as $layoutPart) {
				$nextLayout = explode(':',$layoutPart);
				if ($nextLayout===FALSE) {
					$nextLayout = array( 0, $layoutPart );
				} else if (count($nextLayout)==1) {
					$nextLayout = array( 0, $layoutPart );
				}
				if ( (count($nextLayout)==2) && (in_array((int)$nextLayout[0],$availableStoreIds)) && (is_string($nextLayout[1])) ) {
					$storeId = (int)$nextLayout[0];
					$layoutName = $nextLayout[1];
					if (isset($layoutIds[$layoutName])) {
						$layoutId = (int)$layoutIds[$layoutName];
						if (!isset($layouts[$storeId])) {
							$layouts[$storeId] = $layoutId;
						}
					}
				}
			}
			foreach ($layouts as $storeId => $layoutId) {
				$sql7 = "INSERT INTO `".DB_PREFIX."category_to_layout` (`category_id`,`store_id`,`layout_id`) VALUES ($categoryId,$storeId,$layoutId);";
				$database->query($sql7);
			}
		}
		// restore category paths for faster lookups on the frontend
		$this->load->model( 'catalog/category' );
		$this->model_catalog_category->repairCategories(0);
		// final commit
		$database->query( "COMMIT;" );
		return TRUE;
	}
	function uploadCategories( &$reader, &$database ) 
	{
		// find the default language id
		$languageId = $this->getDefaultLanguageId($database);
		//取得产品的信息
		$availableCategoryIds = $this->getAvailableCategoryIds( $database );
		$data = $reader->getSheet(0);
		$categories = array();
		$isFirstRow = TRUE;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$categoryId = trim($this->getCell($data,$i,$j++));
			if ($categoryId=="") {
				continue;
			}
			$parentId = $this->getCell($data,$i,$j++,'0');
			$filters = $this->getCell($data,$i,$j++);
			$name = $this->getCell($data,$i,$j++);
			$name = htmlentities( $name, ENT_QUOTES, $this->detect_encoding($name) );
			$top = $this->getCell($data,$i,$j++,($parentId=='0')?'true':'false');
			$columns = $this->getCell($data,$i,$j++,($parentId=='0')?'1':'0');
			$sortOrder = $this->getCell($data,$i,$j++,'0');
			$imageName = trim($this->getCell($data,$i,$j++));
			$dateAdded = trim($this->getCell($data,$i,$j++));
			$dateAdded = ((is_string($dateAdded)) && (strlen($dateAdded)>0)) ? $dateAdded : "NOW()";
			$dateModified = trim($this->getCell($data,$i,$j++));
			$dateModified = ((is_string($dateModified)) && (strlen($dateModified)>0)) ? $dateModified : "NOW()";
			$langId = $this->getCell($data,$i,$j++,'1');
			if ($langId != $languageId) {
				continue;
			}
			$keyword = $this->getCell($data,$i,$j++);
			$description = $this->getCell($data,$i,$j++);
			$description = htmlentities( $description, ENT_QUOTES, $this->detect_encoding($description) );
			$meta_title = $this->getCell($data,$i,$j++);
			$meta_title = htmlentities( $meta_title, ENT_QUOTES, $this->detect_encoding($meta_title) );
			$meta_description = $this->getCell($data,$i,$j++);
			$meta_description = htmlentities( $meta_description, ENT_QUOTES, $this->detect_encoding($meta_description) );
			$meta_keywords = $this->getCell($data,$i,$j++);
			$meta_keywords = htmlentities( $meta_keywords, ENT_QUOTES, $this->detect_encoding($meta_keywords) );
			$storeIds = $this->getCell($data,$i,$j++);
			$layout = $this->getCell($data,$i,$j++,'');
			$status = $this->getCell($data,$i,$j++,'true');
			$category = array();
			$category['category_id'] = $categoryId;
			$category['image'] = $imageName;
			$category['parent_id'] = $parentId;
			$filters = trim( $this->clean($filters, FALSE) );
			$category['filters'] = ($filters=="") ? array() : explode( ",", $filters );
			if ($category['filters']===FALSE) {
				$category['filters'] = array();
			}
			$category['sort_order'] = $sortOrder;
			$category['date_added'] = $dateAdded;
			$category['date_modified'] = $dateModified;
			$category['language_id'] = $languageId;
			$category['name'] = $name;
			$category['top'] = $top;
			$category['columns'] = $columns;
			$category['description'] = $description;
			$category['meta_title'] = $meta_title;
			$category['meta_description'] = $meta_description;
			$category['meta_keywords'] = $meta_keywords;
			$category['seo_keyword'] = $keyword;
			$storeIds = trim( $this->clean($storeIds, FALSE) );
			$category['store_ids'] = ($storeIds=="") ? array() : explode( ",", $storeIds );
			if ($category['store_ids']===FALSE) {
				$category['store_ids'] = array();
			}
			$category['layout'] = ($layout=="") ? array() : explode( ",", $layout );
			if ($category['layout']===FALSE) {
				$category['layout'] = array();
			}
			$category['status'] = $status;
			$categories[$categoryId] = $category;
			//验证是否存在重复的分类
			if(isset($availableCategoryIds)){
				if (in_array((int)$categoryId,$availableCategoryIds)) {
						$sql = '';
						$sql .= "DELETE FROM `".DB_PREFIX."category` WHERE `category_id` = '$categoryId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."category_description` WHERE language_id=$languageId AND `category_id` = '$categoryId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."category_to_store` WHERE `category_id` = '$categoryId' ;\n";
						//$sql .= "DELETE FROM `".DB_PREFIX."url_alias` WHERE `query` LIKE 'category_id=$categoryId';\n";
						$sql .= "DELETE FROM `".DB_PREFIX."category_to_layout` WHERE `category_id` = '$categoryId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."category_path` WHERE `category_id` = '$categoryId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."category_filter` WHERE `category_id` = '$categoryId' ;\n";
						$this->multiquery( $database, $sql );
				}	
			}
		}
		return $this->storeCategoriesIntoDatabase( $database, $categories );
	}
	// add by mwb
	function storeFilterGroupsIntoDatabase( &$database, &$filtergroups )
	{
		$languageId = $this->getDefaultLanguageId($database);
		$filterGroupIds = array();    // indexed by [filter_group_id]
		$filterGroupDescIds = array();         // indexed by [filter_group_id][language_id]
		$sql = "SELECT `filter_group_id`,`language_id`  FROM `".DB_PREFIX."filter_group_description`";
		$result = $database->query( $sql );
		foreach ($result->rows as $row) {
			$filterGroupId = $row['filter_group_id'];
			if (!isset($filterGroupIds[$filterGroupId])) {
				$filterGroupIds[$filterGroupId] = $filterGroupId;
			}
			if (!isset($filterGroupDescIds[$filterGroupId])) {
				$filterGroupDescIds[$filterGroupId] = array();
			}
			if (!isset($filterGroupDescIds[$filterGroupId][$languageId])) {
				$filterGroupDescIds[$filterGroupId][$languageId] = $languageId;
			}
		}
		$sql = "START TRANSACTION;\n";
		$first = TRUE;
		$sql = "INSERT INTO `".DB_PREFIX."filter_group_description` (`filter_group_id`, `language_id`, `name`) VALUES "; 
		$sql2  = "INSERT INTO `".DB_PREFIX."filter_group` (`filter_group_id`, `sort_order`) VALUES "; 
		foreach ($filtergroups as $filtergroup) {
			//$productSpecialId += 1;
			$filterGroupId = $filtergroup['filter_group_id'];
			//$languageId = $filtergroup['language_id'];
			$name = $filtergroup['name'];
			$sortOrder = $filtergroup['sort_order'];
			$sql .= ($first) ? "\n" : ",\n";
			$sql .= "($filterGroupId, $languageId, '".$database->escape($name)."')";
			if (!isset($filterGroupIds[$filterGroupId])) {
				$sql2 .= ($first) ? "\n" : ",\n";
				$sql2 .= "($filterGroupId, $sortOrder)";
				$filterGroupIds[$filterGroupId] = $filterGroupId;
			}
			$first = FALSE;
		}
		if (!$first) {
			$database->query($sql);
			$database->query($sql2);
		}
		$database->query("COMMIT;");
		return TRUE;
	}
	function uploadFilterGroups( &$reader, &$database ) 
	{
		$data = $reader->getSheet(1);
		$filtergroups = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$filterGroupId = trim($this->getCell($data,$i,$j++));
			if ($filterGroupId=="") {
				continue;
			}
			$name = trim($this->getCell($data,$i,$j++));
			if ($name=="") {
				continue;
			}
			$sortOrder = $this->getCell($data,$i,$j++,'0');
			$filtergroups[$i] = array();
			$filtergroups[$i]['filter_group_id'] = $filterGroupId;
			$filtergroups[$i]['name'] = $name;
			$filtergroups[$i]['sort_order'] = $sortOrder;
			//验证是否存在重复的filter_group
			$availableFilterGroupIds = $this->getAvailableFilterGroupIds( $database );
			if(isset($availableFilterGroupIds)){
				if (in_array((int)$filterGroupId,$availableFilterGroupIds)) {
						$this->keyids['filtergroup'][] = $filterGroupId;
						$sql = '';
						$sql .= "DELETE FROM `".DB_PREFIX."filter_group` WHERE `filter_group_id` = '$filterGroupId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."filter_group_description` WHERE `filter_group_id` = '$filterGroupId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."filter` WHERE `filter_group_id` = '$filterGroupId' ;\n";
						$sql .= "DELETE FROM `".DB_PREFIX."filter_description` WHERE `filter_group_id` = '$filterGroupId' ;\n";
						$this->multiquery( $database, $sql );
				}	
			}
		}
		return $this->storeFilterGroupsIntoDatabase( $database, $filtergroups );
	}
	function storeFiltersIntoDatabase( &$database, &$filters )
	{
		$languageId = $this->getDefaultLanguageId($database);
		$filterIds = array();    // indexed by [filter_id]
		$filterDescIds = array();         // indexed by [filter_id][language_id]
		$sql = "SELECT `filter_id`,`language_id`  FROM `".DB_PREFIX."filter_description`";
		$result = $database->query( $sql );
		foreach ($result->rows as $row) {
			$filterId = $row['filter_id'];
			if (!isset($filterIds[$filterId])) {
				$filterIds[$filterId] = $filterId;
			}
			if (!isset($filterDescIds[$filterId])) {
				$filterDescIds[$filterId] = array();
			}
		}
		$sql = "START TRANSACTION;\n";
		$first = TRUE;
		$sql = "INSERT INTO `".DB_PREFIX."filter_description` (`filter_id`, `filter_group_id`, `language_id`, `name`) VALUES "; 
		$sql2  = "INSERT INTO `".DB_PREFIX."filter` (`filter_id`, `filter_group_id`, `sort_order`) VALUES "; 
		foreach ($filters as $filter) {
			$filterId = $filter['filter_id'];
			$filterGroupId = $filter['filter_group_id'];
			$name = $filter['name'];
			$sortOrder = $filter['sort_order'];
			$sql .= ($first) ? "\n" : ",\n";
			$sql .= "($filterId, $filterGroupId, $languageId, '".$database->escape($name)."')";
			if (!isset($filterIds[$filterId])) {
				$sql2 .= ($first) ? "\n" : ",\n";
				$sql2 .= "($filterId, $filterGroupId, $sortOrder)";
				$filterIds[$filterId] = $filterId;
			}
			$first = FALSE;
		}
		if (!$first) {
			$database->query($sql);
			$database->query($sql2);
		}
		$database->query("COMMIT;");
		return TRUE;
	}
	function uploadFilters( &$reader, &$database ) 
	{
		$data = $reader->getSheet(2);
		$filters = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$filterId = trim($this->getCell($data,$i,$j++));
			if ($filterId=="") {
				continue;
			}
			$filterGroupId = trim($this->getCell($data,$i,$j++));
			if ($filterGroupId=="" || !in_array($filterGroupId, $this->keyids['filtergroup'])) {
				continue;
			}
			$name = trim($this->getCell($data,$i,$j++));
			if ($name=="") {
				continue;
			}
			$sortOrder = $this->getCell($data,$i,$j++,'0');
			$filters[$i] = array();
			$filters[$i]['filter_id'] = $filterId;
			$filters[$i]['filter_group_id'] = $filterGroupId;
			$filters[$i]['name'] = $name;
			$filters[$i]['sort_order'] = $sortOrder;
		}
		return $this->storeFiltersIntoDatabase( $database, $filters );
	}
	// end by mwb
	function storeOptionsIntoDatabase( &$database, &$options ) 
	{
		$optionIds = array(); 
		$optionValueIds = array();
		$maxOptionSortOrder = 0;
		$maxOptionId = 0;
		$maxOptionValueId = 0;
		$sql  = "SELECT o.*, od.name, ovd.option_value_id, ovd.name AS value, ov.image FROM `".DB_PREFIX."option` o ";
		$sql .= "INNER JOIN `".DB_PREFIX."option_description` od ON od.option_id=o.option_id";
		$sql .= " LEFT JOIN `".DB_PREFIX."option_value` ov ON ov.option_id=o.option_id ";
		$sql .= "LEFT JOIN  `".DB_PREFIX."option_value_description` ovd ON ovd.option_value_id=ov.option_value_id WHERE (o.seller_id = '" . $this->seller->getId().  "' OR o.seller_id =0)";
		$result = $database->query( $sql );
		foreach ($result->rows as $row) {
			$name = $row['name'];
			$type = $row['type'];
			$value = (string)$row['value'];
			$optionId = $row['option_id'];
			$optionValueId = $row['option_value_id'];
			if(empty($row['image'])){
			$image='no_image.png';
			}else{
			$image = $row['image'];
			}
			$optionSortOrder = $row['sort_order'];
			if ($maxOptionId < $optionId) {
				$maxOptionId = $optionId;
			}
			if ($maxOptionValueId < $optionValueId) {
				$maxOptionValueId = $optionValueId;
			}
			if ($maxOptionSortOrder < $optionSortOrder) {
				$maxOptionSortOrder = $optionSortOrder;
			}
			if (!isset($optionIds[$name])) {
				$optionIds[$name] = array();
			}
			if (!isset($optionIds[$name][$type])) {
				$optionIds[$name][$type] = $optionId;
			}
			if (!isset($optionValueIds[$name])) {
				$optionValueIds[$name] = array();
			}
			if (!isset($optionValueIds[$name][$type])) {
				$optionValueIds[$name][$type] = array();
			}
			if (!isset($optionValueIds[$name][$type][$value])) {
				$optionValueIds[$name][$type][$value] = array();
			}
			if (!isset($optionValueIds[$name][$type][$value][$image])) {
				$optionValueIds[$name][$type][$value][$image] = $optionValueId;
			}
		}
		$productOptionIds = array();
		$maxProductOptionId = $this->getMaxproduct_option_id();
		$maxProductOptionValueId = $this->getMaxproduct_option_value_id();
		foreach ($options as $option) {
			$productId = $option['product_id'];
			$name = $option['option'];
			$type = $option['type'];
			$value = $option['value'];
			$image = $option['image'];
			$image1 = $option['image'];
			if(empty($image)){
				$image='no_image.png';
				$image1='no_image.jpg';
			}
			$required = $option['required'];
			$required = ((strtoupper($required)=="TRUE") || (strtoupper($required)=="YES") || (strtoupper($required)=="ENABLED")) ? 1 : 0;
			if (!isset($optionIds[$name])) {
				$optionIds[$name] = array();
			}
			if (($type=='select') || ($type=='checkbox') || ($type=='radio') || ($type=='image')) {
				if (!isset($optionValueIds[$name])) {
					$optionValueIds[$name] = array();
				}
				if (!isset($optionValueIds[$name][$type])) {
					$optionValueIds[$name][$type] = array();
				}
				if (!isset($optionValueIds[$name][$type][$value])) {
					$optionValueIds[$name][$type][$value] = array();
				}	
			}
			if (!isset($productOptionIds[$productId])) {
				$productOptionIds[$productId] = array();
			}
			if (isset($optionIds[$name][$type])) {
				$optionId = $optionIds[$name][$type];
				$seller_id=$this->seller->getId();
				if (in_array($productId, $this->keyids['product']))
				{
					if (!isset($productOptionIds[$productId][$optionId])) {
						$maxProductOptionId += 1;
						$productOptionId = $maxProductOptionId;
						$productOptionIds[$productId][$optionId] = $productOptionId;
						if (($type!='select') && ($type!='checkbox') && ($type!='radio') && ($type!='image')) {
							$productOptionValue = $value;
						} else {
							$productOptionValue = '';
						}
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$productOptionId . "',product_id = '" . (int)$productId . "',option_id = '" . (int)$optionId. "',required = '" . (int)$required . "',seller_id='".(int)$this->seller->getId()."'");
					}
					if (($type=='select') || ($type=='checkbox') || ($type=='radio') || ($type=='image'))
					{
						$quantity = $option['quantity'];
						$subtract = $option['subtract'];
						$subtract = ((strtoupper($subtract)=="TRUE") || (strtoupper($subtract)=="YES") || (strtoupper($subtract)=="ENABLED")) ? 1 : 0;
						$price = $option['price'];
						$pricePrefix = $option['price_prefix'];
						$points = $option['points'];
						$pointsPrefix = $option['points_prefix'];
						$weight = $option['weight'];
						$weightPrefix = $option['weight_prefix'];
						$sortOrder= $option['sort_order'];
						$maxProductOptionValueId += 1;
						$productOptionValueId = $maxProductOptionValueId;
						$optionId = $optionIds[$name][$type];
						$optionValueId = "";
						if(isset($optionValueIds[$name][$type][$value][$image])){
							$optionValueId = $optionValueIds[$name][$type][$value][$image];
						}elseif(isset($optionValueIds[$name][$type][$value][$image1])){
							$optionValueId = $optionValueIds[$name][$type][$value][$image1];
						}
						$productOptionId = $productOptionIds[$productId][$optionId];
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET seller_id='".(int)$this->seller->getId()."',product_option_value_id = '" . (int)$productOptionValueId . "',product_option_id = '" . (int)$productOptionId . "', product_id = '" . (int)$productId . "', option_id = '" . (int)$optionId . "', option_value_id = '" . (int)$optionValueId . "', quantity = '" . (int)$quantity . "', subtract = '" . (int)$subtract . "', price = '" . (float)$price . "', price_prefix = '" . $this->db->escape($pricePrefix) . "', points = '" . (int)$points . "', points_prefix = '" . $this->db->escape($pointsPrefix) . "', weight = '" . (float)$weight . "', weight_prefix = '" . $this->db->escape($weightPrefix) . "'");
					}
				}
			}
		}
		return TRUE;
	}
	function uploadOptions( &$reader, &$database ) 
	{
		$languageId = $this->getDefaultLanguageId($database);
		$data = $reader->getSheet(2);
		$options = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			$option = $this->getCell($data,$i,$j++);
			$type = $this->getCell($data,$i,$j++,'select');
			$value = $this->getCell($data,$i,$j++,'');
			$image = $this->getCell($data,$i,$j++,'');
			$required = $this->getCell($data,$i,$j++,'true');
			$quantity = $this->getCell($data,$i,$j++,'0');
			$subtract = $this->getCell($data,$i,$j++,'false');
			$price = $this->getCell($data,$i,$j++,'0');
			$pricePrefix = $this->getCell($data,$i,$j++,'+');
			$points = $this->getCell($data,$i,$j++,'0');
			$pointsPrefix = $this->getCell($data,$i,$j++,'+');
			$weight = $this->getCell($data,$i,$j++,'0.00');
			$weightPrefix = $this->getCell($data,$i,$j++,'+');
			$sortOrder = $this->getCell($data,$i,$j++,'0');
			$options[$i] = array();
			if(isset($this->session->data['wrongproducts'][$productId])){
				$productId = $this->session->data['wrongproducts'][$productId];
			}
			$options[$i]['product_id'] = $productId;
			$options[$i]['language_id'] = $languageId;
			$options[$i]['option'] = $option;
			$options[$i]['type'] = $type;
			$options[$i]['value'] = (string)$value;
			$options[$i]['image'] = $image;
			$options[$i]['required'] = $required;
			if (($type=='select') || ($type=='checkbox') || ($type=='radio') || ($type=='image')) {
				$options[$i]['quantity'] = $quantity;
				$options[$i]['subtract'] = $subtract;
				$options[$i]['price'] = $price;
				$options[$i]['price_prefix'] = $pricePrefix;
				$options[$i]['points'] = $points;
				$options[$i]['points_prefix'] = $pointsPrefix;
				$options[$i]['weight'] = $weight;
				$options[$i]['weight_prefix'] = $weightPrefix;
				$options[$i]['sort_order'] = $sortOrder;
			}
		}
		return $this->storeOptionsIntoDatabase( $database, $options );
	}
	function storeAttributesIntoDatabase( &$database, &$attributes ) {
		$attributeGroupIds = array();    // indexed by [group]
		$attributeIds = array();         // indexed by [group][name]
		$maxAttributeGroupSortOrder = 0;
		$maxAttributeGroupId = 0;
		$maxAttributeId = 0;
		$maxAttributeSortOrders = array(); // index by [group]
		$sql  = "SELECT ag.attribute_group_id, ag.sort_order AS group_sort_order, agd.name AS `group`, a.sort_order, ad.attribute_id, ad.name FROM `".DB_PREFIX."attribute_group` ag ";
		$sql .= "INNER JOIN `".DB_PREFIX."attribute_group_description` agd ON agd.attribute_group_id=ag.attribute_group_id";
		$sql .= " LEFT JOIN  `".DB_PREFIX."attribute` a ON a.attribute_group_id=ag.attribute_group_id ";
		$sql .= "INNER JOIN  `".DB_PREFIX."attribute_description` ad ON ad.attribute_id=a.attribute_id WHERE (a.seller_id = '" . $this->seller->getId().  "' OR a.seller_id =0)";
		$result = $database->query($sql);
		foreach ($result->rows as $row) {
			$attributeGroupId = $row['attribute_group_id'];
			$attributeId = $row['attribute_id'];
			$group = $row['group'];
			$name = $row['name'];
			$attributeGroupSortOrder = $row['group_sort_order'];
			$attributeSortOrder = $row['sort_order'];
			if ($maxAttributeGroupId < $attributeGroupId) {
				$maxAttributeGroupId = $attributeGroupId;
			}
			if ($maxAttributeId < $attributeId) {
				$maxAttributeId = $attributeId;
			}
			if ($maxAttributeGroupSortOrder < $attributeGroupSortOrder) {
				$maxAttributeGroupSortOrder = $attributeGroupSortOrder;
			}
			if (!isset($maxAttributeSortOrders[$group])) {
				$maxAttributeSortOrders[$group] = $attributeSortOrder;
			}
			if ($maxAttributeSortOrders[$group] < $attributeSortOrder) {
				$maxAttributeSortOrders[$group] = $attributeSortOrder;
			}
			if (!isset($attributeGroupIds[$group])) {
				$attributeGroupIds[$group] = $attributeGroupId;
			}
			if (!isset($attributeIds[$group])) {
				$attributeIds[$group] = array();
			}
			if (!isset($attributeIds[$group][$name])) {
				$attributeIds[$group][$name] = $attributeId;
			}
		}
		$seller_id=$this->seller->getId();
		// add product attributes to the database
		foreach ($attributes as $attribute) {
			$productId = $attribute['product_id'];
			$group = $attribute['group'];
			$name = $attribute['name'];
			$text = $attribute['text'];
			if (!isset($attributeIds[$group])) {
				$attributeIds[$group] = array();
			}
			if(isset($attributeIds[$group][$name])){
				$attributeId = $attributeIds[$group][$name];
				$language_id = 1;
				if (in_array($productId, $this->keyids['product'])) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$productId . "',attribute_id = '" . (int)$attributeId . "',language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($text) . "',seller_id='".(int)$this->seller->getId()."'");
				}	
			}	
		}
		return TRUE;
	}
	function uploadAttributes( &$reader, &$database ) 
	{
		$data = $reader->getSheet(3);
		$attributes = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			$group = trim($this->getCell($data,$i,$j++));
			if ($group=='') {
				continue;
			}
			$name = trim($this->getCell($data,$i,$j++));
			if ($name=='') {
				continue;
			}
			$text = $this->getCell($data,$i,$j++);
			$attributes[$i] = array();
			$attributes[$i]['product_id'] = $productId;
			if(isset($this->session->data['wrongproducts'][$productId])){
				$attributes[$i]['product_id'] = $this->session->data['wrongproducts'][$productId];
			}	
			$attributes[$i]['group'] = $group;
			$attributes[$i]['name'] = $name;
			$attributes[$i]['text'] = $text;
		}
		return $this->storeAttributesIntoDatabase( $database, $attributes );
	}
	function storeSpecialsIntoDatabase( &$database, &$specials )
	{
		$languageId = 1;
		foreach ($specials as $special) {
			$productId = $special['product_id'];
			$priority = $special['priority'];
			$price = $special['price'];
			$dateStart = $special['date_start'];
			$dateEnd = $special['date_end'];
			if (in_array($productId, $this->keyids['product'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET seller_id='".(int)$this->seller->getId()."',product_id = '" . (int)$productId . "', customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "', priority = '" . (int)$priority . "', price = '" . (float)$special['price'] . "', date_start = '" . $this->db->escape($special['date_start']) . "', date_end = '" . $this->db->escape($special['date_end']) . "'");
			}
		}
		return TRUE;
	}
	function uploadSpecials( &$reader, &$database ) 
	{
		$data = $reader->getSheet(4);
		$specials = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			$priority = $this->getCell($data,$i,$j++,'0');
			$price = $this->getCell($data,$i,$j++,'0');
			$dateStart = $this->getCell($data,$i,$j++,'0000-00-00');
			$dateEnd = $this->getCell($data,$i,$j++,'0000-00-00');
			$specials[$i] = array();
			$specials[$i]['product_id'] = $productId;
			if(isset($this->session->data['wrongproducts'][$productId])){
				$specials[$i]['product_id'] = $this->session->data['wrongproducts'][$productId];
			}
			$specials[$i]['priority'] = $priority;
			$specials[$i]['price'] = $price;
			$specials[$i]['date_start'] = $dateStart;
			$specials[$i]['date_end'] = $dateEnd;
		}
		return $this->storeSpecialsIntoDatabase( $database, $specials );
	}
	function storeDiscountsIntoDatabase( &$database, &$discounts )
	{
		foreach ($discounts as $discount) {
			$productId = $discount['product_id'];
			$quantity = $discount['quantity'];
			$priority = $discount['priority'];
			$price = $discount['price'];
			$dateStart = $discount['date_start'];
			$dateEnd = $discount['date_end'];
			if (in_array($productId, $this->keyids['product'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$productId . "',customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "',seller_id='".(int)$this->seller->getId()."',quantity = '" . (int)$discount['quantity'] . "', priority = '" . (int)$discount['priority'] . "', price = '" . (float)$discount['price'] . "', date_start = '" . $this->db->escape($discount['date_start']) . "', date_end = '" . $this->db->escape($discount['date_end']) . "'");
			}
		}
		return TRUE;
	}
	function uploadDiscounts( &$reader, &$database ) 
	{
		$data = $reader->getSheet(5);
		$discounts = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			$quantity = $this->getCell($data,$i,$j++,'0');
			$priority = $this->getCell($data,$i,$j++,'0');
			$price = $this->getCell($data,$i,$j++,'0');
			$dateStart = $this->getCell($data,$i,$j++,'0000-00-00');
			$dateEnd = $this->getCell($data,$i,$j++,'0000-00-00');
			$discounts[$i] = array();
			$discounts[$i]['product_id'] = $productId;
			if(isset($this->session->data['wrongproducts'][$productId])){
				$discounts[$i]['product_id'] = $this->session->data['wrongproducts'][$productId];
			}
			$discounts[$i]['quantity'] = $quantity;
			$discounts[$i]['priority'] = $priority;
			$discounts[$i]['price'] = $price;
			$discounts[$i]['date_start'] = $dateStart;
			$discounts[$i]['date_end'] = $dateEnd;
		}
		return $this->storeDiscountsIntoDatabase( $database, $discounts );
	}
	function storeRewardsIntoDatabase( &$database, &$rewards )
	{
		foreach ($rewards as $reward) {
			$productId = $reward['product_id'];
			$points = $reward['points'];
			if(in_array($productId, $this->keyids['product'])) {
				if ((int)$reward['points'] > 0) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$productId . "', customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "', points = '" . (int)$reward['points'] . "'");
				}
			}
		}
		return TRUE;
	}
	function uploadRewards( &$reader, &$database ) 
	{
		$data = $reader->getSheet(6);
		$rewards = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j= 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			$points = $this->getCell($data,$i,$j++,'0');
			$rewards[$i] = array();
			$rewards[$i]['product_id'] = $productId;
			if(isset($this->session->data['wrongproducts'][$productId])){
				$rewards[$i]['product_id'] = $this->session->data['wrongproducts'][$productId];
			}
			$rewards[$i]['points'] = $points;
		}
		return $this->storeRewardsIntoDatabase( $database, $rewards );
	}
	function storeAdditionalImagesIntoDatabase( &$database, &$images )
	{
		$query = $this->db->query("SELECT foldername FROM " . DB_PREFIX . "sellers WHERE seller_id = '" . (int)$this->seller->getId() . "'");
		$foldername = $query->row['foldername'];
		foreach ($images as $image) {
				$productId = $image['product_id'];	
				$imageName = $foldername.'/'.$image['image'];
				$sortOrder = $image['sort_order'];
				if(in_array($productId, $this->keyids['product'])) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$productId . "', image = '".$this->db->escape(html_entity_decode($imageName, ENT_QUOTES, 'UTF-8')) . "',sort_order = '".(int)$sortOrder."'");
				}
		}
		return TRUE;
	}
	function uploadAdditionalImages( &$reader, &$database ) 
	{
		$data = $reader->getSheet(1);
		$images = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			$j= 1;
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,$j++));
			if(isset($this->session->data['wrongproducts'][$productId])){
				$productId = $this->session->data['wrongproducts'][$productId];
			}
			$image = $this->getCell($data,$i,$j++,'');
			$sortOrder = $this->getCell($data,$i,$j++,'0');
			$images[$i] = array();
			$images[$i]['product_id'] = $productId;
			$images[$i]['image'] = $image;
			$images[$i]['sort_order'] = $sortOrder;
		}
		return $this->storeAdditionalImagesIntoDatabase( $database, $images );
	}
	function getCell(&$worksheet,$row,$col,$default_val='') {
		$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
		$row += 1; // we use 0-based, PHPExcel used 1-based row index
		return ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;
	}
	function validateHeading( &$data, &$expected ) {
		$heading = array();
		$k = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
		if ($k != count($expected)) {
			return FALSE;
		}
		$i = 0;
		for ($j=1; $j <= $k; $j+=1) {
			$heading[] = $this->getCell($data,$i,$j);
		}
		$valid = TRUE;
		for ($i=0; $i < count($expected); $i+=1) {
			if (!isset($heading[$i])) {
				$valid = FALSE;
				//echo "1";
				break;
			}
			if (strtolower($heading[$i]) != strtolower($expected[$i])) {
				$valid = FALSE;
				//echo strtolower($heading[$i]);
				//echo strtolower($expected[$i]);
				//echo "2";
				break;
			}
		}
		return $valid;
	}
	function validateCategories( &$reader )
	{
		$expectedCategoryHeading = array
		( "category_id", "parent_id", "filters", "name", "top", "columns", "sort_order", "image_name", "date_added", "date_modified", "language_id", "seo_keyword", "description", "meta_title", "meta_description", "meta_keywords", "store_ids", "layout", "status\nenabled" );
		$data =& $reader->getSheet(0);
		return $this->validateHeading( $data, $expectedCategoryHeading );
	}
	function validateFilterGroups( &$reader )
	{
		$expectedFilterGroupHeading = array
		( "filter_group_id",  "name", "sort_order" );
		$data =& $reader->getSheet(1);
		return $this->validateHeading( $data, $expectedFilterGroupHeading );
	}
	function validateFilters( &$reader )
	{
		$expectedFilterHeading = array
		( "filter_id", "filter_group_id",  "name", "sort_order" );
		$data =& $reader->getSheet(2);
		return $this->validateHeading( $data, $expectedFilterHeading );
	}
	function validateProducts( &$reader )
	{
		$expectedProductHeading = array
		( "product_id", "name", "categories", "sku", "upc", "ean", "jan", "isbn", "mpn","quantity", "model", "manufacturer", "image_name", "price", "points", "weight", "unit", "length", "width", "height", "lengthunit", "status", "tax_class_id", "seo_keyword", "description", "meta_title", "meta_description", "meta_keywords", "tags");
		$data =& $reader->getSheet(0);
		return $this->validateHeading( $data, $expectedProductHeading );
	}
	function validateAdditionalImages( &$reader )
	{
		$expectedAdditionalImagesHeading = array
		( "product_id", "image", "sort_order" );
		$data =& $reader->getSheet(1);
		return $this->validateHeading( $data, $expectedAdditionalImagesHeading );
	}
	function validateOptions( &$reader )
	{
		$expectedOptionHeading = array
		( "product_id", "option", "type", "value", "image", "required", "quantity", "subtract", "price", "price\nprefix", "points", "points\nprefix", "weight", "weight\nprefix", "sort_order" );
		$data =& $reader->getSheet(2);
		return $this->validateHeading( $data, $expectedOptionHeading );
	}
	function validateAttributes( &$reader )
	{
		$expectedAttributeHeading = array
		( "product_id", "attribute_group", "attribute_name", "text" );
		$data =& $reader->getSheet(3);
		return $this->validateHeading( $data, $expectedAttributeHeading );
	}
	function validateSpecials( &$reader )
	{
		$expectedSpecialsHeading = array
		( "product_id", "priority", "price", "date_start", "date_end" );
		$data =& $reader->getSheet(4);
		return $this->validateHeading( $data, $expectedSpecialsHeading );
	}
	function validateDiscounts( &$reader )
	{
		$expectedDiscountsHeading = array
		( "product_id", "quantity", "priority", "price", "date_start", "date_end" );
		$data =& $reader->getSheet(5);
		return $this->validateHeading( $data, $expectedDiscountsHeading );
	}
	function validateRewards( &$reader )
	{
		$expectedRewardsHeading = array
		( "product_id", "points" );
		$data =& $reader->getSheet(6);
		return $this->validateHeading( $data, $expectedRewardsHeading );
	}
	function validateUpload( &$reader )
	{
		if ($reader->getSheetCount() != 7) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get( 'error_sheet_count' )."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		}
		if (!$this->validateProducts( $reader )) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get('error_products_header')."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		} 
		if (!$this->validateOptions( $reader )) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get('error_options_header')."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		} 
		if (!$this->validateAttributes( $reader )) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get('error_attributes_header')."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		} 
		if (!$this->validateSpecials( $reader )) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get('error_specials_header')."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		}
		if (!$this->validateDiscounts( $reader )) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get('error_discounts_header')."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		}
		if (!$this->validateRewards( $reader )) {
			error_log(date('Y-m-d H:i:s - ', time()).$this->language->get('error_rewards_header')."\n",3,DIR_LOGS."error.txt");
			return FALSE;
		}
		return TRUE;
	}
	function clearCache() {
		$this->cache->delete('*');
	}
	function upload( $filename ) {
		// we use our own error handler
		global $registry;
		$registry = $this->registry;
		set_error_handler('error_handler_for_export',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export');
		try {
			$database =& $this->db;
			$this->session->data['export_nochange'] = 1;
			// we use the PHPExcel package from http://phpexcel.codeplex.com/
			$cwd = getcwd();
			chdir('PHPExcel' );
			require_once( 'Classes/PHPExcel.php' );
			chdir( $cwd );
			// parse uploaded spreadsheet file
			$inputFileType = PHPExcel_IOFactory::identify($filename);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);
			// read the various worksheets and load them to the database
			$ok = $this->validateUpload( $reader );
			if (!$ok) {
				return FALSE;
			}
			$this->clearCache();
			$this->session->data['export_nochange'] = 0;
			unset($this->session->data['wrongproducts']);
			$ok = $this->uploadProducts( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			$ok = $this->uploadAdditionalImages( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			$ok = $this->uploadOptions( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			$ok = $this->uploadAttributes( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			$ok = $this->uploadSpecials( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			$ok = $this->uploadDiscounts( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			$ok = $this->uploadRewards( $reader, $database );
			if (!$ok) {
				return FALSE;
			}
			return $ok;
		} catch (Exception $e) {
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['export_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return FALSE;
		}
	}
	function getStoreIdsForCategories( &$database ) {
		$sql =  "SELECT category_id, store_id FROM `".DB_PREFIX."category_to_store` cs;";
		$storeIds = array();
		$result = $database->query( $sql );
		foreach ($result->rows as $row) {
			$categoryId = $row['category_id'];
			$storeId = $row['store_id'];
			if (!isset($storeIds[$categoryId])) {
				$storeIds[$categoryId] = array();
			}
			if (!in_array($storeId,$storeIds[$categoryId])) {
				$storeIds[$categoryId][] = $storeId;
			}
		}
		return $storeIds;
	}
	function getLayoutsForCategories( &$database ) {
		$sql  = "SELECT cl.*, l.name FROM `".DB_PREFIX."category_to_layout` cl ";
		$sql .= "LEFT JOIN `".DB_PREFIX."layout` l ON cl.layout_id = l.layout_id ";
		$sql .= "ORDER BY cl.category_id, cl.store_id;";
		$result = $database->query( $sql );
		$layouts = array();
		foreach ($result->rows as $row) {
			$categoryId = $row['category_id'];
			$storeId = $row['store_id'];
			$name = $row['name'];
			if (!isset($layouts[$categoryId])) {
				$layouts[$categoryId] = array();
			}
			$layouts[$categoryId][$storeId] = $name;
		}
		return $layouts;
	}
	protected function setCell( &$worksheet, $row/*1-based*/, $col/*0-based*/, $val, $style=NULL ) {
		$worksheet->setCellValueByColumnAndRow( $col, $row, $val );
		if ($style) {
			$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray( $style );
		}
	}
	protected function getCategories( &$database, $languageId ) {
		$query  = "SELECT DISTINCT c.* , cd.*, ua.keyword, GROUP_CONCAT( DISTINCT CAST(cf.filter_id AS CHAR(11)) SEPARATOR \",\" ) AS filters FROM `".DB_PREFIX."category` c ";
		$query .= "INNER JOIN `".DB_PREFIX."category_description` cd ON cd.category_id = c.category_id ";
		$query .= " AND cd.language_id=$languageId ";
		//$query .= "LEFT JOIN `".DB_PREFIX."url_alias` ua ON ua.query=CONCAT('category_id=',c.category_id) ";
		$query .= "LEFT JOIN `".DB_PREFIX."category_filter` cf ON c.category_id=cf.category_id ";
		$query .= "GROUP BY c.`category_id`, cd.`language_id` ";
		$query .= "ORDER BY c.`parent_id`, `sort_order`, c.`category_id`, cf.`filter_id`;";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateCategoriesWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, &$textFormat ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('category_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('parent_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filters'),12)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('top'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('columns')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('sort_order')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image_name'),12)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_added'),19)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_modified'),19)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('language_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('seo_keyword'),16)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_title'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('store_ids'),16)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('layout'),16)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'),5)+1,$textFormat);
		// The heading row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'category_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'parent_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'filters', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'name', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'top', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'columns', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'sort_order', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'image_name', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_added', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_modified', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'language_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'seo_keyword', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'description', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'meta_title', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'meta_description', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'meta_keywords', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'store_ids', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'layout', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, "status\nenabled", $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual categories data
		$i += 1;
		$j = 0;
		$storeIds = $this->getStoreIdsForCategories( $database );
		$layouts = $this->getLayoutsForCategories( $database );
		$categories = $this->getCategories( $database, $languageId );
		foreach ($categories as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$this->setCell( $worksheet, $i, $j++, $row['category_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['parent_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['filters'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['name'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, ($row['top']==0) ? "false" : "true", $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['column'] );
			$this->setCell( $worksheet, $i, $j++, $row['sort_order'] );
			$this->setCell( $worksheet, $i, $j++, $row['image'] );
			$this->setCell( $worksheet, $i, $j++, $row['date_added'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_modified'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['language_id'] );
			$this->setCell( $worksheet, $i, $j++, ($row['keyword']) ? $row['keyword'] : '' );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['description'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['meta_title'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['meta_description'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['meta_keyword'],ENT_QUOTES,'UTF-8') );
			$storeIdList = '';
			$categoryId = $row['category_id'];
			if (isset($storeIds[$categoryId])) {
				foreach ($storeIds[$categoryId] as $storeId) {
					$storeIdList .= ($storeIdList=='') ? $storeId : ','.$storeId;
				}
			}
			$this->setCell( $worksheet, $i, $j++, $storeIdList, $textFormat );
			$layoutList = '';
			if (isset($layouts[$categoryId])) {
				foreach ($layouts[$categoryId] as $storeId => $name) {
					$layoutList .= ($layoutList=='') ? $storeId.':'.$name : ','.$storeId.':'.$name;
				}
			}
			$this->setCell( $worksheet, $i, $j++, $layoutList, $textFormat );
			$this->setCell( $worksheet, $i, $j++, ($row['status']==0) ? "false" : "true", $textFormat );
			$i += 1;
			$j = 0;
		}
	}
	protected function getFilterGroups( &$database, $languageId ) {
		$query  = "SELECT fg.* , fgd.* FROM `".DB_PREFIX."filter_group` fg ";
		$query .= "INNER JOIN `".DB_PREFIX."filter_group_description` fgd ON fg.filter_group_id = fgd.filter_group_id ";
		$query .= " AND fgd.language_id=$languageId ";
		$query .= "ORDER BY fg.`filter_group_id`;";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateFilterGroupWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, &$textFormat ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_group_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('sort_order')+1);
		// The heading row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'filter_group_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'name', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'sort_order', $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual categories data
		$i += 1;
		$j = 0;
		$filterGroups = $this->getFilterGroups( $database, $languageId );
		foreach ($filterGroups as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$this->setCell( $worksheet, $i, $j++, $row['filter_group_id'] );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['name'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, $row['sort_order'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function getFilters( &$database, $languageId ) {
		$query  = "SELECT f.sort_order , fd.* FROM `".DB_PREFIX."filter` f ";
		$query .= "INNER JOIN `".DB_PREFIX."filter_description` fd ON f.filter_id = fd.filter_id ";
		$query .= " AND fd.language_id=$languageId ";
		$query .= "ORDER BY f.`filter_id`;";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateFiltersWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, &$textFormat ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_group_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('sort_order')+1);
		// The heading row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'filter_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'filter_group_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'name', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'sort_order', $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual categories data
		$i += 1;
		$j = 0;
		$filterGroups = $this->getFilters( $database, $languageId );
		foreach ($filterGroups as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$this->setCell( $worksheet, $i, $j++, $row['filter_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['filter_group_id'] );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['name'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, $row['sort_order'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function getStoreIdsForProducts( &$database ) {
		$sql =  "SELECT product_id, store_id FROM `".DB_PREFIX."product_to_store` ps;";
		$storeIds = array();
		$result = $database->query( $sql );
		foreach ($result->rows as $row) {
			$productId = $row['product_id'];
			$storeId = $row['store_id'];
			if (!isset($storeIds[$productId])) {
				$storeIds[$productId] = array();
			}
			if (!in_array($storeId,$storeIds[$productId])) {
				$storeIds[$productId][] = $storeId;
			}
		}
		return $storeIds;
	}
	protected function getLayoutsForProducts( &$database ) {
		$sql  = "SELECT pl.*, l.name FROM `".DB_PREFIX."product_to_layout` pl ";
		$sql .= "LEFT JOIN `".DB_PREFIX."layout` l ON pl.layout_id = l.layout_id ";
		$sql .= "ORDER BY pl.product_id, pl.store_id;";
		$result = $database->query( $sql );
		$layouts = array();
		foreach ($result->rows as $row) {
			$productId = $row['product_id'];
			$storeId = $row['store_id'];
			$name = $row['name'];
			if (!isset($layouts[$productId])) {
				$layouts[$productId] = array();
			}
			$layouts[$productId][$storeId] = $name;
		}
		return $layouts;
	}
	protected function getProducts( &$database, $languageId, $offset=NULL, $rows=NULL, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT ";
		$query .= "  p.product_id,";
		$query .= "  pd.name,";
		$query .= "  GROUP_CONCAT( DISTINCT CAST(pc.category_id AS CHAR(11)) SEPARATOR \",\" ) AS categories,";
		$query .= "  GROUP_CONCAT( DISTINCT CAST(pf.filter_id AS CHAR(11)) SEPARATOR \",\" ) AS filters,";
		$query .= "  p.sku,";
		$query .= "  p.upc,";
		$query .= "  p.ean,";
		$query .= "  p.jan,";
		$query .= "  p.isbn,";
		$query .= "  p.mpn,";
		$query .= "  p.location,";
		$query .= "  p.quantity,";
		$query .= "  p.model,";
		$query .= "  m.name AS manufacturer,";
		$query .= "  p.image AS image_name,";
		$query .= "  p.shipping,";
		$query .= "  p.price,";
		$query .= "  p.points,";
		$query .= "  p.date_added,";
		$query .= "  p.date_modified,";
		$query .= "  p.date_available,";
		$query .= "  p.weight,";
		$query .= "  wc.unit,";
		$query .= "  p.length,";
		$query .= "  p.width,";
		$query .= "  p.height,";
		$query .= "  p.status,";
		$query .= "  p.tax_class_id,";
		$query .= "  p.viewed,";
		$query .= "  p.sort_order,";
		$query .= "  pd.language_id,";
		$query .= "  ua.keyword,";
		$query .= "  pd.description, ";
		$query .= "  pd.meta_title, ";
		$query .= "  pd.meta_description, ";
		$query .= "  pd.meta_keyword, ";
		$query .= "  pd.tag, ";
		$query .= "  p.stock_status_id, ";
		$query .= "  mc.unit AS length_unit, ";
		$query .= "  p.subtract, ";
		$query .= "  p.minimum, ";
		$query .= "  GROUP_CONCAT( DISTINCT CAST(pr.related_id AS CHAR(11)) SEPARATOR \",\" ) AS related ";
		$query .= "FROM `".DB_PREFIX."product` p ";
		$query .= "LEFT JOIN `".DB_PREFIX."product_description` pd ON p.product_id=pd.product_id ";
		$query .= "  AND pd.language_id=$languageId ";
		$query .= "LEFT JOIN `".DB_PREFIX."product_to_category` pc ON p.product_id=pc.product_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."product_filter` pf ON p.product_id=pf.product_id ";
		//$query .= "LEFT JOIN `".DB_PREFIX."url_alias` ua ON ua.query=CONCAT('product_id=',p.product_id) ";
		$query .= "LEFT JOIN `".DB_PREFIX."manufacturer` m ON m.manufacturer_id = p.manufacturer_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."weight_class_description` wc ON wc.weight_class_id = p.weight_class_id ";
		$query .= "  AND wc.language_id=$languageId ";
		$query .= "LEFT JOIN `".DB_PREFIX."length_class_description` mc ON mc.length_class_id=p.length_class_id ";
		$query .= "  AND mc.language_id=$languageId ";
		$query .= "LEFT JOIN `".DB_PREFIX."product_related` pr ON pr.product_id=p.product_id ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "WHERE p.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "GROUP BY p.product_id ";
		$query .= "ORDER BY p.product_id, pc.category_id, pf.filter_id ";
		if(isset($offset) && isset($rows)) {
			$query .= "LIMIT ".$offset.",".$rows."; ";
		} else {
			$query .= "; ";
		}
		$result = $database->query( $query );
		return $result->rows;
	}
	function populateProductsWorksheet( &$worksheet, &$database, $languageId, &$priceFormat, &$boxFormat, &$weightFormat, &$textFormat, $offset=NULL, $rows=NULL, &$minPid=NULL, &$maxPid=NULL)
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('categories'),12)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filters'),12)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sku'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('upc'),12)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('ean'),14)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('jan'),13)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('isbn'),13)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('mpn'),15)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('location'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('quantity'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('model'),8)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('manufacturer'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image_name'),12)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shipping'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$priceFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('points'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_added'),19)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_modified'),19)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_available'),10)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'),6)+1,$weightFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('unit'),3)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('length'),8)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('width'),8)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('height'),8)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('length'),3)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tax_class_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('viewed'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('language_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('seo_keyword'),16)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description'),32)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_title'),32)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description'),32)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords'),32)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('stock_status_id'),3)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('store_ids'),16)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('layout'),16)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('related_ids'),16)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tags'),32)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'),8)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('subtract'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('minimum'),8)+1);
		// The product headings row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'name', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'categories', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'filters', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'sku', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'upc', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'ean', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'jan', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'isbn', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'mpn', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'location', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'quantity', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'model', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'manufacturer', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'image_name', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, "requires\nshipping", $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'price', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'points', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_added', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_modified', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_available', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'weight', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'unit', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'length', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'width', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'height', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, "length\nunit", $boxFormat );
		$this->setCell( $worksheet, $i, $j++, "status\nenabled", $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'tax_class_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'viewed', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'language_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'seo_keyword', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'description', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'meta_title', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'meta_description', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'meta_keywords', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'stock_status_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'store_ids', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'layout', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'related_ids', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'tags', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'sort_order', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, "subtract", $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'minimum', $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual products data
		$i += 1;
		$j = 0;
		$storeIds = $this->getStoreIdsForProducts( $database );
		$layouts = $this->getLayoutsForProducts( $database );
		$products = $this->getProducts( $database, $languageId, $offset, $rows, $minPid, $maxPid );
		$len = count($products);
		$minPid = $products[0]['product_id'];
		$maxPid = $products[$len-1]['product_id'];
		foreach ($products as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$productId = $row['product_id'];
			$this->setCell( $worksheet, $i, $j++, $productId );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['name'],ENT_QUOTES,'UTF-8') );
			$this->setCell( $worksheet, $i, $j++, $row['categories'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['filters'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['sku'] );
			$this->setCell( $worksheet, $i, $j++, $row['upc'] );
			$this->setCell( $worksheet, $i, $j++, $row['ean'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['jan'] );
			$this->setCell( $worksheet, $i, $j++, $row['isbn'] );
			$this->setCell( $worksheet, $i, $j++, $row['mpn'] );
			$this->setCell( $worksheet, $i, $j++, $row['location'] );
			$this->setCell( $worksheet, $i, $j++, $row['quantity'] );
			$this->setCell( $worksheet, $i, $j++, $row['model'] );
			$this->setCell( $worksheet, $i, $j++, $row['manufacturer'] );
			$this->setCell( $worksheet, $i, $j++, $row['image_name'] );
			$this->setCell( $worksheet, $i, $j++, ($row['shipping']==0) ? "no" : "yes", $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['price'], $priceFormat );
			$this->setCell( $worksheet, $i, $j++, $row['points'] );
			$this->setCell( $worksheet, $i, $j++, $row['date_added'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_modified'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_available'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['weight'], $weightFormat );
			$this->setCell( $worksheet, $i, $j++, $row['unit'] );
			$this->setCell( $worksheet, $i, $j++, $row['length'] );
			$this->setCell( $worksheet, $i, $j++, $row['width'] );
			$this->setCell( $worksheet, $i, $j++, $row['height'] );
			$this->setCell( $worksheet, $i, $j++, $row['length_unit'] );
			$this->setCell( $worksheet, $i, $j++, ($row['status']==0) ? "false" : "true", $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['tax_class_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['viewed'] );
			$this->setCell( $worksheet, $i, $j++, $row['language_id'] );
			$this->setCell( $worksheet, $i, $j++, ($row['keyword']) ? $row['keyword'] : '' );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['description'],ENT_QUOTES,'UTF-8'), $textFormat, TRUE );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['meta_title'],ENT_QUOTES,'UTF-8'), $textFormat );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['meta_description'],ENT_QUOTES,'UTF-8'), $textFormat );
			$this->setCell( $worksheet, $i, $j++, html_entity_decode($row['meta_keyword'],ENT_QUOTES,'UTF-8'), $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['stock_status_id'] );
			$storeIdList = '';
			if (isset($storeIds[$productId])) {
				foreach ($storeIds[$productId] as $storeId) {
					$storeIdList .= ($storeIdList=='') ? $storeId : ','.$storeId;
				}
			}
			$this->setCell( $worksheet, $i, $j++, $storeIdList, $textFormat );
			$layoutList = '';
			if (isset($layouts[$productId])) {
				foreach ($layouts[$productId] as $storeId => $name) {
					$layoutList .= ($layoutList=='') ? $storeId.':'.$name : ','.$storeId.':'.$name;
				}
			}
			$this->setCell( $worksheet, $i, $j++, $layoutList, $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['related'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, ($row['tag']) ? $row['tag'] : '' );
			$this->setCell( $worksheet, $i, $j++, $row['sort_order'] );
			$this->setCell( $worksheet, $i, $j++, ($row['subtract']==0) ? "false" : "true", $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['minimum'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function getAdditionalImages( &$database, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT `product_id`, `image`, `sort_order` ";
		$query .= "FROM `".DB_PREFIX."product_image` pi ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "WHERE pi.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "ORDER BY pi.`product_id`, pi.`sort_order`, pi.`image`;";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateAdditionalImagesWorksheet( &$worksheet, &$database, &$boxFormat, $minPid=NULL, $maxPid=NULL )
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'),5)+1);
		// The additional images headings row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'image', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'sort_order', $boxFormat  );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual additional images data
		$i += 1;
		$j = 0;
		$addtionalImages = $this->getAdditionalImages( $database, $minPid, $maxPid );
		foreach ($addtionalImages as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$this->setCell( $worksheet, $i, $j++, $row['product_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['image'] );
			$this->setCell( $worksheet, $i, $j++, $row['sort_order'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function getOptions( &$database, $languageId, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT po.product_id,";
		$query .= "  po.option_id,";
		$query .= "  po.value AS default_value,";
		$query .= "  po.required,";
		$query .= "  pov.option_value_id,";
		$query .= "  pov.quantity,";
		$query .= "  pov.subtract,";
		$query .= "  pov.price,";
		$query .= "  pov.price_prefix,";
		$query .= "  pov.points,";
		$query .= "  pov.points_prefix,";
		$query .= "  pov.weight,";
		$query .= "  pov.weight_prefix,";
		$query .= "  ovd.name AS option_value,";
		$query .= "  ov.image,";
		$query .= "  ov.sort_order,";
		$query .= "  od.name AS option_name,";
		$query .= "  o.type ";
		$query .= "FROM `".DB_PREFIX."product_option` po ";
		$query .= "LEFT JOIN `".DB_PREFIX."option` o ON o.option_id=po.option_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."product_option_value` pov ON pov.product_option_id = po.product_option_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."option_value` ov ON ov.option_value_id=pov.option_value_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."option_value_description` ovd ON ovd.option_value_id=ov.option_value_id AND ovd.language_id=$languageId ";
		$query .= "LEFT JOIN `".DB_PREFIX."option_description` od ON od.option_id=o.option_id AND od.language_id=$languageId ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "WHERE po.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "ORDER BY po.product_id, po.option_id, pov.option_value_id;";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateOptionsWorksheet( &$worksheet, &$database, $languageId, &$priceFormat, &$boxFormat, &$weightFormat, $textFormat, $minPid=NULL, $maxPid=NULL )
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('language_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('type'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('value'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image'),12)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('required'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('quantity'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('subtract'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$priceFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('points'),10)+1,$priceFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('points'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'),10)+1,$priceFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'),5)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'),5)+1);
		// The options headings row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'language_id', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'option', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'type', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'value', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'image', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'required', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'quantity', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'subtract', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'price', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, "price\nprefix", $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'points', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, "points\nprefix", $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'weight', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, "weight\nprefix", $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'sort_order', $boxFormat  );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual options data
		$i += 1;
		$j = 0;
		$options = $this->getOptions( $database, $languageId, $minPid, $maxPid );
		foreach ($options as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$this->setCell( $worksheet, $i, $j++, $row['product_id'] );
			$this->setCell( $worksheet, $i, $j++, $languageId );
			$this->setCell( $worksheet, $i, $j++, $row['option_name'] );
			$this->setCell( $worksheet, $i, $j++, $row['type'] );
			$this->setCell( $worksheet, $i, $j++, ($row['default_value']) ? $row['default_value'] : $row['option_value'] );
			$this->setCell( $worksheet, $i, $j++, $row['image'] );
			$this->setCell( $worksheet, $i, $j++, ($row['required']==0) ? "false" : "true", $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['quantity'] );
			if (is_null($row['option_value_id'])) {
				$subtract = '';
			} else {
				$subtract = ($row['subtract']==0) ? "false" : "true";
			}
			$this->setCell( $worksheet, $i, $j++, $subtract, $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['price'], $priceFormat );
			$this->setCell( $worksheet, $i, $j++, $row['price_prefix'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['points'] );
			$this->setCell( $worksheet, $i, $j++, $row['points_prefix'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['weight'], $weightFormat );
			$this->setCell( $worksheet, $i, $j++, $row['weight_prefix'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['sort_order'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function getAttributes( &$database, $languageId, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT pa.*, ag.attribute_group_id, ag.sort_order AS attribute_group_sort_order, agd.name AS attribute_group, a.attribute_id, a.sort_order, ad.name AS attribute_name ";
		$query .= "FROM `".DB_PREFIX."product_attribute` pa ";
		$query .= "LEFT JOIN `".DB_PREFIX."attribute` a ON a.attribute_id=pa.attribute_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."attribute_description` ad ON ad.attribute_id=a.attribute_id AND ad.language_id=$languageId ";
		$query .= "INNER JOIN `".DB_PREFIX."attribute_group` ag ON ag.attribute_group_id=a.attribute_group_id ";
		$query .= "LEFT JOIN `".DB_PREFIX."attribute_group_description` agd ON agd.attribute_group_id=a.attribute_group_id AND agd.language_id=$languageId ";
		$query .= "WHERE pa.language_id=$languageId ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "AND pa.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "ORDER BY pa.product_id, attribute_group_sort_order, ag.attribute_group_id, a.sort_order, a.attribute_id;";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateAttributesWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, $textFormat, $minPid=NULL, $maxPid=NULL )
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('language_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_group'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_name'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('text'),30)+1);
		// The attributes headings row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'language_id', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'attribute_group', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'attribute_name', $boxFormat  );
		$this->setCell( $worksheet, $i, $j++, 'text', $boxFormat  );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual attributes data
		$i += 1;
		$j = 0;
		$attributes = $this->getAttributes( $database, $languageId, $minPid, $maxPid );
		foreach ($attributes as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$this->setCell( $worksheet, $i, $j++, $row['product_id'] );
			$this->setCell( $worksheet, $i, $j++, $languageId );
			$this->setCell( $worksheet, $i, $j++, $row['attribute_group'] );
			$this->setCell( $worksheet, $i, $j++, $row['attribute_name'] );
			$this->setCell( $worksheet, $i, $j++, $row['text'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function getSpecials( &$database, $languageId, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT ps.*, cgd.name FROM `".DB_PREFIX."product_special` ps ";
		$query .= "LEFT JOIN `".DB_PREFIX."customer_group_description` cgd ON cgd.customer_group_id=ps.customer_group_id ";
		$query .= "  AND cgd.language_id=$languageId ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "WHERE ps.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "ORDER BY ps.product_id, cgd.name";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateSpecialsWorksheet( &$worksheet, &$database, $languageId, &$priceFormat, &$boxFormat, &$textFormat, $minPid=NULL, $maxPid=NULL )
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('priority')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$priceFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_start'),19)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_end'),19)+1,$textFormat);
		// The heading row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'customer_group', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'priority', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'price', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_start', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_end', $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual product specials data
		$i += 1;
		$j = 0;
		$specials = $this->getSpecials( $database, $languageId, $minPid, $maxPid );
		foreach ($specials as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$this->setCell( $worksheet, $i, $j++, $row['product_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['name'] );
			$this->setCell( $worksheet, $i, $j++, $row['priority'] );
			$this->setCell( $worksheet, $i, $j++, $row['price'], $priceFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_start'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_end'], $textFormat );
			$i += 1;
			$j = 0;
		}
	}
	protected function getDiscounts( &$database, $languageId, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT pd.*, cgd.name FROM `".DB_PREFIX."product_discount` pd ";
		$query .= "LEFT JOIN `".DB_PREFIX."customer_group_description` cgd ON cgd.customer_group_id=pd.customer_group_id ";
		$query .= "  AND cgd.language_id=$languageId ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "WHERE pd.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "ORDER BY pd.product_id, cgd.name";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateDiscountsWorksheet( &$worksheet, &$database, $languageId, &$priceFormat, &$boxFormat, &$textFormat, $minPid=NULL, $maxPid=NULL )
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('quantity')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('priority')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$priceFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_start'),19)+1,$textFormat);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_end'),19)+1,$textFormat);
		// The heading row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'customer_group', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'quantity', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'priority', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'price', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_start', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'date_end', $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual product discounts data
		$i += 1;
		$j = 0;
		$discounts = $this->getDiscounts( $database, $languageId, $minPid, $maxPid );
		foreach ($discounts as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$this->setCell( $worksheet, $i, $j++, $row['product_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['name'] );
			$this->setCell( $worksheet, $i, $j++, $row['quantity'] );
			$this->setCell( $worksheet, $i, $j++, $row['priority'] );
			$this->setCell( $worksheet, $i, $j++, $row['price'], $priceFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_start'], $textFormat );
			$this->setCell( $worksheet, $i, $j++, $row['date_end'], $textFormat );
			$i += 1;
			$j = 0;
		}
	}
	protected function getRewards( &$database, $languageId, $minPid=NULL, $maxPid=NULL ) {
		$query  = "SELECT pr.*, cgd.name FROM `".DB_PREFIX."product_reward` pr ";
		$query .= "LEFT JOIN `".DB_PREFIX."customer_group_description` cgd ON cgd.customer_group_id=pr.customer_group_id ";
		$query .= "  AND cgd.language_id=$languageId ";
		if(isset($minPid) && isset($maxPid)) {
			$query .= "WHERE pr.product_id BETWEEN ".$minPid." AND ".$maxPid." ";
		}
		$query .= "ORDER BY pr.product_id, cgd.name";
		$result = $database->query( $query );
		return $result->rows;
	}
	protected function populateRewardsWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, $minPid=NULL, $maxPid=NULL )
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('points')+1);
		// The heading row
		$i = 1;
		$j = 0;
		$this->setCell( $worksheet, $i, $j++, 'product_id', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'customer_group', $boxFormat );
		$this->setCell( $worksheet, $i, $j++, 'points', $boxFormat );
		$worksheet->getRowDimension($i)->setRowHeight(30);
		// The actual product rewards data
		$i += 1;
		$j = 0;
		$rewards = $this->getRewards( $database, $languageId, $minPid, $maxPid );
		foreach ($rewards as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$this->setCell( $worksheet, $i, $j++, $row['product_id'] );
			$this->setCell( $worksheet, $i, $j++, $row['name'] );
			$this->setCell( $worksheet, $i, $j++, $row['points'] );
			$i += 1;
			$j = 0;
		}
	}
	protected function clearSpreadsheetCache() {
		$files = glob(DIR_CACHE . 'Spreadsheet_Excel_Writer' . '*');
		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					@unlink($file);
					clearstatcache();
				}
			}
		}
	}
	// 获取最大值 product_option_id
	 function getMaxproduct_option_id() {
	  	$query = $this->db->query( "SELECT max(product_option_id) as Maxproduct_option_id FROM `".DB_PREFIX."product_option`" );
			if ($query->row['Maxproduct_option_id']) {
				$MaxID = $query->row['Maxproduct_option_id'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
    function getMaxproduct_option_value_id() {
	  	$query = $this->db->query( "SELECT max(product_option_value_id) as Maxproduct_option_value_id FROM `".DB_PREFIX."product_option_value`" );
			if ($query->row['Maxproduct_option_value_id']) {
				$MaxID = $query->row['Maxproduct_option_value_id'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
	  //获取最大 product_special_id
	function getMaxproduct_special_id() {
	  	$query = $this->db->query( "SELECT max(product_special_id) as Maxproduct_special_id FROM `".DB_PREFIX."product_special`" );
			if ($query->row['Maxproduct_special_id']) {
				$MaxID = $query->row['Maxproduct_special_id'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
	  //获取最大的 getMaxproductRewardId
	  function getMaxproductRewardId() {
	  	$query = $this->db->query( "SELECT max(product_reward_id) as MaxproductRewardId FROM `".DB_PREFIX."product_reward`" );
			if ($query->row['MaxproductRewardId']) {
				$MaxID = $query->row['MaxproductRewardId'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
//获取最大 getMaxproductImageId
	function getMaxproductImageId() {
	  	$query = $this->db->query( "SELECT max(product_image_id) as MaxproductImageId FROM `".DB_PREFIX."product_image`" );
			if ($query->row['MaxproductImageId']) {
				$MaxID = $query->row['MaxproductImageId'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
	  //获取最大 getMaxproductImageId
	function getMaxproductdiscountId() {
	  	$query = $this->db->query( "SELECT max(product_discount_id) as MaxproductdiscountId FROM `".DB_PREFIX."product_discount`" );
			if ($query->row['MaxproductdiscountId']) {
				$MaxID = $query->row['MaxproductdiscountId'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
	  //获取最大 product_id
	function getMaxproduct_id() {
	  	$query = $this->db->query( "SELECT max(product_id) as Maxproduct_id FROM `".DB_PREFIX."product`" );
			if ($query->row['Maxproduct_id']) {
				$MaxID = $query->row['Maxproduct_id'];
			} else {
				$MaxID = 0;
			}
	    return $MaxID;
	  }
	  //获取最小 product_id
	function getMinproduct_id() {
	  	$query = $this->db->query( "SELECT min(product_id) as Minproduct_id FROM `".DB_PREFIX."product`" );
			if ($query->row['Minproduct_id']) {
				$MinID = $query->row['Minproduct_id'];
			} else {
				$MinID = 0;
			}
	    return $MinID;
	  }
	  //获取产品数量
	function getCountproduct() {
	  	$query = $this->db->query( "SELECT count(product_id) as Countproduct FROM `".DB_PREFIX."product`" );
			if ($query->row['Countproduct']) {
				$Count = $query->row['Countproduct'];
			} else {
				$Count = 0;
			}
	    return $Count;
	  }
	function download($offset=NULL, $rows =NULL, $minPid=NULL, $maxPid=NULL) {
		// we use our own error handler
		global $registry;
		$registry = $this->registry;
		set_error_handler('error_handler_for_export',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export');
		// PHPExcel package from http://phpexcel.codeplex.com/
		$cwd = getcwd();
		chdir('PHPExcel' );
		require_once( 'Classes/PHPExcel.php' );
		chdir( $cwd );
		try {
			// set appropriate timeout limit
			set_time_limit( 1800 );
			ini_set('memory_limit', '512M');
			$database =& $this->db;
			$languageId = $this->getDefaultLanguageId($database);
			// create a new workbook
			$workbook = new PHPExcel();
			// set default font name and size
			$workbook->getDefaultStyle()->getFont()->setName('Arial');
			$workbook->getDefaultStyle()->getFont()->setSize(10);
			$workbook->getDefaultStyle()->getAlignment()->setIndent(1);
			// pre-define some commonly used styles
			$boxFormat = array(
				'font' => array(
					'name' => 'Arial',
					'size' => '10',
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
			);
			$textFormat = array(
				'font' => array(
					'name' => 'Arial',
					'size' => '10',
				),
				'numberformat' => array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
			);
			$priceFormat = array(
				'font' => array(
					'name' => 'Arial',
					'size' => '10',
				),
				'numberformat' => array(
					'code' => '######0.00'
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
			);
			$weightFormat = array(
				'font' => array(
					'name' => 'Arial',
					'size' => '10',
				),
				'numberformat' => array(
					'code' => '##0.00'
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
			);
			// creating the Categories worksheet
			$workbook->setActiveSheetIndex(0);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Categories' );
			$this->populateCategoriesWorksheet( $worksheet, $database, $languageId, $boxFormat, $textFormat );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the filter group worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(1);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'FilterGroup' );
			$this->populateFilterGroupWorksheet( $worksheet, $database, $languageId, $boxFormat, $textFormat );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the filter worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(2);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Filter' );
			$this->populateFiltersWorksheet( $worksheet, $database, $languageId, $boxFormat, $textFormat );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the Products worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(3);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Products' );
			$this->populateProductsWorksheet( $worksheet, $database, $languageId, $priceFormat, $boxFormat, $weightFormat, $textFormat, $offset, $rows, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the AdditionalImages worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(4);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'AdditionalImages' );
			$this->populateAdditionalImagesWorksheet( $worksheet, $database, $boxFormat, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the Options worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(5);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Options' );
			$this->populateOptionsWorksheet( $worksheet, $database, $languageId, $priceFormat, $boxFormat, $weightFormat, $textFormat, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the Attributes worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(6);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Attributes' );
			$this->populateAttributesWorksheet( $worksheet, $database, $languageId, $boxFormat, $textFormat, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the Specials worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(7);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Specials' );
			$this->populateSpecialsWorksheet( $worksheet, $database, $languageId, $priceFormat, $boxFormat, $textFormat, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the Discounts worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(8);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Discounts' );
			$this->populateDiscountsWorksheet( $worksheet, $database, $languageId, $priceFormat, $boxFormat, $textFormat, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			// creating the Rewards worksheet
			$workbook->createSheet();
			$workbook->setActiveSheetIndex(9);
			$worksheet = $workbook->getActiveSheet();
			$worksheet->setTitle( 'Rewards' );
			$this->populateRewardsWorksheet( $worksheet, $database, $languageId, $boxFormat, $minPid, $maxPid );
			$worksheet->freezePaneByColumnAndRow( 1, 2 );
			$workbook->setActiveSheetIndex(0);
//smartexportimport execl
      $datetime = date('Y-m-d');
		  header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="SmartExportImport_products_'.$datetime.'.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');
			$objWriter->save('php://output');
			// Clear the spreadsheet caches
			$this->clearSpreadsheetCache();
			exit;
		} catch (Exception $e) {
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['export_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return;
		}
	}
}
// Error Handler
function error_handler_for_export($errno, $errstr, $errfile, $errline) {
	global $registry;
	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}
	$config = $registry->get('config');
	$url = $registry->get('url');
	$request = $registry->get('request');
	$session = $registry->get('session');
	$log = $registry->get('log');
	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $errors . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}
	if (($errors=='Warning') || ($errors=='Unknown')) {
		return true;
	}
	if (($errors != "Fatal Error") && isset($request->get['route']) && ($request->get['route']!='tool/smartexportimport/download'))  {
		if ($config->get('config_error_display')) {
			echo '<b>' . $errors . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
		}
	} else {
		$session->data['export_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
		$token = $request->get['token'];
		$link = $url->link( 'tool/smartexportimport', 'token='.$token, 'SSL' );
		header('Status: ' . 302);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $link));
		exit();
	}
	return true;
}
function fatal_error_shutdown_handler_for_export()
{
	$last_error = error_get_last();
	if ($last_error['type'] === E_ERROR) {
		// fatal error
		error_handler_for_export(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}
?>