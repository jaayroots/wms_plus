<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of shelfLifeReport
 *
 * @author Pong-macbook
 */
class shelf_life_report extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        
        $this->load->helper('form');
        
        $this->load->model("shelf_life_model", "r");
        $this->load->model("encoding_conversion", "conv");
        
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));        
    }

    public function index() {
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        } else {
            $this->shelfLifeForm();
        }
    }

    public function shelfLifeForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        

        $this->load->model("company_model", "company");
        $this->load->model("warehouse_model", "w");
        $this->load->model("product_model", "p");
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE); //add by kik  เพื่อแก้ไขเรื่อง ไม่ default renter id โดยการส่ง parameter เพิ่มเข้าไปอีก สองตัวด้านหลัง เพื่อไว้ใช้แสดงผลให้เหมือนกับ report อื่นๆ: 20140329
//        $renter_list = genOptionDropdown($r_renter, "COMPANY"); //comment by kik  เพื่อแก้ไขเรื่อง ไม่ default renter id : 20140329
        $parameter['renter_list'] = $renter_list;

        #Get warehouse list
        $q_warehouse = $this->w->getWarehouseList();
        $r_warehouse = $q_warehouse->result();
        $warehouse_list = genOptionDropdown($r_warehouse, "WH");
        $parameter['warehouse_list'] = $warehouse_list;

        # Get Product Category
        $q_category = $this->p->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/shelf_life_report", $parameter, TRUE);
# PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Shelf Life'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmShelflife"></i>'
            // END Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));

        // exit();
    }

    function showShelfLifeReport() {
        $renter_id = $this->input->get("renter_id");
        $warehouse_id = $this->input->get("warehouse_id");
        $category_id = $this->input->get("category_id");
        $product_id = $this->input->get("product_id");
        //$as_date = convertDate($this->input->get("as_date"), "eng", "iso", "-"); COMMENT BY POR 2013-11-28 ยกเลิกการแปลงวันที่ เนื่องจากไปแปลงในหน้า model แทน เพราะมีส่วนของ print รายงานที่ต้องแปลงด้วย
        $as_date=$this->input->get("as_date");
        $limit_start = (int) $this->input->get("iDisplayStart");
        $limit_offset = (int) $this->input->get("iDisplayLength");                
        $condition_value = array(
            "renter_id" => $renter_id
            , "warehouse_id" => $warehouse_id
            , "category_id" => $category_id
            , "product_id" => $product_id
            , "as_date" => $as_date
        );
        
        //p();
        $age_rang = $this->r->countProductShelfLife(TRUE,$condition_value, $limit_start, $limit_offset);
        $response = array();
        foreach ($age_rang as $k => $v) :
			$response[] = array(($limit_start + $k + 1)
	        	,$v->Product_Code
	        	,thai_json_encode($v->Product_NameEN)
				,!empty($v->threeMonth)?set_number_format($v->threeMonth):''
				,!empty($v->sixMonth)?set_number_format( $v->sixMonth):''
				,!empty($v->nineMonth)?set_number_format( $v->nineMonth):''
				,!empty($v->oneYear)?set_number_format( $v->oneYear):''
				,!empty($v->oneHalfYear)?set_number_format( $v->oneHalfYear):''
				,!empty($v->twoYear)?set_number_format( $v->twoYear):''
				,!empty($v->moreTwoYear)?set_number_format( $v->moreTwoYear):''
				,!empty($v->total)?set_number_format( $v->total):''
        	);
        endforeach;
                
        $output = array(
			"sEcho" => intval($this->input->get('sEcho')),
			//"iTotalRecords" => $this->r->count_shelf_report($condition_value),
                        "iTotalRecords" => count($this->r->countProductShelfLife(FALSE,$condition_value)), //EDIT BY POR 2014-03-20 Change to use single query
			//"iTotalDisplayRecords" => $this->r->count_shelf_report($condition_value),
			"iTotalDisplayRecords" => count($this->r->countProductShelfLife(FALSE,$condition_value)), //EDIT BY POR 2014-03-20 Change to use single query
                        "aaData" => $response
        );

        echo json_encode($output);
                
        exit();
        $data = $age_rang;
        $view['data'] = $data;
        $view['search'] = $condition_value;
        //$this->load->view("report/shelf_life_report.php", $view);
    }

    function export_shelf_life_pdf() {
        $this->load->library('pdf/mpdf');
        date_default_timezone_set('Asia/Bangkok');
        $renter_id = $this->input->post("renter_id");
        $warehouse_id = $this->input->post("warehouse_id");
        $category_id = $this->input->post("category_id");
        $product_id = $this->input->post("product_id");
        //$as_date = convertDate($this->input->post("as_date"), "eng", "iso", "-"); //COMMENT BY POR 2014-01-14 ไป convert ใน model แทน
        $condition_value = array(
        		"renter_id" => $renter_id
        		, "warehouse_id" => $warehouse_id
        		, "category_id" => $category_id
        		, "product_id" => $product_id
        		//, "as_date" => $as_date //COMMENT BY POR 2014-01-14 ไป convert ใน model แทน
                        , "as_date" => $this->input->post("as_date") //ADD BY POR 2014-01-14 เรียกค่าที่ post มาโดยตรง แล้วไป convert ใน model แทน
                        
        );        
//        $data = $this->r->countProductShelfLife($condition_value); //comment by kik แก้ defect pdf ตัวเลขขึ้นเป็น 0 ทั้งหมด: 20140329
         $data = $this->r->countProductShelfLife(FALSE,$condition_value);// add by kik ส่ง parameter $limit_flag ไปด้วย เพราะว่าของเดิมไม่ได้ส่งไว้ จึงทำให้ตัวเลขขึ้นเป็น 0 ทั้งหมด : 20140329
        //p($data);
        $view['data'] = $data;

        $view['text_header'] = 'Shelf Life Report';

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        $this->load->view("report/export_shelf_life_pdf", $view);
    }

    function export_shelf_life_excel() {
        $renter_id = $this->input->post("renter_id");
        $warehouse_id = $this->input->post("warehouse_id");
        $category_id = $this->input->post("category_id");
        $product_id = $this->input->post("product_id");
        //$as_date = convertDate($this->input->post("as_date"), "eng", "iso", "-"); //COMMENT BY POR 2014-01-14 ไป convert ใน model แทน
        $condition_value = array(
        		"renter_id" => $renter_id
        		, "warehouse_id" => $warehouse_id
        		, "category_id" => $category_id
        		, "product_id" => $product_id
        		//, "as_date" => $as_date
                        , "as_date" => $this->input->post("as_date") //EDIT BY POR 2014-01-14 ให้เรียก date ที่ส่งมาโดยตรงเนื่องจากไป convert ใน model แทน 
        );
      
//        $data = $this->r->countProductShelfLife($condition_value);//comment by kik แก้ defect pdf ตัวเลขขึ้นเป็น 0 ทั้งหมด: 20140329
        $data = $this->r->countProductShelfLife(FALSE,$condition_value);// add by kik ส่ง parameter $limit_flag ไปด้วย เพราะว่าของเดิมไม่ได้ส่งไว้ จึงทำให้ตัวเลขขึ้นเป็น 0 ทั้งหมด : 20140329
        $view['body'] = $data;
        $view['file_name'] = 'shelf_live_report';
        $this->load->view("report/shelf_live_excel_template", $view);
    }

}

?>
