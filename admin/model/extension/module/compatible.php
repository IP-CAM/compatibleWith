<?php

/**
 * Created by Basheir Hassan.
 */






class ModelExtensionModuleCompatible extends Model {



    public function getTag($id_ref) {
        return $this->db->query( "SELECT * from `" . DB_PREFIX . "compatible_tags`  WHERE `id_ref` = '". $this->db->escape($id_ref). " ' ;" )->rows;
    }


    public function addTag($tag) {


        $issetTitle = $this->checkIssetTag($tag);

        if(count($issetTitle)>0 ){
          return array(
            'result'=>false,
            'error'=>$issetTitle,
            'lastID'=>0
            );

        }


        $lastID = $this->addRef();

        if ($lastID) {
            foreach ($tag['compatible_tag_title'] as $language_id => $value) {

                $this->db->query("INSERT INTO ".DB_PREFIX
                  ."compatible_tags (`id_ref`,`language_id`,`title`) VALUES ('"
                  .$lastID."','".$this->db->escape($language_id)."','"
                  .$this->db->escape($value)."')");

            }
        }

        return array(
          'result'=>true,
          'error'=>null,
          'lastID'=>$lastID
        );

    }


    public function checkIssetTag($tags){

        $issetTitle = array();
        foreach ($tags['compatible_tag_title'] as $language_id => $value) {
            $result = $this->db->query( "SELECT * from `" . DB_PREFIX . "compatible_tags`  WHERE `title` = '". $this->db->escape($value). " ' ;" )->rows;
            if($this->db->countAffected()){
                $issetTitle[] = $language_id  ;
            }

        }

        return $issetTitle;

    }



    public function updateTag($data) {

        $result = false;
        if ($data) {
            foreach ($data['compatible_tag_title'] as $language_id => $value) {

                $this->db->query( "UPDATE  `" . DB_PREFIX. "compatible_tags` SET `title` = '".$this->db->escape( $value )."' WHERE `language_id` ='". $this->db->escape($language_id)."' and  `id_ref` ='". $this->db->escape($data['compatible_ref_id']) ."';");

                $result= $this->db->countAffected() ;

            }

        }


        return $result;
    }





    public function getTags() {

        $this->model_extension_module_compatible->getLanguages();
        $current_language_id =$this->config->get('config_language_id');
        $tags = $this->db->query( "SELECT * from `" . DB_PREFIX . "compatible_ref` as ref INNER JOIN " . DB_PREFIX . "compatible_tags as tag ON tag.id_ref=ref.id WHERE  `language_id` = '". $this->db->escape($current_language_id). "';" )->rows;
        $allTags =array();
        foreach ($tags as $tag){
            $allTags[$tag['id_ref']]= array("title"=>$tag['title']) ;
        }

        return $allTags;

    }





        public function addRef() {
            $this->db->query( "INSERT INTO " . DB_PREFIX. "compatible_ref (`date`) VALUES (NOW())" );
            return  $this->db->getLastId();
    }






    public function getRefs() {
        return $this->db->query( "SELECT * from `" . DB_PREFIX . "compatible_ref`;" )->rows;
    }





    /*
     * delete Urls And Dashboard
     */


	public function deleteRef($id_ref) {


		$this->db->query( "DELETE from `" . DB_PREFIX . "compatible_ref` WHERE  `id` = '". $this->db->escape($id_ref). "';" );;
		$this->db->query( "DELETE from `" . DB_PREFIX . "compatible_tags` WHERE `id_ref` = '". $this->db->escape($id_ref). "';" );
		$this->db->query( "DELETE from `" . DB_PREFIX . "compatible_product` WHERE `id_ref` = '". $this->db->escape($id_ref). "';" );

		return  $this->db->countAffected();

	}





public function getCountRef() {
    $query = $this->db->query("SELECT COUNT(`product_id`) as count from " . DB_PREFIX. "compatible_dashboard" );
		return $query->row['count'];
	}





    public function addTagProduct($product_id,$data) {

        foreach ($data['product_compatble'] as $key => $product) {
            $this->db->query("INSERT INTO ".DB_PREFIX."compatible_product (`product_id`,`id_ref`,`date`) 
                              VALUES ('".$this->db->escape($product_id)."','" .$this->db->escape($key)."',NOW())");

        }

     }


 public function deleteTagProduct($data) {

        $productID = (int)$data;
        $this->db->query( "DELETE from `" . DB_PREFIX . "compatible_product` WHERE  `product_id` = '". $productID. "';" );

     }



    public function getTagProduct($product_id) {

        $this->model_extension_module_compatible->getLanguages();
        $current_language_id =$this->config->get('config_language_id');
        $tags = $this->db->query( "SELECT pr.id_ref,ref.id,	tags.title from `" . DB_PREFIX . "compatible_product` as pr 
        LEFT JOIN " . DB_PREFIX . "compatible_ref  as ref  ON pr.id_ref = ref.id 
        LEFT JOIN " . DB_PREFIX . "compatible_tags as tags ON ref.id = tags.id_ref  
        WHERE  tags.language_id = '". $this->db->escape($current_language_id). "' AND  pr.product_id = '". (int) $product_id. "';" )->rows;

        $allTags =array();

        foreach ($tags as $tag){
            $allTags[$tag['id_ref']]= array("title"=>$tag['title']) ;
        }

        return $allTags;

    }







    public function getLanguages() {
        $this->load->model( 'localisation/language' );
        $languages = $this->model_localisation_language->getLanguages();
        $allLanguages=array();
        foreach ($languages as $lang){
            $allLanguages[]= array("code"=>$lang['code'],"name"=>$lang['name'],'language_id'=>$lang['language_id']) ;
        }
        return $allLanguages;
    }




}


