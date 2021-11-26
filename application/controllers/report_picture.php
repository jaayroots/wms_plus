<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class report_picture extends CI_Controller {

    public $mnu_NavigationUri;
    public $path;

    function __construct() {
        parent::__construct();
        $this->load->model("menu_model", "mnu");
        $this->load->model("system_management_model", "sys");
        $conf = $this->config->item('_xml');
        $this->path = str_replace("/var/www/DG_R1.0/WebAppForPC/", "./", $conf['uploads']['upload_path']);
        $this->path = str_replace("./", "/", $this->path);

        $this->mnu_NavigationUri = "report_picture";
    }

    public function index() {

        $this->output->enable_profiler($this->config->item('set_debug'));

        $conf = $this->config->item('_xml');
        $parameter['status'] = $this->sys->getSystemDetail("PROD_STATUS")->result();
        $parameter['path'] = $this->path;
        $str_form = $this->parser->parse('form/report_picture_form', array("parameter" => $parameter), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Print Picture'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    public function search() {
        $this->load->model("criteria_model", "criteria");
        $params = $this->input->post();
        $result = $this->criteria->getSearchResult($params)->result();
        $response = array();
        foreach ($result as $idx => $val) {
            $val->Cont_No = tis620_to_utf8($val->Cont_No);
        }
        echo json_encode(array("RESPONSE" => $result));
    }

    public function export_dg_report_pdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $this->load->library('email');

        $this->load->model("stock_model", "stock");

        $img_path = base_url($this->path);
        $params = $this->input->post();

        $dt = $this->stock->getOrderDetailById($params['order_id'], $params['item_id']);
        $data['date_in'] = $dt->Actual_Action_Date;
        $data['product_name'] = implode("<br/>", $params['product_list']);
        $data['document_external'] = $dt->Doc_Refer_Ext;
        $data['document_invoice'] = $dt->Doc_Refer_Inv;
        $data['product_lot'] = ($params['lot_or_serial'] == "Free_Text" ? $params['free_text'] : $dt->{$params['lot_or_serial']});
        $data['container_name'] = $dt->ContainerName;
        $data['po_no'] = $dt->Doc_Refer_Ext;
        $data['qty'] = (strlen(trim($params['quantity'])) == 0 ? $dt->Reserv_Qty : tis620_to_utf8($params['quantity']) );
        $data['status'] = $dt->Product_Status;
        $data['company_name'] = $dt->Company_NameEN;
        $data['description_type_1'] = ($params['description_type_1'] == 1 ? 'checked="checked"' : '');
        $data['description_type_2'] = ($params['description_type_2'] == 1 ? 'checked="checked"' : '');
        $data['description_type_3'] = ($params['description_type_3'] == 1 ? 'checked="checked"' : '');
        $data['description_type_4'] = ($params['description_type_4'] == 1 ? 'checked="checked"' : '');
        $data['description_type_3_desc'] = tis620_to_utf8($params['description_type_3_desc']);
        $data['description_type_4_desc'] = tis620_to_utf8($params['description_type_4_desc']);
        $data['remark'] = (strlen(trim($params['remark'])) == 0 ? str_repeat(".", 260) : str_repeat(".", 10) . tis620_to_utf8($params['remark']) );
        $data['email'] = $params['email_address'];
        $data['document_export_type'] = $params['document_export_type'];

        //p($data, FALSE);
        //p($params); exit;

        foreach ($params['image_id'] as $idx => $val) {
            $path = "/";
            $image = getImagePath($val);
            foreach ($image['PATH'] as $i => $v) {
                $path .= $v . "/";
            }
            $data['images'][] = $img_path . $path . $image['FULL'];
        }

        if ($params['document_export_type'] == "damage_report") {
            $this->load->view("report/export_damage_pdf", $data);
        } else {
            $this->load->view("report/export_intake_outtake_pdf", $data);
        }
    }

    public function print_damage_report() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $img_path = base_url($this->path);
        $params = $this->input->post();
        foreach ($params['image_id'] as $idx => $val) {
            $path = "/";
            $image = getImagePath($val);
            foreach ($image['PATH'] as $i => $v) {
                $path .= $v . "/";
            }
            $data['images'][] = $img_path . $path . $image['FULL'];
        }
        $this->load->view("report/export_damage_pdf", $data);
    }

    public function getProductList() {
        $this->load->model("stock_model", "stock");
        $params = $this->input->post();
        $product_list = $this->stock->getProductList($params['order_id']);
        die(json_encode($product_list));
    }

    public function get_detail() {

        $params = $this->input->post();

        $template_path = getcwd() . $this->path;

        // Container
        $image_by_cont = generateImageId($params['cont_id'], "c");
        $c_path = "";
        foreach ($image_by_cont['PATH'] as $i => $v) {
            $c_path .= $v . "/";
        }

        $container_path = $template_path . $c_path . $image_by_cont['FULL'];

        // Item
        $image_by_item = generateImageId($params['item_id'], "i");
        $i_path = "";
        foreach ($image_by_item['PATH'] as $i => $v) {
            $i_path .= $v . "/";
        }
        $item_path = $template_path . $i_path . $image_by_item['FULL'];

        // Pallet
        $image_by_pallet = generateImageId($params['pallet_id'], "p");
        $p_path = "";
        foreach ($image_by_pallet['PATH'] as $i => $v) {
            $p_path .= $v . "/";
        }

        $pallet_path = $template_path . $p_path . $image_by_pallet['FULL'];

        $response = array("container" => $this->getAllImageInDir($container_path, $c_path), "item" => $this->getAllImageInDir($item_path, $i_path), "pallet" => $this->getAllImageInDir($pallet_path, $p_path));

        echo json_encode($response);
    }

    private function getAllImageInDir($path, $web_path) {

        $list = glob($path . "*.png");

        $response = array();

        foreach ($list as $idx => $val) {
            list($name, $ext) = explode(".", basename($val));
            $file_path = $web_path . $name . "." . $ext;
            $file_time = date('Y-m-d h:i:s', filemtime($val));
            $response[$name] = array("path" => $file_path, "time" => $file_time, "name" => $name);
        }

        return $response;
    }

}
