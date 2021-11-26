<?php

class Company_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getContactAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        $this->db->from("CTL_M_Company");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("Active", YES);
        $query = $this->db->get();
        return $query;
    }

    function getRenterAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsRenter", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        return $query;
    }

    //By por 2013-09-20 สำหรับดึง RenterBranch ออกทั้งหมดออกมาแสดง
    //=======Start
    function getRenterBranchAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsRenterBranch", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        return $query;
    }

    //========End

    function getSupplierAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsSupplier", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $this->db->order_by("Company_NameEN"); // Add by Akkarapol, 27/08/2013, จะได้เรียงชื่อให้มันสวยๆ
        $query = $this->db->get();
        return $query;
    }

    function getOwnerAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsOwner", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function getNotOwner($Company_Id) {
        $this->db->select("*");
        $this->db->where("IsOwner", YES);
        $this->db->where("Active", YES);
        $this->db->where("Company_Id !=", $Company_Id);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function getCustomerAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {// Edit function name form getCuustomerAll in getCustomerAll by Ton! 20130823
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsCustomer", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        return $query;
    }

    function getVendorAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsVendor", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        return $query;
    }

    function getCompanyIdByNameEN($CompanyNameEN) {
        $this->db->select("CTL_M_Company.Company_Id");
        $this->db->where("CTL_M_Company.Company_NameEN", $CompanyNameEN);
        $this->db->where("CTL_M_Company.Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result[0]->Company_Id;
        else :
            return NULL;
        endif;
    }

    function getCompanyByCompanyCode($companyCode, $companyId = '') {// Add by Ton! 20130911
        $this->db->select("CTL_M_Company.*");
        $this->db->where("CTL_M_Company.Company_Code", $companyCode);
        if ($companyId != '') :
            $this->db->where("CTL_M_Company.Company_Id <> " . $companyId);
        endif;
        $this->db->where("CTL_M_Company.Active", YES);
        $this->db->from("CTL_M_Company");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getBusinessType($BusinessType_Id = NULL, $BusinessType_Code = NULL, $BusinessType_NameEN = NULL, $Active = TRUE) {// Add by Ton! 20130910
        $this->db->select("*");
        $this->db->from("CTL_M_BusinessType");
        if (!empty($BusinessType_Id)):
            $this->db->where("CTL_M_BusinessType.BusinessType_Id", $BusinessType_Id);
        endif;
        if (!empty($BusinessType_Code)):
            $this->db->where("CTL_M_BusinessType.BusinessType_Code", $BusinessType_Code);
        endif;
        if (!empty($BusinessType_NameEN)):
            $this->db->where("CTL_M_BusinessType.BusinessType_NameEN", $BusinessType_NameEN);
        endif;
        if ($Active === TRUE):// Add by Ton! 20131204
            $this->db->where("CTL_M_BusinessType.Active", YES);
        endif;
        $this->db->order_by("CTL_M_BusinessType.BusinessType_Id");
        $query = $this->db->get();
        return $query;
    }

    function getCompanyList() {// Add by Ton! 20130910
        $this->db->select("DISTINCT CTL_M_Company.Company_Id AS Id, CTL_M_Company.Company_NameEN
            , CASE CTL_M_Company.IsOwner WHEN 1 THEN 'YES' ELSE 'NO' END AS IsOwner
            , CASE CTL_M_Company.IsCustomer WHEN 1 THEN 'YES' ELSE 'NO' END AS IsCustomer
            , CASE CTL_M_Company.IsSupplier WHEN 1 THEN 'YES' ELSE 'NO' END AS IsSupplier
            , CASE CTL_M_Company.IsVendor WHEN 1 THEN 'YES' ELSE 'NO' END AS IsVendor
            , CASE CTL_M_Company.IsShipper WHEN 1 THEN 'YES' ELSE 'NO' END AS IsShipper
            , CASE CTL_M_Company.IsRenter WHEN 1 THEN 'YES' ELSE 'NO' END AS IsRenter
            , CASE CTL_M_Company.IsRenterBranch WHEN 1 THEN 'YES' ELSE 'NO' END AS IsRenterBranch
            , CASE WHEN CTL_M_Company.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_Company");
//        $this->db->where("CTL_M_Company.Active", YES);
        $this->db->order_by("CTL_M_Company.Company_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getCompanyByID($companyID) {// Add by Ton! 20130910
        $this->db->select("*");
        $this->db->from("CTL_M_Company");
        $this->db->where("CTL_M_Company.Company_Id", $companyID);
//        $this->db->where("CTL_M_Company.Active", YES);
        $query = $this->db->get();
        return $query;
    }

    function getCompanyIsRenter() {
        $this->db->select("Company_Id");
        $this->db->from("CTL_M_Company");
        $this->db->where("IsRenter", 1);
//        $this->db->where("CTL_M_Company.Active", YES);
        $query = $this->db->get();
        return $query;
    }

    # Comment Ouy by Ton! 20140211 Not Used.
//    function deleteDataCompany($data, $where) {// Add by Ton! 20130910
//        $this->db->where($where);
//        $this->db->update('CTL_M_Company', $data);
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows > 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }

    function saveDataCompany($type, $data, $where) {// Add by Ton! 20130911
        if ($type == 'ist') :// Insert.
            $this->db->insert('CTL_M_Company', $data);
            $Company_Id = $this->db->insert_id();
//            echo $this->db->last_query();
            if ($Company_Id > 0 && !empty($Company_Id)) :
                return TRUE;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('CTL_M_Company', $data);
//            echo $this->db->last_query();exit();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'del') :// Delete.
            $this->db->where($where);
            $this->db->delete('CTL_M_Company', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function getBranchNotOwner($Company_Id = '') {
        $this->db->select("*");
        $this->db->from("CTL_M_Company");
        if ($Company_Id != ''):
            $this->db->where("Company_Id <>" . $Company_Id);
        endif;
        $this->db->where("IsRenterBranch", YES);
        $this->db->where("Active", YES);
        $query = $this->db->get();
        // echo $this->db->last_query(); 
        return $query;
    }


    function get_CTL_M_CompanyGroup($Columns = NULL, $CompanyGroup_Id = NULL, $CompanyGroup_Code = NULL, $Active = TRUE) {
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("CTL_M_CompanyGroup.CompanyGroup_Id
                , CTL_M_CompanyGroup.CompanyGroup_Code
                , CTL_M_CompanyGroup.CompanyGroup_NameEN
                , CTL_M_CompanyGroup.CompanyGroup_NameTH
                , CTL_M_CompanyGroup.CompanyGroup_Desc
                , CTL_M_CompanyGroup.Active");
        endif;
        $this->db->from("CTL_M_CompanyGroup");
        if (!empty($CompanyGroup_Id)):
            $this->db->where("CTL_M_CompanyGroup.CompanyGroup_Id", $CompanyGroup_Id);
        endif;
        if (!empty($CompanyGroup_Code)):
            $this->db->where("CTL_M_CompanyGroup.CompanyGroup_Code", $CompanyGroup_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_CompanyGroup.Active", YES);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function get_CTL_M_CompanyGroup_List() {
        $this->db->select("CTL_M_CompanyGroup.CompanyGroup_Id AS Id
            , CTL_M_CompanyGroup.CompanyGroup_Code 
            , CTL_M_CompanyGroup.CompanyGroup_NameEN
            , CTL_M_CompanyGroup.CompanyGroup_NameTH
            , CTL_M_CompanyGroup.CompanyGroup_Desc
            , CASE WHEN CTL_M_CompanyGroup.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_CompanyGroup");
        $this->db->order_by("CTL_M_CompanyGroup.CompanyGroup_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_member_company_group_list() {
        $this->db->select("CTL_M_Company.Company_Id
            , CTL_M_Company.Company_Code
            , CTL_M_Company.Company_NameEN 
            , CTL_M_BusinessType.BusinessType_NameEN");
        $this->db->from("CTL_M_Company");
        $this->db->join("CTL_M_BusinessType", "CTL_M_BusinessType.BusinessType_Id = CTL_M_Company.BusinessType_Id", "LEFT");
        $this->db->order_by("CTL_M_Company.Company_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function get_CTL_R_CompanyGroupMembers($Columns = NULL, $CompanyGroupMember_Id = NULL, $CompanyGroup_Id = NULL, $Company_Id = NULL, $Active = TRUE) {
        if ($Columns != NULL):
            $this->db->select($Columns);
        else:
            $this->db->select("CTL_R_CompanyGroupMembers.CompanyGroupMember_Id
                , CTL_R_CompanyGroupMembers.CompanyGroup_Id
                , CTL_M_CompanyGroup.CompanyGroup_NameEN
                , CTL_R_CompanyGroupMembers.Company_Id
                , CTL_M_Company.Company_NameEN
                , CTL_R_CompanyGroupMembers.Active");
        endif;
        $this->db->from("CTL_R_CompanyGroupMembers");
        $this->db->join("CTL_M_CompanyGroup", "CTL_M_CompanyGroup.CompanyGroup_Id = CTL_R_CompanyGroupMembers.CompanyGroup_Id", "LEFT");
        $this->db->join("CTL_M_Company", "CTL_M_Company.Company_Id = CTL_R_CompanyGroupMembers.Company_Id", "LEFT");
        if (!empty($CompanyGroupMember_Id)):
            $this->db->where("CTL_R_CompanyGroupMembers.CompanyGroupMember_Id", $CompanyGroupMember_Id);
        endif;
        if (!empty($CompanyGroup_Id)):
            $this->db->where("CTL_R_CompanyGroupMembers.CompanyGroup_Id", $CompanyGroup_Id);
        endif;
        if (!empty($Company_Id)):
            $this->db->where("CTL_R_CompanyGroupMembers.Company_Id", $Company_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_R_CompanyGroupMembers.Active", YES);
        endif;
        $this->db->order_by("CTL_R_CompanyGroupMembers.Company_Id");
        $query = $this->db->get();
        //echo $this->db->last_query();//exit();
        return $query;
    }

    # Add by Ton!20131203
    # For save data CTL_M_CompanyGroup (insert Or update).
    # Parameter - $type(string)['ist' = insert, 'upd' = update], - $data(string), - $where(string).
    # Return - insert CompanyGroup_Id(int) Or FALSE, - update TRUE Or FALSE.

    function save_CTL_M_CompanyGroup($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_CompanyGroup', $data);
                $CompanyGroup_Id = $this->db->insert_id();
                if ($CompanyGroup_Id != 0):
                    return $CompanyGroup_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_CompanyGroup', $data);
//                echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'del'):
                $this->db->where($where);
                $this->db->delete('CTL_M_CompanyGroup', $data);
//                echo $this->db->last_query();exit();
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

    function save_CTL_R_CompanyGroupMembers($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_R_CompanyGroupMembers', $data);
                $CompanyGroupMember_Id = $this->db->insert_id();
                if ($CompanyGroupMember_Id != 0):
                    return $CompanyGroupMember_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_R_CompanyGroupMembers', $data);
//                echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'del'):
                $this->db->where($where);
                $this->db->delete('CTL_R_CompanyGroupMembers', $data);
//                echo $this->db->last_query();exit();
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

    function getDestinationAll($Company_Id = '', $Company_Code = '', $Company_NameEN = '') {
        $this->db->select("*");
        if ($Company_Id != ''):
            $this->db->where("Company_Id", $Company_Id);
        endif;
        if ($Company_Code != ''):
            $this->db->where("Company_Code", $Company_Code);
        endif;
        if ($Company_NameEN != ''):
            $this->db->where("Company_NameEN", $Company_NameEN);
        endif;
        $this->db->where("IsRenterBranch", YES);
        $this->db->where("Active", YES);
        $this->db->from("CTL_M_Company");
        $this->db->order_by("Company_NameEN");
        $query = $this->db->get();
        return $query;
    }

}
