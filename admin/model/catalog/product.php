<?php
use Google\Cloud\Translate\TranslateClient;

class mikran_ModelCatalogProduct extends ModelCatalogProduct {

    public function translate($source,$target,$default_source='pl') {
        $client = new Predis\Client();
        $translate = new TranslateClient([
            'key' => GCS_KEY,
        ]);
        
        $name = $client->get($target.'/'.$source);
        if(!$name) {
            $trans = $translate->translate($source,['target'=>$target,'source'=>$default_source]);
            $name = $trans['text'];
            $client->set($target.'/'.$source, $name);
        }
        
        return $name;
    }
    
    public function editProduct($product_id, $data) {

        $translate = new TranslateClient([
            'key' => GCS_KEY,
        ]);

        $client = new Predis\Client();

        //Source string is always pl-pl, this one can be modified only
        $this->load->model('localisation/language');
        $pl = $this->model_localisation_language->getLanguageByCode('pl-pl');
        $language_id = $pl['language_id'];

        $pl_tags = explode(',',$data['product_description'][$pl['language_id']]['tag']);
        
        $source_name = $data['product_description'][$language_id]['name'];
        $languages = $this->model_localisation_language->getLanguages();
        unset($languages['pl-pl']);

        //Translate from pl to other available languages
        foreach($languages as $language) {
            $code = explode("-",$language['code'])[0];
            $name = $this->translate($source_name,$code);

            //Automatically generate title tag
	    $shop_title = $this->translate('sklep',$code);
            
            $data['product_description'][$language['language_id']]['name'] = $name;
	    $data['product_description'][$language['language_id']]['meta_keyword'] = $name;
            $data['product_description'][$language['language_id']]['meta_title'] = $this->config->get('config_name').' - '.$shop_title.' - '.$name;

	    //translate tags
	    if(is_array($pl_tags)) {
		$tags = array();
		foreach($pl_tags as $tag) {
		    $tags[] = $this->translate($tag,$code);
		}
		$data['product_description'][$language['language_id']]['tag'] = implode(",",$tags);
	    }
        }

        //Automatically generate title tag for PL - overwrite whatever is there
	$data['product_description'][$pl['language_id']]['meta_title'] = $this->config->get('config_name').' - sklep - '.$data['product_description'][$pl['language_id']]['name'];
	$data['product_description'][$pl['language_id']]['meta_keyword'] = $data['product_description'][$pl['language_id']]['name'];
        $data['product_description'][$pl['language_id']]['meta_description'] = strip_tags(html_entity_decode($data['product_description'][$pl['language_id']]['description']));

	//Autogenerate title keywords in format: cateogories,name
	$this->load->model('catalog/product');
	$this->load->model('catalog/category');
	$product_cat = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);

	if(isset($product_cat[0])) {
	    $product_cat = $product_cat[0];
	    $path = $this->model_catalog_category->getCategoryPath($product_cat);
	    $level = array_column($path, 'level');
	    array_multisort($level, SORT_ASC, $path);
	    foreach($path as $p) {
		$descriptions = $this->model_catalog_category->getCategoryDescriptions($p['path_id']);
		foreach($descriptions as $lang_id => $description) {
		    $data['product_description'][$lang_id]['meta_keyword'] .= ','. $description['name'];
		}
	    }
	}

        parent::editProduct($product_id,$data);
    }
}
