<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class native_session extends CI_Controller {

    public $native_session;

    public function __construct() {

        $CI = & get_instance();

        $sess_xml = $CI->session->userdata('xml_path');


        /**
         * Zone Set by default.xml
         */
        $sess_xml_default = $CI->session->userdata('xml_path');
        $sess_xml_default = explode("/", $sess_xml_default);
        unset($sess_xml_default[count($sess_xml_default) - 1]);
        $sess_xml_default[] = "default.xml";
        $sess_xml_default = implode("/", $sess_xml_default);
        $path_default = getcwd() . "/" . $sess_xml_default;
        ## END Zone Set by default.xml


        if (!empty($sess_xml)) {

            if ($CI->config->item('cryptography')) :
                $encode_data = read_file($sess_xml);
                $decode_data = $CI->cryptography->DecryptSource($encode_data);
                $objXML = simplexml_load_string($decode_data);
            else :
                $objXML = simplexml_load_file($sess_xml);
            endif;

            //$this->native_session = json_decode(json_encode((array) $objXML), 1); // change how to decode xml object to array;
            //$this->native_session = $this->xml2array($objXML);
            $xml = $this->xml2array($objXML);


            /**
             * Zone Set by default.xml
             */
            if (file_exists($path_default)) :
                if ($CI->config->item('cryptography')) :
                    $encode_data_default = read_file($sess_xml_default);
                    $decode_data_default = $CI->cryptography->DecryptSource($encode_data_default);
                    $objXML_default = simplexml_load_string($decode_data_default);
                else :
                    $objXML_default = simplexml_load_file($sess_xml_default);
                endif;

                $xml_default = $this->xml2array($objXML_default);

                $xml = my_array_replace_recursive($xml_default, $xml);

            endif;
            ## END Zone Set by default.xml


            $this->native_session = $xml['body'];

            if (array_key_exists('build_pallet', $this->native_session)) {
                $CI->config->set_item('build_pallet', $this->native_session['build_pallet']);
            }

            if (array_key_exists('uploads', $this->native_session)) {
                $CI->config->set_item('uploads', $this->native_session['uploads']);
            }

            if (array_key_exists('format_number', $this->native_session)) {
                $CI->config->set_item('format_number', $this->native_session['format_number']);
            }

            if (array_key_exists('set_debug', $this->native_session)) {
                $CI->config->set_item('set_debug', $this->native_session['set_debug']);
            }

            if (array_key_exists('debug_mode', $this->native_session)) {
                $CI->config->set_item('debug_mode', $this->native_session['debug_mode']);
            }

            if (array_key_exists('gen_dispatch_record', $this->native_session)) {
                $CI->config->set_item('gen_dispatch_record', $this->native_session['gen_dispatch_record']);
            }

            if (array_key_exists('hs_code', $this->native_session)) {
                $CI->config->set_item('hs_code', $this->native_session['hs_code']);
            }

            if (array_key_exists('logo_alt', $this->native_session)) {
                $CI->config->set_item('logo_alt', $this->native_session['logo_alt']);
            }

            if (array_key_exists('logo_patch', $this->native_session)) {
                $CI->config->set_item('logo_patch', $this->native_session['logo_patch']);
            }

            if (array_key_exists('approve_print_receive', $this->native_session)) {
                $CI->config->set_item('approve_print_receive', $this->native_session['approve_print_receive']);
            }

            if (array_key_exists('approve_print_dispatch', $this->native_session)) {
                $CI->config->set_item('approve_print_dispatch', $this->native_session['approve_print_dispatch']);
            }

            if (array_key_exists('modal_auto_hide', $this->native_session)) {
                $CI->config->set_item('modal_auto_hide', $this->native_session['modal_auto_hide']);
            }

            if (array_key_exists('language_config', $this->native_session)) {
                $CI->config->set_item('language_config', $this->native_session['language_config']);
            }

            $CI->config->set_item('_xml', $this->native_session);
        }
    }

    public function retrieve() {
        return $this->native_session->native_session;
    }

    public function get_xml_data() {
        return $this->native_session;
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

}