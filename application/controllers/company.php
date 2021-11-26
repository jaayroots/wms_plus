<?php

/*
 * Create by Ton! 20130910
 * Location: ./application/controllers/company.php 
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class company extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("company_model", "com");
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->mnu_NavigationUri = "company";
    }

    public function index() {
        $this->companyList();
    }

    function companyList() {
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
             ONCLICK=\"openForm('company','company/companyProcessec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_company = $this->com->getCompanyList();
        $r_company = $q_company->result();

        // Button View, Edit, Delete in Datatable.
        $column = array("ID", "Company Name", "IsOwner", "IsCustomer", "IsSupplier", "IsVendor", "IsShipper", "IsRenter", "IsRenterBranch", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130
        $datatable = $this->datatable->genTableFixColumn($q_company, $r_company, $column, "company/companyProcessec", $action);

        $this->parser->parse('list_template', array(
            //'user_login' => $this->data['UserLogin_Id']
            'user_login' => $this->session->userdata('user_id')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Company'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('company','company/companyProcessec/','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140130
        ));
    }

    function companyProcessec() {// select processec (Add, Edit Delete) Company.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $companyID = "";
            $this->companyForm($mode, $companyID);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $companyID = $data['id'];
            $this->companyForm($mode, $companyID);
        elseif ($mode == "D") :// Delete
            $companyID = $data['id'];
            $result = $this->deleteCompany($companyID);
            if ($result === TRUE) :
                echo "<script type='text/javascript'>alert('Delete data Company Success.')</script>";
                redirect('company', 'refresh');
            else :
                echo "<script type='text/javascript'>alert('Delete data Company Unsuccess. Please check?')</script>";
                redirect('company', 'refresh');
            endif;
        endif;
    }

    function companyForm($mode, $companyID) {// Form Add&Edit Company. (call view/form/companyMasterForm.php) // Add by Ton! 20130910
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $r_company = $this->com->getCompanyByID($companyID)->result(); // get company by id for pass to form.
        // define for pass to form.     
        $Company_Id = $companyID;
        $BusinessType_Id = "";
        $Company_Code = "";
        $Company_NameEN = "";
        $Company_NameTH = "";
        $Company_Desc = "";
        $IsOwner = "";
        $IsCustomer = "";
        $IsSupplier = "";
        $IsVendor = "";
        $IsShipper = "";
        $IsRenter = "";
        $IsRenterBranch = "";
        $Active = "";

        // Initial define for pass to form edit.
        if (count($r_company) > 0) :
            foreach ($r_company as $companyList) :
                $BusinessType_Id = $companyList->BusinessType_Id;
                $Company_Code = $companyList->Company_Code;
                $Company_NameEN = $companyList->Company_NameEN;
                $Company_NameTH = $companyList->Company_NameTH;
                $Company_Desc = $companyList->Company_Desc;
                $IsOwner = $companyList->IsOwner;
                $IsCustomer = $companyList->IsCustomer;
                $IsSupplier = $companyList->IsSupplier;
                $IsVendor = $companyList->IsVendor;
                $IsShipper = $companyList->IsShipper;
                $IsRenter = $companyList->IsRenter;
                $IsRenterBranch = $companyList->IsRenterBranch;
                $Active = $companyList->Active;
            endforeach;
        endif;

        // get all Business Type for dispaly dropdown list.
        $r_BusinessType = $this->com->getBusinessType()->result();
        $optionBusinessType = genOptionDropdown($r_BusinessType, "BT");

        $this->load->helper('form');
        $str_form = form_fieldset('Company Information');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/company_master', array("mode" => $mode, "Company_Id" => $Company_Id
            , "optionBusinessType" => $optionBusinessType, "BusinessType_Id" => $BusinessType_Id
            , "Company_Code" => $Company_Code, "Company_NameEN" => $Company_NameEN, "Company_NameTH" => $Company_NameTH
            , "Company_Desc" => $Company_Desc, "IsOwner" => $IsOwner, "IsCustomer" => $IsCustomer
            , "IsSupplier" => $IsSupplier, "IsVendor" => $IsVendor, "IsShipper" => $IsShipper
            , "IsRenter" => $IsRenter, "IsRenterBranch" => $IsRenterBranch, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Company'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    private function deleteCompany($companyID = NULL) {// Add by Ton! 20130910
        if (!empty($companyID)):
            $whereCompany['Company_Id'] = ($companyID == "") ? "" : $companyID;
            $rCompany['Active'] = FALSE;
            $this->transaction_db->transaction_start();
            $result = $this->com->saveDataCompany("upd", $rCompany, $whereCompany);
            if ($result === TRUE):
                $this->transaction_db->transaction_commit(); // Save Ok.
            else:
                $this->transaction_db->transaction_rollback(); // Save Not Ok.
            endif;
            return $result;
        else:
            return FALSE;
        endif;
    }

    function validation() {// Add by Ton! 20140228
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check Company Code already exists.
        $r_company = $this->com->getCompanyByCompanyCode($data['Company_Code'], $data['Company_Id'])->result();
        if (count($r_company) > 0) :
            $result['result'] = 0;
            $result['note'] = "COM_CODE_ALREADY";
            echo json_encode($result);
            return;
        endif;

        echo json_encode($result);
        return;
    }

    function saveCompany() {// Add by Ton! 20130910
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %H:%i:%s", time());

        $rCompany['BusinessType_Id'] = ($data['BusinessType_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['BusinessType_Id']);
        $rCompany['Company_Code'] = ($data['Company_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Company_Code']);
        $rCompany['Company_NameEN'] = ($data['Company_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Company_NameEN']);
        $rCompany['Company_NameTH'] = ($data['Company_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Company_NameTH']);
        $rCompany['Company_Desc'] = ($data['Company_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Company_Desc']);

        $IsOwner = $this->input->post("IsOwner");
        if ($IsOwner != 1) :
            $IsOwner = 0;
        endif;
        $rCompany['IsOwner'] = $IsOwner;

        $IsCustomer = $this->input->post("IsCustomer");
        if ($IsCustomer != 1) :
            $IsCustomer = 0;
        endif;
        $rCompany['IsCustomer'] = $IsCustomer;

        $IsSupplier = $this->input->post("IsSupplier");
        if ($IsSupplier != 1) :
            $IsSupplier = 0;
        endif;
        $rCompany['IsSupplier'] = $IsSupplier;

        $IsVendor = $this->input->post("IsVendor");
        if ($IsVendor != 1) :
            $IsVendor = 0;
        endif;
        $rCompany['IsVendor'] = $IsVendor;

        $IsShipper = $this->input->post("IsShipper");
        if ($IsShipper != 1) :
            $IsShipper = 0;
        endif;
        $rCompany['IsShipper'] = $IsShipper;

        $IsRenter = $this->input->post("IsRenter");
        if ($IsRenter != 1) :
            $IsRenter = 0;
        endif;
        $rCompany['IsRenter'] = $IsRenter;

        $IsRenterBranch = $this->input->post("IsRenterBranch");
        if ($IsRenterBranch != 1) :
            $IsRenterBranch = 0;
        endif;
        $rCompany['IsRenterBranch'] = $IsRenterBranch;

        $Active = $this->input->post("Active");
        if ($Active != 1) :
            $Active = 0;
        endif;
        $rCompany['Active'] = $Active;

        $whereCompany['Company_Id'] = ($data['Company_Id'] == "") ? "" : $data['Company_Id'];

        $this->transaction_db->transaction_start();
        $result = TRUE;
        $type = $data['type']; // Add, Edit
        switch ($type) :
            case "A" : {
                    $rCompany['Created_Date'] = $human;
                    $rCompany['Created_By'] = $this->session->userdata('user_id');
                    $rCompany['Modified_Date'] = $human;
                    $rCompany['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->com->saveDataCompany('ist', $rCompany, $whereCompany);
                    if ($result <= 0):
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $rCompany['Modified_Date'] = $human;
                    $rCompany['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->com->saveDataCompany('upd', $rCompany, $whereCompany);
                }break;
        endswitch;

        if ($result == TRUE):
            $this->transaction_db->transaction_commit(); // Save Ok.
        else:
            $this->transaction_db->transaction_rollback(); // Save Not Ok.
        endif;

        echo $result;
    }

}
