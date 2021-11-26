<?php

/**
 * Description of Admin_model
 * -------------------------------------
 * Put this class on project at 22/04/2013 
 * @author Pakkaphon P.(PHP PG) 
 * Create by NetBean IDE 7.3
 * SWA WMS PLUS Project.
 * Use with dispatch module function
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class Authen_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function verify_user($user, $password) {
        //        $q = $this->db->where('UserAccount', $user)
        //                ->where('Password', sha1($password))//use sha1 for encrytion class for CI
        //                ->where('Active', TRUE)// Add by Ton! 20140210
        //                ->limit(1)
        //                ->get('ADM_M_UserLogin');
        ////        echo $this->db->last_query();exit();
        ////        P($q->row());exit();
        //        if ($q->num_rows > 0) :
        //            // person has account with us
        //            return $q->row();
        //        else :
        //            return false;
        //        endif;
        //        
                # Edit by Ton! 20140512
                $this->db->select('ADM_M_UserLogin.*, CTL_M_Contact.Email_Address, ADM_M_UserGroup.UserGroup_Code');
                $this->db->from("ADM_M_UserLogin");
                $this->db->join("CTL_M_Contact", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
                $this->db->join("ADM_R_UserGroupMembers", "ADM_M_UserLogin.UserLogin_Id = ADM_R_UserGroupMembers.UserLogin_Id", "LEFT");
                $this->db->join("ADM_M_UserGroup", "ADM_R_UserGroupMembers.UserGroup_Id = ADM_M_UserGroup.UserGroup_Id", "LEFT");
                $this->db->where('ADM_M_UserLogin.UserAccount', $user);
                $this->db->where('ADM_M_UserLogin.Password', sha1($password));
                $this->db->where('ADM_M_UserLogin.Active', TRUE);
                $this->db->limit(1);
                $result = $this->db->get()->result();
             
                // p($result->conn_id->affected_rows);
                // exit;
            //    echo $this->db->last_query();exit();
                if (count($result) > 0) :
                    return $result[0];
                else:
                    return false;
                endif;
            }

    public function check_user_inactive($user) {// Add by Ton! 20140320
        $this->db->select('ADM_M_UserLogin.UserLogin_Id');
        $this->db->from("ADM_M_UserLogin");
        $this->db->where('ADM_M_UserLogin.UserAccount', $user);
        $this->db->where('ADM_M_UserLogin.Active', FALSE);
        $result = $this->db->get()->result();
        if (count($result) > 0):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    public function getBranch() {
//        $str = "CTL_M_Company a WHERE Active = 1 AND IsCustomer != 1";
        $query = $this->db->get("CTL_M_Company a WHERE Active = " . TRUE . " AND IsCustomer != " . TRUE);
        return $query;
    }

    public function getRenter() {
//        $str = "CTL_M_Company a WHERE Active = 1 AND IsRenter = 1";
        $query = $this->db->get("CTL_M_Company a WHERE Active = " . TRUE . " AND IsRenter = " . TRUE);
        return $query;
    }

    // Add BY Akkarapol, 16/12/2013, เพิ่มฟังก์ชั่นสำหรับ get_branch ด้วย $branch_id 
    public function get_branch_by_id($branch_id = '') {
        $this->db->select('Company_Id,Company_NameEN'); // Add By Akkarapol, 16/12/2013, Add Company_NameEN for Display "Company Name" 
        $this->db->where('Active ', TRUE);
        $this->db->where('IsCustomer != 1');
        $this->db->limit(1);
        if (!empty($branch_id)):
        //$this->db->where('Company_Id', $branch_id);
        endif;
        $query = $this->db->get('CTL_M_Company');
        return $query;
    }

    // END Add BY Akkarapol, 16/12/2013, เพิ่มฟังก์ชั่นสำหรับ get_branch ด้วย $branch_id 

    public function get_branch() {
        $this->db->select('Company_Id,Company_NameEN'); // Add By Akkarapol, 16/12/2013, Add Company_NameEN for Display "Company Name" 
        $this->db->where('Active ', TRUE);
        $this->db->where('IsCustomer != 1 ');
        $this->db->limit(1);
        $query = $this->db->get('CTL_M_Company');
        
        return $query;
    }

    public function get_renter($renter) {
        $this->db->select('Company_Id,Company_NameEN'); // Add By Akkarapol, 16/12/2013, Add Company_NameEN for Display "Company Name" 
        $this->db->where('Active ', TRUE);
        $this->db->where('IsRenter ', TRUE);
        $this->db->where('Company_Code', $renter);
        $this->db->limit(1);
        $query = $this->db->get('CTL_M_Company');
        return $query;
    }

    //
    public function get_renter_login($renter) {
        $this->db->select('Company_Id,Company_NameEN'); // Add By Akkarapol, 16/12/2013, Add Company_NameEN for Display "Company Name"
        $this->db->where('Company_Code', $renter);
        $this->db->limit(1);
        $query = $this->db->get('CTL_M_Company');
        return $query;
    }

    public function get_department($renter) {
        $this->db->select('Company_Id,Company_NameEN'); // Add By Akkarapol, 16/12/2013, Add Company_NameEN for Display "Company Name"
        $this->db->where('Active ', TRUE);
        $this->db->where('Company_Code', $renter);
        $this->db->limit(1);
        $query = $this->db->get('CTL_M_Company');
        return $query;
    }

    # Edit by Ton!20140314
    # For get data SYS_M_Browser by Browser_Id Or Browser_Name.
    # Parameter - Browser_Id(int), - Browser_Name(string)
    # Return - SQL Query.

    public function getBrowser($Browser_Id = NULL, $Browser_Name = NULL) {
        $this->db->select("SYS_M_Browser.*");
        $this->db->from("SYS_M_Browser");
        if ($Browser_Id != NULL):
            $this->db->where("SYS_M_Browser.Browser_Id", $Browser_Id);
        endif;
        if ($Browser_Name != NULL):
            $this->db->where("SYS_M_Browser.Browser_Name", $Browser_Name);
        endif;
        $this->db->where("SYS_M_Browser.Active", TRUE);
        $query = $this->db->get();
        return $query;
    }

    # Edit by Ton!20140313
    # For get data SYS_L_UserLogin.
    # Parameter - UserLogin_Id(int), - $IP_Address(string), - $App_Type(string HH, PC), - $Logged_in(TRUE, FALSE)
    # Return - SQL Query

    public function get_SYS_L_UserLogin($UserLogin_Id = NULL, $IP_Address = NULL, $App_Type = NULL, $Logged_in = TRUE) {
        $this->db->select("SYS_L_UserLogin.Log_Id
                    , SYS_L_UserLogin.UserLogin_Id
                    , SYS_L_UserLogin.IP_Address
                    , SYS_L_UserLogin.Browser_Id
                    , SYS_M_Browser.Browser_Name
                    , SYS_L_UserLogin.Browser_Version
                    , SYS_L_UserLogin.Login_Date
                    , SYS_L_UserLogin.Login_Time
                    , SYS_L_UserLogin.Expiration_Time
                    , SYS_L_UserLogin.User_Login
                    , SYS_L_UserLogin.Force_Logout_Date
                    , SYS_L_UserLogin.Force_Logout_Date
                    , SYS_L_UserLogin.App_Type");
        $this->db->from("SYS_L_UserLogin");
        $this->db->join("SYS_M_Browser", "SYS_M_Browser.Browser_Id = SYS_L_UserLogin.Browser_Id", "left");

        if (!empty($UserLogin_Id)):
            $this->db->where("SYS_L_UserLogin.UserLogin_Id", $UserLogin_Id);
        endif;

        if (!empty($IP_Address)):
            $this->db->where("SYS_L_UserLogin.IP_Address", $IP_Address);
        endif;

        if (!empty($App_Type)):
            $this->db->where("SYS_L_UserLogin.App_Type", $App_Type);
        endif;

        if ($Logged_in == TRUE):
            $this->db->where("SYS_L_UserLogin.User_Login", TRUE);
        endif;

        $this->db->where("SYS_M_Browser.Active", 'YES');
        $query = $this->db->get();
       // p('-'.$UserLogin_Id.'-');
    //    echo $this->db->last_query();exit();
        return $query;
    }

    # Add by Ton!20131101
    # For save data SYS_L_UserLogin (insert Or update).
    # Parameter - $type(string)['ist' = insert, 'upd' = update], - $data(string), - $where(string).
    # Return - insert $log_id(int) Or FALSE, - update TRUE Or FALSE.

    public function save_SYS_L_UserLogin($type = NULL, $data = NULL, $where = NULL) {
        
        if ($type !== NULL):
            if ($type === 'ist'):
                 $pss = $this->db->insert('SYS_L_UserLogin', $data);
                $log_id = $this->db->insert_id();
                $log_id = 1;
                if ($log_id !== 0):
                    return $log_id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type === 'upd'):
                $this->db->where($where);
                $this->db->update('SYS_L_UserLogin', $data);
//                echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    function get_ADM_M_UserGroup_List() {
        $this->db->select("ADM_M_UserGroup.UserGroup_Id AS Id
            , ADM_M_UserGroup.UserGroup_Code
            , ADM_M_UserGroup.UserGroup_NameEN
            , ADM_M_UserGroup.UserGroup_NameTH
            , ADM_M_UserGroup.UserGroup_Desc
            , CASE WHEN ADM_M_UserGroup.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("ADM_M_UserGroup");
        $this->db->order_by("ADM_M_UserGroup.UserGroup_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    # Add by Ton!20131111
    # For get data ADM_M_UserGroup by UserGroup_Id Or UserGroup_Code.
    # Parameter - UserGroup_Id(int), - UserGroup_Code(string), - $Object(TRUE Or FALSE), - $Check(TRUE Or FALSE)
    # Return - ADM_M_UserGroup.* Object Or SQL Query. ($Object), - TRUE Or FALSE ($Check)

    function get_ADM_M_UserGroup($UserGroup_Id = NULL, $UserGroup_Code = NULL, $Active = TRUE) {
        $this->db->select("ADM_M_UserGroup.UserGroup_Id
                , ADM_M_UserGroup.UserGroup_Code
                , ADM_M_UserGroup.UserGroup_NameEN
                , ADM_M_UserGroup.UserGroup_NameTH
                , ADM_M_UserGroup.UserGroup_Desc
                , ADM_M_UserGroup.Active");
        $this->db->from("ADM_M_UserGroup");
        if (!empty($UserGroup_Id)):
            $this->db->where("ADM_M_UserGroup.UserGroup_Id", $UserGroup_Id);
        endif;
        if (!empty($UserGroup_Code)):
            $this->db->where("ADM_M_UserGroup.UserGroup_Code", $UserGroup_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_M_UserGroup.Active", YES);
        endif;

        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    # Add by Ton!20131111
    # For save data ADM_M_UserGroup (insert Or update).
    # Parameter - $type(string)['ist' = insert, 'upd' = update], - $data(string), - $where(string).
    # Return - insert UserGroup_Id(int) Or FALSE, - update TRUE Or FALSE.

    function save_ADM_M_UserGroup($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_UserGroup', $data);
                $UserGroup_Id = $this->db->insert_id();
                if ($UserGroup_Id != 0):
                    return $UserGroup_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_M_UserGroup', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    # Add by Ton!20131111
    # For get data ADM_R_UserGroupMembers by UserGroupMember_Id Or UserGroup_Id Or UserLogin_Id.
    # Parameter - UserGroupMember_Id(int), - UserGroup_Id(int), - UserLogin_Id(int), - $Object(TRUE Or FALSE), - $Check(TRUE Or FALSE)
    # Return - ADM_R_UserGroupMembers.* Object Or SQL Query. ($Object), - TRUE Or FALSE ($Check)

    function get_ADM_R_UserGroupMembers($Columns = NULL, $UserGroupMember_Id = NULL, $UserGroup_Id = NULL, $UserLogin_Id = NULL, $Active = TRUE) {
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("ADM_R_UserGroupMembers.UserGroupMember_Id
                , ADM_R_UserGroupMembers.UserGroup_Id
                , ADM_M_UserGroup.UserGroup_Code
                , ADM_M_UserGroup.UserGroup_NameEN
                , ADM_R_UserGroupMembers.UserLogin_Id
                , ADM_R_UserGroupMembers.Active");
        endif;
        $this->db->from("ADM_R_UserGroupMembers");
        $this->db->join("ADM_M_UserGroup", "ADM_M_UserGroup.UserGroup_Id = ADM_R_UserGroupMembers.UserGroup_Id", "LEFT"); // Add by Ton! 20140314
        if (!empty($UserGroupMember_Id)):
            $this->db->where("ADM_R_UserGroupMembers.UserGroupMember_Id", $UserGroupMember_Id);
        endif;
        if (!empty($UserGroup_Id)):
            $this->db->where("ADM_R_UserGroupMembers.UserGroup_Id", $UserGroup_Id);
        endif;
        if (!empty($UserLogin_Id)):
            $this->db->where("ADM_R_UserGroupMembers.UserLogin_Id", $UserLogin_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_R_UserGroupMembers.Active", TRUE);
        endif;
        $this->db->order_by("ADM_R_UserGroupMembers.UserLogin_Id");

        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    # Add by Ton!20131111
    # For save data ADM_R_UserGroupMembers (insert Or update).
    # Parameter - $type(string)['ist' = insert, 'upd' = update], - $data(string), - $where(string).
    # Return - insert UserGroupMember_Id(int) Or FALSE, - update TRUE Or FALSE.

    function save_ADM_R_UserGroupMembers($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_R_UserGroupMembers', $data);
                $UserGroupMember_Id = $this->db->insert_id();
                if ($UserGroupMember_Id != 0):
                    return $UserGroupMember_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_R_UserGroupMembers', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'del'):// Add by Ton! 20140314
                $this->db->delete('ADM_R_UserGroupMembers', $where);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    # Add by Ton!20131111
    # For get data ADM_R_UserGroupMenus by UserGroupMenu_Id Or UserGroup_Id Or Menu_Id.
    # Parameter - UserGroupMenu_Id(int), - UserGroup_Id(int), - Menu_Id(int), - $Object(TRUE Or FALSE), - $Check(TRUE Or FALSE)
    # Return - ADM_R_UserGroupMenus.* Object Or SQL Query. ($Object), - TRUE Or FALSE ($Check)

    function get_ADM_R_UserGroupMenus($Columns = NULL, $UserGroupMenu_Id = NULL, $UserGroup_Id = NULL, $Menu_Id = NULL, $Active = TRUE) {
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("ADM_R_UserGroupMenus.UserGroupMenu_Id
                , ADM_R_UserGroupMenus.UserGroup_Id
                , ADM_R_UserGroupMenus.Menu_Id
                , ADM_R_UserGroupMenus.IsView
                , ADM_R_UserGroupMenus.IsCreate
                , ADM_R_UserGroupMenus.IsEdit
                , ADM_R_UserGroupMenus.IsDelete
                , ADM_R_UserGroupMenus.Active");
        endif;
        $this->db->from("ADM_R_UserGroupMenus");
        if (!empty($UserGroupMenu_Id)):
            $this->db->where("ADM_R_UserGroupMenus.UserGroupMenu_Id", $UserGroupMenu_Id);
        endif;
        if (!empty($UserGroup_Id)):
            $this->db->where("ADM_R_UserGroupMenus.UserGroup_Id", $UserGroup_Id);
        endif;
        if (!empty($Menu_Id)):
            $this->db->where("ADM_R_UserGroupMenus.Menu_Id", $Menu_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_R_UserGroupMenus.Active", YES);
        endif;

        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;
    }

    # Add by Ton!20131111
    # For save data ADM_R_UserGroupMenus (insert Or update).
    # Parameter - $type(string)['ist' = insert, 'upd' = update], - $data(string), - $where(string).
    # Return - insert UserGroupMenu_Id(int) Or FALSE, - update TRUE Or FALSE.

    function save_ADM_R_UserGroupMenus($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_R_UserGroupMenus', $data);
                $UserGroupMenu_Id = $this->db->insert_id();
                if ($UserGroupMenu_Id != 0):
                    return $UserGroupMenu_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'): $this->db->where($where);
                $this->db->update('ADM_R_UserGroupMenus', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    function get_ADM_M_MenuBar_List($Menu_Type = 'PC') {
        $this->db->select("ADM_M_MenuBar.MenuBar_Id AS Id
            , ADM_M_MenuBar.MenuBar_Code
            , ADM_M_MenuBar.MenuBar_NameEn
            , ADM_M_MenuBar.MenuBar_NameTh
            , CASE WHEN ADM_M_MenuBar.Active IN ('" . YES . "') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("ADM_M_MenuBar");
        $this->db->where("ADM_M_MenuBar.Parent_Id", 0);
        $this->db->where("ADM_M_MenuBar.Menu_Type", $Menu_Type);
        $this->db->order_by("ADM_M_MenuBar.MenuBar_Id, ADM_M_MenuBar.Sequence");
        $query = $this->db->get();

        //echo $this->db->last_query();
        return $query;
    }

    # Add by Ton!20131111
    # For get data ADM_M_MenuBar by MenuBar_Id Or MenuBar_Code.
    # Parameter - $get_Parent(TRUE Or FALSE), - $Parent_Id(int), - MenuBar_Id(int), - MenuBar_Code(string), - $Active(TRUE Or FALSE)
    # Return - ADM_M_MenuBar.* Object Or SQL Query. ($Object), - TRUE Or FALSE ($Check)

    function get_ADM_M_MenuBar($get_Parent = FALSE, $Parent_Id = NULL, $MenuBar_Id = NULL, $MenuBar_Code = NULL, $Active = TRUE, $Menu_Type = NULL) {
        $this->db->select("ADM_M_MenuBar.MenuBar_Id
            , ADM_M_MenuBar.Parent_Id
            , ADM_M_MenuBar.MenuBar_Code
            , ADM_M_MenuBar.MenuBar_NameEn
            , ADM_M_MenuBar.MenuBar_NameTh
            , ADM_M_MenuBar.MenuBar_Desc
            , ADM_M_MenuBar.NavigationUri
            , ADM_M_MenuBar.Sequence
            , ADM_M_MenuBar.Icon_Image_Id
            , ADM_M_MenuBar.IsUri
            , ADM_M_MenuBar.Active
            , ADM_M_MenuBar.Menu_Type
            , ADM_M_ImageItem.ImageName + ADM_M_ImageItem.ImageExt AS ImageName
            , ADM_M_MenuBar.Module
            , SYS_M_Action_Menu.IsView
            , SYS_M_Action_Menu.IsAdd
            , SYS_M_Action_Menu.IsEdit
            , SYS_M_Action_Menu.IsDelete");
        $this->db->from("ADM_M_MenuBar");
        $this->db->join("ADM_M_ImageItem", "ADM_M_ImageItem.ImageItem_Id = ADM_M_MenuBar.Icon_Image_Id", "LEFT");
        $this->db->join("SYS_M_Action_Menu", "SYS_M_Action_Menu.MenuBar_Id = ADM_M_MenuBar.MenuBar_Id", "LEFT");
        if (!empty($MenuBar_Id)):
            $this->db->where("ADM_M_MenuBar.MenuBar_Id", $MenuBar_Id);
        endif;
        if (!empty($MenuBar_Code)):
            $this->db->where("ADM_M_MenuBar.MenuBar_Code", $MenuBar_Code);
        endif;
        if ($get_Parent === TRUE):
            $this->db->where("ADM_M_MenuBar.Parent_Id", 0);
        else:
            if (!empty($Parent_Id)):
                $this->db->where("ADM_M_MenuBar.Parent_Id", $Parent_Id);
            endif;
        endif;
        if (!empty($Menu_Type)):
            $this->db->where("ADM_M_MenuBar.Menu_Type", $Menu_Type);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_M_MenuBar.Active", YES);
        endif;
        $this->db->order_by("ADM_M_MenuBar.MenuBar_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    # Add by Ton!20131111
    # For save data ADM_M_MenuBar (insert Or update).
    # Parameter - $type(string)['ist' = insert, 'upd' = update], - $data(string), - $where(string).
    # Return - insert MenuBar_Id(int) Or FALSE, - update TRUE Or FALSE.

    function save_ADM_M_MenuBar($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_MenuBar', $data);
//            echo $this->db->last_query();exit();
                $MenuBar_Id = $this->db->insert_id();
                if ($MenuBar_Id != 0):
                    return $MenuBar_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_M_MenuBar', $data);
//                echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            endif;

            $this->cache->memcached->clean();
        else:
            return FALSE;
        endif;
    }

    function set_active_ADM_M_MenuBar($MenuBar_Id = NULL, $active = TRUE, $Parent = TRUE) {
        if (!empty($MenuBar_Id)):
            if ($Parent === TRUE):
                $this->db->where('(ADM_M_MenuBar.MenuBar_Id = ' . $MenuBar_Id . ' AND ADM_M_MenuBar.Parent_Id = 0) OR (ADM_M_MenuBar.Parent_Id = ' . $MenuBar_Id . ')');
            else:
                $this->db->where('ADM_M_MenuBar.MenuBar_Id = ' . $MenuBar_Id);
            endif;
            if ($active === TRUE):
                $this->db->update('ADM_M_MenuBar', array('ADM_M_MenuBar.Active' => 1));
            else:
                $this->db->update('ADM_M_MenuBar', array('ADM_M_MenuBar.Active' => 0));
            endif;
//            echo $this->db->last_query();exit();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows > 0) :
                $this->cache->memcached->clean();
                return TRUE; // Save Success.
            else:
                return FALSE; // Save Unsuccess.
            endif;
        else:
            return FALSE; // Save Unsuccess.
        endif;
    }

//    function get_ADM_M_ImageItem($ImageItem_Id = NULL) {
    function get_ADM_M_ImageItem($ImageItem_Id = NULL, $ImageName = NULL, $ImageCategory_Id = NULL, $Active = TRUE) {// Edit by Ton! 20131217
        $this->db->select("ADM_M_ImageItem.*");
        $this->db->from("ADM_M_ImageItem");
        if (!empty($ImageItem_Id)):
            $this->db->where("ADM_M_ImageItem.ImageItem_Id", $ImageItem_Id);
        endif;
        if (!empty($ImageName)):
            $this->db->where("ADM_M_ImageItem.ImageName", $ImageName);
        endif;
        if (!empty($ImageCategory_Id)):
            $this->db->where("ADM_M_ImageItem.ImageCategory_Id", $ImageCategory_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_M_ImageItem.Active", YES);
        endif;
//        $this->db->where("ADM_M_ImageItem.Active", 1);
//        $this->db->order_by("ADM_M_ImageItem.ImageItem_Id, ADM_M_ImageItem.ImageCategory_Id");
        $this->db->order_by("ADM_M_ImageItem.ImageCategory_Id, ADM_M_ImageItem.ImageItem_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function get_member_list() {
        $this->db->select("ADM_M_UserLogin.UserLogin_Id
            , ADM_M_UserLogin.UserAccount
            , CTL_M_Contact.First_NameEN + ' ' + CTL_M_Contact.Last_NameEN AS Contact_Name
            , CTL_M_Company.Company_NameEN AS Company_Name");
        $this->db->from("ADM_M_UserLogin");
        $this->db->join("CTL_M_Contact", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
        $this->db->join("CTL_M_Company", "CTL_M_Company.Company_Id = CTL_M_Contact.Company_Id", "LEFT");
        $this->db->where("ADM_M_UserLogin.Active", YES);
        $this->db->order_by("ADM_M_UserLogin.UserLogin_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function get_SYS_M_Action_Menu($MenuBar_Id) {
        $this->db->select("SYS_M_Action_Menu.*");
        $this->db->from("SYS_M_Action_Menu");
        if ($MenuBar_Id != NULL):
            $this->db->where("SYS_M_Action_Menu.MenuBar_Id", $MenuBar_Id);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }


    function get_ADM_M_User_Permission($UserLogin_Id = NULL, $MenuBar_Id = NULL, $Edge_Id = NULL, $Action_Id = NULL, $NavigationUri = NULL) {// Add by Ton! 20140116
        $this->db->select("ADM_M_User_Permission.*");
        $this->db->from("ADM_M_User_Permission");
        $this->db->join("ADM_M_MenuBar", "ADM_M_MenuBar.MenuBar_Id = ADM_M_User_Permission.MenuBar_Id", "LEFT");        
        
        if ($UserLogin_Id != NULL):
            $this->db->where_in("ADM_M_User_Permission.UserLogin_Id", explode(",", $UserLogin_Id));
//        $this->db->where("ADM_M_User_Permission.UserLogin_Id IN (" . $UserLogin_Id . ")");
        endif;
        if ($MenuBar_Id != NULL):
            $this->db->where_in("ADM_M_User_Permission.MenuBar_Id", explode(",", $MenuBar_Id));
//        $this->db->where("ADM_M_User_Permission.MenuBar_Id IN (" . $MenuBar_Id . ")");
        endif;
        if ($Edge_Id != NULL):
            $this->db->where_in("ADM_M_User_Permission.Edge_Id", explode(",", $Edge_Id));
//        $this->db->where("ADM_M_User_Permission.Edge_Id IN (" . $Edge_Id . ")");
        endif;
        if ($Action_Id != NULL):
            $this->db->where_in("ADM_M_User_Permission.Action_Id", explode(",", $Action_Id));
//        $this->db->where("ADM_M_User_Permission.Action_Id IN (" . $Action_Id . ")");
        endif;
        if (!empty($NavigationUri)):
            $srt_NavigationUri = "";
            foreach ($NavigationUri as $value) :
                if ($srt_NavigationUri === ""):
                    $srt_NavigationUri = "'" . $value . "'";
                else:
                    $srt_NavigationUri = $srt_NavigationUri . ", " . "'" . $value . "'";
                endif;
            endforeach;

            $this->db->where("ADM_M_MenuBar.NavigationUri IN (" . $srt_NavigationUri . ")");
        endif;
        $query = $this->db->get();
// 		echo $this->db->last_query(); exit;
        return $query;
    }

    function save_ADM_M_User_Permission($type = NULL, $data = NULL, $where = NULL, $sql = NULL) {// Add by Ton! 20140116
        if (empty($sql)):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_User_Permission', $data);
//            echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Insert Success.
                else:
                    return FALSE; // Insert Unsuccess.
                endif;
            elseif ($type == 'upd'): $this->db->where($where);
                $this->db->update('ADM_M_User_Permission', $data);
//            echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Update Success.
                else:
                    return FALSE; // Update Unsuccess.
                endif;
            elseif ($type == 'del') :
                $this->db->where($where);
                $this->db->delete('ADM_M_User_Permission');
//            echo $this->db->last_query();exit();
            endif;
        else:
            $this->db->query($sql);
//        echo $this->db->last_query();exit();
        endif;
    }

    function get_ADM_M_Group_Permission($UserGroup_Id = NULL, $MenuBar_Id = NULL, $Edge_Id = NULL, $Action_Id = NULL, $NavigationUri = NULL) {// Add by Ton! 20140123
        $this->db->select("ADM_M_Group_Permission.*");
        $this->db->from("ADM_M_Group_Permission");
        $this->db->join("ADM_M_MenuBar", "ADM_M_MenuBar.MenuBar_Id = ADM_M_Group_Permission.MenuBar_Id", "LEFT");
        
        if ($UserGroup_Id != NULL):
            $this->db->where_in("ADM_M_Group_Permission.UserGroup_Id ", explode(",", $UserGroup_Id));
//            $this->db->where("ADM_M_Group_Permission.UserGroup_Id IN (" . $UserGroup_Id . ")");
        endif;
        if ($MenuBar_Id != NULL):
            $this->db->where_in("ADM_M_Group_Permission.MenuBar_Id", explode(",", $UserGroup_Id));
//        $this->db->where("ADM_M_Group_Permission.MenuBar_Id IN (" . $UserGroup_Id . ")");
        endif;
        if ($Edge_Id != NULL):
            $this->db->where_in("ADM_M_Group_Permission.Edge_Id", explode(",", $Edge_Id));
//        $this->db->where("ADM_M_Group_Permission.Edge_Id IN (" . $Edge_Id . ")");
        endif;
        if ($Action_Id != NULL):
            $this->db->where_in("ADM_M_Group_Permission.Action_Id", explode(",", $Action_Id));
//        $this->db->where("ADM_M_Group_Permission.Action_Id IN (" . $Action_Id . ")");
        endif;
        if (!empty($NavigationUri)):
            $srt_NavigationUri = "";
            foreach ($NavigationUri as $value) :
                if ($srt_NavigationUri === ""):
                    $srt_NavigationUri = "'" . $value . "'";
                else:
                    $srt_NavigationUri = $srt_NavigationUri . ", " . "'" . $value . "'";
                endif;
            endforeach;

            $this->db->where("ADM_M_MenuBar.NavigationUri IN (" . $srt_NavigationUri . ")");
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function save_ADM_M_Group_Permission($type = NULL, $data = NULL, $where = NULL, $sql = NULL) {// Add by Ton! 20140123
        if (empty($sql)):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_Group_Permission', $data);
//            echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Insert Success.
                else:
                    return FALSE; // Insert Unsuccess.
                endif;
            elseif ($type == 'upd'): $this->db->where($where);
                $this->db->update('ADM_M_Group_Permission', $data);
//            echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Update Success.
                else:
                    return FALSE; // Update Unsuccess.
                endif;
            elseif ($type == 'del') : $this->db->where($where);
                $this->db->delete('ADM_M_Group_Permission', $data);
//            echo $this->db->last_query();exit();
            endif;
        else:
            $this->db->query($sql);
        endif;
    }

    function get_ADM_M_Role_Permission($UserRole_Id = NULL, $MenuBar_Id = NULL, $Edge_Id = NULL, $Action_Id = NULL, $NavigationUri = NULL) {// Add by Ton! 20140123
        $this->db->select("ADM_M_Role_Permission.*");
        $this->db->from("ADM_M_Role_Permission");
        $this->db->join("ADM_M_MenuBar", "ADM_M_MenuBar.MenuBar_Id = ADM_M_Role_Permission.MenuBar_Id", "LEFT");
        if ($UserRole_Id != NULL):
            $this->db->where_in("ADM_M_Role_Permission.UserRole_Id", explode(",", $UserRole_Id));
//        $this->db->where("ADM_M_Role_Permission.UserRole_Id IN (" . $UserRole_Id . ")");
        endif;
        if ($MenuBar_Id != NULL):
            $this->db->where_in("ADM_M_Role_Permission.MenuBar_Id", explode(",", $MenuBar_Id));
//        $this->db->where("ADM_M_Role_Permission.MenuBar_Id IN (" . $MenuBar_Id . ")");
        endif;
        if ($Edge_Id != NULL):
            $this->db->where_in("ADM_M_Role_Permission.Edge_Id", explode(",", $Edge_Id));
//        $this->db->where("ADM_M_Role_Permission.Edge_Id IN (" . $Edge_Id . ")");
        endif;
        if ($Action_Id != NULL):
            $this->db->where_in("ADM_M_Role_Permission.Action_Id", explode(",", $Action_Id));
//        $this->db->where("ADM_M_Role_Permission.Action_Id IN (" . $Action_Id . ")");
        endif;
        if (!empty($NavigationUri)):
            $srt_NavigationUri = "";
            foreach ($NavigationUri as $value) :
                if ($srt_NavigationUri === ""):
                    $srt_NavigationUri = "'" . $value . "'";
                else:
                    $srt_NavigationUri = $srt_NavigationUri . ", " . "'" . $value . "'";
                endif;
            endforeach;

            $this->db->where("ADM_M_MenuBar.NavigationUri IN (" . $srt_NavigationUri . ")");
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function save_ADM_M_Role_Permission($type = NULL, $data = NULL, $where = NULL, $sql = NULL) {// Add by Ton! 20140123
        if (empty($sql)):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_Role_Permission', $data);
//            echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Insert Success.
                else:
                    return FALSE; // Insert Unsuccess.
                endif;
            elseif ($type == 'upd'): $this->db->where($where);
                $this->db->update('ADM_M_Role_Permission', $data);
//            echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Update Success.
                else:
                    return FALSE; // Update Unsuccess.
                endif;
            elseif ($type == 'del') : $this->db->where($where);
                $this->db->delete('ADM_M_Role_Permission', $data);
//            echo $this->db->last_query();exit();
            endif;
        else:
            $this->db->query($sql);
        endif;
    }

    function get_ADM_M_UserRole_List() {// Add by Ton! 20140123
        $this->db->select("ADM_M_UserRole.UserRole_Id AS Id
            , ADM_M_UserRole.UserRole_Code
            , ADM_M_UserRole.UserRole_NameEN
            , ADM_M_UserRole.UserRole_NameTH
            , ADM_M_UserRole.UserRole_Desc
            , CASE WHEN ADM_M_UserRole.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("ADM_M_UserRole");
        $this->db->order_by("ADM_M_UserRole.UserRole_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function get_ADM_M_UserRole($Columns = NULL, $UserRole_Id = NULL, $UserRole_Code = NULL, $Active = TRUE) {// Add by Ton! 20140123
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("ADM_M_UserRole.UserRole_Id, ADM_M_UserRole.UserRole_Code
                    , ADM_M_UserRole.UserRole_NameEN, ADM_M_UserRole.UserRole_NameTH, ADM_M_UserRole.UserRole_Desc, ADM_M_UserRole.Active");
        endif;
        $this->db->from("ADM_M_UserRole");
        if (!empty($UserRole_Id)):
            $this->db->where("ADM_M_UserRole.UserRole_Id", $UserRole_Id);
        endif;
        if (!empty($UserRole_Code)):
            $this->db->where("ADM_M_UserRole.UserRole_Code", $UserRole_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_M_UserRole.Active", YES);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function save_ADM_M_UserRole($type = NULL, $data = NULL, $where = NULL) {// Add by Ton! 20140123
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_UserRole', $data);
                $UserRole_Id = $this->db->insert_id();
                if ($UserRole_Id != 0):
                    return $UserRole_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_M_UserRole', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    function get_ADM_R_UserRoleMembers($Columns = NULL, $UserRoleMember_Id = NULL, $UserRole_Id = NULL, $UserLogin_Id = NULL, $Active = TRUE) {// Add by Ton! 20140123
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("ADM_R_UserRoleMembers.UserRoleMember_Id
                , ADM_R_UserRoleMembers.UserRole_Id
                , ADM_M_UserRole.UserRole_Code
                , ADM_M_UserRole.UserRole_NameEN
                , ADM_R_UserRoleMembers.UserLogin_Id
                , ADM_R_UserRoleMembers.Active");
        endif;
        $this->db->from("ADM_R_UserRoleMembers");
        $this->db->join("ADM_M_UserRole", "ADM_M_UserRole.UserRole_Id = ADM_R_UserRoleMembers.UserRole_Id", "LEFT"); // Add by Ton! 20140314
        if (!empty($UserRoleMember_Id)):
            $this->db->where("ADM_R_UserRoleMembers.UserRoleMember_Id", $UserRoleMember_Id);
        endif;
        if (!empty($UserRole_Id)):
            $this->db->where("ADM_R_UserRoleMembers.UserRole_Id", $UserRole_Id);
        endif;
        // if (!empty($UserLogin_Id)):
        //     $this->db->where("ADM_R_UserRoleMembers.UserLogin_Id", $UserLogin_Id);
        // endif;
        if ($Active === TRUE):
            $this->db->where("ADM_R_UserRoleMembers.Active", TRUE);
        endif;
        $this->db->order_by("ADM_R_UserRoleMembers.UserLogin_Id");

        $query = $this->db->get();
    //    echo $this->db->last_query();exit();
        return $query;
    }

    function save_ADM_R_UserRoleMembers($type = NULL, $data = NULL, $where = NULL) {// Add by Ton! 20140123
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_R_UserRoleMembers', $data);
                $UserRoleMember_Id = $this->db->insert_id();
                if ($UserRoleMember_Id != 0):
                    return $UserRoleMember_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_R_UserRoleMembers', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'del'):// Add by Ton! 20140314
                $this->db->delete('ADM_R_UserRoleMembers', $where);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    function get_ADM_R_UserRoleMenus($Columns = NULL, $UserRoleMenu_Id = NULL, $UserRole_Id = NULL, $Menu_Id = NULL, $Active = TRUE) {// Add by Ton! 20140123
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("ADM_R_UserRoleMenus.UserRoleMenu_Id
                , ADM_R_UserRoleMenus.UserRole_Id
                , ADM_R_UserRoleMenus.Menu_Id
                , ADM_R_UserRoleMenus.IsView
                , ADM_R_UserRoleMenus.IsCreate
                , ADM_R_UserRoleMenus.IsEdit
                , ADM_R_UserRoleMenus.IsDelete
                , ADM_R_UserRoleMenus.Active");
        endif;
        $this->db->from("ADM_R_UserRoleMenus");
        if (!empty($UserRoleMenu_Id)):
            $this->db->where("ADM_R_UserRoleMenus.UserRoleMenu_Id", $UserRoleMenu_Id);
        endif;
        if (!empty($UserRole_Id)):
            $this->db->where("ADM_R_UserRoleMenus.UserRole_Id", $UserRole_Id);
        endif;
        if (!empty($Menu_Id)):
            $this->db->where("ADM_R_UserRoleMenus.Menu_Id", $Menu_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_R_UserRoleMenus.Active", YES);
        endif;

        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;
    }

    function save_ADM_R_UserRoleMenus($type = NULL, $data = NULL, $where = NULL) {// Add by Ton! 20140123
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_R_UserRoleMenus', $data);
                $UserRoleMenu_Id = $this->db->insert_id();
                if ($UserRoleMenu_Id != 0):
                    return $UserRoleMenu_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd') :
                $this->db->where($where);
                $this->db->update('ADM_R_UserRoleMenus', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.

                endif;
            endif;
        else:
            return FALSE;
        endif;
    }

    function save_SYS_M_Action_Menu($type = NULL, $data = NULL, $where = NULL) {
        $afftectedRows = NULL;
        if ($type == 'ist'):
            $this->db->insert('SYS_M_Action_Menu', $data);
//            echo $this->db->last_query();exit();
            $afftectedRows = $this->db->affected_rows();
//            $MenuBar_Id = $this->db->insert_id();
            if ($afftectedRows > 0):
                return TRUE; // Save Success.
            else:
                return FALSE; // Save Unsuccess.
            endif;
        elseif ($type == 'upd'):
            $this->db->where($where);
            $this->db->update('SYS_M_Action_Menu', $data);
            $afftectedRows = $this->db->affected_rows();
//            echo $this->db->last_query();exit(); $afftectedRows = $this->db->affected_rows ();
            if ($afftectedRows > 0) :
                return TRUE; // Update Success.
            else :
                return FALSE; // Update Unsuccess.
            endif;
        elseif ($type == 'del') :
            $this->db->where($where);
            $this->db->delete('SYS_M_Action_Menu', $data);
//            echo $this->db->last_query();exit();
        endif;
    }

    function get_permission_menu($UserLogin_Id = NULL, $UserRole_Id = NULL, $UserGroup_Id = NULL, $MenuBar_Id = NULL) {// Add by Ton! 20140124
        /*
         * 
          $this->db->select("ADM_M_User_Permission.MenuBar_Id, ADM_M_User_Permission.Edge_Id, ADM_M_User_Permission.Action_Id");
          $this->db->from("ADM_M_User_Permission");
          if (!empty($UserLogin_Id)):
          $this->db->where("ADM_M_User_Permission.UserLogin_Id", $UserLogin_Id);
          endif;
          if (!empty($MenuBar_Id)):
          $this->db->where("ADM_M_User_Permission.MenuBar_Id", $MenuBar_Id);
          endif;
          //        $sql_user = $this->db->return_query(FALSE);
          $this->db->get();
          $sql_user = $this->db->last_query();

          echo "(" . $sql_user . ") UNION";

          $this->db->select("ADM_M_Role_Permission.MenuBar_Id, ADM_M_Role_Permission.Edge_Id, ADM_M_Role_Permission.Action_Id");
          $this->db->from("ADM_M_Role_Permission");
          if (!empty($UserRole_Id)):
          $this->db->where_in("ADM_M_Role_Permission.UserRole_Id", explode(",", $UserRole_Id));
          endif;
          if (!empty($MenuBar_Id)):
          $this->db->where("ADM_M_Role_Permission.MenuBar_Id", $MenuBar_Id);
          endif;
          //        $sql_role = $this->db->return_query(FALSE);
          $this->db->get();
          $sql_role = $this->db->last_query();

          echo " (" . $sql_role . ") UNION";

          $this->db->select("ADM_M_Group_Permission.MenuBar_Id, ADM_M_Group_Permission.Edge_Id, ADM_M_Group_Permission.Action_Id");
          $this->db->from("ADM_M_Group_Permission");
          if (!empty($UserGroup_Id)):
          $this->db->where_in("ADM_M_Group_Permission.UserGroup_Id", explode(",", $UserGroup_Id));
          endif;
          if (!empty($MenuBar_Id)):
          $this->db->where("ADM_M_Group_Permission.MenuBar_Id", $MenuBar_Id);
          endif;
          //        $sql_group = $this->db->return_query(FALSE);
          $this->db->get();
          $sql_group = $this->db->last_query();

          echo " (" . $sql_group . ")";
          die();

          $sql_final = $this->db->query("(" . $sql_user . ") UNION ALL (" . $sql_role . ") UNION ALL (" . $sql_group . ")");
          $query = $this->db->query($sql_final);
          return $query;
         *
         */

        $sql_user = "(SELECT ADM_M_User_Permission.MenuBar_Id, ADM_M_User_Permission.Edge_Id, ADM_M_User_Permission.Action_Id 
            FROM ADM_M_User_Permission 
            WHERE ADM_M_User_Permission.UserLogin_Id = " . $UserLogin_Id . " AND ADM_M_User_Permission.MenuBar_Id = " . $MenuBar_Id . ")";

        $sql_role = "";
        if (!empty($UserRole_Id)):
            $sql_role = "(SELECT ADM_M_Role_Permission.MenuBar_Id, ADM_M_Role_Permission.Edge_Id, ADM_M_Role_Permission.Action_Id 
                FROM ADM_M_Role_Permission 
                WHERE ADM_M_Role_Permission.UserRole_Id IN ( " . $UserRole_Id . " ) AND ADM_M_Role_Permission.MenuBar_Id = " . $MenuBar_Id . ")";
        endif;

        $sql_group = "";
        if (!empty($UserGroup_Id)):
            $sql_group = "(SELECT ADM_M_Group_Permission.MenuBar_Id, ADM_M_Group_Permission.Edge_Id, ADM_M_Group_Permission.Action_Id 
                FROM ADM_M_Group_Permission 
                WHERE ADM_M_Group_Permission.UserGroup_Id IN ( " . $UserGroup_Id . " ) AND ADM_M_Group_Permission.MenuBar_Id = " . $MenuBar_Id . ")";
        endif;

        $sql = $sql_user . ($sql_role != "" ? " UNION " . $sql_role : "") . ($sql_group != "" ? " UNION " . $sql_group : "") . "ORDER BY MenuBar_Id, Edge_Id, Action_Id";
