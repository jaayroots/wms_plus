<?php

/*
 * Create by Ton! 20130912
 * Location: ./application/controllers/employee.php 
 * Rename employee.php is contact_user.php
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class contact_user extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.
    public $settings;

    function __construct() {
        parent::__construct();
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->load->model("user_model", "user");

        $this->load->model("position_model", "pos");
        $this->load->model("department_model", "dep");

        $this->load->model("company_model", "com");
        $this->load->model("contact_model", "con");
        $this->load->model("product_model", "prod");
        $this->load->model("title_name_model", "ttn");

        $this->mnu_NavigationUri = "contact_user";
        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->contact_user_list();
    }

    function contact_user_list() {
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
             ONCLICK=\"openForm('contact_user','contact_user/contact_user_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_contact_user = $this->con->getContactList();
        $r_contact_user = $q_contact_user->result();

        // Button View, Edit, Delete in Datatable.
        $column = array("ID", "Contact Name", "User Account", "IsCustomer", "IsEmployee", "IsSupplier", "IsVendor", "IsRenter", "IsShipper", "Active");
//        $action = array(VIEW, EDIT, DEL);// Comment Out by Ton! 20140130
        $datatable = $this->datatable->genTableFixColumn($q_contact_user, $r_contact_user, $column, "contact_user/contact_user_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Contact User'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('contact_user','contact_user/contact_user_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function contact_user_processec() {// select processec (Add, Edit Delete) Employee.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $contactID = "";
            $this->contact_user_form($mode, $contactID);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $contactID = $data['id'];
            $this->contact_user_form($mode, $contactID);
        elseif ($mode == "D") :// Delete
            $contactID = $data['id'];
            $this->transaction_db->transaction_start();
            $resultDelUser = $this->delete_user_login($contactID);
            $resultDelCon = $this->delete_contact($contactID);
            if ($resultDelCon == TRUE && $resultDelUser == TRUE) :
                $this->transaction_db->transaction_commit(); // Save Ok.
                echo "<script type='text/javascript'>alert('Delete data success')</script>"; // fix defect 504 : Delete: เพิ่ม POP UP delete success : add by kik : 2013-12-03
                redirect('contact_user', 'refresh');
            else :
                log_message('error', 'Set InActive [ADM_M_UserLogin & CTL_M_Contact] Contact_Id = ' . $contactID . ' Unsuccess.');
                $this->transaction_db->transaction_rollback(); // save Not Ok.
                echo "<script type='text/javascript'>alert('Deleting data contact_user not complete. Please check?')</script>"; // fix defect 504 : Delete: เพิ่ม POP UP delete unsuccess : add by kik : 2013-12-03
                redirect('contact_user', 'refresh');
            endif;
        endif;
    }

    private function delete_contact($contactID) {// save Storage call contact_model\deleteDataContact.
        $result_del = FALSE;
        if (!empty($contactID) && $contactID !== ''):
            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());

            $rContact['Active'] = NO;
            $rContact['Modified_Date'] = $human;
            $rContact['Modified_By'] = $this->session->userdata('user_id');

            $whereContact['Contact_Id'] = $contactID;

            $result_del = $this->con->save_CTL_M_Contact('upd', $rContact, $whereContact);
            if ($result_del === FALSE):
                log_message('error', 'Set InActive [CTL_M_Contact] Contact_Id = ' . $contactID . ' Unsuccess.');
            endif;
        else:
            log_message('error', 'Set InActive [CTL_M_Contact] Unsuccess. Contact_Id IS NULL');
        endif;

        return $result_del;
    }

    private function delete_user_login($contactID) {// save Storage call contact_model\deleteDataUserLogin.
        $result_del = FALSE;

        if (!empty($contactID) && $contactID !== ''):
            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());

            $UserLoginID = NULL;
            $rUser['Active'] = NO;
            $rUser['Modified_Date'] = $human;
            $rUser['Modified_By'] = $this->session->userdata('user_id');
            $r_contact_user = $this->con->getContactByID($contactID)->result();
            if (count($r_contact_user) > 0) :
                foreach ($r_contact_user as $contact_user_list) :
                    $UserLoginID = $contact_user_list->UserLogin_Id;
                endforeach;
                $whereUser['UserLogin_Id'] = $UserLoginID;

                $result_del = $this->con->save_ADM_M_UserLogin('upd', $rUser, $whereUser);
                if ($result_del === FALSE):
                    log_message('error', 'Set InActive [ADM_M_UserLogin] Unsuccess.');
                endif;
            else:
                log_message('error', 'Set InActive [ADM_M_UserLogin] Unsuccess. UserLogin_Id not found');
            endif;
        else:
            log_message('error', 'Set InActive [ADM_M_UserLogin] Unsuccess. Contact_Id IS NULL');
        endif;

        return $result_del;
    }

    function contact_user_form($mode, $contactID) {// Form Add&Edit Contact User. (call view/form/contact_user_master.php) // Add by Ton! 20130912
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $r_contact_user = "";
        if ($contactID != "") :// get contact_user by id for pass to form.
            $r_contact_user = $this->con->getContactByID($contactID)->result();
        endif;
        // define for pass to form.  
        $Contact_Id = $contactID;
        $Contact_Code = "";
        $First_NameEN = "";
        $Last_NameEN = "";
        $First_NameTH = "";
        $Last_NameTH = "";
        $TitleName_Id = ""; //Dropdown List
        $Phone_No1 = "";
        $Phone_No2 = "";
        $Fax_No = "";
        $Email_Address = "";
        $Deparment_Id = ""; //Dropdown List
        $Position_Id = ""; //Dropdown List
        $Company_Id = ""; //Dropdown List
        $IsCustomer = "";
        $IsEmployee = "";
        $IsSupplier = "";
        $IsVendor = "";
        $IsRenter = "";
        $IsShipper = "";
//        $Image_Id="";

        $UserLogin_Id = "";
        $UserAccount = "";
        $Password = "";
        $IsSuperUser = "";

        $Active = "";

        // Initial define for pass to form edit.
        if ($r_contact_user != "") :
            foreach ($r_contact_user as $contact_user_list) :
                $Contact_Code = $contact_user_list->Contact_Code;
                $First_NameEN = $contact_user_list->First_NameEN;
                $Last_NameEN = $contact_user_list->Last_NameEN;
                $First_NameTH = $contact_user_list->First_NameTH;
                $Last_NameTH = $contact_user_list->Last_NameTH;
                $TitleName_Id = $contact_user_list->TitleName_Id;
                $Phone_No1 = $contact_user_list->Phone_No1;
                $Phone_No2 = $contact_user_list->Phone_No2;
                $Fax_No = $contact_user_list->Fax_No;
                $Email_Address = $contact_user_list->Email_Address;
                $Deparment_Id = $contact_user_list->Deparment_Id;
                $Position_Id = $contact_user_list->Position_Id;
                $Company_Id = $contact_user_list->Company_Id;
                $IsCustomer = $contact_user_list->IsCustomer;
                $IsEmployee = $contact_user_list->IsEmployee;
                $IsSupplier = $contact_user_list->IsSupplier;
                $IsVendor = $contact_user_list->IsVendor;
                $IsRenter = $contact_user_list->IsRenter;
                $IsShipper = $contact_user_list->IsShipper;
//                $Image_Id = $contact_user_list->Image_Id;

                $UserLogin_Id = $contact_user_list->UserLogin_Id;
                $UserAccount = $contact_user_list->UserAccount;
                $Password = $contact_user_list->Password;
                $IsSuperUser = $contact_user_list->IsSuperUser;

                if (($contact_user_list->User_Active == TRUE) && ($contact_user_list->Active == TRUE)):
                    $Active = TRUE;
                else:
                    $Active = FALSE;
                endif;

            endforeach;
        endif;

        // get all TitleName for dispaly dropdown list.
        $r_TitleName = $this->ttn->get_CTL_M_TitleName(NULL, NULL, NULL, TRUE)->result(); // Edit by Ton! 20131206
        $optionTitleName = genOptionDropdown($r_TitleName, 'TITLENAME');

        // get all Deparment for dispaly dropdown list.
        $r_Department = $this->dep->get_CTL_M_Department(NULL, NULL, TRUE)->result(); // Edit by Ton! 20131206
        $optionDeparment = genOptionDropdown($r_Department, 'DEPARTMENT');

        // get all Position for dispaly dropdown list.
        $r_Position = $this->pos->get_CTL_M_Position(NULL, NULL, TRUE)->result(); // Edit by Ton! 20131206
        $optionPosition = genOptionDropdown($r_Position, 'POSITION');

        // get all Company for dispaly dropdown list.
        $r_Company = $this->com->getContactAll()->result();
        $optionCompany = genOptionDropdown($r_Company, 'COMPANY');

        // Add by Ton! 20140227
        $ststus_login_list = NULL;
        if ($UserLogin_Id !== ""):
            $ststus_login_list = $this->atn->get_SYS_L_UserLogin($UserLogin_Id, NULL, NULL, TRUE)->result();
        endif;

        # Add by Ton! 20140512 [True = reset random password & send mail., False = reset password = 'password'] 
        $reset_pw = FALSE;
        if (isset($this->settings['reset_password'])):
            $reset_pw = ($this->settings['reset_password'] == "TRUE" ? TRUE : FALSE);
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Contact & User Login Information');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/contact_user_master', array("mode" => $mode, "Contact_Id" => $Contact_Id
            , "Contact_Code" => $Contact_Code, "First_NameEN" => $First_NameEN, "Last_NameEN" => $Last_NameEN
            , "First_NameTH" => $First_NameTH, "Last_NameTH" => $Last_NameTH, "TitleName_Id" => $TitleName_Id
            , "Phone_No1" => $Phone_No1, "Phone_No2" => $Phone_No2, "Fax_No" => $Fax_No, "Email_Address" => $Email_Address
            , "Deparment_Id" => $Deparment_Id, "Position_Id" => $Position_Id, "Company_Id" => $Company_Id, "IsCustomer" => $IsCustomer
            , "IsEmployee" => $IsEmployee, "IsSupplier" => $IsSupplier, "IsVendor" => $IsVendor, "IsRenter" => $IsRenter
            , "IsShipper" => $IsShipper, "UserLogin_Id" => $UserLogin_Id, "UserAccount" => $UserAccount, "Password" => $Password
            , "IsSuperUser" => $IsSuperUser, "optionTitleName" => $optionTitleName, "optionDeparment" => $optionDeparment
            , "optionPosition" => $optionPosition, "optionCompany" => $optionCompany
            , "Active" => $Active, "ststus_login_list" => $ststus_login_list, "reset_pw" => $reset_pw), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Contact & User Login'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clear_data_input()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140210
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # check_contact_code
        $chk_Contact = $this->con->getContaclByCode($data['Contact_Code'], $data['Contact_Id'])->result();
        if (count($chk_Contact) > 0) :
            $result['result'] = 0;
            $result['note'] = "CON_CODE_ALREADY";
            echo json_encode($result);
            return;
        endif;

        # check_user_account
        $chk_User = $this->con->getUserByAccount($data['UserAccount'], $data['UserLogin_Id'])->result();
        if (count($chk_User) > 0) :
            $result['result'] = 0;
            $result['note'] = "USER_CODE_ALREADY";
            echo json_encode($result);
            return;
        endif;

        # check_change_password
        if ($data['type'] === "E"):
            if ($data['fPassword'] !== $data['Password']):

                $currentPW = $data['fPassword'];
                $newPW = sha1($data['Password']);
                $confirmNewPW = sha1($data['conNewPassword']);
                $confirmOldPW = sha1($data['oldPassword']);

                if ($newPW !== $confirmNewPW) : // ยืนยัน Password(ใหม่) ต้องเหมือนกับ Password(ใหม่)!!
                    $result['result'] = 0;
                    $result['note'] = "PASS_CON";
                    echo json_encode($result);
                    return;
                elseif ($currentPW !== $confirmOldPW) : // Password(เดิม) ไม่ถูกต้อง... กรุณาตรวจสอบ !!
                    $result['result'] = 0;
                    $result['note'] = "PASS_OLD";
                    echo json_encode($result);
                    return;
                elseif ($confirmOldPW === $newPW) : // Password(ใหม่) ต้องไม่เหมือนกับ Password(เดิม)!!
                    $result['result'] = 0;
                    $result['note'] = "PASS_NEW";
                    echo json_encode($result);
                    return;
                endif;

                if (isset($this->settings['check_log_password_user'])):// Add by Ton! 20140512
                    $chk_pw = ($this->settings['check_log_password_user'] == "TRUE" ? TRUE : FALSE);
                    if ($chk_pw === TRUE):
                        $path = $this->settings["path"]["log_password_user"];
                        $read_pw = read_file_old_password($data['UserLogin_Id'], $data['Password'], $path);
                        if ($read_pw === TRUE):
                            $result['result'] = 0;
                            $result['note'] = "PASS_NEW_OLD";
                            echo json_encode($result);
                            return;
                        endif;
                    endif;
                endif;

            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_contact_user() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $rContact['Contact_Code'] = (trim($data['Contact_Code']) == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Contact_Code']);
        $rContact['TitleName_Id'] = ($data['TitleName_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['TitleName_Id']);
        $rContact['First_NameEN'] = ($data['First_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['First_NameEN']);
        $rContact['Last_NameEN'] = ($data['Last_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Last_NameEN']);
        $rContact['First_NameTH'] = ($data['First_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['First_NameTH']);
        $rContact['Last_NameTH'] = ($data['Last_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Last_NameTH']);
        $rContact['Phone_No1'] = ($data['PhoneNo1'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['PhoneNo1']);
        $rContact['Phone_No2'] = ($data['PhoneNo2'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['PhoneNo2']);
        $rContact['Fax_No'] = ($data['FaxNo'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['FaxNo']);
        $rContact['Email_Address'] = ($data['EmailAddress'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['EmailAddress']);
        $rContact['Deparment_Id'] = ($data['Deparment_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Deparment_Id']);
        $rContact['Position_Id'] = ($data['Position_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Position_Id']);
        $rContact['Company_Id'] = ($data['Company_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Company_Id']);

        $IsCustomer = $this->input->post("IsCustomer");
        if ($IsCustomer != 1) :
            $IsCustomer = 0;
        endif;
        $rContact['IsCustomer'] = $IsCustomer;

        $IsEmployee = $this->input->post("IsEmployee");
        if ($IsEmployee != 1) :
            $IsEmployee = 0;
        endif;
        $rContact['IsEmployee'] = $IsEmployee;

        $IsSupplier = $this->input->post("IsSupplier");
        if ($IsSupplier != 1) :
            $IsSupplier = 0;
        endif;
        $rContact['IsSupplier'] = $IsSupplier;

        $IsVendor = $this->input->post("IsVendor");
        if ($IsVendor != 1) :
            $IsVendor = 0;
        endif;
        $rContact['IsVendor'] = $IsVendor;

        $IsRenter = $this->input->post("IsRenter");
        if ($IsRenter != 1) :
            $IsRenter = 0;
        endif;
        $rContact['IsRenter'] = $IsRenter;

        $IsShipper = $this->input->post("IsShipper");
        if ($IsShipper != 1) :
            $IsShipper = 0;
        endif;
        $rContact['IsShipper'] = $IsShipper;

        $Active = $this->input->post("Active");
        if ($Active != 1) :
            $Active = 0;
        endif;
        $rContact['Active'] = $Active;

        $whereContact['Contact_Id'] = ($data['Contact_Id'] == "") ? "" : $data['Contact_Id'];

        $this->transaction_db->transaction_start();
        $result_contact = FALSE;
        $result_save_user = FALSE;
        $Contact_Id = NULL;
        $type = $data['type']; // Add, Edit
        switch ($type) :
            case "A" : {
                    $rContact['Created_Date'] = $human;
                    $rContact['Created_By'] = $this->session->userdata('user_id');
                    $rContact['Modified_Date'] = $human;
                    $rContact['Modified_By'] = $this->session->userdata('user_id');
                    $result_contact = $this->con->save_CTL_M_Contact('ist', $rContact);
                    if ($result_contact > 0):
                        $Contact_Id = $result_contact;
                        $result_contact = TRUE;
                    endif;
                }break;
            case "E" : {
                    $rContact['Modified_Date'] = $human;
                    $rContact['Modified_By'] = $this->session->userdata('user_id');
                    $result_contact = $this->con->save_CTL_M_Contact('upd', $rContact, $whereContact);
                }break;
        endswitch;

        if ($result_contact === TRUE):
            $rUser['UserAccount'] = trim(($data['UserAccount'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserAccount']));

            if ($this->input->post("savePW") == "SAVE") :
                $rUser['Password'] = trim(($data['Password'] == "") ? NULL : iconv("UTF-8", "TIS-620", sha1($data['Password'])));
            endif;

            if (!empty($Contact_Id)):// Add New.
                $rUser['Contact_Id'] = $Contact_Id;
            else:// Edit.
                $rUser['Contact_Id'] = ($data['Contact_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Contact_Id']);
            endif;

            $IsSuperUser = $this->input->post("IsSuperUser");
            if ($IsSuperUser != 1) :
                $IsSuperUser = 0;
            endif;
            $rUser['IsSuperUser'] = $IsSuperUser;

            $Active = $this->input->post("Active");
            if ($Active != 1) :
                $Active = 0;
            endif;
            $rUser['Active'] = $Active;

            $whereUser['UserLogin_Id'] = ($data['UserLogin_Id'] == "") ? "" : $data['UserLogin_Id'];

            switch ($type) :
                case "A" : {
                        $rUser['Created_Date'] = $human;
                        $rUser['Created_By'] = $this->session->userdata('user_id');
                        $rUser['Modified_Date'] = $human;
                        $rUser['Modified_By'] = $this->session->userdata('user_id');
                        $result_save_user = $this->con->save_ADM_M_UserLogin('ist', $rUser);
                        if ($result_save_user > 0):
                            $result_save_user = TRUE;
                        endif;
                    }break;
                case "E" : {
                        $rUser['Modified_Date'] = $human;
                        $rUser['Modified_By'] = $this->session->userdata('user_id');
                        $result_save_user = $this->con->save_ADM_M_UserLogin('upd', $rUser, $whereUser);
                    }break;
            endswitch;

            if ($result_save_user === FALSE):
                log_message('error', 'save ADM_M_UserLogin Unsuccess.');
            endif;
        else:
            log_message('error', 'save CTL_M_Contact Unsuccess.');
        endif;

        if ($result_contact && $result_save_user):
            if ($this->input->post("savePW") == "SAVE") : // Add by Ton! 20140512
                if (isset($this->settings['check_log_password_user'])):
                    $chk_pw = ($this->settings['check_log_password_user'] == "TRUE" ? TRUE : FALSE);
                    if ($chk_pw === TRUE):
                        $path = $this->settings["path"]["log_password_user"];
                        write_file_old_password($data['UserLogin_Id'], $data['Password'], $path);
                    endif;
                endif;
            endif;

            $this->transaction_db->transaction_commit();
            $this->cache->memcached->clean();
            echo TRUE;
        else:
            log_message('error', 'save Contact User Unsuccess.');
            $this->transaction_db->transaction_rollback();
            echo FALSE;
        endif;

        exit();
    }

    function check_current_password() {// Add by Ton! 20131225 For user can edit password by myself.
        $Current_Password = sha1($this->input->post('Current_Password'));

        $r_user = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        if (count($r_user) > 0):
//            P($r_user['Password']) . "<br>" . P($Current_Password);
            if ($Current_Password !== $r_user['Password']):
                echo TRUE;
            else:
                echo FALSE;
            endif;
        endif;
    }

    function check_old_password() {// Add by Ton! 20140512
        if (isset($this->settings['check_log_password_user'])):
            $chk_pw = ($this->settings['check_log_password_user'] == "TRUE" ? TRUE : FALSE);
            if ($chk_pw === TRUE):
                $path = $this->settings["path"]["log_password_user"];

                $new_password = $this->input->post('Password');
                $UserLogin_Id = $this->input->post('UserLogin_Id');

                $read_pw = read_file_old_password($UserLogin_Id, $new_password, $path);

                echo $read_pw;
            endif;
        endif;

        echo FALSE;
    }

    function reset_password() {# Reset Random Password & Send mail. Add by Ton! 20140512
        $path = $this->settings["path"]["log_password_user"];
        $UserLogin_Id = $this->input->post('UserLogin_Id');

        $new_password = random_password(6);
        if (!empty($new_password)):
            $this->transaction_db->transaction_start();

            $rUser['Password'] = (sha1($new_password) === "") ? NULL : iconv("UTF-8", "TIS-620", sha1($new_password));
            $whereUser['UserLogin_Id'] = ($UserLogin_Id === "") ? NULL : iconv("UTF-8", "TIS-620", $UserLogin_Id);

            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());
            $rUser['Modified_Date'] = $human;
            $rUser['Modified_By'] = $this->session->userdata('user_id');

            $result = $this->con->save_ADM_M_UserLogin('upd', $rUser, $whereUser);

            if ($result === TRUE):
                if (isset($this->settings['check_log_password_user'])):
                    $chk_pw = ($this->settings['check_log_password_user'] == "TRUE" ? TRUE : FALSE);
                    if ($chk_pw === TRUE):
                        write_file_old_password($UserLogin_Id, $this->input->post('Password'), $path); // Add by Ton! 20140512
                    endif;
                endif;

                $this->transaction_db->transaction_commit();

                if (isset($this->settings['reset_password'])):
                    $reset_pw = ($this->settings['reset_password'] == "TRUE" ? TRUE : FALSE);
                    if ($reset_pw === TRUE):
                        $mail_to = $this->input->post('EmailAddress');
                        $subject = "Reset Password for Logg-in WMSPLUS+ [User Account : " . $this->input->post('UserAccount') . "]";

                        $message = "Your password has been changed to ' " . $new_password . " '." . "<br>";
                        $message .= "If you can not access the system. Please contact Admin.";

                        $headers = "From: " . $this->session->userdata('user_login_mail') . "\r\n" .
                                "Reply-To: " . $this->session->userdata('user_login_mail') . "\r\n" .
                                "X-Mailer: PHP/" . phpversion();

                        send_mail_reset_password($mail_to, $subject, $message, $headers);
                    endif;
                endif;

                echo $new_password;
            else:
                log_message('error', 'save ADM_M_UserLogin Unsuccess. Reset Password Unsuccess.');
                $this->transaction_db->transaction_rollback();
                echo NULL;
            endif;
        else:
            echo FALSE;
        endif;
    }

    function change_password() {// For change password only. (WH Info. & Myself)
        $this->transaction_db->transaction_start();

        $path = $this->settings["path"]["log_password_user"];
        $newPW = sha1($this->input->post('Password'));
        $userId = $this->input->post('UserLogin_Id');

        if (empty($newPW) || empty($userId)):
            echo NULL;
            exit();
        else:
            $rUser['Password'] = ($newPW == "") ? NULL : iconv("UTF-8", "TIS-620", $newPW);
            $whereUser['UserLogin_Id'] = ($userId == "") ? NULL : iconv("UTF-8", "TIS-620", $userId);

            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());
            $rUser['Modified_Date'] = $human;
            $rUser['Modified_By'] = $this->session->userdata('user_id');

            $result = $this->con->save_ADM_M_UserLogin('upd', $rUser, $whereUser);
        endif;

        if ($result == TRUE):
            if (isset($this->settings['check_log_password_user'])):
                $chk_pw = ($this->settings['check_log_password_user'] == "TRUE" ? TRUE : FALSE);
                if ($chk_pw === TRUE):
                    write_file_old_password($userId, $this->input->post('Password'), $path); // Add by Ton! 20140512
                endif;
            endif;

            $this->transaction_db->transaction_commit();
            echo $newPW;
        else:
            log_message('error', 'save ADM_M_UserLogin Unsuccess. Reset Password Unsuccess.');
            $this->transaction_db->transaction_rollback();
            echo NULL;
        endif;
        exit();
    }

    function check_contact_code() {
        $contactId = $this->input->post('Contact_Id');
        $contactCode = $this->input->post('Contact_Code');

        $r_Contact = $this->con->getContaclByCode($contactCode, $contactId)->result();
        if (count($r_Contact) > 0) :
            echo TRUE;
        else :
            echo FALSE;
        endif;
    }

    function check_user_account() {
        $UserLoginId = $this->input->post('UserLogin_Id');
        $UserAccount = $this->input->post('UserAccount');

        $r_User = $this->con->getUserByAccount($UserAccount, $UserLoginId)->result();
        if (count($r_User) > 0) :
            echo TRUE;
        else :
            echo FALSE;
        endif;
    }

    function force_user_logout() {// Add by Ton! 20140227
        $this->transaction_db->transaction_start();
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $dataUser['User_Login'] = FALSE;
        $dataUser['Force_Logout_Time'] = time();
        $dataUser['Force_Logout_Date'] = $human;

        $whereUser['Log_Id'] = $data['Log_id'];
        $result_logout = $this->atn->save_SYS_L_UserLogin('upd', $dataUser, $whereUser);
        if ($result_logout == TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save SYS_L_UserLogin Unsuccess. Force User Logout Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result_logout;
    }

    function get_about_user_detail() {// Add by Ton! 20140314
        $json = array();
        $about_user = array();

        $UserLoginId = $this->input->post('UserLogin_Id');
        if ($UserLoginId !== ""):
            $data_group = $this->atn->get_ADM_R_UserGroupMembers(NULL, NULL, NULL, $UserLoginId, TRUE)->result();
            $data_role = $this->atn->get_ADM_R_UserRoleMembers(NULL, NULL, NULL, $UserLoginId, TRUE)->result();

            $group_id = array();
            if (count($data_group) > 0):
                $i = 0;
                foreach ($data_group as $value_group) :
                    $group_id[] = $value_group->UserGroup_Id;
                    $about_user['group'][$i]['UserGroup_Id'] = $value_group->UserGroup_Id;
                    $about_user['group'][$i]['UserGroup_Code'] = $value_group->UserGroup_Code;
                    $about_user['group'][$i]['UserGroup_Name'] = $value_group->UserGroup_NameEN;
                    $i++;
                endforeach;
            endif;
            if (count($group_id) > 1):
                $group_id = implode(",", $group_id);
            endif;

            $role_id = array();
            if (count($data_role) > 0):
                $j = 0;
                foreach ($data_role as $value_role) :
                    $role_id[] = $value_role->UserRole_Id;
                    $about_user['role'][$j]['UserRole_Id'] = $value_role->UserRole_Id;
                    $about_user['role'][$j]['UserRole_Code'] = $value_role->UserRole_Code;
                    $about_user['role'][$j]['UserRole_Name'] = $value_role->UserRole_NameEN;
                    $j++;
                endforeach;
            endif;
            if (count($role_id) > 1):
                $role_id = implode(",", $role_id);
            endif;

            $data_individual = $this->con->get_about_user_individual($UserLoginId, $group_id, $role_id)->result();
            if (count($data_individual) > 0):
                $k = 0;
                foreach ($data_individual as $value_individual) :
                    $about_user['individual_mnu'][$k]['MenuBar_Id'] = $value_individual->MenuBar_Id;
                    $about_user['individual_mnu'][$k]['MenuBar_Code'] = $value_individual->MenuBar_Code;
                    $about_user['individual_mnu'][$k]['MenuBar_Name'] = $value_individual->MenuBar_NameEn;
                    $k++;
                endforeach;
            endif;
        endif;

        $json['$about_user_detail'][] = $about_user;
//        P($json);exit();

        echo json_encode($json);
    }

}
