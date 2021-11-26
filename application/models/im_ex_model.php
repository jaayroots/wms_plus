<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of im_ex_model
 *
 * @author P'Zex
 */
class im_ex_model extends CI_Model {

    //put your code here
    function import($Header, $Detail) {
        $r = 0;
        foreach ($Header as $H) {
            //print_r($Header[$r]);
            //echo "<br/>";
            $this->db->insert('IMP_H_Picking', $Header[$r]);
            foreach ($Detail[$r] as $row) {
                $this->db->insert('IMP_D_Picking', $row);
            }
            $r++;
        }
    }

    function getHeaderPicking() {
        $query = $this->db->get('IMP_H_Picking');
        return $query->result();
    }

//    function im($d){
//        $da['IMP_JobNo']=$d;
//        $this->db->insert('IMP_H_Picking',$da);
//    }

    function LastId() {
        $sql = "Select max(IMP_JobNo)+1 as lastid from IMP_H_Picking";
        $query = $this->db->query($sql);
        $row = $query->result();
        $lastid = $row[0]->lastid;
        if ($lastid == null)
            $lastid = 1;
        return $lastid;
    }

    function saveIMP_H_Picking($Header) {// Add by Ton! 20130530
        $id_value = 0;  //ADD BY POR 2014-03-06 set value=0
        $this->db->insert('IMP_H_Picking', $Header);
        $id = $this->db->insert_id(); //return last id : COMMENT BY POR 2014-03-06
        //ADD BY POR 2014-03-05 check insert complete
        $countrow = $this->db->affected_rows();
        if ($countrow > 0) : //insert complete if more than zero.
            $id_value = $id; //new value is last id 
        endif;
        //END ADD

        return $id_value; //if insert complete value more than zero but not value is zero : COMMENT BY POR 2014-03-06
    }

    function saveIMP_D_Picking($Detail) {// Add by Ton! 20130530
        $this->db->insert('IMP_D_Picking', $Detail);
        $id = $this->db->insert_id();
        return $id;
    }

    function logIMP($strLog, $IMP_ID, $IMP_DSeq) {// Add by Ton! 20130604
        $sql = " UPDATE IMP_D_Picking SET IMP_Remark = '" . $strLog . "' WHERE ";
        $sql.="IMP_ID = " . $IMP_ID . "AND ";
        $sql.="IMP_DSeq = " . $IMP_DSeq;
        $this->db->query($sql);
    }

    function getIMPUnsuccess($IMP_ID) {// Add by Ton! 20130605
        $this->db->select("IMP_PRDBarcode, IMP_PRDDesc, IMP_FromSubInv, IMP_ToSubInv, IMP_PRDQTY, IMP_Remark");
        $this->db->from("IMP_D_Picking");
        $this->db->where("IMP_D_Picking.IMP_ID", $IMP_ID);
        $this->db->where("IMP_D_Picking.IMP_Remark IS NOT NULL");
        $query = $this->db->get();
        return $query;
    }

    // Comment Out by Ton! Not Used. 20131028
//    function getSumSuccess($IMP_ID) {// Add by Ton! 20130605
////        SELECT COUNT(*) AS sum_success FROM IMP_D_Picking WHERE IMP_Remark IS NULL AND IMP_ID = 1
//        $this->db->select("COUNT(*) AS sum_success");
//        $this->db->from("IMP_D_Picking");
//        $this->db->where("IMP_D_Picking.IMP_ID", $IMP_ID);
//        $this->db->where("IMP_D_Picking.IMP_Remark IS NULL");
//        $query = $this->db->get();
//        $result = $query->result();
//        if ($query->num_rows > 0) {
//            return $result[0]->sum_success;
//        } else {
//            return 0;
//        }
//    }
    // Comment Out by Ton! Not Used. 20131028
//    function getSumUnsuccess($IMP_ID) {// Add by Ton! 20130605
////        SELECT COUNT(*) AS sum_success FROM IMP_D_Picking WHERE IMP_Remark IS NOT NULL AND IMP_ID = 1
//        $this->db->select("COUNT(*) AS sum_unsuccess");
//        $this->db->from("IMP_D_Picking");
//        $this->db->where("IMP_D_Picking.IMP_ID", $IMP_ID);
//        $this->db->where("IMP_D_Picking.IMP_Remark IS NOT NULL");
//        $query = $this->db->get();
//        $result = $query->result();
//        if ($query->num_rows > 0) {
//            return $result[0]->sum_success;
//        } else {
//            return 0;
//        }
//    }

