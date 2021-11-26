<?php

/*
 * Create by Ton! 20131209
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class product_group extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("product_group_model", "gro");
        $this->load->model("uom_model", "uom");
        $this->load->model("product_model", "prod");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "product_group";
    }

    public function index() {
        $this->product_group_list();
    }

    function product_group_list() {// Display List of Brand.
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
//             ONCLICK=\"openForm('position','product_group/product_group_processec/','A','')\">";
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_group = $this->gro->get_CTL_M_ProductGroup_List();
        $r_group = $q_group->result();

        $column = array("ID", "ProductGroup Code", "ProductGroup Name EN", "ProductGroup Name TH", "ProductGroup Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_group, $r_group, $column, "product_group/product_group_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Product Group.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','product_group/product_group_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function product_group_processec() {// select processec (Add, Edit, Delete) Brand.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->product_group_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->product_group_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->product_group_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Product Group Success.')</script>";
                redirect('product_group', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data  Product Group  Unsuccess. Please check?')</script>";
                redirect('product_group', 'refresh');
            endif;
        }
    }

    function product_group_set_inactive($Brand_Id) {// Set Inactive Brand.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Gro['ProductGroup_Id'] = $Brand_Id;

        $data_Gro['Active'] = 0;

        $data_Gro['Modified_Date'] = $human;
        $data_Gro['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->gro->save_CTL_M_ProductGroup('upd', $data_Gro, $where_Gro);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save CTL_M_ProductGroup Unsuccess. Inactive Product Group Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function product_group_management($mode, $ProductGroupId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $ProductGroup_Id = '';
        $ProductGroup_Code = '';
        $ProductGroup_NameEN = '';
        $ProductGroup_NameTH = '';
        $ProductGroup_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($ProductGroupId)):
            $q_group = $this->gro->get_CTL_M_ProductGroup($ProductGroupId, NULL, FALSE);
            $r_group = $q_group->result();
            if (count($r_group) > 0):
                foreach ($r_group as $value) :
                    $ProductGroup_Id = $ProductGroupId;
                    $ProductGroup_Code = $value->ProductGroup_Code;
                    $ProductGroup_NameEN = $value->ProductGroup_NameEN;
                    $ProductGroup_NameTH = $value->ProductGroup_NameTH;
                    $ProductGroup_Desc = $value->ProductGroup_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        // Add By Akkarapol, 15,16/01/2014, เพิ่มการจัดการกับ UOM สำหรับ ProductGroup ทั้งการ query ในส่วนของ Template ทั้งหมดที่จะให้เลือกใช้ และที่ Group นี้เลือกใช้แล้ว เพื่อนำไปแสดงเป็น CheckBox ให้เลือก
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

        if (!empty($ProductGroupId)):
            $filter_query['order'] = '
                Unit_Value_In ASC ,
                Unit_Value_Out ASC ,
                
                CASE 
                WHEN ProductGroup_Id = ' . $ProductGroupId . ' then 1
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

        $set_group_uom = array();
        if (!empty($ProductGroupId)):
            $filter_query = array(
                'column' => array(
                    'CTL_M_UOM_Template_Of_Product.id'
                ),
                'where' => array(
                    "ProductGroup_Id" => $ProductGroup_Id
                )
            );
            $group_uoms = $this->uom->get_uom_of_product($filter_query['column'], $filter_query['where'])->result();
            foreach ($group_uoms as $key_group_uom => $group_uom):
                $set_group_uom[] = $group_uom->id;
            endforeach;
        endif;

        // END Add By Akkarapol, 15,16/01/2014, เพิ่มการจัดการกับ UOM สำหรับ ProductGroup ทั้งการ query ในส่วนของ Template ทั้งหมดที่จะให้เลือกใช้ และที่ Group นี้เลือกใช้แล้ว เพื่อนำไปแสดงเป็น CheckBox ให้เลือก
        // get all product_unit for dispaly dropdown list.
        $prodUnit_list = $this->prod->getProductUnit()->result();
        $optionProdUnit = genOptionDropdown($prodUnit_list);

        $this->load->helper('form');
        $str_form = form_fieldset('');
        $str_form.=$this->parser->parse('form/product_group_master', array("mode" => $mode
            , "ProductGroup_Id" => $ProductGroup_Id, "ProductGroup_Code" => $ProductGroup_Code
            , "ProductGroup_NameEN" => $ProductGroup_NameEN, "ProductGroup_NameTH" => $ProductGroup_NameTH
            , "ProductGroup_Desc" => $ProductGroup_Desc, "Active" => $Active
            , 'all_uoms' => $all_uoms
            , 'group_uom' => @$set_group_uom
            , "prodUnitList" => $optionProdUnit
                ), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Product Group.'
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

        # Check Product Group Code already exists.
        if ($data['ProductGroup_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Current_Code'] !== $data['ProductGroup_Code']):
                    $result_check = $this->check_product_group($data['ProductGroup_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "GROUP_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_product_group($data['ProductGroup_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "GROUP_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_product_group() {
        $data = $this->input->post();
        
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Gro['ProductGroup_Id'] = ($data['ProductGroup_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductGroup_Id']);
        $data_Gro['ProductGroup_Code'] = ($data['ProductGroup_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductGroup_Code']);
        $data_Gro['ProductGroup_NameEN'] = ($data['ProductGroup_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductGroup_NameEN']);
        $data_Gro['ProductGroup_NameTH'] = ($data['ProductGroup_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductGroup_NameTH']);
        $data_Gro['ProductGroup_Desc'] = ($data['ProductGroup_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductGroup_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Gro['Active'] = TRUE;
        else:
            $data_Gro['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Gro['Created_Date'] = $human;
                    $data_Gro['Created_By'] = $this->session->userdata('user_id');
                    $data_Gro['Modified_Date'] = $human;
                    $data_Gro['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->gro->save_CTL_M_ProductGroup('ist', $data_Gro);
                    $data['ProductGroup_Id'] = $result; // Add By Akkarapol, 15+16/01/2014, เพิ่ม $data['ProductGroup_Id'] = $result ใน type = Add เนื่องจากถ้าเป็นการเพิ่ม จะยังไม่มี ProductGroup_Id ให้เรียกใช้ซึ่งจะนำไปใช้งานต่อในขั้นของการเลือก UOM และการทำ Custom UOM นั่นเอง
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $data_Gro['Modified_Date'] = $human;
                    $data_Gro['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->gro->save_CTL_M_ProductGroup('upd', $data_Gro, $where_Gro);
                }break;
        endswitch;

        // Add By Akkarapol, 15+16/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductGroup นี้เลือกมาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductGroup ต่อไป แต่ถ้าไม่มีนั่นหมายความว่า จะต้องไปนำข้อมูลที่เคยเลือกไว้แล้วออกไปนั่นเอง
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
                        'ProductGroup_Id' => $data['ProductGroup_Id'],
                        'standard_unit_in_id' => $spl_chk_uom[1],
                        'standard_unit_out_id' => $spl_chk_uom[2]
                    )
                );

                // Add By Akkarapol, 15+16/01/2014, เพิ่มการตรวจเช็คว่า UOM ที่เลือกมานั้น ProductGruop นี้เคยมีแล้วหรือไม่ ถ้ายังไม่มีก็จะทำการเซฟเข้าตัวแปรเพื่อเตรียมทำการ insert ต่อไป
                $old_or_new = $this->uom->get_uom_of_product('', $filter_query['where'])->result();
                if (empty($old_or_new)):
                    $save_uom_of_product[] = $filter_query['where'];
                endif;
                // END Add By Akkarapol, 15+16/01/2014, เพิ่มการตรวจเช็คว่า UOM ที่เลือกมานั้น ProductGruop นี้เคยมีแล้วหรือไม่ ถ้ายังไม่มีก็จะทำการเซฟเข้าตัวแปรเพื่อเตรียมทำการ insert ต่อไป

            endforeach;

            $filter_query = array(
                'where' => array(
                    'ProductGroup_Id' => $data['ProductGroup_Id']
                )
            );

            $this->uom->del_uom_of_product($arr_list_chk_uom, $filter_query['where']);
            if (!empty($save_uom_of_product)):
                $result = $this->uom->save_uom_of_product('ist', $save_uom_of_product);
                if ($result > 0):
                    $result = TRUE;
                else:
                    log_message('error', 'save CTL_M_UOM_Template_Of_Product Unsuccess.');
                    $result = FALSE;
                endif;
            endif;
        else:
            $filter_query = array(
                'where' => array(
                    'ProductGroup_Id' => $data['ProductGroup_Id']
                )
            );
            $this->uom->del_uom_of_product('', $filter_query['where']);
        endif;
        // END Add By Akkarapol, 15+16/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductGroup นี้เลือกมาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductGroup ต่อไป แต่ถ้าไม่มีนั่นหมายความว่า จะต้องไปนำข้อมูลที่เคยเลือกไว้แล้วออกไปนั่นเอง
        // Add By Akkarapol, 15+16/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductGroup นี้ทำการ Custom มาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductGroup ต่อไป
        if (!empty($data['custom_uom'])):
            foreach ($data['custom_uom'] as $key_cus_uom => $cus_uom):
                $save_uom_of_product[] = array(
                    'ProductGroup_Id' => $data['ProductGroup_Id'],
                    'standard_unit_in_id' => $cus_uom['in'],
                    'standard_unit_out_id' => $cus_uom['out']
                );
            endforeach;
            $result = $this->uom->save_uom_of_product('ist', $save_uom_of_product);
            if ($result > 0):
                $result = TRUE;
            else:
                log_message('error', 'save CTL_M_UOM_Template_Of_Product Unsuccess.');
                $result = FALSE;
            endif;
        endif;
        // END Add By Akkarapol, 15+16/01/2014, เพิ่มการตรวจสอบว่ามี UOM ใดๆที่ ProductGroup นี้ทำการ Custom มาหรือไม่ ถ้ามีก็จะทำงานในขึ้นตอนของการจัดการ UOM Of ProductGroup ต่อไป

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_product_group($ProductGroup_Code = NULL) {// Check ProductGroup_Code Already.
        $result = FALSE;

        if (!empty($ProductGroup_Code)):
            $r_group = $this->gro->get_CTL_M_ProductGroup(NULL, $ProductGroup_Code, FALSE)->result();
            if (count($r_group) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
