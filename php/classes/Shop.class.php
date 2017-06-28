<?php
	require_once(__DIR__ . '/Config.class.php');
	
	class Shop {
		private static $_instance = null;
	
		private $_user = '';
	    private $_cart = array();
		private $_catalog = array();
		
		public function __construct($user = '') {
		    $this->_user = $user;
			$this->_catalog = json_decode(file_get_contents( Config::get('domain') . Config::get('documentroot') . Config::get('mediaroot') . Config::get('shopfile'), true));
  		}
		
		public static function getInstance() {
			if (self::$_instance == null)
				self::$_instance = new Shop();
			return self::$_instance;
		}
		
		public function getCatalog() {
			return (array)$this->_catalog;
		}
		
		public function getProduct($productId) {
			$catalog = $this->getCatalog();
			$product = array_search($productid, array_column($catalog, 'Product-Id'));
			return $product;
		}
		
		public function getProductBySku($sku)
		{
			$catalog = $this->getCatalog();
			$prodId = array_search($sku, $this->_catalog);
			return $prodId;
		}
		
		public function getProductIdBySku($sku)
		{
			$product = $this->_catalog->$sku->{'Product-Id'};
			return $product;
		}
		
		public function getProductDataBySku($sku)
		{
			$product = $this->_catalog->$sku->{'data'};
			return $product;
		}
		
		public function getProductNameBySku($sku)
		{
			$product = $this->_catalog->$sku->{'Name'};
			return $product;
		}
		
		public function getProductPriceBySku($sku)
		{
			$product = $this->_catalog->$sku->{'Price'};
			return $product;
		}

		public static function getCheckoutURL() {
		    ///TODO: jump to checkout page
		    $url = '';
			// return $url;
		}
		
	}
?>