//        $query = $this->db->query($sql);
        //        return $query;

        $key = md5($sql);
        $cache = $this->cache->memcached->get($key);

        if (!$cache) :
            $query = $this->db->query($sql);
            $response = $query->result();
            $this->cache->memcached->save($key, $response, NULL, 600);

        else :
            $response = $cache;
        endif;

        return $response;
    }

    function get_Dash_Board_by_Permission($MenuBar_Id = NULL, $Parent_Id = NULL, $Parent = TRUE) {// Add by Ton! 20140129
        $this->db->select("ADM_M_MenuBar.MenuBar_Id
           , ADM_M_MenuBar.MenuBar_Code
           , ADM_M_MenuBar.MenuBar_NameEn
           , ADM_M_MenuBar.NavigationUri
           , SYS_M_Dash_Board.Dash_Board_Function
           , ADM_M_MenuBar.Module");
        $this->db->from("ADM_M_MenuBar");
        $this->db->join("SYS_M_Dash_Board", "SYS_M_Dash_Board.MenuBar_Id = ADM_M_MenuBar.MenuBar_Id");
        if ($Parent == TRUE):
            $this->db->where("ADM_M_MenuBar.Parent_Id", 0);
        else:
            $this->db->where("ADM_M_MenuBar.Parent_Id", $Parent_Id);
        endif;
        if (!empty($MenuBar_Id)):
            $this->db->where_in("ADM_M_MenuBar.MenuBar_Id", explode(",", $MenuBar_Id));
        endif;
        $this->db->where("ADM_M_MenuBar.Active", 'YES');
        $this->db->where("ADM_M_MenuBar.Menu_Type", "PC");
        $this->db->order_by("ADM_M_MenuBar.MenuBar_Id");

        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;

//        $sql = $this->db->return_query(FALSE);
//        $key = md5($sql);
//        $cache = $this->cache->memcached->get($key);
//
//        if (!$cache) :
//            $response = $this->db->query($sql);
//            $this->cache->memcached->save($key, $response, NULL, 600);
//        else:
//            $response = $cache;
//        endif;
//
//        return $response;
    }

}
