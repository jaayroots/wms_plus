<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class resultUploadDispatch extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("workflow_model", "flow");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "prod");
        $this->load->model('im_ex_model', 'imex');
    }

    function resultUploadList() {

        if (!isset($_SESSION)) {
            session_start();
        }

        $impID = unserialize($_GET['r']);

        session_write_close();

        $action = array(VIEW);
        $action_module = "resultUploadDispatch/resultProcessec";
        $column = array("ID", "Document No.", "All Order", "Success", "Unsuccess");

        if (Count($impID) > 0) {
            $query = $this->imex->getResultIMP_Pre_Dispatch($impID);
            $result_list = $query->result();
        } else {
            $result_list = array();
        }

        $data = array();
        $data_list = array();
        if (is_array($result_list) && count($result_list)) {
            $count = 1;
            foreach ($result_list as $rows) {
                $data['Id'] = $rows->IMP_ID;
                $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $data['sum_order'] = $rows->sum_order;
                $data['sum_success'] = $rows->sum_success;
                $data['sum_unsuccess'] = $rows->sum_unsuccess;
                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'Result Upload'
            , 'datatable' => $datatable
            , 'button_add' => ""
        ));
    }

    function resultProcessec() {
        $data = $this->input->post();
        $mode = $data['mode'];
        $id = $data['id'];
        if ($mode == "V") {
            $this->resultUnsuccessList($id);
        }
    }

    function resultUnsuccessList($IMP_ID) {
        $action = array();
        $action_module = "resultUploadDispatch/addNewPorduct";
        $column = array(
            _lang('no')
            , _lang('document_no')
            , _lang('product_code')
            , _lang('qty')
            , _lang('remark')
        );

        $query = $this->imex->getResultIMP_Unsuccess2($IMP_ID);
        $unsuccess_list = $query->result();

        $data = array();
        $data_list = array();
        if (is_array($unsuccess_list) && count($unsuccess_list)) {
            $count = 1;
            foreach ($unsuccess_list as $rows) {
                $data['Id'] = $count;
                $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $data['Product_Code'] = $rows->Product_Code;
                $data['Qty'] = set_number_format($rows->Reserv_Qty);
                $data['Remark'] = $rows->IMP_Remark;

                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'Result Unsuccess'
            , 'datatable' => $datatable
            , 'button_add' => "<input type='button' class='button dark_blue' value='" . BACK . "' onclick=\"history.back()\">"
        ));
    }

}
