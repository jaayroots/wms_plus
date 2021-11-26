<?php

class menu_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getMenuAll() {
        $this->db->select("*");
        $this->db->from("ADM_M_MenuBar");
        $query = $this->db->get();
        return $query->result();
    }

    # Authen Menu by User Group : Add by Ton! 20131108
    # Parameter : UserLogin_Id

    function getMenuAuthGroup($UserLogin_Id) {
        //$this->db->select(" ADM_M_MenuBar.*, ADM_M_ImageItem.ImageName +''+ADM_M_ImageItem.ImageExt  AS ImageName");
        $this->db->select("ADM_M_MenuBar.*");
        $this->db->from("ADM_R_UserGroupMembers");
        $this->db->join("ADM_R_UserGroupMenus", "ADM_R_UserGroupMenus.UserGroup_Id = ADM_R_UserGroupMembers.UserGroup_Id AND ADM_R_UserGroupMenus.Active = 1", "INNER");
        $this->db->join("ADM_M_MenuBar", "ADM_M_MenuBar.MenuBar_Id = ADM_R_UserGroupMenus.Menu_id", "INNER");
        //$this->db->join("ADM_M_ImageItem", "ADM_M_ImageItem.ImageItem_Id = ADM_M_MenuBar.Icon_Image_Id", "LEFT OUTER");
        $this->db->where("ADM_R_UserGroupMembers.UserLogin_Id", $UserLogin_Id);
        $this->db->where("ADM_R_UserGroupMembers.Active", 1);
        $this->db->where("ADM_M_MenuBar.Menu_Type", "PC");
        $this->db->where("ADM_M_MenuBar.Active", 1);
        $this->db->order_by("ADM_M_MenuBar.MenuBar_Id, ADM_M_MenuBar.Sequence");
        $sql = $this->db->return_query(FALSE);
        $key = md5($sql);
        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );
        if ($remain >= 600) :
            $query = $this->db->query($sql);
            $response = $query->result();
            $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    # Get Menu Parent by MenuBar_Id : Add by Ton! 20131108
    # Parameter : Array(MenuBar_Id)

    function getMenuAuthParent($MenuBar_Id = NULL, $Menu_Type = "PC") {
        $this->db->select(" ADM_M_MenuBar.*");
        $this->db->from("ADM_M_MenuBar");
        if (!empty($MenuBar_Id)):
            $this->db->where_in("ADM_M_MenuBar.MenuBar_Id", explode(",", $MenuBar_Id));
//            $this->db->where("ADM_M_MenuBar.MenuBar_Id IN (" . $MenuBar_Id . ")");
        endif;
        $this->db->where("ADM_M_MenuBar.Menu_Type", $Menu_Type);
        $this->db->where("ADM_M_MenuBar.Active", TRUE);
        $this->db->where("ADM_M_MenuBar.Parent_Id", 0);
        $this->db->order_by("ADM_M_MenuBar.MenuBar_Id, ADM_M_MenuBar.Sequence");
        // p($this->db->last_query(FALSE));
        // exit;
        $sql = $this->db->get()->result();
        // $sql = $this->db->return_query(FALSE);
        $key = md5(json_encode($sql));
        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );

        if ($remain >= 600) :
            // $query = $this->db->query($sql);
            $response = $sql;
            // $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    # Get Menu Child by Parent_Id : Add by Ton! 20131108
    # Parameter : Array(Parent_Id)

    function getMenuAuthChild($parent_id = NULL, $mnu_permission = NULL, $Menu_Type = "PC") {// Add $mnu_permission. Edit by Ton! 20140123
        // Query for line array
        // BALL
        $line_result = $this->get_separate_line($parent_id);
        if ($line_result != NULL) :
            $mnu_permission .= $line_result;
        endif;
        // END

        $this->db->select(" ADM_M_MenuBar.*");
        $this->db->from("ADM_M_MenuBar");
        if (!empty($parent_id)):
            $this->db->where_in("ADM_M_MenuBar.Parent_Id", explode(",", $parent_id));
//            $this->db->where("ADM_M_MenuBar.Parent_Id IN (" . $parent_id . ")");
        endif;
        if (!empty($mnu_permission)):
            $this->db->where_in("ADM_M_MenuBar.MenuBar_Id", explode(",", $mnu_permission));
//            $this->db->where("ADM_M_MenuBar.MenuBar_Id IN (" . $mnu_permission . ")");
        endif;
        $this->db->where("ADM_M_MenuBar.Menu_Type", $Menu_Type);
        $this->db->where("ADM_M_MenuBar.Active", TRUE);
        $this->db->order_by("ADM_M_MenuBar.Sequence, ADM_M_MenuBar.MenuBar_Id");
        // $sql = $this->db->return_query(FALSE);
        //echo $sql . "<br/><br/>";
        // $key = md5($sql);
                $sql = $this->db->get()->result();
        // $sql = $this->db->return_query(FALSE);
        $key = md5(json_encode($sql));

        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );
        if ($remain >= 600) :
            // $query = $this->db->query($sql);
            $response = $sql;
            // $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    function get_Parent_Menu_ADM_M_User_Permission($UserLogin_Id) {// Get Menu by Permission. Add by Ton! 20140122
        $sql = "SELECT ADM_M_MenuBar.* FROM ADM_M_MenuBar
            WHERE ADM_M_MenuBar.Menu_Type = 'PC' AND ADM_M_MenuBar.Active = 1 AND ADM_M_MenuBar.MenuBar_Id
            IN (SELECT ADM_M_User_Permission.MenuBar_Id FROM ADM_M_User_Permission WHERE ADM_M_User_Permission.UserLogin_Id = " . $UserLogin_Id . ") ";
