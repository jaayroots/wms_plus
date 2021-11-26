<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Admin_controller
 * -------------------------------------
 * Put this class on project at 22/04/2013 
 * @author Pakkaphon P.(PHP PG) 
 * Create by NetBean IDE 7.3
 * SWA WMS PLUS Project.
 * Use with dispatch module function
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class authen extends CI_Controller {

    public $AppType; // Add by Ton! 20140314

    public function __construct() {
        // session_start();
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        // $this->load->library('load');
        $this->AppType = "PC";
    }

    public function index() {
        $userIsNowInsession = $this->session->userdata('user_id');
        if ($userIsNowInsession == "") :
            $this->login($this->input->get('e'));
        else:
            redirect(base_url() . "index.php/welcome");
        endif;
    }

    /**
     * convert simplexmlelement to array
     * @param unknown $xml
     * @return multitype:string Ambigous <multitype:, multitype:string multitype: >
     */
    public function xml2array($xml) {
        $arr = array();
        foreach ($xml as $element) :
            $tag = $element->getName();
            $e = get_object_vars($element);
            if (!empty($e)) :
                $arr[$tag] = $element instanceof SimpleXMLElement ? $this->xml2array($element) : $e;
            else :
                if (trim($element) === "TRUE") :
                    $response = TRUE;
                elseif (trim($element) === "FALSE") :
                    $response = FALSE;
                else :
                    $response = trim($element);
                endif;
                $arr[$tag] = $response;
            endif;
        endforeach;
        return $arr;
    }

    /**
     * DOMDocument
     * @param unknown $root
     * @return multitype:NULL |Ambigous <multitype:NULL multitype:Ambigous <multitype:>  , multitype:, multitype:multitype: multitype:Ambigous <multitype:>  NULL >
     */
    public function xml_to_array($root) {
        $result = array();

        if ($root->hasAttributes()) :
            $attrs = $root->attributes;
            foreach ($attrs as $attr) :
                $result['@attributes'][$attr->name] = $attr->value;
            endforeach;
        endif;

        if ($root->hasChildNodes()) :
            $children = $root->childNodes;
            if ($children->length == 1) :
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) :
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                endif;
            endif;
            $groups = array();
            foreach ($children as $child) :
                if ($child->nodeName != "#text") :
                    if (!isset($result[$child->nodeName])) :
                        $result[$child->nodeName] = $this->xml_to_array($child);
                    else :
                        if (!isset($groups[$child->nodeName])) :
                            $result[$child->nodeName] = array($result[$child->nodeName]);
                            $groups[$child->nodeName] = 1;
                        endif;
                        $result[$child->nodeName][] = $this->xml_to_array($child);
                    endif;
                endif;
            endforeach;
        endif;

        return $result;
    }

    public function login($e = "") {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->library('cryptography');
        $this->load->library('encrypt');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('user', 'User Name', 'required|min_length[4]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[4]');
        $this->form_validation->set_rules('branch', 'Department', 'required');
        $this->form_validation->set_rules('renter', 'Renter', 'required');

        // remove for temp
        //$listBranch = $this->getBranch();
        //$listRenter = $this->getRenter();
        $branch = array();
        $renter = array();
        /* foreach ($listBranch as $row) :
          $branch[$row->Company_Id] = $row->Company_NameEN;
          endforeach; */

        // START
        $doc = new DOMDocument();

        $renter_path = APPPATH . $this->config->item('renter_list');
        if ($this->config->item('cryptography')) :
            $encode_data = read_file($renter_path);
            $decode_data = $this->cryptography->DecryptSource($encode_data);
            $doc->loadXML($decode_data);
        else :
            $doc->load($renter_path); //xml file loading here
        endif;
        $obj = $doc->getElementsByTagName("renters");

        foreach ($obj as $key => $values) :
            $fullname = $values->getElementsByTagName("fullname");
            $alias = $values->getElementsByTagName("alias");
            $id = $values->getElementsByTagName("id");
            $renter_data[$alias->item(0)->nodeValue . "|" . $id->item(0)->nodeValue] = $fullname->item(0)->nodeValue;
        endforeach;

        // remove        
        /*
          foreach ($listRenter as $row) :
          $renter[$row->Company_Id] = $row->Company_NameEN;
          endforeach;
         */

        // END

        if ($this->form_validation->run() !== false) :
            // Load database config
            $branch = explode("|", $this->input->post("branch"));
            $renter = explode("|", $this->input->post("renter"));
            $db_config = array();
            $db_sess = array();

            // Add and Check By Ball
            $config_database = APPPATH . 'config/database/' . $branch['1'];
            if ($this->config->item('cryptography')) :
                $encode_data = read_file($config_database);
                $decode_data = $this->cryptography->DecryptSource($encode_data);
                $objXML = simplexml_load_string($decode_data);
            else :
                $objXML = simplexml_load_file($config_database);
            endif;
            $_xml = $this->xml2array($objXML);
            $xml = $_xml['body'];
            // Load Database Configuration
            $db_config['hostname'] = 'localhost:3306';
            $db_config['username'] = 'root';
            $db_config['password'] = '';
            $db_config['database'] = 'tf_ecolab';
            $db_config['dbdriver'] = 'mysqli';
            $db_config['dbprefix'] = '';
            $db_config['pconnect'] = FALSE;
            $db_config['db_debug'] = FALSE;
            $db_config['cache_on'] = TRUE;
            $db_config['cachedir'] = '';
            $db_config['char_set'] = 'utf8';
            $db_config['dbcollat'] = 'utf8_general_ci';

            //load database config
            
            $db_connection = $this->load->database($db_config);

            // manual load config prevent overide data
            // BALL
            $db_sess['db_hostname'] = 'localhost:3306';
            $db_sess['db_username'] = 'root';
            $db_sess['db_password'] = '';
            $db_sess['db_database'] = 'tf_ecolab';
            $db_sess['db_dbdriver'] = 'mysqli';

            // Check and create folder            
            // $this->check_create_folder($xml['uploads']['upload_path']);
            // $this->check_create_folder($xml['path']['images']);
            // $this->check_create_folder($xml['path']['fonts']);
            // $this->check_create_folder($xml['path']['css']);
            // $this->check_create_folder($xml['path']['validate']);
            // $this->check_create_folder($xml['path']['log_password_user']); // Add by Ton! 20140414 
            $this->check_create_folder('./uploads/default/files/');
            $this->check_create_folder('./uploads/default/images/');
            $this->check_create_folder('./uploads/default/fonts/');
            $this->check_create_folder('./uploads/default/css/');
            $this->check_create_folder('./uploads/default/validate/');
            $this->check_create_folder('./uploads/default/log_password_users/'); // Add by Ton! 20140414 
            // End load
            // then validation passed. Get from db

            $log_id = NULL;
            $IP = get_client_ip(); # Get IP Address

            $browser_id = NULL; # Get Browser
            $r_Browser = $this->atn->getBrowser(NULL, check_browser())->result();
            if (count($r_Browser) > 0):
                foreach ($r_Browser as $DataBrowser) :
                    $browser_id = $DataBrowser->Browser_Id;
                endforeach;
            endif;

            $data = array('error' => '', 'branch' => $branch, 'renter' => $renter_data);

            $res = $this->atn->verify_user($this->input->post('user'), $this->input->post('password'));

//            if (count($res) > 0):
            if ($res !== false) :
                // READ DATA FOR renter AND branch

                $owner_results = $this->atn->get_branch_by_id($branch['0'])->result(); // Add By Akkarapol, 16/12/2013, เพิ่ม $owner_results = $this->atn->get_branch($branch['0'])->result(); สำหรับหา owner_results จะได้เอาไปใช้ในส่วนต่อๆไป
          
                // $owner_results = $this->atn->get_branch_by_id()->result(); // Add By Akkarapol, 16/12/2013, เพิ่ม $owner_results = $this->atn->get_branch($branch['0'])->result(); สำหรับหา owner_results จะได้เอาไปใช้ในส่วนต่อๆไป
                $renter_results = $this->atn->get_renter_login($renter['0'])->result();
                $dept_results = $this->atn->get_department($branch['2'])->result();

                if (count($renter_results) <= 0) :
                    $data = array('error' => "Renter not found, please contact administrator.", 'branch' => '', 'renter' => $renter_data);
                else :
                    # Check User Logged-in
                    $check_logged_in = $this->atn->get_SYS_L_UserLogin($res->UserLogin_Id, NULL, NULL, TRUE)->result();
                    if (count($check_logged_in) > 0):
                        $logged_in_HH = 0;
                        $logged_in_PC = 0;
                        $already_IP = NULL;
                        foreach ($check_logged_in as $value) :
                            if ($this->AppType === $value->App_Type):
                                if ($value->IP_Address === $IP):// Check Current IP.
                                    $whereUser['Log_Id'] = $value->Log_Id;
                                    $log_id = $value->Log_Id;
                                else:
                                    $already_IP = $value->IP_Address;
                                endif;
                                $logged_in_PC++;
                            else:
                                $logged_in_HH++;
                            endif;
                        endforeach;

                        if ($logged_in_HH > 0 || $logged_in_PC > 1): // User Logged-in already. (HH & PC)
                            $data = array('error' => "Can't login!. User login already.", 'branch' => $branch, 'renter' => $renter_data, 'call_node_check_user_online' => true);
                        else:
                            $dataUser['Browser_Id'] = $browser_id;
                            $dataUser['Browser_Version'] = check_browser_version();
                            $dataUser['Expiration_Time'] = time() + (60 * 15);
                            $result_login = FALSE;
                            if (!empty($log_id)):// Update SYS_L_UserLogin
                                $result_login = $this->atn->save_SYS_L_UserLogin('upd', $dataUser, $whereUser);
                                if ($result_login === TRUE):
                                    $username_session = $this->input->post('user');
                                    $username_data = array('username' => $username_session
                                        , 'user_id' => $res->UserLogin_Id
                                        , 'log_id' => $log_id
                                        , 'browser_id' => $browser_id
                                        , 'ip_address' => get_client_ip()
                                        , 'renter_id' => $renter_results['0']->Company_Id
                                        , 'branch_id' => $branch_results['0']->Company_Id
                                        , 'owner_id' => $owner_results['0']->Company_Id
                                        , 'renter_name' => $renter_results['0']->Company_NameEN
                                        , 'owner_name' => $owner_results['0']->Company_NameEN
                                        , 'dept_name' => $dept_results['0']->Company_NameEN
                                        , 'path_images' => $xml['path']['images']
                                        , 'xml_path' => $config_database
                                        , 'user_login_mail' => $res->Email_Address
                                        , 'user_group_code' => $res->UserGroup_Code
                                    );

                                    $this->session->set_userdata($username_data);
                                    $this->session->set_userdata($db_sess); // Add DB Configuration to session

                                    if (empty($data['error'])) :
                                        redirect('welcome');
                                    endif;
                                else:
                                    $data = array('error' => "Log-in Unsuccess !! [Update SYS_L_UserLogin Unsuccess.]", 'branch' => $branch, 'renter' => $renter_data);
                                endif;
                            else:
                                $data = array('error' => "Can't login!. User login already in another IP Address. " . " [IP = " . $already_IP . "]", 'branch' => $branch, 'renter' => $renter_data, 'call_node_check_user_online' => true);
                            endif;
                        endif;
                    else:
                        # New Log-in
                        $this->load->helper("date");
                        $human = mdate("%Y-%m-%d %h:%i:%s", time());

                        $dataUser['UserLogin_Id'] = $res->UserLogin_Id;
                        $dataUser['IP_Address'] = $IP;
                        $dataUser['Browser_Id'] = $browser_id;
                        $dataUser['Browser_Version'] = check_browser_version();
                        $dataUser['Login_Date'] = $human;
                        $dataUser['Login_Time'] = time();
                        $dataUser['Expiration_Time'] = time() + (60 * 15);
                        $dataUser['User_Login'] = TRUE;
                        $dataUser['App_Type'] = "PC";
          
                        $log_id = $this->atn->save_SYS_L_UserLogin('ist', $dataUser);
                        if ($log_id === 0 || $log_id === FALSE || $log_id === NULL):
                            $data = array('error' => "Log-in Unsuccess !! [Insert SYS_L_UserLogin Unsuccess.]", 'branch' => $branch, 'renter' => $renter_data);
                        else:
                            $username_session = $this->input->post('user');
                            $username_data = array('username' => $username_session
                                , 'user_id' => $res->UserLogin_Id
                                , 'log_id' => $log_id
                                , 'browser_id' => $browser_id
                                , 'ip_address' => $IP
                                , 'renter_id' => $renter_results['0']->Company_Id
                                , 'branch_id' => $branch_results['0']->Company_Id
                                , 'owner_id' => $owner_results['0']->Company_Id
                                , 'renter_name' => $renter_results['0']->Company_NameEN
                                , 'owner_name' => $owner_results['0']->Company_NameEN
                                , 'dept_name' => $dept_results['0']->Company_NameEN
                                , 'path_images' => $xml['path']['images']
                                , 'xml_path' => $config_database
                                , 'user_login_mail' => $res->Email_Address
                                , 'user_group_code' => $res->UserGroup_Code
                            );

                            $this->session->set_userdata($username_data);
                            $this->session->set_userdata($db_sess); // Add DB Configuration to session

                            if (empty($data['error'])) :
                                redirect('welcome');
                            endif;
                        endif;
                    endif;
                endif;
            else :
                $result_check = $this->atn->check_user_inactive($this->input->post('user'));
                if ($result_check === TRUE):
                    $data = array('error' => "Can't login! Users InActive!", 'branch' => $branch, 'renter' => $renter_data);
                else:
                    $data = array('error' => "Username or Password is wrong!", 'branch' => $branch, 'renter' => $renter_data);
                endif;
            endif;
        else :
            $data = array('error' => $e, 'branch' => '', 'renter' => $renter_data);
        endif;
        $data['db_config'] = @$db_config;

        if (!empty($data)):
            $this->load->view('login_view', $data);
        endif;
    }

    public function logout() {
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $dataUser['User_Login'] = FALSE;
        $dataUser['Logout_Time'] = time();
        $dataUser['Logout_Date'] = $human;

        $whereUser['Log_Id'] = $this->session->userdata('log_id');
        if (empty($whereUser['Log_Id'])) :
            $this->session->unset_userdata(); //session_destroy();
            $this->session->sess_destroy();
            redirect('authen');
        else :
            $this->atn->save_SYS_L_UserLogin('upd', $dataUser, $whereUser);
            $this->session->unset_userdata(); //session_destroy();
            $this->session->sess_destroy();
            sleep(1);
            redirect('authen');
        endif;
    }

    public function get_department() {
        // START
        $renter = explode("|", $this->input->get("renter_id"));
        $dept = array();
        $doc = new DOMDocument();

        $renter_path = APPPATH . '/config/renter/list.xml';

        if ($this->config->item('cryptography')) :
            $encode_data = read_file($renter_path);
            $decode_data = $this->cryptography->DecryptSource($encode_data);
            $doc->loadXML($decode_data);
        else :
            $doc->load($renter_path);
        endif;

        $obj = $doc->getElementsByTagName("renter");
        foreach ($obj as $key => $values) :
            $alias = $values->getElementsByTagName("alias");
            $id = $values->getElementsByTagName("id");
            if ($renter['0'] == $alias->item(0)->nodeValue) :
                $department = $values->getElementsByTagName("department");
                foreach ($department as $_department) :
                    $id = $_department->getElementsByTagName("id");
                    $database = $_department->getElementsByTagName("database");
                    $ownerNameEN = $_department->getElementsByTagName("ownerNameEN");
                    $alias = $_department->getElementsByTagName("alias");
                    $dept[$id->item(0)->nodeValue . "|" . $database->item(0)->nodeValue . "|" . $alias->item(0)->nodeValue] = $ownerNameEN->item(0)->nodeValue;
                endforeach;
            endif;
        endforeach;

        echo json_encode($dept);
    }

    public function getBranch() {
        $branch = $this->atn->getBranch()->result();
        return $branch;
    }

    public function getRenter() {
        $renter = $this->atn->getRenter()->result();
        return $renter;
    }

    private function check_create_folder($url) {

        if(empty($url)){
            $temp_url = explode("/", $url);
            $path = "";
            foreach ($temp_url as $idx => $value) {
                if(!empty($value)){
                    $path .= $value . "/";
                    if(!is_dir($path)){
                        mkdir($path, 0777, TRUE);
                        chmod($path, 0777);
                    }
                }
            }
        }
        // if ($url !== "") :
        //     $temp_url = explode("/", $url);
        //     $path = "";
        //     foreach ($temp_url as $idx => $value) :
        //         if (!empty($value)) :
        //             $path .= $value . "/";
        //             if (!is_dir($path)) :
        //                 mkdir($path, 0777, TRUE);
        //                 chmod($path, 0777);
        //             endif;
        //         endif;
        //     endforeach;
        // endif;
    }

}

/* End of file */
/* Location: ./application/controllers/authen.php */
