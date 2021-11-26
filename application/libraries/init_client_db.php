<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class init_client_db extends CI_Controller {

    public $demo;

    public function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        $CI->load->library('encryption');

        if (isset($_COOKIE['connection'])) :

            $temp = $CI->encryption->safe_b64decode($_COOKIE['connection']);
            $cookie = explode("|", $temp);

            if ($cookie) :
                $CI->session->set_userdata('user_id', $cookie['4']);
                $db_config['hostname'] = $cookie['0'];
                $db_config['username'] = $cookie['1'];
                $db_config['password'] = $cookie['2'];
                $db_config['database'] = $cookie['3'];
                $db_config['dbdriver'] = 'mssql';
                $db_config['dbprefix'] = '';
                $db_config['pconnect'] = FALSE;
                $db_config['db_debug'] = TRUE;
                $db_config['cache_on'] = FALSE;
                $db_config['cachedir'] = '';
                $db_config['char_set'] = 'utf8';
                $db_config['dbcollat'] = 'utf8_general_ci';

                //load database config
                $db_connection = $CI->load->database($db_config);
            endif;
        endif;


        if ($CI->session->userdata('db_hostname')) :

            $db_config['hostname'] = $CI->session->userdata('db_hostname');
            $db_config['username'] = $CI->session->userdata('db_username');
            $db_config['password'] = $CI->session->userdata('db_password');
            $db_config['database'] = $CI->session->userdata('db_database');
            $db_config['dbdriver'] = $CI->session->userdata('db_dbdriver');
            $db_config['dbprefix'] = '';
            $db_config['pconnect'] = FALSE;
            $db_config['db_debug'] = TRUE;
            $db_config['cache_on'] = FALSE;
            $db_config['cachedir'] = '';
            $db_config['char_set'] = 'utf8';
            $db_config['dbcollat'] = 'utf8_general_ci';

            //load database config
            $db_connection = $CI->load->database($db_config);

        else :
            log_message("ERROR", "Can't load session database");
        //echo "FAILED";
        //exit();
        //redirect('authen/login');
        endif;

        $flag = FALSE;
        $log_file = getcwd() . "/js/logfile.txt";
        $jsmin_file = getcwd() . "/js/jsmin.js";
        $files = array('js/util.js'
            , 'js/jquery.js'
            , 'js/jquery.dataTables.min.js'
            , 'js/jquery.dataTables.editable.js'
            , 'js/FixedHeader.min.js'
            , 'js/jquery.jeditable.js'
            , 'js/jquery-ui.js'
            , 'js/jquery.jeditable.datepicker.js'
            , 'js/jquery.validate.js'
            , 'js/bootstrap.min.js'
            , 'js/bootstrap-datepicker.js'
            , 'js/FixedColumns.js'
            , 'js/jquery.checkboxtree.min.js'
                /* , 'js/validate_data.js' */                );
        $file_list = array();

        $logs = $this->check_file_logs($log_file, $files);

        foreach ($logs as $idx => $val) :
            if ($val != "") :
                $tmp = explode("|", $val);
                $file_list[$tmp['0']] = $tmp['1'];
            endif;
        endforeach;

        foreach ($files as $file) :
            $logs_time = $file_list[$file];
            if (filemtime($file) > $logs_time) :
                $flag = TRUE;
            endif;
        endforeach;

        if (!file_exists($jsmin_file)) :
            $this->create_compress_list($jsmin_file, $files);
        elseif ($flag) :
            unlink($log_file);
            unlink($jsmin_file);
            $this->create_compress_list($jsmin_file, $files);
        endif;
    }

    public function check_file_logs($path, $files) {
        $data = "";
        $f = @fopen($path, "rw");
        if (!$f) :
            foreach ($files as $file) :
                $data .= $file . "|" . filemtime($file) . "\r\n";
            endforeach;
            file_put_contents(getcwd() . "/js/logfile.txt", $data);
        else :
            $data = file_get_contents($path);
        endif;

        return explode("\r\n", $data);
    }

    public function create_compress_list($path, $files) {

        $CI = & get_instance();
        $CI->load->library('jsmin');
        //$CI->load->library('jsqueeze');

        $minifiedCode = "";
        foreach ($files as $file) :
            $minifiedCode .= $CI->jsmin->minify(file_get_contents($file));
        //$minifiedCode .= $CI->jsqueeze->squeeze(file_get_contents($file));
        endforeach;

        file_put_contents($path, $minifiedCode);
    }

}