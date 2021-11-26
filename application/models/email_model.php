<?php

class email_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_data_email() {
        $this->db->select('*');
        $this->db->from('CTL_M_EmailAddress');
        $this->db->order_by('Email_Name', 'ASC');
        $query = $this->db->get();
        $result = $query->result();
        if ($result) {
            return $result;
        } else {
            return FALSE;
        }
    }

    function insert_email($Email_Name) {
        $this->db->insert('CTL_M_EmailAddress', $Email_Name);
        $Email_Id = $this->db->insert_id();
        if (!empty($Email_Id)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function validate_duplicate($Email_Name) {
        $this->db->select('Email_Name');
        $this->db->from('CTL_M_EmailAddress');
        $this->db->where('Email_Name', $Email_Name);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function modify_email($id) {
        $this->db->select('Email_Name');
        $this->db->from('CTL_M_EmailAddress');
        $this->db->where('Email_Id', $id);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function update_email($new_array, $id) {
        $this->db->where('Email_Id', $id);
        $this->db->update('CTL_M_EmailAddress', $new_array);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function delete_email($id) {
        $this->db->where('Email_Id', $id);
        $this->db->delete('CTL_M_EmailAddress');
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function get_company_name() {
        $this->db->select("Company_NameEN , Company_Id");
        $this->db->from("CTL_M_Company");
        $this->db->where("IsRenter", 1);
        $query = $this->db->get();
        $result = $query->result();
        if ($result) {
            return $result;
        } else {
            return FALSE;
        }
    }

    function get_receive_today() {
        $sql = "select inb.Document_No, inb.Doc_Refer_Ext, inb.Product_Code,"
                . "(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=inb.Product_Id) Product_Name, "
                . "inb.Product_Status, inb.Product_Lot, inb.Product_Serial, CONVERT(VARCHAR(20), inb.Product_Mfd, 103) as Product_Mfd, "
                . "CONVERT(VARCHAR(20), inb.Product_Exp, 103) as Product_Exp, inb.Unit_Id, sum(inb.Receive_Qty) Receive_Qty, "
                . "CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date, CONVERT(VARCHAR(20), inb.Receive_Date, 120) as Receive_Date_sort, "
                . "(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Owner_Id) To_sup, "
                . "(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Renter_Id) From_sup, "
                . "isnull((Select TOP 1 CAST(stuff((Select case t.Remark WHEN '' THEN '' WHEN NULL THEN '' ELSE ', ' + t.Remark END "
                . "from STK_T_Order_Detail t where t.Order_Id=r.Order_Id AND t.Product_Id=inb.Product_Id AND t.Inbound_Item_Id=inb.Inbound_Id "
                . "order by Item_Id for xml path('')), 1, 1, '') as nvarchar(1000) ) as myRemark from STK_T_Order_Detail ), '') Remark, "
                . "inb.Doc_Refer_Int, inb.Doc_Refer_Inv, inb.Doc_Refer_CE, inb.Doc_Refer_BL,"
                . "S1.public_name AS Unit_Value, 'Is_reject' = CASE WHEN w.Present_State <> -1 THEN 'N' WHEN w.Present_State = -1 THEN 'Y' END, "
                . "inb.Invoice_Id, STK_T_Invoice.Invoice_No, inb.Cont_Id, CTL_M_Container.Cont_No, CTL_M_Container_Size.Cont_Size_No, "
                . "CTL_M_Container_Size.Cont_Size_Unit_Code, inb.Price_Per_Unit, SYS_M_Domain.Dom_EN_SDesc AS Unit_price, "
                . "(Receive_Qty * inb.Price_Per_Unit) AS All_price, pall.Pallet_Code, inb.Pallet_Id "
                . "from STK_T_Inbound inb "
                . "left join STK_T_Order r ON inb.Document_No=r.Document_No "
                . "left join STK_T_Workflow w ON inb.Flow_Id = w.Flow_Id "
                . "join CTL_M_UOM_Template_Language S1 ON inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = 'eng' "
                . "left join STK_T_Pallet pall ON inb.Pallet_Id = pall.Pallet_Id "
                . "left join SYS_M_Domain ON inb.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y' "
                . "left join STK_T_Invoice on STK_T_Invoice.Invoice_Id = inb.Invoice_Id "
                . "left join CTL_M_Container on CTL_M_Container.Cont_Id = inb.Cont_Id  "
                . "left join CTL_M_Container_Size ON CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id "
                . "where CONVERT(varchar(10),inb.Receive_Date,120) >= '" . date("Y-m-d") . "' "
                . "AND CONVERT(varchar(10),inb.Receive_Date,120) <= '" . date("Y-m-d") . "' "
                . "AND w.Process_Id IN (1) and w.Present_State in(5, 6, -2, -1) "
                . "GROUP BY inb.Document_No, inb.Doc_Refer_Ext, inb.Product_Code, inb.Product_Id,"
                . "inb.Product_Status, inb.Product_Lot, inb.Product_Serial, CONVERT(VARCHAR(20),"
                . "inb.Product_Mfd,103), CONVERT(VARCHAR(20), inb.Product_Exp,103), inb.Unit_Id,"
                . "CONVERT(VARCHAR(20), inb.Receive_Date, 103), CONVERT(VARCHAR(20), inb.Receive_Date, 120),"
                . "inb.Owner_Id, inb.Renter_Id, r.Order_Id, inb.Inbound_Id, inb.Doc_Refer_Int,"
                . "inb.Doc_Refer_Inv, inb.Doc_Refer_CE, inb.Doc_Refer_BL, S1.public_name, w.Present_State,"
                . "inb.Invoice_Id, STK_T_Invoice.Invoice_No, inb.Cont_Id, CTL_M_Container.Cont_No,"
                . "CTL_M_Container_Size.Cont_Size_No, CTL_M_Container_Size.Cont_Size_Unit_Code,"
                . "inb.Price_Per_Unit, SYS_M_Domain.Dom_EN_SDesc, (Receive_Qty * inb.Price_Per_Unit),"
                . "inb.Pallet_Id, pall.Pallet_Code "
                . "ORDER BY CONVERT(VARCHAR(20), inb.Receive_Date, 103) asc,"
                . "inb.Document_No asc, inb.Product_Code asc";

        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_dispatch_today() {
        $sql = "select order1.Document_No, pro.Product_Code,pro.Product_NameEN ,detail.Product_Lot,detail.Product_Serial,detail.Product_Mfd,"
                . "detail.Product_Exp,sum(detail.Confirm_Qty) as Confirm_Qty "
                . "from STK_T_Order order1 "
                . "join STK_T_Order_Detail detail on order1.order_id = detail.order_id "
                . "join STK_M_Product pro on detail.product_id = pro.product_id "
                . "join STK_T_Workflow work on work.Flow_Id = order1.Flow_Id "
                . "where CONVERT(VARCHAR(10),order1.Actual_Action_Date,120) = '" . date("Y-m-d") . "' "
                . "and work.Process_Id = 2 and work.Present_State = '-2' and detail.Active='Y' "
                . "group by order1.Document_No,pro.Product_Code,pro.Product_NameEN,detail.Product_lot,detail.Product_Serial,detail.Product_Mfd,detail.Product_Exp,"
                . "CONVERT(VARCHAR(10),order1.Actual_Action_Date,120)";
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    // ****************************  Connect All Database Manually********************************************
    // Joke Create 5/1/2017 Auto Send mail and send All database
    function get_db_mysql($data_connect_mysql) { // Function Get Name Database All      
        //connection to the database
        $dbhandle = mysql_connect($data_connect_mysql['Hostname'], $data_connect_mysql['Username'], $data_connect_mysql['Password']);
        if (!$dbhandle) {
            die("Unable to connect to MySQL");
        } else {
            $selected = mysql_select_db("db_wmsplus", $dbhandle);
            if (!$selected) {
                die("Could not select examples");
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

    function get_email_all($database) { // Get email all from database all mssql
        // Connect to MSSQL
        $link = mssql_connect($database["Server_Name"], $database["User_Name"], $database["Password"]);

        if (!$link) {
            die('Something went wrong while connecting to MSSQL');
        } else {
            $sql = "select * from [" . $database["Database_Name"] . "].[dbo].[CTL_M_EmailAddress]";
            $query = mssql_query($sql);
            $email_name = array();
            while ($row = mssql_fetch_object($query)) {
                $email_name[] = $row;
            }
            return $email_name;
        }
    }

    function get_data_receive_today($date, $database) {

        // Connect to MSSQL
        $link = mssql_connect($database["Server_Name"], $database["User_Name"], $database["Password"]);

        if (!$link) {
            die('Something went wrong while connecting to MSSQL');
        } else {
            $sql = "select inb.Document_No, inb.Doc_Refer_Ext, inb.Product_Code,"
                    . "(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=inb.Product_Id) Product_Name, "
                    . "inb.Product_Status, inb.Product_Lot, inb.Product_Serial, CONVERT(VARCHAR(20), inb.Product_Mfd, 103) as Product_Mfd, "
                    . "CONVERT(VARCHAR(20), inb.Product_Exp, 103) as Product_Exp, inb.Unit_Id, sum(inb.Receive_Qty) Receive_Qty, "
                    . "CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date, CONVERT(VARCHAR(20), inb.Receive_Date, 120) as Receive_Date_sort, "
                    . "(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Owner_Id) To_sup, "
                    . "(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Renter_Id) From_sup, "
                    . "isnull((Select TOP 1 CAST(stuff((Select case t.Remark WHEN '' THEN '' WHEN NULL THEN '' ELSE ', ' + t.Remark END "
                    . "from STK_T_Order_Detail t where t.Order_Id=r.Order_Id AND t.Product_Id=inb.Product_Id AND t.Inbound_Item_Id=inb.Inbound_Id "
                    . "order by Item_Id for xml path('')), 1, 1, '') as nvarchar(1000) ) as myRemark from STK_T_Order_Detail ), '') Remark, "
                    . "inb.Doc_Refer_Int, inb.Doc_Refer_Inv, inb.Doc_Refer_CE, inb.Doc_Refer_BL,"
                    . "S1.public_name AS Unit_Value, 'Is_reject' = CASE WHEN w.Present_State <> -1 THEN 'N' WHEN w.Present_State = -1 THEN 'Y' END, "
                    . "inb.Invoice_Id, STK_T_Invoice.Invoice_No, inb.Cont_Id, CTL_M_Container.Cont_No, CTL_M_Container_Size.Cont_Size_No, "
                    . "CTL_M_Container_Size.Cont_Size_Unit_Code, inb.Price_Per_Unit, SYS_M_Domain.Dom_EN_SDesc AS Unit_price, "
                    . "(Receive_Qty * inb.Price_Per_Unit) AS All_price, pall.Pallet_Code, inb.Pallet_Id "
                    . "from [" . $database["Database_Name"] . "].[dbo].[STK_T_Inbound] inb "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[STK_T_Order] r ON inb.Document_No=r.Document_No "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[STK_T_Workflow] w ON inb.Flow_Id = w.Flow_Id "
                    . "join [" . $database["Database_Name"] . "].[dbo].[CTL_M_UOM_Template_Language] S1 ON inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = 'eng' "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[STK_T_Pallet] pall ON inb.Pallet_Id = pall.Pallet_Id "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[SYS_M_Domain] ON inb.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y' "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[STK_T_Invoice] on STK_T_Invoice.Invoice_Id = inb.Invoice_Id "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[CTL_M_Container] on CTL_M_Container.Cont_Id = inb.Cont_Id  "
                    . "left join [" . $database["Database_Name"] . "].[dbo].[CTL_M_Container_Size] ON CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id "
                    . "where CONVERT(varchar(10),inb.Receive_Date,120) >= '" . $date . "' "
                    . "AND CONVERT(varchar(10),inb.Receive_Date,120) <= '" . $date . "' "
                    . "AND w.Process_Id IN (1) and w.Present_State in(5, 6, -2, -1) "
                    . "GROUP BY inb.Document_No, inb.Doc_Refer_Ext, inb.Product_Code, inb.Product_Id,"
                    . "inb.Product_Status, inb.Product_Lot, inb.Product_Serial, CONVERT(VARCHAR(20),"
                    . "inb.Product_Mfd,103), CONVERT(VARCHAR(20), inb.Product_Exp,103), inb.Unit_Id,"
                    . "CONVERT(VARCHAR(20), inb.Receive_Date, 103), CONVERT(VARCHAR(20), inb.Receive_Date, 120),"
                    . "inb.Owner_Id, inb.Renter_Id, r.Order_Id, inb.Inbound_Id, inb.Doc_Refer_Int,"
                    . "inb.Doc_Refer_Inv, inb.Doc_Refer_CE, inb.Doc_Refer_BL, S1.public_name, w.Present_State,"
                    . "inb.Invoice_Id, STK_T_Invoice.Invoice_No, inb.Cont_Id, CTL_M_Container.Cont_No,"
                    . "CTL_M_Container_Size.Cont_Size_No, CTL_M_Container_Size.Cont_Size_Unit_Code,"
                    . "inb.Price_Per_Unit, SYS_M_Domain.Dom_EN_SDesc, (Receive_Qty * inb.Price_Per_Unit),"
                    . "inb.Pallet_Id, pall.Pallet_Code "
                    . "ORDER BY CONVERT(VARCHAR(20), inb.Receive_Date, 103) asc,"
                    . "inb.Document_No asc, inb.Product_Code asc";

            $query = mssql_query($sql);
            $data = array();
            while ($row = mssql_fetch_object($query)) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function get_data_dispatch_today($date, $database) {
        // Connect to MSSQL
        $link = mssql_connect($database["Server_Name"], $database["User_Name"], $database["Password"]);

        if (!$link) {
            die('Something went wrong while connecting to MSSQL');
        } else {
            $sql = "select order1.Document_No, pro.Product_Code,pro.Product_NameEN ,detail.Product_Lot,detail.Product_Serial,detail.Product_Mfd,"
                    . "detail.Product_Exp,sum(detail.Confirm_Qty) as Confirm_Qty "
                    . "from [" . $database["Database_Name"] . "].[dbo].[STK_T_Order] order1 "
                    . "join [" . $database["Database_Name"] . "].[dbo].[STK_T_Order_Detail] detail on order1.order_id = detail.order_id "
                    . "join [" . $database["Database_Name"] . "].[dbo].[STK_M_Product] pro on detail.product_id = pro.product_id "
                    . "join [" . $database["Database_Name"] . "].[dbo].[STK_T_Workflow] work on work.Flow_Id = order1.Flow_Id "
                    . "where CONVERT(VARCHAR(10),order1.Actual_Action_Date,120) = '" . $date . "' "
                    . "and work.Process_Id = 2 and work.Present_State = '-2' and detail.Active='Y' "
                    . "group by order1.Document_No,pro.Product_Code,pro.Product_NameEN,detail.Product_lot,detail.Product_Serial,detail.Product_Mfd,detail.Product_Exp,"
                    . "CONVERT(VARCHAR(10),order1.Actual_Action_Date,120)";
            $query = mssql_query($sql);
            $data = array();
            while ($row = mssql_fetch_object($query)) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function get_data_inventory_today($new, $database) {
        // Connect to MSSQL
        $link = mssql_connect($database["Server_Name"], $database["User_Name"], $database["Password"]);
        if (!$link) {
            die('Something went wrong while connecting to MSSQL');
        } else {
            $sql = "select STK_T_Inbound.Product_Code, Product_NameEN, CTL_M_Company.Company_NameEN,"
                    . "STK_T_Inbound.Product_Lot, STK_T_Inbound.Invoice_Id, STK_T_Invoice.Invoice_No, STK_T_Inbound.Cont_Id,"
                    . "CAST(CTL_M_Container.Cont_No AS VARCHAR(50)) + ' '+ CAST(CTL_M_Container_Size.Cont_Size_No AS VARCHAR(5))+ ' '+CTL_M_Container_Size.Cont_Size_Unit_Code AS Cont,"
                    . "STK_T_Pallet.Pallet_Code, STK_T_Inbound.Pallet_Id,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='PENDING' then Balance_Qty end), 0) as counts_1,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='NORMAL' then Balance_Qty end), 0) as counts_2,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='DAMAGE' then Balance_Qty end), 0) as counts_3,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='REPACK' then Balance_Qty end), 0) as counts_4,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='NC' then Balance_Qty end), 0) as counts_5,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='SHORTAGE' then Balance_Qty end), 0) as counts_6,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='GRADE' then Balance_Qty end), 0) as counts_7,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='BLOCK' then Balance_Qty end), 0) as counts_8,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='DG' then Balance_Qty end), 0) as counts_9,"
                    . "isnull(SUM(case when STK_T_Inbound.Product_Status='BORROW' then Balance_Qty end), 0) as counts_10,"
                    . "sum(Balance_Qty) as totalbal, ISNULL ((SELECT SUM(Reserv_Qty) AS total_allow "
                    . "FROM [" . $database["Database_Name"] . "].dbo.vw_inbound_productDetail_booked AS vw WHERE (Product_Code = [" . $database["Database_Name"] . "].dbo.STK_T_Inbound.Product_Code)"
                    . "AND (Product_Lot = [" . $database["Database_Name"] . "].dbo.STK_T_Inbound.Product_Lot) AND (Invoice_Id = STK_T_Inbound.Invoice_Id) "
                    . "AND (Cont_Id = STK_T_Inbound.Cont_Id) AND (Pallet_Id = STK_T_Inbound.Pallet_Id)"
                    . "GROUP BY Product_Code, Product_Lot, Invoice_Id, Cont_Id, Pallet_Id), 0) AS Booked,"
                    . "ISNULL ((SELECT SUM(Reserv_Qty) AS total_allow FROM [" . $database["Database_Name"] . "].dbo.vw_inbound_productDetail_dispatch AS vw "
                    . "WHERE (Product_Code = [" . $database["Database_Name"] . "].dbo.STK_T_Inbound.Product_Code) AND (Product_Lot = [" . $database["Database_Name"] . "].dbo.STK_T_Inbound.Product_Lot) "
                    . "AND (Invoice_Id = STK_T_Inbound.Invoice_Id) AND (Cont_Id = STK_T_Inbound.Cont_Id) AND (Pallet_Id = STK_T_Inbound.Pallet_Id) "
                    . "GROUP BY Product_Code, Product_Lot, Invoice_Id, Cont_Id, Pallet_Id), 0) AS Dispatch,"
                    . "STK_T_Inbound.Unit_Id, UOM_Temp.public_name AS Unit_Value, 0 as Uom_Qty, STK_M_Product.Standard_Unit_In_Id,"
                    . "UOM_Temp_Prod.public_name AS Uom_Unit_Val "
                    . "from [" . $database["Database_Name"] . "].[dbo].[STK_T_Inbound] "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[STK_M_Location] ON STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[STK_M_Product] ON STK_T_Inbound.Product_Id=STK_M_Product.Product_Id "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[CTL_M_Company] ON CTL_M_Company.Company_Id = STK_T_Inbound.Renter_Id "
                    . "join [" . $database["Database_Name"] . "].[dbo].[CTL_M_UOM_Template_Language] UOM_Temp_Prod ON STK_M_Product.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id "
                    . "AND UOM_Temp_Prod.language = 'eng' "
                    . "join [" . $database["Database_Name"] . "].[dbo].[CTL_M_UOM_Template_Language] UOM_Temp ON STK_T_Inbound.Unit_Id = UOM_Temp.CTL_M_UOM_Template_id "
                    . "AND UOM_Temp.language = 'eng' "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[STK_T_Pallet] ON STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[STK_T_Invoice] ON STK_T_Invoice.Invoice_Id = STK_T_Inbound.Invoice_Id "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[CTL_M_Container] ON CTL_M_Container.Cont_Id = STK_T_Inbound.Cont_Id "
                    . "LEFT join [" . $database["Database_Name"] . "].[dbo].[CTL_M_Container_Size] ON CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id "
                    . "where STK_T_Inbound.Active = 'Y' AND 1=1 "
                    . "AND STK_T_Inbound.Product_Status IN ('PENDING','NORMAL','DAMAGE','REPACK','NC','SHORTAGE','GRADE','BLOCK','DG','BORROW') "
                    . "GROUP BY STK_T_Inbound.Product_Code, Product_NameEN, STK_T_Inbound.Product_Lot, STK_T_Inbound.Invoice_Id,"
                    . "STK_T_Invoice.Invoice_No, STK_T_Inbound.Cont_Id, CTL_M_Container.Cont_No, CTL_M_Container_Size.Cont_Size_No,"
                    . "CTL_M_Container_Size.Cont_Size_Unit_Code, STK_T_Inbound.Pallet_Id, STK_T_Pallet.Pallet_Code, STK_T_Inbound.Unit_Id,"
                    . "UOM_Temp.public_name, STK_M_Product.Standard_Unit_In_Id, UOM_Temp_Prod.public_name, CTL_M_Company.Company_NameEN "
                    . "HAVING sum(Balance_Qty)<>0";
            $query = mssql_query($sql);
            $data = array();
            while ($row = mssql_fetch_object($query)) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function get_company($data_connect) {
        // Connect to MSSQL
        $link = mssql_connect($data_connect["Server_Name"], $data_connect["User_Name"], $data_connect["Password"]);
        if (!$link) {
            die('Something went wrong while connecting to MSSQL');
        } else {
            $sql = "select Company_NameEN "
                    . "from [" . $data_connect["Database_Name"] . "].[dbo].[CTL_M_Company] "
                    . "where IsRenter = 1 ";
            $query = mssql_query($sql);
            $data = array();
            while ($row = mssql_fetch_object($query)) {
                $data[] = $row;
            }
            return $data;
        }
    }

    //End Joke Create 5/1/2017 Auto Send mail and send All database
}
