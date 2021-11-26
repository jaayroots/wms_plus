<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Test extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->model("test_model", "test");
        $this->settings = native_session::retrieve();
        $isUserLogin = $this->session->userdata("user_id");
//        if ($isUserLogin == "") {
//            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
//            echo "<script>window.location.replace('" . site_url() . "');</script>";
//        }
    }

    public function query() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $query = $this->test->query(44);
        p($query);
    }

    public function insert_val_in_middle_array() {
        $array = array(1, 2, 3, 4, 5, 6, 7, 8, 9.10);
        array_splice($array, 3, 0, array('xxx'));
        p($array);
        $key = array_search('xxx', $array);
        p($key);

        array_splice($array, array_search('Putaway By', $array), 0, array('Pallet Code'));
        p($array);
    }

    public function cut_text_after_me() {
        $str = 'Re Order Point of product "0110500200647" = "100" , But remain = "76" , Now in stock have = "476" You can Reserve Qty = "376" please, check this.';
        echo $str;
        echo '<br>';

//        $cut = $this->split_on($str, '11');
        $cut = explode('You can', $str);
        p($cut);
    }

    function split_on($string, $num) {
        $length = strlen($string);
        $output[0] = substr($string, 0, $num);
        $output[1] = substr($string, $num, $length);
        return $output;
    }

    function force_logout($user_name = NULL) {
        if (!empty($user_name)):
            $Force_Logout_Time = time();
            $Force_Logout_Date = date('Y-m-d h:i:s');
            $this->db->query("
                UPDATE SYS_L_UserLogin 
                SET User_Login = 'FALSE',                
                Force_Logout_Time = '{$Force_Logout_Time}',
                Force_Logout_Date = '{$Force_Logout_Date}'
                WHERE UserLogin_Id = 
                    (
                        SELECT UserLogin_Id 
                        FROM ADM_M_UserLogin 
                        WHERE UserAccount = '{$user_name}'
                    )
                AND User_Login = 'TRUE'
            ");

            $this->session->unset_userdata(); //session_destroy();
            $this->session->sess_destroy();

            echo 'Ok, force logout complete.';

//            sleep(1);
//            redirect('authen');

        endif;
    }

    function test_create_document_no_by_type($doc_type = "UNK", $create_date, $view_create_date) {
        $doc_no = "";

        $sql = "SELECT TOP 1 STK_T_Workflow.* FROM STK_T_Workflow WHERE STK_T_Workflow.Document_No LIKE '"
                . $doc_type . "%' AND STK_T_Workflow.Create_Date = '" . $create_date . "' ORDER BY STK_T_Workflow.Document_No DESC";
        $CI = &get_instance();
        $result = $CI->db->query($sql)->row();

        $tmp_date = explode('/', $view_create_date);

        $CI->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());
        $current_year = substr($tmp_date[2], -2);
        $current_month = $tmp_date[1];
        $current_day = $tmp_date[0];

        if (!empty($result)):
            $last_doc_no_year = substr($result->Document_No, strlen($doc_type), 2);
            $last_doc_no_month = substr($result->Document_No, strlen($doc_type) + 2, 2);
            $last_doc_no_day = substr($result->Document_No, strlen($doc_type) + 4, 2);
            $last_doc_no = substr($result->Document_No, strlen($doc_type) + 7, 5);

            $No = str_pad(1, 3, "0", STR_PAD_LEFT);
            if ((int) $current_year >= (int) $last_doc_no_year):
                if ((int) $current_month >= (int) $last_doc_no_month):
                    if ((int) $current_day <= (int) $last_doc_no_day):// New Day.
                        $No = str_pad((int) $last_doc_no + 1, 3, "0", STR_PAD_LEFT);
                    endif;
                endif;
            endif;

            $doc_no = $doc_type . $current_year . $current_month . $current_day . '-' . $No;
        else:
            $No = str_pad(1, 3, "0", STR_PAD_LEFT);
            $doc_no = $doc_type . $current_year . $current_month . $current_day . '-' . $No;
        endif;


//        $doc_no = $doc_type . $current_year . $current_month . $current_day . '-000';


        return $doc_no;
    }

    public function update_document_no($doc_type, $val, $flow_id) {

        $all_table_have_document_no = array(
            'STK_T_Counting',
            'STK_T_Inbound',
            'STK_T_Order',
            'STK_T_Outbound',
            'STK_T_Relocate',
            'STK_T_Workflow'
        );
        $sql = '';
        foreach ($all_table_have_document_no as $key_tb => $tb):
            if ($tb == 'STK_T_Relocate'):
                $sql .= "
                UPDATE {$tb}
                SET {$tb}.Doc_Relocate = '{$val}' 
                WHERE {$tb}.Flow_Id = '{$flow_id}' 
                
            ";

                $relocation_id = $this->db->query("SELECT STK_T_Relocate.Order_Id FROM STK_T_Relocate WHERE STK_T_Relocate.Flow_Id = '{$flow_id}'")->row_array();
                if (!empty($relocation_id)):
                    $relocation_id = $relocation_id['Order_Id'];
                    $sql .= "
                        UPDATE STK_T_Relocate_Detail
                        SET STK_T_Relocate_Detail.Document_No = '{$val}' 
                        WHERE STK_T_Relocate_Detail.Order_Id = '{$relocation_id}' 

                    ";
                endif;
            else:
                $sql .= "
                UPDATE {$tb}
                SET {$tb}.Document_No = '{$val}' 
                WHERE {$tb}.Flow_Id = '{$flow_id}' 
                
            ";
            endif;


        endforeach;

//        $this->db->query($sql);
    }

    public function change_document_no_style() {
        $get_set = $this->input->get('set');
        $in_set = 1000;
        $start_of_set = ($get_set*$in_set)-$in_set;
        $end_of_set = $get_set*$in_set;
//        p($start_of_set);
//        p($end_of_set);
//        exit();
        $this->db->select("*,CONVERT(VARCHAR(10),STK_T_Workflow.Create_Date,103) AS view_Create_Date");
        $this->db->from("STK_T_Workflow");

        $results = $this->db->get()->result();

        
        
//        foreach ($results as $key_result => $result):
        for($i=$start_of_set;$i<$end_of_set;$i++):
            if(empty($results[$i])):
                break;
            else:
                $result = $results[$i];
                if (stripos($result->Document_No, "GRN") !== false) :

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("GRN", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("GRN", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "DDR") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("DDR", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("DDR", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "REL") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("REL", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("REL", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "CH") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("CH", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("CH", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "CD") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("CD", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("CD", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "CC") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("CC", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("CC", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "PAR") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("PAR", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("PAR", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "ADJ") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("ADJ", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("ADJ", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "TO") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("TO", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("TO", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "PE") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("PE", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("PE", $new_document_no, $result->Flow_Id);

                elseif (stripos($result->Document_No, "UNK") !== false):

                    echo $result->Flow_Id . ' > ' . $result->Document_No . '<BR>';
                    $new_document_no = $this->test_create_document_no_by_type("UNK", $result->Create_Date, $result->view_Create_Date);
                    $this->update_document_no("UNK", $new_document_no, $result->Flow_Id);

                else:
    //                p($result->Document_No);
                endif;
            
            endif;
        endfor;
//        endforeach;
    }

    function convert_uom() {

        $this->load->model("uom_model", "uom");
        $this->load->model("product_model", "product");

        $data_post = $this->input->post();
        if (!empty($data_post)):
//            p($data_post);
            $from_uom = $data_post['from_uom'];
            $from_qty = $data_post['from_qty'];
            $to_uom = $data_post['to_uom'];
            $view['data_post'] = $data_post;
            $sql_from = "    
                WITH rootChild AS 
                    (
                        SELECT *
                        FROM CTL_M_UOM_Template
                        WHERE id = {$from_uom}
                        UNION ALL
                        SELECT ut.*
                        FROM CTL_M_UOM_Template ut
                        JOIN rootChild ON ut.id = rootChild.child_id
                   )
                 SELECT *
                 FROM rootChild
            ";
            $results_from = $this->db->query($sql_from)->result();

            $sql_to = "    
                WITH rootChild AS 
                    (
                        SELECT *
                        FROM CTL_M_UOM_Template
                        WHERE id = {$to_uom}
                        UNION ALL
                        SELECT ut.*
                        FROM CTL_M_UOM_Template ut
                        JOIN rootChild ON ut.id = rootChild.child_id
                   )
                 SELECT *
                 FROM rootChild
            ";
            $results_to = $this->db->query($sql_to)->result();

            $cal_from = 1;
            $cal_to = 1;

            foreach ($results_from as $key_result => $result):
                if (!empty($result->child_id)):
                    $cal_from *= $result->quantity;
                endif;
            endforeach;
            foreach ($results_to as $key_result => $result):
                if (!empty($result->child_id)):
                    $cal_to *= $result->quantity;
                endif;
            endforeach;

//            p($cal_from);
//            p($cal_to);

            $view['quotient'] = ($cal_from / $cal_to) * $from_qty;
//            
//            p($quotient);

        endif;

//        $all_uoms = $this->uom->get_template_all()->result();
        $all_uoms = $this->product->getProductUnit()->result();
//        p($all_uoms);
        $view['optionUom'] = genOptionDropdown($all_uoms, 'SYS', TRUE, FALSE);
//        p($view);
        $this->load->view("test/convert_uom.php", $view);



//        $this->db->select("*,CONVERT(VARCHAR(10),STK_T_Workflow.Create_Date,103) AS view_Create_Date");
//        $this->db->from("STK_T_Workflow");
//
//        $results = $this->db->get()->result();
    }

    function log_array($level = 'error', $array, $php_error = FALSE) {
        static $LOG;

        $config = & get_config();
        if ($config['log_threshold'] == 0) {
            return;
        }

        $LOG = & load_class('Log');

        ob_start();
        var_export($array);
        $tab_debug = ob_get_contents();
        ob_end_clean();

        $LOG->write_log($level, $tab_debug, $php_error);
    }

    function log_get() {

        $get = $this->input->get();
        log_array('debug',$get);
    }

}

?>