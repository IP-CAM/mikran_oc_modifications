<?php
class mikran_ModelCatalogOption extends ModelCatalogOption {

    public function getProductOptionValues($option_value_id) {
        $sql = "SELECT pd.product_id,pd.name FROM " . DB_PREFIX . "product_option_value pov left join ". DB_PREFIX ."product_description pd on (pov.product_id = pd.product_id)  WHERE pd.language_id = '". (int)$this->config->get('config_language_id') ."' and pov.option_value_id = '" . (int)$option_value_id . "'";

        $query = $this->db->query($sql);

        foreach($query->rows as $value) {
            $retval[] = $value['name'].'=>'.$value['product_id'];
        }
        return $retval;
    }
}