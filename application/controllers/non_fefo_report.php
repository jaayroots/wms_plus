<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of countingCriteria
 *
 * @author Pong-macbook
 */
class non_fefo_report extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model("non_fefo_model", "fefo");
    }

    public function index() {
       
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        } else {
            $this->non_fefo_list();
        }
    }

    public function non_fefo_list() {

        $query = $this->fefo->get_non_fefo_list();
        $list_result = $query->result();

        $str_form = $this->parser->parse('report/nonFefoReport', array("listCounting" => $list_result), TRUE);
        $column = array("Material No.", "Material Description", "Pallet Code", "Location", "Stock Material Mfd.", "Dispatch Material Mfd.","Dispatch Date","Balance Qty.","UOM"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow รวมทั้งเพิ่ม Target Date เข้าไปด้วยเนื่องจาก query นี้มันไปเชื่อมกับที่หน้า สำหรับ Daily แต่ไม่ได้มาปรับที่หน้านี้
        $datatable = $this->datatable->genTableFixColumn($query, $list_result, $column, $module=null, $action=null, $module_del = null, $other = NULL);
    
    
            $this->parser->parse('non_fefo_template', array(
              'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'form' => $str_form
            , 'menu_title' => 'Report : Non FEFO'
 
            , 'button_export_excel' => '<input class="button dark_blue" type="button" ONCLICK="exportFileExcel()" value="Export To Excel">'

        ));

       
    }

   
    function exportExcelNonFEFO(){

               
        $tmp = $this->fefo->get_non_fefo_list();
        $list_result = $tmp->result();
        
                $view['file_name'] = 'Non_FEFO_'. date('Ymd-His');
                $view['header'] = array(
                                      
                                         _lang('Material No.')
                                        , _lang('Material Description')
                                        , _lang('Pallet Code')
                                        , _lang('Location')
                                        , _lang('Stock Material Mfd.')
                                        , _lang('Dispatch Material Mfd')
                                        , _lang('Dispatch Date')
                                        , _lang('Balance Qty')
                                        , _lang('UOM')
                                        
                                    );
                $view['body'] = $list_result;
                //  p($view);
                $this->load->view('report/export_non_fefo_excel',$view);
            
    }

    
    

    
    
}
