<?php

// Create by Ton! 20130828

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class resultUploadReceive extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("workflow_model", "flow");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "prod");
        $this->load->model('im_ex_model', 'imex');

        $this->load->controller('product_master', 'prodM');
    }

    function resultUploadList() {

        if (!isset($_SESSION)) {
            session_start();
        }

        //$impID = $_SESSION["impID"];
        $impID = unserialize($_GET['r']);
        
        session_write_close();

        $action = array(VIEW);
        $action_module = "resultUploadReceive/resultProcessec";
        $column = array("ID", "Document No.", "All Order", "Success", "Unsuccess");

        if (Count($impID) > 0) {
            $query = $this->imex->getResultIMP_Pre_Receive($impID);
            $result_list = $query->result();
        } else {
            $result_list = array();
        }

        $data = array();
        $data_list = array();
        if (is_array($result_list) && count($result_list)) {
            $count = 1;
            foreach ($result_list as $rows) {
                $data['Id'] = $rows->IMP_ID; //$count;
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
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
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

    function addNewPorduct() {
        $this->product_master->productForm('A', NULL, $this->input->get_post('id'), 'C', base64_decode_for_url($this->input->get('product_name')));
    }

    function resultUnsuccessList($IMP_ID) {
        $action = array();
        $action_module = "resultUploadReceive/addNewPorduct";
        $column = array(
            _lang('no')
            , _lang('document_no')
            , _lang('product_code')
            , _lang('qty')
            , _lang('remark')
            , _lang('add_product')
            );

        $query = $this->imex->getIMP_Pre_Receive_Unsuccess($IMP_ID);
        $unsuccess_list = $query->result();

        $data = array();
        $data_list = array();
        if (is_array($unsuccess_list) && count($unsuccess_list)) {
            $count = 1;
            foreach ($unsuccess_list as $rows) {
                $data['Id'] = $count;
                $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $data['Product_Code'] = $rows->Product_Code;
                
                if($rows->IMP_Check=="F"): //ADD BY POR 2014-03-06 ให้เรียกใช้ number_format ธรรมดาเนื่องจากจำเป็นต้องแสดงข้อมูลจริงตามที่ได้บันทึก
                    $data['Qty'] = number_format($rows->Reserv_Qty,2);
                else:
                    $data['Qty'] = set_number_format($rows->Reserv_Qty); //ถ้าไม่มีปัญหาอะไรให้แสดงข้อมูลตาม format ที่กำหนด
                endif;
                
                $data['Remark'] = $rows->IMP_Remark;          
//                $data['AddProd'] = "<a href=\"addNewPorduct?mode=A&id=$rows->Product_Code\" target=\"_blank\"><INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'></a>"; //Comment Out for Debug by Ton! 20130827
                
                if($rows->IMP_Check=="F" || $rows->IMP_Check=="D" || $rows->IMP_Check=="PF" || $rows->IMP_Check=="UF" || $rows->IMP_Check=="SP"): //ADD BY POR 2014-03-06 เพิ่มเติมให้ไม่ต้อง add product ได้ กรณีที่ข้อมูลซ้ำ (confirm by fern ^^)
                    $data['AddProd'] = "";
                elseif($rows->IMP_Check=="P"):
                    $new_product_name = base64_encode_for_url($rows->Product_Name);
                    $data['AddProd'] = anchor('resultUploadReceive/addNewPorduct?mode=A&id=' . $rows->Product_Code . '&product_name=' . $new_product_name, 'Add Product', array('target' => '_blank', 'class' => 'new_window'));
                endif;
                

                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'Result Unsuccess'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . BACK . "'
             ONCLICK=\"history.back()\">"
        ));
    }

}

?>
