<?php

/**
 * Created by Basheir Hassan.
 */



class ControllerExtensionModuleCompatible extends Controller {
    private $error = array();

    public function index() {


        $this->load->language('extension/module/compatible');
        $this->load->model('setting/setting');



        $this->document->setTitle($this->language->get('compatible_heading_title'));





        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_compatible', $this->request->post);
            $this->session->data['success'] = $this->language->get('compatible_text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }




        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
          'text' => $this->language->get('text_home'),
          'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
          'text' => $this->language->get('compatible_text_extension'),
          'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
          'text' => $this->language->get('compatible_heading_title'),
          'href' => $this->url->link('extension/module/compatible', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/compatible', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);



        $data['add_tag']    = html_entity_decode($this->url->link('extension/module/compatible/addTag', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        $data['get_tags_json']    = html_entity_decode($this->url->link('extension/module/compatible/getTagsJson', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        $data['delete_ref'] = html_entity_decode($this->url->link('extension/module/compatible/deleteRef', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        $data['update_tag'] = html_entity_decode($this->url->link('extension/module/compatible/updateTag', 'user_token=' . $this->session->data['user_token'], 'SSL'));



        /*Admin Module Status*/
        if (isset($this->request->post['module_compatible_status'])) {
            $data['module_compatible_status'] = $this->request->post['module_compatible_status'];
        } elseif ($this->config->get('module_compatible_status')) {
            $data['module_compatible_status'] = $this->config->get('module_compatible_status');
        } else {
            $data['module_compatible_status'] = 0;
        }




        /*Admin Module title*/
        if (isset($this->request->post['module_compatible_title'])) {
            $data['module_compatible_title'] = $this->request->post['module_compatible_title'];
        } elseif ($this->config->get('module_compatible_title')) {
            $data['module_compatible_title'] = $this->config->get('module_compatible_title');
        } else {
            $data['module_compatible_title'] = 0;
        }









        $data['header']      =      $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      =      $this->load->controller('common/footer');
        $data['languages']   =    $this->getLanguages();


        $this->response->setOutput($this->load->view('extension/module/compatible', $data));

    }



    public function install(){



        $this->load->model( 'setting/event' );
        $this->model_setting_event->addEvent( 'compatible_post_add', 'admin/model/catalog/product/addProduct/after', 'extension/module/compatible/addTagEvent' );
        $this->model_setting_event->addEvent( 'compatible_post_edit', 'admin/model/catalog/product/editProduct/after', 'extension/module/compatible/editTagEvent' );
        $this->model_setting_event->addEvent( 'compatible_post_delete', 'admin/model/catalog/product/deleteProduct/after', 'extension/module/compatible/deleteTagEvent' );




        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."compatible_product` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `date` date NOT NULL,
                      `product_id` int(11) NOT NULL,
                      `id_ref` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");




        $this->db->query(	"CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."compatible_tags` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `id_ref` int(4) DEFAULT NULL,
                      `language_id` int(3) NOT NULL,
                      `title` varchar(99) DEFAULT NULL,
                      `date` date NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");



          $this->db->query(	"CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."compatible_ref` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `date` date NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");


    }



    public function uninstall(){
        $this->load->model( 'setting/event' );
        $this->model_setting_event->deleteEventByCode('compatible_post_add' );
        $this->model_setting_event->deleteEventByCode('compatible_post_edit' );
        $this->model_setting_event->deleteEventByCode('compatible_post_delete' );
    }








    public function addTag() {




        $result = false;
        if ( isset( $this->request->post['compatible_tag_title'] ) ) {

            $this->load->model('extension/module/compatible');
            $query = $this->model_extension_module_compatible->addTag($this->request->post);

            $result = $this->db->countAffected();

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
          'result' => $result,
          'title'   => $this->request->post['compatible_tag_title'],
          'lastID'   => $query,
        ]));


    }







    public function getTags() {

        $this->load->model('extension/module/compatible');
        return $this->model_extension_module_compatible->getTagJson();
    }




    public function deleteRef() {

        $id_ref = (int)$this->request->post['id_ref'];
        $this->load->model('extension/module/compatible');
        $result = $this->model_extension_module_compatible->deleteRef($id_ref);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([ 'result' => $result ]));

    }





    public function updateTag() {


        $this->load->model('extension/module/compatible');
        $result = $this->model_extension_module_compatible->updateTag($this->request->post);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['result' => $result ]));

    }






    function addTagEvent($route, $data,$product_id){

        $this->load->model('setting/setting');

        if (!$this->config->get('module_compatible_status')){
            return;
        }

        if (isset($data[0]['product_compatble'])) {
            $this->load->model('extension/module/compatible');
            $this->model_extension_module_compatible->addTagProduct($product_id,$data[0]);

        }


    }


    function editTagEvent($route, $data){



        $product_id = $data[0];
        $this->load->model('extension/module/compatible');
        $this->load->model('setting/setting');
        if (!$this->config->get('module_compatible_status')){
            return;
        }

        $this->model_extension_module_compatible->deleteTagProduct($product_id);
        $this->model_extension_module_compatible->addTagProduct($product_id,$this->request->post);



    }


    function deleteTagEvent($route, &$data){

        $this->load->model('extension/module/compatible');

        $this->load->model('setting/setting');

        if (!$this->config->get('module_compatible_status')){
            return;
        }

        $this->model_extension_module_compatible->deleteTagProduct($data[0]);

    }




    public function getLanguages() {

        $this->load->model( 'localisation/language' );
        $languages = $this->model_localisation_language->getLanguages();
        $allLanguages=array();
        foreach ($languages as $lang){
            $allLanguages[]= array("code"=>$lang['name'],'language_id'=>$lang['language_id']) ;
        }
        return $allLanguages;
    }




    public function getTagsJson() {
        $this->load->model('extension/module/compatible');
        $this->load->model( 'localisation/language' );
        $refs = $this->model_extension_module_compatible->getRefs();


//              var_dump($refs);
        $allRefs = array();

        foreach ($refs as $ref){
            $title=[];
            $tags = $this->model_extension_module_compatible->getTag($ref['id']);
            foreach ($tags as $tag){
                $title[$tag['language_id']] =  $tag['title'];
            }

            $allRefs[]= array('id_ref'=>$ref['id'],'title'=>$title,'date'=>$ref['date']) ;

        }

        echo json_encode(array_values($allRefs),true);
    }









    protected function validate() {

        if (!$this->user->hasPermission('modify', 'extension/module/compatible')) {
            $this->error['warning'] = $this->language->get('compatible_error_permission');
        }

        return !$this->error;
    }




}