    function getResultIMP($IMP_ID) {// Add by Ton! 20130605
        $sql = "
            SELECT             
            CASE WHEN a.IMP_DocNo IS NULL THEN b.IMP_DocNo ELSE a.IMP_DocNo END AS IMP_DocNo
            , CASE WHEN a.IMP_ID IS NULL THEN b.IMP_ID ELSE a.IMP_ID END AS IMP_ID
            , CASE WHEN (a.x + b.y) IS NULL THEN CASE WHEN a.x IS NULL THEN b.y + 0 
            ELSE CASE WHEN b.y IS NULL THEN a.x + 0 ELSE (b.y + a.x) END END 
            ELSE CASE WHEN b.y IS NULL THEN a.x + 0 ELSE CASE WHEN a.x IS NULL THEN a.x + 0 ELSE (a.x + b.y) END END END AS sum_order
            , CASE WHEN a.x IS null THEN 0 ELSE a.x end AS sum_success
            , CASE WHEN b.y IS null THEN 0 ELSE b.y end AS sum_unsuccess

            FROM (SELECT COUNT(CASE WHEN aa.IMP_Remark is null THEN 1 ELSE 0 END) as x ,aa.IMP_ID , aa.IMP_DocNo
            FROM IMP_D_Picking aa 
            WHERE aa.IMP_Remark is null 
            GROUP BY aa.IMP_ID, aa.IMP_DocNo) As a 
            FULL OUTER JOIN 
            (SELECT COUNT(CASE WHEN bb.IMP_Remark IS NOT null THEN 1 ELSE 0 END) as y ,bb.IMP_ID ,bb.IMP_DocNo 
            FROM IMP_D_Picking bb 
            WHERE bb.IMP_Remark is not null 
            GROUP BY bb.IMP_ID,bb.IMP_DocNo) As b 
            ON a.IMP_ID = b.IMP_ID 
            WHERE a.IMP_ID IN (" . implode(",", $IMP_ID) . ") 
            OR b.IMP_ID IN (" . implode(",", $IMP_ID) . ")";

        $query = $this->db->query($sql);
        return $query;
    }

