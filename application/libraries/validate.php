<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class validate extends CI_Controller {

    /**
     * Validate Class from XML.
     *
     */
    public function __construct() {
        
    }

    public function generate_validate_script($params, $xml = NULL) {

        if ($xml == NULL) :
            log_message('info', 'The purpose of some variable is to provide some value.');
        endif;

        $objXML = simplexml_load_file($xml);

        $xml = $this->xml2array($objXML);

        $tmp = $xml['pages']['page']['_attributes'];

        if ($tmp['controller'] == $params) :
            $response = "$('#" . $tmp['formname'] . "').validate({
                //errorElement: 'div', 
                //errorClass: 'help-block help-block-required',
                focusInvalid: false,
                ignore: '',					
				rules: {
					" . $this->generate_js_script($xml['pages']['page'], 'script') . "
				},
                messages: {
					" . $this->generate_js_script($xml['pages']['page'], 'message') . "
                },
                highlight : function (element, errorClass) {
					$(element).addClass('error');
                },
                unhighlight : function (element, errorClass) {
					$(element).removeClass('error');
                },							
			});
			return $('#" . $tmp['formname'] . "').valid();		
			";
            return $response;
        else :
            return NULL;
        endif;
    }

    public function generate_js_script($xml, $type = 'script') {
        $response = "";
        foreach ($xml as $idx => $value) :
            if (isset($value['_attributes']['id'])) {
                $result = $this->generate_validator($value['validation'], $type);
                if ($result) {
                    $response .= $value['_attributes']['id'] . ": {" . $result . "},";
                }
            }
        endforeach;

        return $response;
    }

    private function generate_validator($validation, $type = NULL) {
        if ($type == NULL) {
            log_message('info', 'Object type is not defined');
            return FALSE;
        }

        $response = "";

        foreach ($validation as $idx => $val) :
            $f = "validator_" . $idx;
            $data = ($type == "script" ? $val['active'] : $val['error']);
            if (!empty($data)) :
                $response .= $idx . ":" . $this->$f($data, $type) . ",";
            endif;
        endforeach;

        return $response;
    }

    private function validator_required($flag, $type) {
        $response = "";
        if ($type == "script") {
            if (strtoupper($flag) == "TRUE") :
                $response = strtolower($flag);
            endif;
        } else if ($type == "message") {
            if ($flag != "") :
                $response = "'" . $flag . "'";
            endif;
        }
        return $response;
    }

    private function validator_minlength($flag, $type) {
        $response = "";
        if ($flag > 0) :
            switch ($type) {
                case 'dropdown' :
                    $response = "";
                    break;
                case 'text' :
                    $response = $flag;
                    break;
            }
        endif;
        return $response;
    }

    private function validator_maxlength($flag, $type) {
        $response = "";
        if ($flag > 0) :
            switch ($type) {
                case 'dropdown' :
                    $response .= "";
                    break;
                case 'text' :
                    $response .= $flag;
                    break;
            }
        endif;
        return $response;
    }

    private function validator_rangelength($flag, $type) {
        $response = "";
        if ($flag > 0) :
            switch ($type) {
                case 'text' :
                    $explode = explode(",", $flag);
                    $response .= "[" . $explode[0] . "," . $explode[1] . "]";
                    break;
            }
        endif;
        return $response;
    }

    private function validator_min($flag, $type) {
        $response = "";
        if ($flag > 0) :
            switch ($type) {
                case 'dropdown' :
                    $response = "";
                    break;
                case 'text' :
                    $response = $flag;
                    break;
            }
        endif;
        return $response;
    }

    private function validator_max($flag, $type) {
        $response = "";
        if ($flag > 0) :
            switch ($type) {
                case 'dropdown' :
                    $response = "";
                    break;

                case 'text' :
                    $response = $flag;
                    break;
            }
        endif;
        return $response;
    }

    private function validator_range($flag, $type) {
        $response = "";
        if ($flag > 0) :
            switch ($type) {
                case 'text' :
                    $explode = explode(",", $flag);
                    $response .= "[" . $explode[0] . "," . $explode[1] . "]";
                    break;
            }
        endif;
        return $response;
    }

    private function validator_email($flag, $type) {
        $response = "";
        if (strtoupper($flag) == "TRUE") :
            $response = strtolower($flag);
        endif;
        return $response;
    }

    private function validator_url($flag, $type) {
        $response = "";
        if (strtoupper($flag) == "TRUE") :
            $response = strtolower($flag);
        endif;
        return $response;
    }

    private function validator_date($flag, $type) {
        $response = "";
        if (strtoupper($flag) == "TRUE") :
            $response = strtolower($flag);
        endif;
        return $response;
    }

    private function validator_custom_date($flag, $type) {
        $response = "";
        if ($type == "script") {
            if (strtoupper($flag) == "TRUE") :
                $response = strtolower($flag);
            endif;
        } else if ($type == "message") {
            if ($flag != "") :
                $response = "'" . $flag . "'";
            endif;
        }
        return $response;
    }

    private function validator_number($flag, $type) {
        $response = "";
        if (strtoupper($flag) == "TRUE") :
            $response = strtolower($flag);
        endif;
        return $response;
    }

    private function validator_digits($flag, $type) {
        $response = "";
        if ($type == "script") {
            if (strtoupper($flag) == "TRUE") :
                $response = strtolower($flag);
            endif;
        } else if ($type == "message") {
            if ($flag != "") :
                $response = "'" . $flag . "'";
            endif;
        }
        return $response;
    }

    private function validator_include($flag, $type) {
        $response = "";
        if ($type == "script") {
            $response = "/^[" . $flag . "]+$/";
        } else if ($type == "message") {
            $response = "'" . $flag . "'";
        }
        return $response;
    }

    private function validator_exclude($flag, $type) {
        $response = "";
        if ($type == "script") {
            $response = "/^[^" . $flag . "]+$/";
        } else if ($type == "message") {
            $response = "'" . $flag . "'";
        }
        return $response;
    }

    public function xml2array($xml) {
        $arr = array();
        $flag = FALSE;
        $i = 0;
        foreach ($xml as $element) {
            $tag = $element->getName();
            $e = get_object_vars($element);

            if ($tag == "object") :
                $tag = $tag . "_" . $i;
            endif;

            if (!empty($e)) {
                $arr[$tag] = $element instanceof SimpleXMLElement ? $this->xml2array($element) : $e;
                if ($element->attributes()) {
                    foreach (get_object_vars($element->attributes()) as $attr) {
                        $arr[$tag]['_attributes'] = $attr;
                    }
                }
            } else {
                $arr[$tag] = trim($element);
                if ($element->attributes()) {
                    $arr[$tag]['_attributes'] = get_object_vars($element->attributes());
                    foreach (get_object_vars($element->attributes()) as $attr) {
                        $arr[$tag]['_attributes'] = $attr;
                    }
                }
            }
            $i++;
        }
        return $arr;
    }

}

/* End of file validate.php */
/* Location: ./application/libraries/validate.php */