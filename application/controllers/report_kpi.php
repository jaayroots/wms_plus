<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class report_kpi extends CI_Controller {

    public $path;

    function __construct() {
        parent::__construct();
        $this->load->model("system_management_model", "sys");
        $conf = $this->config->item('_xml');
    }

    public function index() {

        $this->output->enable_profiler($this->config->item('set_debug'));

        $conf = $this->config->item('_xml');
        $parameter['status'] = $this->sys->getSystemDetail("PROD_STATUS")->result();
        $parameter['path'] = $this->path;
        $str_form = $this->parser->parse('form/report_kpi', array("parameter" => $parameter), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Report KPI'
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
        $result = $this->criteria->searchKPI($params)->result();
        echo json_encode(array("RESPONSE" => $result));
    }

    public function export_to_excel() {

    }
    public function export_to_pdf() {
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
        $data['product_lot'] = $dt->{$params['lot_or_serial']};
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

        //p($data);
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

}