    function getResultIMP_Unsuccess($IMP_ID) {// Add by Ton! 20130917
        $this->db->select("IMP_DocNo, IMP_PRDBarcode, IMP_PRDQTY, IMP_Remark");
        $this->db->from("IMP_D_Picking");
        $this->db->where("IMP_D_Picking.IMP_ID", $IMP_ID);
//        $this->db->where("IMP_D_Picking.IMP_Status IS NOT NULL");
        $this->db->where("IMP_D_Picking.IMP_Remark IS NOT NULL"); // Add By Akkarapol, 11/02/2014, เปลี่ยนการ filter จาก IMP_Status เป็น IMP_Remark เพราะต้องหาอันที่มี Remark ไม่ใช่อันที่มี Status
        $this->db->order_by("IMP_D_Picking.IMP_DSeq");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getResultIMP_Unsuccess2($IMP_ID) {
        $this->db->select("Doc_Refer_Ext, Reserv_Qty, Product_Code,IMP_Remark");
        $this->db->from("IMP_D_Pre_Dispatch");
        $this->db->where("IMP_D_Pre_Dispatch.IMP_ID", $IMP_ID);
        $this->db->where("IMP_D_Pre_Dispatch.IMP_Remark IS NOT NULL");
        $this->db->order_by("IMP_D_Pre_Dispatch.IMP_DSeq");
        $query = $this->db->get();
        return $query;
    }

    function checkDocNo($docNo, $process_type) {
        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->where("STK_T_Order.Doc_Refer_Ext", $docNo);
        $this->db->where("STK_T_Order.Process_Type", $process_type);
        $this->db->join('STK_T_Workflow', 'STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id AND STK_T_Workflow.Present_State != "-1"', 'INNER');

        $query = $this->db->get();
        $query->result();
        if ($query->num_rows > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function saveIMP_H_Pre_Receive($Header) {// Add by Ton! 20130618
        $this->db->insert('IMP_H_Pre_Receive', $Header);
        $id = $this->db->insert_id();
        return $id;
    }

    function saveIMP_D_Pre_Receive($Detail) {// Add by Ton! 20130618
        $this->db->insert('IMP_D_Pre_Receive', $Detail);
        $id = $this->db->insert_id();
        return $id;
    }

    function logIMP_D_Pre_Receive($strLog, $IMP_ID, $IMP_DSeq) {// Add by Ton! 20130618
        $sql = " UPDATE IMP_D_Pre_Receive SET IMP_Remark = '" . $strLog . "' WHERE ";
        $sql.="IMP_ID = " . $IMP_ID . "AND ";
        $sql.="IMP_DSeq = " . $IMP_DSeq;
        $this->db->query($sql);
    }

    function getResultIMP_Pre_Receive($IMP_ID) {// Add by Ton! 20130618
        $sql = "SELECT CASE WHEN b.Doc_Refer_Ext IS NULL THEN a.Doc_Refer_Ext ELSE b.Doc_Refer_Ext END AS Doc_Refer_Ext
            , CASE WHEN (a.x + b.y) IS NULL THEN CASE WHEN a.x IS NULL THEN b.y + 0 
            ELSE CASE WHEN b.y IS NULL THEN a.x + 0 ELSE (b.y + a.x) END END 
            ELSE CASE WHEN b.y IS NULL THEN a.x + 0 ELSE CASE WHEN a.x IS NULL THEN a.x + 0 ELSE (a.x + b.y) END END END AS sum_order
            , CASE WHEN a.x IS NULL THEN 0 ELSE a.x END AS sum_success
            , CASE WHEN b.y IS NULL THEN 0 ELSE b.y END AS sum_unsuccess
            , CASE WHEN b.IMP_ID IS NULL THEN a.IMP_ID ELSE b.IMP_ID END AS IMP_ID
            FROM
            (SELECT COUNT(CASE WHEN aa.IMP_Check = 'N' THEN 1 ELSE 0 END) AS x, IMP_ID, Doc_Refer_Ext 
                FROM dbo.IMP_D_Pre_Receive AS aa WHERE (IMP_Check = 'N') 
                GROUP BY IMP_ID, Doc_Refer_Ext) AS a 
            FULL OUTER JOIN 
            (SELECT COUNT(CASE WHEN bb.IMP_Check <> 'N' THEN 1 ELSE 0 END) AS y, IMP_ID, Doc_Refer_Ext 
                FROM dbo.IMP_D_Pre_Receive AS bb WHERE (IMP_Check <> 'N')
                GROUP BY IMP_ID, Doc_Refer_Ext) AS b 
            ON a.IMP_ID = b.IMP_ID
            WHERE (a.IMP_ID IN (" . implode(",", $IMP_ID) . ") OR b.IMP_ID IN (" . implode(",", $IMP_ID) . "))"; // Edit by Ton! 20130828
//            WHERE a.IMP_ID IN (".implode(",", $IMP_ID).")";
        $query = $this->db->query($sql);
//        echo $this->db->last_query();
        return $query;
    }

    function getIMP_Pre_Receive_Unsuccess($IMP_ID) {// Add by Ton! 20130618
        $this->db->select("Doc_Refer_Ext, Product_Code, Product_Name, Reserv_Qty, IMP_Remark,IMP_Check"); //EDIT BY POR 2014-03-06 put IMP_Check
        $this->db->from("IMP_D_Pre_Receive");
        $this->db->where("IMP_D_Pre_Receive.IMP_ID", $IMP_ID);
//        $this->db->where("IMP_D_Pre_Receive.IMP_Remark IS NOT NULL");
        $this->db->where("IMP_D_Pre_Receive.IMP_Check <> 'N'"); // Edit by Ton! 20130726
        $this->db->order_by("IMP_D_Pre_Receive.IMP_DSeq");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    /**
     * function for get data from table IMP_H_Picking by IMP_ID
     * @param type $IMP_ID
     * @return type
     */
    function get_IMP_H_Picking_by_IMP_ID($IMP_ID) {
        $this->db->select(" IMP_ID, IMP_DocNo, IMP_Check_DocNo");
        $this->db->from("IMP_H_Picking");
        $this->db->where_in("IMP_H_Picking.IMP_ID", $IMP_ID);

        return $this->db->get();
    }

    /**
     * function for insert data to IMP_H_Pre_Dispatch
     * @param type $Header
     * @return type
     */
    function saveIMP_H_Pre_Dispatch($Header) {
        $this->db->insert('IMP_H_Pre_Dispatch', $Header);
        $id = $this->db->insert_id();
        return $id;
    }

    /**
     * function for insert data to IMP_D_Pre_Dispatch
     * @param type $Detail
     * @return type
     */
    function saveIMP_D_Pre_Dispatch($Detail) {
        $this->db->insert('IMP_D_Pre_Dispatch', $Detail);
        $id = $this->db->insert_id();
        return $id;
    }

    /**
     * function for update data IMP_Remark for logData
     * @param type $strLog
     * @param type $IMP_ID
     * @param type $IMP_DSeq
     */
    function logIMP_D_Pre_Dispatch($strLog, $IMP_ID, $IMP_DSeq) {
        $sql = " UPDATE IMP_D_Pre_Dispatch SET IMP_Remark = '" . $strLog . "' WHERE ";
        $sql.="IMP_ID = " . $IMP_ID . " AND ";
        $sql.="IMP_DSeq = " . $IMP_DSeq;
        $this->db->query($sql);
    }

    /**
     *
     * @param type $IMP_ID
     * @return type
     */
    function getResultIMP_Pre_Dispatch($IMP_ID) {// Add by Ton! 20130618
        $sql = "SELECT CASE WHEN b.Doc_Refer_Ext IS NULL THEN a.Doc_Refer_Ext ELSE b.Doc_Refer_Ext END AS Doc_Refer_Ext
            , CASE WHEN (a.x + b.y) IS NULL THEN CASE WHEN a.x IS NULL THEN b.y + 0
            ELSE CASE WHEN b.y IS NULL THEN a.x + 0 ELSE (b.y + a.x) END END
            ELSE CASE WHEN b.y IS NULL THEN a.x + 0 ELSE CASE WHEN a.x IS NULL THEN a.x + 0 ELSE (a.x + b.y) END END END AS sum_order
            , CASE WHEN a.x IS NULL THEN 0 ELSE a.x END AS sum_success
            , CASE WHEN b.y IS NULL THEN 0 ELSE b.y END AS sum_unsuccess
            , CASE WHEN b.IMP_ID IS NULL THEN a.IMP_ID ELSE b.IMP_ID END AS IMP_ID
            FROM
            (SELECT COUNT(CASE WHEN aa.IMP_Check = 'N' THEN 1 ELSE 0 END) AS x, IMP_ID, Doc_Refer_Ext
                FROM dbo.IMP_D_Pre_Dispatch AS aa WHERE (IMP_Check = 'N')
                GROUP BY IMP_ID, Doc_Refer_Ext) AS a
            FULL OUTER JOIN
            (SELECT COUNT(CASE WHEN bb.IMP_Check <> 'N' THEN 1 ELSE 0 END) AS y, IMP_ID, Doc_Refer_Ext
                FROM dbo.IMP_D_Pre_Dispatch AS bb WHERE (IMP_Check <> 'N')
                GROUP BY IMP_ID, Doc_Refer_Ext) AS b
            ON a.IMP_ID = b.IMP_ID
            WHERE (a.IMP_ID IN (" . implode(",", $IMP_ID) . ") OR b.IMP_ID IN (" . implode(",", $IMP_ID) . "))"; // Edit by Ton! 20130828
//            WHERE a.IMP_ID IN (".implode(",", $IMP_ID).")";
        $query = $this->db->query($sql);
//        echo $this->db->last_query();
        return $query;
    }

    /**
     *
     * @param type $IMP_ID
     * @return type
     */
    function getIMP_Pre_Dispatch_Unsuccess($IMP_ID) {// Add by Ton! 20130618
        $this->db->select("Doc_Refer_Ext, Product_Code, Product_Name, Reserv_Qty, IMP_Remark,IMP_Check"); //EDIT BY POR 2014-03-06 put IMP_Check
        $this->db->from("IMP_D_Pre_Dispatch");
        $this->db->where("IMP_D_Pre_Dispatch.IMP_ID", $IMP_ID);
//        $this->db->where("IMP_D_Pre_Dispatch.IMP_Remark IS NOT NULL");
        $this->db->where("IMP_D_Pre_Dispatch.IMP_Check <> 'N'"); // Edit by Ton! 20130726
        $this->db->order_by("IMP_D_Pre_Dispatch.IMP_DSeq");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

}
