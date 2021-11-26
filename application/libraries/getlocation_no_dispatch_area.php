<?php
if (!defined('BASEPATH'))
exit('No direct script access allowed');


class getlocation_no_dispatch_area {

    /**
     * set construct
     */
    function __construct() {
      $CI = & get_instance();
    }
    
    // function test(){
    //   p('ssss');
    // }

     function get_location($params = NULL) {   
      //  p('ssss'); exit;
        $CI = & get_instance();
        $location = $params['location_code'];
        $getlocation = $this->location_donotmove();
          foreach ($getlocation as $key => $value) {
          $alocation[ 'Location_Code'] = $value['Location_code'];
          $temp[$value['Location_Code']] = $alocation;
          $response = array_key_exists($location, $temp);
          if($response == 1){
          $result = TRUE;
          }else{
          $result = NULL;
          }
        }
        echo json_encode($result);

    }

    function location_donotmove(){
      $CI = & get_instance();
      $CI->db->select("l.Location_Code");
      $CI->db->from("STK_M_Location l");
      $CI->db->join("STK_M_Storage s "," s.Storage_Id = l.Storage_Id");
      $CI->db->join("STK_M_Storage_Type t "," t.StorageType_Id = s.StorageType_Id");
      $CI->db->join("SYS_M_Domain d "," d.Dom_Code = t.StorageType_Code");
      $CI->db->where("d.Dom_Host_Code ='NODISP_STORAGE' and d.Dom_Active ='Y'");
      $query = $CI->db->get();
      // p($CI->db->last_query()); 
      return $query->result_array();


      }

}
