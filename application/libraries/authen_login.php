<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class authen_login {

    /**
     * authen for user valid
     */
    function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        $log_id = (int) $CI->session->userdata("log_id");
        $user_id = $CI->session->userdata("user_id");
        $ip = $CI->session->userdata("ip_address");
        $controller = $CI->uri->segment(1);

//        if (strtoupper($controller) != "AUTHEN" && strtoupper($controller) != "CROSS_SITE" && strtoupper($controller) != "LOCATION" && strtoupper($controller) != "SENDMAIL" && ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'])) : //ADD BY POR 2013-11-18 (CREDIT BY BALL) แก้ไขให้ support เครื่อง localhost
        if (!in_array(strtoupper($controller), array("C_BYPASS", "AUTHEN", "CROSS_SITE", "LOCATION", "SENDMAIL")) && ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'])) :
            if ($log_id > 0):
                # check user logging.
                $CI->db->select("SYS_L_UserLogin.Log_Id");
                $CI->db->from("SYS_L_UserLogin");
                $CI->db->where("SYS_L_UserLogin.UserLogin_Id", $user_id);
                $CI->db->where("SYS_L_UserLogin.IP_Address", $ip);
                $CI->db->where("SYS_L_UserLogin.User_Login", YES);
                $result = $CI->db->get()->result();
                if (count($result) > 0):// logging.
                    $dataUser['Expiration_Time'] = time() + (60 * 15); // + 15 min.
                    $dataUser['User_Login'] = TRUE;
                    $whereUser['Log_Id'] = $log_id;
                    $CI->db->where($whereUser);
                    $CI->db->update('SYS_L_UserLogin', $dataUser);
                else: // Not logging.
                    $CI->session->unset_userdata();
                    $CI->session->sess_destroy();
                    redirect('authen');
                endif;
            else:
                $CI->session->unset_userdata();
                $CI->session->sess_destroy();
                redirect('authen');
            endif;
        endif;
        if (!empty($log_id)):
            $this->extend_time_limit_user_login();
        endif;
    }

    function extend_time_limit_user_login($time = NULL) {// Add by Ton! 20140424
        $CI = & get_instance();
        $CI->load->model("authen_model", "auth");

        if (empty($time)):
            $time = 120;
        endif;

        $whereUser['Log_Id'] = $CI->session->userdata("log_id");
        $dataUser['Expiration_Time'] = time() + (60 * $time);

        $result_extend_time = $CI->auth->save_SYS_L_UserLogin("upd", $dataUser, $whereUser);

        return $result_extend_time;
    }

}

/* End of file authen_login.php */
/* Location: ./application/libraries/authen_login.php */