<?php

class contact_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getContactAll($Contact_Id = '', $Contact_Code = '', $First_NameEN = '') {
        $this->db->select("*");
        $this->db->from("CTL_M_Contact");
        if ($Contact_Id != ''):
            $this->db->where("Contact_Id", $Contact_Id);
        endif;
        if ($Contact_Code != ''):
            $this->db->where("Contact_Code", $Contact_Code);
        endif;
        if ($First_NameEN != ''):
            $this->db->where("First_NameEN", $First_NameEN);
        endif;
        $query = $this->db->get();
        return $query;
    }

    function getRenterAll($Contact_Id = '', $Contact_Code = '', $First_NameEN = '') {
        $this->db->select("*");
        $this->db->where("IsRenter", YES);
        if ($Contact_Id != ''):
            $this->db->where("Contact_Id", $Contact_Id);
        endif;
        if ($Contact_Code != ''):
            $this->db->where("Contact_Code", $Contact_Code);
        endif;
        if ($First_NameEN != ''):
            $this->db->where("First_NameEN", $First_NameEN);
        endif;
        $this->db->from("CTL_M_Contact");
        $query = $this->db->get();
        return $query;
    }

    function getSupplierAll($Contact_Id = '', $Contact_Code = '', $First_NameEN = '') {
        $this->db->select("*");
        $this->db->where("IsSupplier", YES);
        if ($Contact_Id != ''):
            $this->db->where("Contact_Id", $Contact_Id);
        endif;
        if ($Contact_Code != ''):
            $this->db->where("Contact_Code", $Contact_Code);
        endif;
        if ($First_NameEN != ''):
            $this->db->where("First_NameEN", $First_NameEN);
        endif;
        $this->db->from("CTL_M_Contact");
        $query = $this->db->get();
        return $query;
    }

    function getCustomerAll($Contact_Id = '', $Contact_Code = '', $First_NameEN = '') {
        $this->db->select("*");
        $this->db->where("IsCustomer", YES);
        if ($Contact_Id != ''):
            $this->db->where("Contact_Id", $Contact_Id);
        endif;
        if ($Contact_Code != ''):
            $this->db->where("Contact_Code", $Contact_Code);
        endif;
        if ($First_NameEN != ''):
            $this->db->where("First_NameEN", $First_NameEN);
        endif;
        $this->db->from("CTL_M_Contact");
        $query = $this->db->get();
        return $query;
    }

    function getWorkerAll($Contact_Id = '', $Contact_Code = '', $First_NameEN = '') {
        $this->db->select("*");
        $this->db->where("IsEmployee", YES);
        if ($Contact_Id != ''):
            $this->db->where("Contact_Id", $Contact_Id);
        endif;
        if ($Contact_Code != ''):
            $this->db->where("Contact_Code", $Contact_Code);
        endif;
        if ($First_NameEN != ''):
            $this->db->where("First_NameEN", $First_NameEN);
        endif;
        $this->db->from("CTL_M_Contact");
        $query = $this->db->get();
        return $query;
    }

    // Add By Akkarapol, 17/09/2013, สร้างฟังก์ชั่นใหม่ เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id โดย Join เอาเฉพาะ Contact ที่มี UserLogin เท่านั้น เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน
    function getWorkerAllWithUserLogin() {
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin");
        $this->db->join("CTL_M_Contact", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id AND CTL_M_Contact.IsEmployee = 1 AND ADM_M_UserLogin.Active = 1 "); // Add Active = 1
        $query = $this->db->get();
        return $query;
    }

    // END Add By Akkarapol, 17/09/2013, สร้างฟังก์ชั่นใหม่ เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id โดย Join เอาเฉพาะ Contact ที่มี UserLogin เท่านั้น เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน

    function getDepartment($Department_Id = '', $Department_Code = '', $Department_NameEN = '') {// Add by Ton! 20130912
        $this->db->select("*");
        $this->db->from("CTL_M_Department");
        if ($Department_Id != ''):
            $this->db->where("Department_Id", $Department_Id);
        endif;
        if ($Department_Code != ''):
            $this->db->where("Department_Code", $Department_Code);
        endif;
        if ($Department_NameEN != ''):
            $this->db->where("Department_NameEN", $Department_NameEN);
        endif;
        $this->db->where("CTL_M_Department.Active", True);
        $query = $this->db->get();
        return $query;
    }

    function getPosition($Position_Id = NULL, $Position_Code = NULL, $Position_NameEN = NULL) {// Add by Ton! 20130912
        $this->db->select("*");
        $this->db->from("CTL_M_Position");
        if (!empty($Position_Id)) :
            $this->db->where("Position_Id", $Position_Id);
        endif;
        if (!empty($Position_Code)) :
            $this->db->where("Position_Code", $Position_Code);
        endif;
        if (!empty($Position_NameEN)) :
            $this->db->where("Position_NameEN", $Position_NameEN);
        endif;
        $this->db->where("CTL_M_Position.Active", True);
        $query = $this->db->get();
        return $query;
    }

    function getUserLogin($Contact_Id = NULL, $UserLogin_Id = NULL, $UserAccount = NULL, $Active = TRUE) {// Edit by Ton! 20140428
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin");
        if (!empty($Contact_Id)) :
            $this->db->where("Contact_Id", $Contact_Id);
        endif;
        if (!empty($UserLogin_Id)):
            $this->db->where("UserLogin_Id", $UserLogin_Id);
        endif;
        if (!empty($UserAccount)) :
            $this->db->where("UserAccount", $UserAccount);
        endif;
        if ($Active === TRUE):
            $this->db->where("Active", TRUE);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getContactList() {// Add by Ton! 20130912
        $this->db->select("DISTINCT CTL_M_Contact.Contact_Id AS Id
            , CTL_M_TitleName.TitleName_EN + ' ' + CTL_M_Contact.First_NameEN + ' ' + CTL_M_Contact.Last_NameEN AS Contact_Name
            , ADM_M_UserLogin.UserAccount
            , CASE CTL_M_Contact.IsCustomer WHEN 1 THEN 'YES' ELSE 'NO' END AS IsCustomer
            , CASE CTL_M_Contact.IsEmployee WHEN 1 THEN 'YES' ELSE 'NO' END AS IsEmployee
            , CASE CTL_M_Contact.IsSupplier WHEN 1 THEN 'YES' ELSE 'NO' END AS IsSupplier
            , CASE CTL_M_Contact.IsVendor WHEN 1 THEN 'YES' ELSE 'NO' END AS IsVendor
            , CASE CTL_M_Contact.IsRenter WHEN 1 THEN 'YES' ELSE 'NO' END AS IsRenter
            , CASE CTL_M_Contact.IsShipper WHEN 1 THEN 'YES' ELSE 'NO' END AS IsShipper
            , CASE WHEN CTL_M_Contact.Active IN ('" . YES . "') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_Contact");
        $this->db->join("ADM_M_UserLogin", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
        $this->db->join("CTL_M_TitleName", "CTL_M_Contact.TitleName_Id = CTL_M_TitleName.TitleName_Id", "LEFT");
//        $this->db->where("CTL_M_Contact.Active", TRUE);
//        $this->db->where("ADM_M_UserLogin.Active", TRUE);
        $this->db->order_by("CTL_M_Contact.Contact_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function getContactByID($contactID) {
        $this->db->select("CTL_M_Contact.*
            , ADM_M_UserLogin.UserLogin_Id
            , ADM_M_UserLogin.UserAccount
            , ADM_M_UserLogin.Password
            , ADM_M_UserLogin.IsSuperUser
            , ADM_M_UserLogin.Active AS User_Active");
        $this->db->from("CTL_M_Contact");
        $this->db->join("ADM_M_UserLogin", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
        $this->db->where("CTL_M_Contact.Contact_Id", $contactID);
//        $this->db->where("CTL_M_Contact.Active", 1);
//        $this->db->where("ADM_M_UserLogin.Active", 1);
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    # comment Out by Ton! 20140210 Not Used.
//    function deleteDataContact($data, $where) {// Add by Ton! 20130912
//        $this->db->where($where);
//        $this->db->update('CTL_M_Contact', $data);
//        $afftectedRows = $this->db->affected_rows();
////        echo $this->db->last_query();
//        if ($afftectedRows > 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }
    # Comment Out by Ton! 20140210 Not Used.
//    function deleteDataUserLogin($data, $where) {// Add by Ton! 20130912
//        $this->db->where($where);
//        $this->db->update('ADM_M_UserLogin', $data);
//        $afftectedRows = $this->db->affected_rows();
////        echo $this->db->last_query();
//        if ($afftectedRows > 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }
    # comment Out by Ton! 20140210 Not Used.
//    function saveInsertContact($data) {
//        $this->db->insert('CTL_M_Contact', $data);
//        $contactID = $this->db->insert_id();
//        return (int) $contactID;
//    }
    # Comment out by Ton! 20140210 Not Used.
//    function saveUpdateContact($data, $where) {
//        $this->db->where($where);
//        $this->db->update('CTL_M_Contact', $data);
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows >= 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }

    function save_CTL_M_Contact($type = NULL, $data = NULL, $where = NULL) {// Add by Ton! 20140210 Instead saveInsertContact($data) & saveUpdateContact($data, $where)
        if ($type == 'ist'):
            $this->db->insert('CTL_M_Contact', $data);
            $Contact_Id = $this->db->insert_id();
//            echo $this->db->last_query();
            if ($Contact_Id > 0 && !empty($Contact_Id)) :
                return $Contact_Id;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd'):
            $this->db->where($where);
            $this->db->update('CTL_M_Contact', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'del'):
            $this->db->where($where);
            $this->db->delete('CTL_M_Contact', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function save_ADM_M_UserLogin($type = NULL, $data = NULL, $where = NULL) {// Rename saveDataUser is save_ADM_M_UserLogin by Ton! 20140210
        if ($type == 'ist') :// Insert.
            $this->db->insert('ADM_M_UserLogin', $data);
            $UserLogin_Id = $this->db->insert_id();
//            echo $this->db->last_query();
            if ($UserLogin_Id > 0 && !empty($UserLogin_Id)) :
                return $UserLogin_Id;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('ADM_M_UserLogin', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'del'):
            $this->db->where($where);
            $this->db->delete('ADM_M_UserLogin', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function getContaclByCode($Contact_Code, $Contact_Id = NULL) {
        $this->db->select("*");
        $this->db->from("CTL_M_Contact");
        $this->db->where("Contact_Code", $Contact_Code);
        if (!empty($Contact_Id)) :
            $this->db->where("Contact_Id <> " . $Contact_Id);
        endif;
        $this->db->where("Active", True);
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function getUserByAccount($UserAccount, $UserLogin_Id = NULL) {
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin");
        $this->db->where("UserAccount", $UserAccount);
        if (!empty($UserLogin_Id)) :
            $this->db->where("UserLogin_Id <> " . $UserLogin_Id);
        endif;
        $this->db->where("Active", True);
        $query = $this->db->get();
        return $query;
    }

    # Get Individual Permission Menu. [Add by Ton! 20140314]

    function get_about_user_individual($UserLogin_Id, $UserGroup_Id = NULL, $UserRole_Id = NULL) {
        $sql_user = "(SELECT DISTINCT ADM_M_User_Permission.MenuBar_Id
            , ADM_M_MenuBar.MenuBar_Code
            , ADM_M_MenuBar.MenuBar_NameEn  
            FROM ADM_M_User_Permission 
            LEFT JOIN ADM_M_MenuBar ON ADM_M_MenuBar.MenuBar_Id = ADM_M_User_Permission.MenuBar_Id
            WHERE ADM_M_User_Permission.UserLogin_Id = " . $UserLogin_Id . ")";

        $sql_group = "";
        if (!empty($UserGroup_Id)):
            $sql_group = "(SELECT DISTINCT ADM_M_Group_Permission.MenuBar_Id
                , ADM_M_MenuBar.MenuBar_Code
                , ADM_M_MenuBar.MenuBar_NameEn  
                FROM ADM_M_Group_Permission 
                LEFT JOIN ADM_M_MenuBar ON ADM_M_MenuBar.MenuBar_Id = ADM_M_Group_Permission.MenuBar_Id
                WHERE ADM_M_Group_Permission.UserGroup_Id IN (" . $UserGroup_Id . "))";
        endif;

        $sql_role = "";
        if (!empty($UserRole_Id)):
            $sql_role = "(SELECT DISTINCT ADM_M_Role_Permission.MenuBar_Id
                , ADM_M_MenuBar.MenuBar_Code
                , ADM_M_MenuBar.MenuBar_NameEn  
                FROM ADM_M_Role_Permission 
                LEFT JOIN ADM_M_MenuBar ON ADM_M_MenuBar.MenuBar_Id = ADM_M_Role_Permission.MenuBar_Id
                WHERE ADM_M_Role_Permission.UserRole_Id IN (" . $UserRole_Id . "))";
        endif;

        $sql = "";
        if ($sql_group !== "" && $sql_role !== ""):
            $sql = $sql_user . " EXCEPT " . $sql_group . " UNION " . $sql_role;
        elseif ($sql_group === "" && $sql_role === ""):
            $sql = $sql_user;
        else:
            if ($sql_group === ""):
                $sql = $sql_user . " EXCEPT " . $sql_role;
            endif;

            if ($sql_role === ""):
                $sql = $sql_user . " EXCEPT " . $sql_group;
            endif;
        endif;

        $query = $this->db->query($sql);
        return $query;
    }

}

?>
