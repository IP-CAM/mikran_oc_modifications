<?php
class mikran_ControllerCatalogProduct extends ControllerCatalogProduct {
    public function edit() {
        parent::edit();
    }

    protected function validateForm() {
        parent::validateForm();
        
        //Private members are so so so STUPID as well as PHP is
        $r = new ReflectionObject($this);
        $p = $r->getParentClass()->getProperty('error');
        $p->setAccessible(true);
        $error = $p->getValue($this);
        //var_dump($error);

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        //var_dump($languages);

        foreach($error as $error_field=>$lang_message) {
            if(is_array($lang_message)) {
                foreach($languages as $code=>$language) {
                    if(isset($lang_message[$language['language_id']])) {
                        if(strcmp($code,'pl-pl') != 0) {
                            unset($error[$error_field][$language['language_id']]);
                        }
                    }
                }
            }
        }

        if(empty($error['name'])) {
            unset($error['name']);
        }
        if(empty($error['meta_title'])) {
            unset($error['meta_title']);
        }
        if(!empty($error['warning']) && sizeof($error) == 1) {
            unset($error['warning']);
        }

        //Again thank you fucking PHP
        $p->setValue($this,$error);

        return !$error;
    }
}