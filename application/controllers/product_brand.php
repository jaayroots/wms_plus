<?php

/*
 * Create by Ton! 20131209
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class product_brand extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("product_brand_model", "brn");
        $this->load->model("uom_model", "uom");
        $this->load->model("product_model", "prod");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "product_brand";
    }

    public function index() {
        $this->product_brand_list();
    }

    function product_brand_list() {// Display List of Brand.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;
        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('position','product_brand/product_brand_processec/','A','')\">";
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_brand = $this->brn->get_CTL_M_ProductBrand_List();
        $r_brand = $q_brand->result();

        $column = array("ID", "ProductBrand Code", "ProductBrand Name EN", "ProductBrand Name TH", "ProductBrand Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_brand, $r_brand, $column, "product_brand/product_brand_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Product Brand.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','product_brand/product_brand_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function product_brand_processec() {// select processec (Add, Edit, Delete) Brand.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->product_brand_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->product_brand_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->product_brand_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Product Brand Success.')</script>";
                redirect('product_brand', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Product Brand Unsuccess. Please check?')</script>";
                redirect('product_brand', 'refresh');
            endif;
        }
    }

    function product_brand_set_inactive($Brand_Id) {// Set Inactive Brand.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Brn['ProductBrand_Id'] = $Brand_Id;

        $data_Brn['Active'] = 0;

        $data_Brn['Modified_Date'] = $human;
        $data_Brn['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->brn->save_CTL_M_ProductBrand('upd', $data_Brn, $where_Brn);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save CTL_M_ProductBrand Unsuccess. Inactive Product Brand Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function product_brand_management($mode, $ProductBrandId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $ProductBrand_Id = '';
        $ProductBrand_Code = '';
        $ProductBrand_NameEN = '';
        $ProductBrand_NameTH = '';
        $ProductBrand_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($ProductBrandId)):
            $q_brand = $this->brn->get_CTL_M_ProductBrand($ProductBrandId, NULL, FALSE);
            $r_brand = $q_brand->result();
            if (count($r_brand) > 0):
                foreach ($r_brand as $value) :
                    $ProductBrand_Id = $ProductBrandId;
                    $ProductBrand_Code = $value->ProductBrand_Code;
                    $ProductBrand_NameEN = $value->ProductBrand_NameEN;
                    $ProductBrand_NameTH = $value->ProductBrand_NameTH;
                    $ProductBrand_Desc = $value->ProductBrand_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        // Add By Akkarapol, 17/01/2014, เพิ่มการจัดการกับ UOM สำหรับ ProductGroup ทั้งการ query ในส่วนของ Template ทั้งหมดที่จะให้เลือกใช้ และที่ Group นี้เลือกใช้แล้ว เพื่อนำไปแสดงเป็น CheckBox ให้เลือก
        $filter_query = array(
            'column' => array(
                'CTL_M_UOM_Template_Of_Product.id',
                'TLIN.public_name AS Unit_Value_In',
                'TLOUT.public_name AS Unit_Value_Out',
                'TLIN.CTL_M_UOM_Template_id AS Unit_Id_In',
                'TLOUT.CTL_M_UOM_Template_id AS Unit_Id_Out'
            ),
            'where' => array(),
            'order' => '
                Unit_Value_In ASC ,
                Unit_Value_Out ASC 
                '
        );

        if (!empty($ProductBrand_Id)):
            $filter_query['order'] = '
                Unit_Value_In ASC ,
                Unit_Value_Out ASC ,
                
                CASE 
                WHEN ProductBrand_Id = ' . $ProductBrand_Id . ' then 1
                else 5
                END
                ';
        endif;

        $all_uoms = $this->uom->get_uom_of_product($filter_query['column'], $filter_query['where'], $filter_query['order'])->result();

        $tmp_in_out = '';
        foreach ($all_uoms as $key_all_uom => $all_uom):
            $in_out_tmp = $all_uom->Unit_Id_In . '-' . $all_uom->Unit_Value_In . '|||' . $all_uom->Unit_Id_Out . '-' . $all_uom->Unit_Value_Out;
            if ($tmp_in_out != $in_out_tmp):
                $tmp_uom[$key_all_uom] = $all_uom;
            endif;
            $tmp_in_out = $in_out_tmp;
        endforeach;
//        p($all_uoms);
//        p($tmp_uom);
        $all_uoms = @$tmp_uom;

        $set_brand_uom = array();
        if (!empty($ProductBrand_Id)):
            $filter_query = array(
                'column' => array(
                    'CTL_M_UOM_Template_Of_Product.id'
                ),
                'where' => array(
                    "ProductBrand_Id" => $ProductBrand_Id
                )
            );
            $group_uoms = $this->uom->get_uom_of_product($filter_query['column'], $filter_query['where'])->result();
            foreach ($group_uoms as $key_brand_uom => $group_uom):
                $set_brand_uom[] = $group_uom->id;
            endforeach;
        endif;

        // END Add By Akkarapol, 17/01/2014, เพิ่มการจัดการกับ UOM สำหรับ ProductGroup ทั้งการ query ในส่วนของ Template ทั้งหมดที่จะให้เลือกใช้ และที่ Group นี้เลือกใช้แล้ว เพื่อนำไปแสดงเป็น CheckBox ให้เลือก
        // get all product_unit for dispaly dropdown list.
        $queryProdU = $this->prod->getProductUnit();
        $prodUnit_list = $queryProdU->result();
        $optionProdUnit = genOptionDropdown($prodUnit_list);

        $this->load->helper('form');
        $str_form = form_fieldset('');
        $str_form.=$this->parser->parse('form/product_brand_master', array("mode" => $mode
            , "ProductBrand_Id" => $ProductBrand_Id, "ProductBrand_Code" => $ProductBrand_Code
            , "ProductBrand_NameEN" => $ProductBrand_NameEN, "ProductBrand_NameTH" => $ProductBrand_NameTH
            , "ProductBrand_Desc" => $ProductBrand_Desc, "Active" => $Active
            , 'all_uoms' => $all_uoms
            , 'brand_uom' => @$set_brand_uom
            , "prodUnitList" => $optionProdUnit
                ), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Product Brand.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140306
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check Product Brand Code already exists.
        if ($data['ProductBrand_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Current_Code'] !== $data['ProductBrand_Code']):
                    $result_check = $this->check_product_brand($data['ProductBrand_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "BRAND_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_product_brand($data['ProductBrand_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "BRAND_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_product_brand() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Brn['ProductBrand_Id'] = ($data['ProductBrand_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_Id']);

        $data_Brn['ProductBrand_Code'] = ($data['ProductBrand_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_Code']);
        $data_Brn['ProductBrand_NameEN'] = ($data['ProductBrand_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_NameEN']);
        $data_Brn['ProductBrand_NameTH'] = ($data['ProductBrand_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_NameTH']);
        $data_Brn['ProductBrand_Desc'] = ($data['ProductBrand_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Brn['Active'] = TRUE;
        else:
            $data_Brn['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Brn['Created_Date'] = $human;
                    $data_Brn['Created_By'] = $this->session->userdata('user_id');
                    $data_Brn['Modified_Date'] = $human;
                    $data_Brn['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->brn->save_CTL_M_ProductBrand('ist', $data_Brn);
                    $data['ProductBrand_Id'] = $result; // Add By Akkarapol, 17/01/2014, เพิ่ม $data['ProductBrand_Id'] = $result ใน type = Add เนื่องจากถ้าเป็นการเพิ่ม จะยังไม่มี ProductGroup_Id ให้เรียกใช้ซึ่งจะนำไปใช้งานต่อในขั้นของการเลือก UOM และการทำ Custom UOM นั่นเอง
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_Brn['Modified_Date'] = $human;
                    $data_Brn['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->brn->save_CTL_M_ProductBrand('upd', $data_Brn, $where_Brn);
                }break;
        endswitch;

        if ($result === TRUE):
            // Add By Akkarapol, 17/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductBrand นี้เลือกมาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductBrand ต่อไป แต่ถ้าไม่มีนั่นหมายความว่า จะต้องไปนำข้อมูลที่เคยเลือกไว้แล้วออกไปนั่นเอง
            if (!empty($data['check_uom'])):

                $list_chk_uom = '';
                foreach ($data['check_uom'] as $key_chk_uom => $chk_uom):

                    $spl_chk_uom = explode(SEPARATOR, $chk_uom);
                    if ($key_chk_uom == 0):
                        $list_chk_uom = $spl_chk_uom[0];
                    else:
                        $list_chk_uom .= ',' . $spl_chk_uom[0];
                    endif;

                    $arr_list_chk_uom[] = $spl_chk_uom[0];

                    $filter_query = array(
                        'where' => array(
                            'ProductBrand_Id' => $data['ProductBrand_Id'],
                            'standard_unit_in_id' => $spl_chk_uom[1],
                            'standard_unit_out_id' => $spl_chk_uom[2]
                        )
                    );

                    // Add By Akkarapol, 17/01/2014, เพิ่มการตรวจเช็คว่า UOM ที่เลือกมานั้น ProductBrand นี้เคยมีแล้วหรือไม่ ถ้ายังไม่มีก็จะทำการเซฟเข้าตัวแปรเพื่อเตรียมทำการ insert ต่อไป
                    $old_or_new = $this->uom->get_uom_of_product('', $filter_query['where'])->result();
                    if (empty($old_or_new)):
                        $save_uom_of_product[] = $filter_query['where'];
                    endif;
                    // END Add By Akkarapol, 17/01/2014, เพิ่มการตรวจเช็คว่า UOM ที่เลือกมานั้น ProductBrand นี้เคยมีแล้วหรือไม่ ถ้ายังไม่มีก็จะทำการเซฟเข้าตัวแปรเพื่อเตรียมทำการ insert ต่อไป

                endforeach;

                $filter_query = array(
                    'where' => array(
                        'ProductBrand_Id' => $data['ProductBrand_Id']
                    )
                );

                $this->uom->del_uom_of_product($arr_list_chk_uom, $filter_query['where']);
                if (!empty($save_uom_of_product)):
                    $result = $this->uom->save_uom_of_product('ist', $save_uom_of_product);
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $result = FALSE;
                        log_message('error', 'save CTL_M_UOM_Template_Of_Product Unsuccess.');
                    endif;
                endif;
            else:
                $filter_query = array(
                    'where' => array(
                        'ProductBrand_Id' => $data['ProductBrand_Id']
                    )
                );
                $this->uom->del_uom_of_product('', $filter_query['where']);
            endif;
            // END Add By Akkarapol, 17/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductBrand นี้เลือกมาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductBrand ต่อไป แต่ถ้าไม่มีนั่นหมายความว่า จะต้องไปนำข้อมูลที่เคยเลือกไว้แล้วออกไปนั่นเอง
            // Add By Akkarapol, 17/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductBrand นี้ทำการ Custom มาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductBrand ต่อไป
            if (!empty($data['custom_uom'])):
                foreach ($data['custom_uom'] as $key_cus_uom => $cus_uom):
                    $save_uom_of_product[] = array(
                        'ProductBrand_Id' => $data['ProductBrand_Id'],
                        'standard_unit_in_id' => $cus_uom['in'],
                        'standard_unit_out_id' => $cus_uom['out']
                    );
                endforeach;
                $result = $this->uom->save_uom_of_product('ist', $save_uom_of_product);
                if ($result > 0):
                    $result = TRUE;
                else:
                    $result = FALSE;
                    log_message('error', 'save CTL_M_UOM_Template_Of_Product Unsuccess.');
                endif;
            endif;
        // END Add By Akkarapol, 17/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductBrand นี้ทำการ Custom มาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductBrand ต่อไป
        endif;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_product_brand($ProductBrand_Code = NULL) {// Check ProductBrand_Code Already.
        $result = FALSE;

        if (!empty($ProductBrand_Code)):
            $r_brand = $this->brn->get_CTL_M_ProductBrand(NULL, $ProductBrand_Code, FALSE)->result();
            if (count($r_brand) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