//        return $this->db->query($sql);

        $key = md5($sql);
        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );
        if ($remain >= 600) :
            $query = $this->db->query($sql);
            $response = $query->result();
            $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    function get_Parent_Menu_ADM_M_Role_Permission($UserRole_Id) {// Get Menu by Permission. Add by Ton! 20140123
        $sql = "SELECT ADM_M_MenuBar.* FROM ADM_M_MenuBar
            WHERE ADM_M_MenuBar.Menu_Type = 'PC' AND ADM_M_MenuBar.Active = 1 AND ADM_M_MenuBar.MenuBar_Id
            IN (SELECT ADM_M_Role_Permission.MenuBar_Id FROM ADM_M_Role_Permission WHERE ADM_M_Role_Permission.UserRole_Id IN (" . $UserRole_Id . ")) ";
//        return $this->db->query($sql);

        $key = md5($sql);
        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );
        if ($remain >= 600) :
            $query = $this->db->query($sql);
            $response = $query->result();
            $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    function get_Parent_Menu_ADM_M_Group_Permission($UserGroup_Id) {// Get Menu by Permission. Add by Ton! 20140123
        $sql = "SELECT ADM_M_MenuBar.* FROM ADM_M_MenuBar
            WHERE ADM_M_MenuBar.Menu_Type = 'PC' AND ADM_M_MenuBar.Active = 1 AND ADM_M_MenuBar.MenuBar_Id
            IN (SELECT ADM_M_Group_Permission.MenuBar_Id FROM ADM_M_Group_Permission WHERE ADM_M_Group_Permission.UserGroup_Id IN (" . $UserGroup_Id . ")) ";
//        return $this->db->query($sql);

        $key = md5($sql);
        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );
        if ($remain >= 600) :
            $query = $this->db->query($sql);
            $response = $query->result();
            $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    // Add By Akkarapol, 24/01/2014, Add function for get MenuBar_Id fOr use in Auth
    public function get_menu_id_by_uri($NavigationUri) {

        $this->db->select('MenuBar_Id');
        $this->db->from("ADM_M_MenuBar");
        $this->db->where("NavigationUri", $NavigationUri);
        // $this->db->where("Active", 1);
        // $this->db->where("Menu_Type", "PC");
//        $query = $this->db->get();
        // p($this->db->last_query());
//        return $query;
        $sql = $this->db->get()->result();
        // p($sql);
        // exit;
        $key = md5(json_encode($sql));
        // $sql = $this->db->return_query(FALSE);
        // $key = md5($sql);
        $cache = $this->cache->memcached->get($key);
        $meta_data = $this->cache->memcached->get_metadata($key);
        $remain = abs($meta_data['expire'] - time() );
        if ($remain >= 600) :
            // $query = $this->db->query($sql);
            $response = $sql;
            // $this->cache->memcached->save($key, $response, NULL, 600);
        else:
            $response = $cache;
        endif;

        return $response;
    }

    private function get_separate_line($parent_id) {
        $this->db->select('MenuBar_Id');
        $this->db->where_in("ADM_M_MenuBar.Parent_Id", explode(",", $parent_id));
        $this->db->where("ADM_M_MenuBar.Menu_Type", "PC");
        $this->db->where("ADM_M_MenuBar.Active", 1);
        $this->db->where("ADM_M_MenuBar.MenuBar_NameEn", '<hr/>');
        $query = $this->db->get('ADM_M_MenuBar');
        if ($query->result()) :
            $result = "";
            foreach ($query->result() as $idx => $value) :
                $result .= "," . $value->MenuBar_Id;
            endforeach;
            return $result;
        else :
            return NULL;
        endif;
    }

    // END Add By Akkarapol, 24/01/2014, Add function for get MenuBar_Id fOr use in Auth
}

?>
