<?php

/**
 * Created by Basheir Hassan.
 */


class ControllerExtensionModuleCompatible extends Controller
{

    public function index() {

        $statUS = $this->config->get('module_compatible_status');

        if ($statUS) {
            $this->addClicked();
        }

    }


    public function addClicked() {


        $result = false;

        if (isset($this->request->post['product_id'])
          and !empty($this->request->post['product_id'])
          and $this->request->post['product_id'] != 0
          and isset($this->request->post['stores_id'])
          and !empty($this->request->post['stores_id'])
          and $this->request->post['stores_id'] != 0
          and $this->request->post['key'] != 0
        ) {

            $product_id = (int) $this->request->post['product_id'];
            $stores_id = (int) $this->request->post['stores_id'];

            $key =  $this->request->post['key'];
            $session = $this->session->data["AvailableOnStores"][$stores_id][$product_id];


//            var_dump($key);
//            var_dump($session);


            if($session==$key){

                $this->db->query("INSERT INTO `".DB_PREFIX ."compatible_dashboard` (`product_id`,`stores_id`) VALUES ($product_id,$stores_id);");
                $result = $this->db->countAffected();


               $this->session->data["AvailableOnStores"][$stores_id][$product_id]=null;


            }else{
                $result = false;
            }


        }

        echo json_encode(['result' => $result]);
    }



    public function getStoreUrls() {


        $statUS = $this->config->get('module_compatible_status');
        if (!$statUS) {
            echo 'AvailableOnStores Module Not enabled';
            return null;
        }


        $theme = $this->config->get('config_theme');
        $language_id = $this->config->get('config_language_id');

        if ($statUS) {
            if (isset($this->request->get['product_id'])
              and !empty($this->request->get['product_id'])
              and $this->request->get['product_id'] != 0
            ) {

                $id = (int) $this->request->get['product_id'];


                $result = $this->db->query("SELECT * FROM `".DB_PREFIX
                  ."compatible_urls` `".DB_PREFIX."compatible_urls`
												INNER JOIN  `oc_compatible`
												ON `".DB_PREFIX
                  ."compatible_urls`.`stores_id` = `".DB_PREFIX."compatible`.
												`stores_id` WHERE `product_id` = '$id' ")->rows;


                $results = [];
                foreach ($result as $value) {


                   $randomKey = md5(uniqid());



                    $name = json_decode($value['name'], true)[$language_id];

                    $results[] = ['url'        => $value['url'],
                                  'stores_id'  => $value['stores_id'],
                                  'product_id' => $value['product_id'],
                                  'name'       => $name,
                                  'key'       => $randomKey,
                    ];


                    $this->session->data["AvailableOnStores"][$value['stores_id']][$value['product_id']]= $randomKey;

                }


                if ($this->db->countAffected() > 0) {
                    $json = ['result' => $results, 'theme' => $theme];
                } else {
                    $json = ['result' => false, 'theme' => $theme];

                }

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));


            }

        }


    }


}
