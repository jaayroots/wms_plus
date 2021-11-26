<?php // Create by Ton! 20130422                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   ?>
<?php

class location_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getLocationByID($locID = NULL) {// get data for edit.
//        Select for Table STK_M_Location
        $this->db->select("*");
        $this->db->from("STK_M_Location");
        if (!empty($locID)) :
            $this->db->where("STK_M_Location.Location_Id", $locID);
        endif;
        $query = $this->db->get();
        return $query;
    }

    function getLocationList() {// get Location for display in Dropdown list.
//        Select for Table STK_M_Location
        $this->db->select("STK_M_Location.Location_Id, STK_M_Location.Location_Code");
        $this->db->from("STK_M_Location");
        $this->db->where("STK_M_Location.Active", ACTIVE);
        $this->db->order_by("STK_M_Location.Location_Id");
        $query = $this->db->get();
        return $query;
    }

    function getLocationAll($criteria = NULL) {
//        Select for Table STK_M_Location, STK_M_Warehouse, STK_M_Zone, STK_M_Storage, STK_M_Storage_Detail

        $this->db->select("dbo.STK_M_Location.Location_Id AS Id, dbo.STK_M_Warehouse.Warehouse_Code, dbo.STK_M_Zone.Zone_Code, 
            dbo.STK_M_Location.Category_Id, dbo.STK_M_Storage.Storage_NameEn, dbo.STK_M_Storage_Detail.Storage_Code, dbo.STK_M_Location.Location_Code");
        $this->db->from("dbo.STK_M_Storage_Detail INNER JOIN
                      dbo.STK_M_Location INNER JOIN
                      dbo.STK_M_Warehouse ON dbo.STK_M_Location.Warehouse_Id = dbo.STK_M_Warehouse.Warehouse_Id ON 
                      dbo.STK_M_Storage_Detail.Storage_Detail_Id = dbo.STK_M_Location.Storage_Detail_Id LEFT OUTER JOIN
                      dbo.STK_M_Zone ON dbo.STK_M_Location.Zone_Id = dbo.STK_M_Zone.Zone_Id LEFT OUTER JOIN
                      dbo.STK_M_Storage ON dbo.STK_M_Location.Storage_Id = dbo.STK_M_Storage.Storage_Id");
        $this->db->where("dbo.STK_M_Location.Active", ACTIVE);
        if ($criteria) {
            foreach ((array) $criteria as $key => $value) :

            endforeach;
        }
        $this->db->order_by("dbo.STK_M_Location.Location_Id, dbo.STK_M_Location.Warehouse_Id, dbo.STK_M_Location.Zone_Id, 
            dbo.STK_M_Location.Category_Id, dbo.STK_M_Location.Storage_Id, dbo.STK_M_Location.Storage_Detail_Id");
        $query = $this->db->get();
        return $query;
    }

    function saveDataLocation($type, $data, $where = '') {// save Location (Insert, Update).
        if ($type == 'ist') {// Insert.
            $query = $this->db->insert('STK_M_Location', $data);
            if ($query == true) {
                return TRUE; // Save Success.
            } else {
                return FALSE; // Save UnSuccess.
            }
        } elseif ($type == 'upd') {// Update.
            $this->db->where($where);
            $query = $this->db->update('STK_M_Location', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) {
                return TRUE; // Save Success.
            } else {
                return FALSE; // Save UnSuccess.
            }
        }
    }

//    function deleteDataLocation($data, $where) {// delete Location.
//        $this->db->where($where);
//        $this->db->update('STK_M_Location', $data);
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows > 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }

    function checkLocation($Warehouse_Id = NULL, $Zone_Id = NULL, $Category_Id = NULL, $Storage_Id = NULL, $Location_Id = NULL, $Location_Code = NULL, $Active = FALSE) {
//        Select for Table STK_M_Location
        $this->db->select("*");
        $this->db->from("STK_M_Location");
        if (!empty($Warehouse_Id)):
            $this->db->where("STK_M_Location.Warehouse_Id", $Warehouse_Id);
        endif;
        if (!empty($Zone_Id)):
            $this->db->where("STK_M_Location.Zone_Id", $Zone_Id);
        endif;
        if (!empty($Category_Id)) :
            $this->db->where("STK_M_Location.Category_Id", $Category_Id);
        endif;
        if (!empty($Storage_Id)) :
            $this->db->where("STK_M_Location.Storage_Id", $Storage_Id);
        endif;
        if (!empty($Location_Id)):
            $this->db->where("STK_M_Location.Location_Id", $Location_Id);
        endif;
        if (!empty($Location_Code)) :
            $this->db->where("STK_M_Location.Location_Code", $Location_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("STK_M_Location.Active", ACTIVE);
        endif;
//        echo $this->db->last_query();exit();
        $query = $this->db->get();
        if ($query->num_rows > 0) :
            return TRUE;
        else :
            return FALSE;
        endif;
    }

#add Product_Mfd_Ori,Product_Exp_Ori,Receive_Date_Ori,Sub_Status_Code : by kik : 2013-12-12
#add pallet_code in modal product in location

    function showProductInLocationById($location_id = "", $location_code = "") {

#Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];


        $this->db->select("
            i.*
            ,i.Product_Mfd AS Product_Mfd_Ori,
            ,i.Product_Exp AS Product_Exp_Ori,
            ,i.Receive_Date AS Receive_Date_Ori,
            ,i.Product_Sub_Status as Sub_Status_Code
            ,CONVERT(VARCHAR(20), Product_Mfd, 103) AS Product_Mfd,
            ,CONVERT(VARCHAR(20), Product_Exp, 103) AS Product_Exp,
            ,CONVERT(VARCHAR(20), Receive_Date, 103) AS Receive_Date
            ,Product_NameEN
            ,SYS_M_Domain.Dom_EN_Desc as Product_Sub_Status"); //add by kik : 12-09-2013
#check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            $this->db->select("pallet.Pallet_Code");
        endif; // end of query of Pallet


        $this->db->from("STK_T_Inbound i");
        $this->db->join("STK_M_Product p", "i.Product_Id=p.Product_Id", "LEFT");
        $this->db->join("SYS_M_Domain", "i.Product_Sub_Status = SYS_M_Domain.Dom_Code  AND SYS_M_Domain.Dom_Active = 'Y'", "LEFT"); //add by kik : 12-09-2013
#check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            $this->db->join("STK_T_Pallet pallet", "i.Pallet_Id = pallet.Pallet_Id", 'LEFT');
        endif; // end of query of Pallet

        if ($location_id != "") {
            $this->db->where("i.Actual_Location_Id", $location_id);
        } else {
            $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "left");
            $this->db->where("l.Location_Code", $location_code);
        }
        $this->db->where("i.Active", ACTIVE);
        $query = $this->db->get();
//        echo $this->db->last_query();        
        return $query;
    }

    function getLocationIdByCode($Location_Code, $Location_Id = NULL) {
        $this->db->select("Location_Id");
        $this->db->from("STK_M_Location");
        $this->db->where("Location_Code", $Location_Code);
        if (!empty($Location_Id)):
            $this->db->where("Location_Id <> " . $Location_Id);
        endif;
        $this->db->where("Active", ACTIVE);

        $query = $this->db->get();
//        echo $this->db->last_query();exit;
        $result = $query->result();

        if (count($result) != 0) :
            return $result[0]->Location_Id;
        else:
            return NULL;
        endif;
    }

    function getLocationCodeById($Location_Id) {
        $this->db->select("Location_Code");
        $this->db->from("STK_M_Location");
        $this->db->where("Location_Id", $Location_Id);
        $this->db->where("Active", ACTIVE);

        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)):
            return $result[0]->Location_Code;
        else:
            return NULL;
        endif;
    }

    function getLocationbyStorID($Storage_Id, $Active = FALSE) {
        $this->db->select("*");
        $this->db->from("STK_M_Location");
        $this->db->where("Storage_Id", $Storage_Id);
        if ($Active === TRUE):
            $this->db->where("Active", ACTIVE);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

#====================================================
#	Ground of Receive
#====================================================

    function getLocationPreReceive() {
        $sql = "SELECT * from dbo.STK_M_Location  a1, STK_M_Storage a2, STK_M_Storage_Type a3
            WHERE a1.Storage_Id		= a2.Storage_Id
            AND a2.StorageType_Id	=  a3.StorageType_Id
            AND a3.StorageType_Code = 'ST05' ";
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

//    ----- START #2429 Add by Ton! 20130822 -----
    function getProductLocation($Product_status = '') {
        $this->db->select("*");
        $this->db->from("STK_M_Product_Location");
        if ($Product_status != '') {
            $this->db->where_in("Product_status", $Product_status);
        }
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

//    ----- END #2429 Add by Ton! 20130822 -----

    function getLocationByLikeCode($Location_Code = '') {

//        if ($Location_Code != "") :  // Comment By Akkarapol, 29/10/2013, คอมเม้นต์ทิ้งเพราะ เราไม่จำเป็นต้องเช็คอีกแล้วเนื่องจาก แค่ focus ที่ textbox ก็จะให้แสดง Location ออกมาเลย
        $this->db->select("Location_Id, Location_Code");
        $this->db->from("STK_M_Location");
        $this->db->like("Location_Code", $Location_Code);
        $this->db->limit(30);
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
//        else:// Comment By Akkarapol, 29/10/2013, คอมเม้นต์ทิ้งเพราะ เราไม่จำเป็นต้องเช็คอีกแล้วเนื่องจาก แค่ focus ที่ textbox ก็จะให้แสดง Location ออกมาเลย
//            return NULL;// Comment By Akkarapol, 29/10/2013, คอมเม้นต์ทิ้งเพราะ เราไม่จำเป็นต้องเช็คอีกแล้วเนื่องจาก แค่ focus ที่ textbox ก็จะให้แสดง Location ออกมาเลย
//        endif;// Comment By Akkarapol, 29/10/2013, คอมเม้นต์ทิ้งเพราะ เราไม่จำเป็นต้องเช็คอีกแล้วเนื่องจาก แค่ focus ที่ textbox ก็จะให้แสดง Location ออกมาเลย
    }

    function get_edit_location_code_list() {
        $this->db->select("STK_M_Location.Location_Id AS Id, STK_M_Location.Location_Code
            , STK_M_Warehouse.Warehouse_Code, STK_M_Zone.Zone_Code, STK_M_Storage.Storage_NameEn
            , STK_M_Storage_Detail.Storage_Code, COUNT(STK_T_Inbound.Inbound_Id) AS Items
            , CASE WHEN STK_M_Location.Active IN ('1', 'Y') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("STK_M_Location");
        $this->db->join("STK_M_Warehouse", "STK_M_Location.Warehouse_Id = STK_M_Warehouse.Warehouse_Id", "LEFT");
        $this->db->join("STK_M_Zone", "STK_M_Location.Zone_Id = STK_M_Zone.Zone_Id", "LEFT");
        $this->db->join("STK_M_Storage", "STK_M_Location.Storage_Id = STK_M_Storage.Storage_Id", "LEFT");
        $this->db->join("STK_M_Storage_Detail", "STK_M_Location.Storage_Detail_Id = STK_M_Storage_Detail.Storage_Detail_Id", "LEFT");
        $this->db->join("STK_T_Inbound", "STK_T_Inbound.Actual_Location_Id = STK_M_Location.Location_Id AND STK_T_Inbound.Active = 'Y'", "LEFT");

        $this->db->group_by("STK_M_Location.Location_Id, STK_M_Location.Location_Code
            , STK_M_Location.Warehouse_Id, STK_M_Warehouse.Warehouse_Code
            , STK_M_Location.Zone_Id, STK_M_Zone.Zone_Code
            , STK_M_Location.Storage_Id, STK_M_Storage.Storage_NameEn
            , STK_M_Location.Storage_Detail_Id, STK_M_Storage_Detail.Storage_Code
            , STK_M_Location.Active");

        $this->db->order_by("STK_M_Location.Location_Id, STK_M_Location.Warehouse_Id, STK_M_Location.Zone_Id, STK_M_Location.Storage_Id, STK_M_Location.Storage_Detail_Id");

        $query = $this->db->get();
//        echo $this->db->last_query();
//        exit();
        return $query;
    }

    function get_edit_location_code($Location_Id) {
        $this->db->select("STK_M_Location.*, STK_M_Warehouse.Warehouse_Code, STK_M_Zone.Zone_Code, STK_M_Storage.Storage_NameEn, STK_M_Storage_Detail.Storage_Code");
        $this->db->from("STK_M_Location");
        $this->db->join("STK_M_Warehouse", "STK_M_Location.Warehouse_Id = STK_M_Warehouse.Warehouse_Id", "LEFT");
        $this->db->join("STK_M_Zone", "STK_M_Location.Zone_Id = STK_M_Zone.Zone_Id", "LEFT");
        $this->db->join("STK_M_Storage", "STK_M_Location.Storage_Id = STK_M_Storage.Storage_Id", "LEFT");
        $this->db->join("STK_M_Storage_Detail", "STK_M_Location.Storage_Detail_Id = STK_M_Storage_Detail.Storage_Detail_Id", "LEFT");

        $this->db->where("STK_M_Location.Location_Id", $Location_Id);

        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function searchLocation($Location_Code) {
        $this->db->select("*");
        $this->db->from("STK_M_Location");
        if (trim($Location_Code) != "") :
            $this->db->like("Location_Code", $Location_Code);
        endif;
        $this->db->where("Active", ACTIVE);
        $this->db->limit(100);
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) != 0) :
            return $result;
        else:
            return NULL;
        endif;
    }

    function get_location_code_by_inbound_id($inbound_id) {
        $this->db->select("STK_M_Location.Location_Code");
        $this->db->from("STK_T_Inbound");
        $this->db->join("STK_M_Location", "STK_T_Inbound.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
        $this->db->where("STK_T_Inbound.Inbound_Id", $inbound_id);
        $query = $this->db->get();
        
        return $query;
    }

    function get_db_from_mysql($db_connect) {
//connection to the database
        $dbhandle = mysql_connect($db_connect["hostname"], $db_connect["username"], $db_connect["password"]);
        if (!$dbhandle) {
            die("Unable to connect to MySQL");
        } else {
            $selected = mysql_select_db($db_connect["database"], $dbhandle);
            if (!$selected) {
                die("Could not select database name");
            } else {
                $database = array();
                $result = mysql_query("SELECT * FROM tbl_database");
                //fetch tha data from the database
                while ($row = mysql_fetch_object($result)) {
                    $database[$row->database_name] = $row->database_name;
                }

                return $database;
            }
        }
    }

    function get_db_mssql_all($data_connect) {
        $link = mssql_connect($data_connect["server_name"], $data_connect["username"], $data_connect['password']);
        if (!$link || !mssql_select_db($data_connect['database'], $link)) {
            die('Unable to connect or select database!');
        } else {
            $sql = "exec sp_databases";
            $query = mssql_query($sql);
            $db = array();
            $name = array("master", 'model', 'msdb', 'ReportServer', 'ReportServerTempDB', 'tempdb', 'WMSP_DEMO003', 'WMSP_DG_LOCAL');
            while ($row = mssql_fetch_object($query)) {
                if (in_array($row->DATABASE_NAME, $name)) {
                    unset($row);
                } else {
                    $db[] = $row;
                }
            }
            return $db;
        }
    }

    function get_location_remain($db_connect_mssql, $database, $showType) {

        if ($showType == "show_used") {
            $where = " and STK_T_Inbound.Active = 'Y' ";
        } elseif ($showType == "show_available") {
        //    $where = " and STK_T_Inbound.Active = 'N' or STK_T_Inbound.Inbound_Id is null";
            $where = " and STK_M_Location.Location_Id NOT IN   (select Location_Id
                                                    from STK_T_Inbound inb
                                                    JOIN STK_M_Location lo ON inb.Actual_Location_Id = lo.Location_Id
                                                    where inb.Active = 'Y'
                                                    group by Location_Id)
                                                    ";
        } else {
            $where = "";
        }

        // Connect to MSSQL
        $datalist = array();
            $ResultData = "";
                $sql = ("SELECT STK_M_Location.Location_Code , STK_M_Location.Location_Id , STK_M_Storage.Capacity_Max_Pallet , STK_M_Storage.Max_Capacity , "
                        . " STK_T_Inbound.Inbound_Id, STK_T_Inbound.Product_Id , STK_T_Inbound.Pallet_Id,"
                        . " STK_T_Inbound.Product_Code , STK_T_Inbound.Product_Status,STK_M_Product.UNno,"
                        . " CTL_M_ProductGroup.ProductGroup_NameEN,"
                        . " STK_T_Pallet.Pallet_Code , STK_T_Inbound.Product_Lot , STK_T_Inbound.Product_Serial , CONVERT(VARCHAR(10), CAST(STK_T_Inbound.Receive_Date AS DATE), 103) as Receive_Date ,"
                        . " CONVERT(VARCHAR(10), CAST(STK_T_Inbound.Product_Mfd AS DATE), 103) as Product_Mfd , "
                        . " CONVERT(VARCHAR(10), CAST(STK_T_Inbound.Product_Exp AS DATE), 103) as Product_Exp , STK_T_Inbound.Balance_Qty , STK_T_Inbound.Active as Product_Active, "
                        . " STK_M_Product.Product_NameEN , SYS_M_Domain.Dom_EN_SDesc , CTL_M_Company.Company_NameEN as Company "
                        . " FROM STK_M_Location"
                        . " LEFT JOIN STK_T_Inbound ON STK_M_Location.Location_Id = STK_T_Inbound.Actual_Location_Id"
                        . " LEFT JOIN STK_M_Storage ON STK_M_Storage.Storage_Id = STK_M_Location.Storage_Id"
                        . " LEFT JOIN STK_T_Pallet ON STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id"
                        . " LEFT JOIN STK_M_Product on STK_T_Inbound.Product_Id = STK_M_Product.Product_Id"
                        . " LEFT JOIN SYS_M_Domain on STK_T_Inbound.Product_Sub_Status = SYS_M_Domain.Dom_Code"
                        . " LEFT JOIN CTL_M_Company on STK_T_Inbound.Renter_Id = Company_Id"
                        . " LEFT JOIN CTL_M_ProductGroup ON STK_M_Product.ProductGroup_Id = CTL_M_ProductGroup.ProductGroup_Id"
                        . " WHERE STK_M_Location.Active = 'Y' "
                        . $where
                        . " group by STK_M_Location.Location_Code , STK_M_Location.Location_Id , STK_M_Storage.Capacity_Max_Pallet , "
                        . " STK_M_Storage.Max_Capacity , STK_T_Inbound.Inbound_Id , STK_T_Inbound.Product_Id ,  STK_T_Inbound.Pallet_Id, "
                        . " STK_T_Inbound.Product_Code ,STK_M_Product.Product_NameEN ,STK_T_Inbound.Product_Status, "
                        . " SYS_M_Domain.Dom_EN_SDesc ,STK_T_Pallet.Pallet_Code , STK_T_Inbound.Product_Lot , STK_T_Inbound.Product_Serial , "
                        . " CONVERT(VARCHAR(10), CAST(STK_T_Inbound.Receive_Date AS DATE), 103), STK_T_Inbound.Product_Mfd , "
                        . " STK_T_Inbound.Product_Exp , STK_T_Inbound.Balance_Qty , STK_T_Inbound.Active , CTL_M_Company.Company_NameEN,STK_M_Product.UNno,CTL_M_ProductGroup.ProductGroup_NameEN"
                        . " ORDER BY STK_M_Location.Location_Code ASC");

                $query = $this->db->query($sql);
		$result = $query->result();
		foreach ($result as $row) {
                    $datalist[$row->Location_Code]['Capacity_Max'] = $row->Max_Capacity;
                    $datalist[$row->Location_Code]["Location_Code"] = $row->Location_Code;
                    $datalist[$row->Location_Code]["Location_Id"] = $row->Location_Id;
                    $datalist[$row->Location_Code]["Capacity_Max_Pallet"] = $row->Capacity_Max_Pallet;
                    $datalist[$row->Location_Code]["Max_Capacity"] = $row->Max_Capacity;

                    if (array_key_exists($row->Location_Code, $datalist)) {
                        if ($row->Product_Active == "Y") {
                            $list["Inbound_Id"] = $row->Inbound_Id;
                            $list["Product_Id"] = $row->Product_Id;
                            $list["Product_Code"] = $row->Product_Code;
                            $list["Product_Name"] = $row->Product_NameEN;
                            $list["Product_Status"] = $row->Product_Status;
                            $list["ProductGroup_NameEN"] = $row->ProductGroup_NameEN;
                            $list["Product_Sub_Status"] = $row->Dom_EN_SDesc;
                            $list["Pallet_Code"] = $row->Pallet_Code;
                            $list["Product_Lot"] = $row->Product_Lot != null ? $row->Product_Lot : "";
                            $list["Product_Serial"] = $row->Product_Serial != null ? $row->Product_Serial : "";
                            $list["Receive_Date"] = $row->Receive_Date != null ? $row->Receive_Date : "";
                            $list["Product_Mfd"] = $row->Product_Mfd != null ? $row->Product_Mfd : "";
                            $list["Product_Exp"] = $row->Product_Exp != null ? $row->Product_Exp : "";
                            $list["Balance_Qty"] = $row->Balance_Qty;
                            $list["Product_Active"] = $row->Product_Active;
                            // $list["UNno"] = !empty($row->UNno) ? $row->UNno : "NA";
                            $list["UNno"] = !empty($row->ProductGroup_NameEN) ? $row->ProductGroup_NameEN : "NA";
                            $list["Company"] = $row->Company;
                            $datalist[$row->Location_Code]['Detail'][] = $list;
                        }
                    } else {
                        if ($row->Product_Active == "Y") {
                            $list["Inbound_Id"] = $row->Inbound_Id;
                            $list["Product_Id"] = $row->Product_Id;
                            $list["Product_Code"] = $row->Product_Code;
                            $list["Product_Name"] = $row->Product_NameEN;
                            $list["Product_Status"] = $row->Product_Status;
                            $list["ProductGroup_NameEN"] = $row->ProductGroup_NameEN;
                            $list["Product_Sub_Status"] = $row->Dom_EN_SDesc;
                            $list["Pallet_Code"] = $row->Pallet_Code;
                            $list["Product_Lot"] = $row->Product_Lot != null ? $row->Product_Lot : "";
                            $list["Product_Serial"] = $row->Product_Serial != null ? $row->Product_Serial : "";
                            $list["Receive_Date"] = $row->Receive_Date != null ? $row->Receive_Date : "";
                            $list["Product_Mfd"] = $row->Product_Mfd != null ? $row->Product_Mfd : "";
                            $list["Product_Exp"] = $row->Product_Exp != null ? $row->Product_Exp : "";
                            $list["Balance_Qty"] = $row->Balance_Qty;
                            $list["Product_Active"] = $row->Product_Active;
                            // $list["UNno"] = !empty($row->UNno) ? $row->UNno : "NA";
                            $list["UNno"] = !empty($row->ProductGroup_NameEN) ? $row->ProductGroup_NameEN : "NA";
                            $list["Company"] = $row->Company;
                            $datalist[$row->Location_Code]['Detail'][] = $list;
                        }
                    }
                }





                $ResultData = $datalist;
            
        
        return $datalist;
    }

}
