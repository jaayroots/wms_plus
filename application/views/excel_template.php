<?php
error_reporting(E_ALL);
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="' . $file_name . '.xls"'); # �������
header("Pragma: no-cache");
header("Expires: 0");
set_time_limit(5000);
function checkIsAValidDate($myDateString){

   $a = substr_count($myDateString, '/');
//    p($a);
  if(strlen($myDateString) == 10 && $a == 2 ){
    $myDateString = str_replace('/', '-', $myDateString);
    
    // p($myDateString);
    return (bool)strtotime($myDateString);
   
  }else{
      return false;
  }
    
}
?>
<HTML xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
    <!--<HTML>-->
    <HEAD>
        <meta http-equiv="Content-type" content="text/html;charset=tis-620" />
    </HEAD>
<body>
<table x:str BORDER="1">
            <?php
            if (!empty($header)) {
                echo '<tr>';
                foreach ($header as $head) {
                    echo "<td><b>" . $head . "</b></td>";
                }
                echo '</tr>';
                //$excel_date = (checkIsAValidDate(" ") ? ' style=\'mso-number-format:"Short Date";\' ' : ''  );
                // p($excel_date);
                if (!empty($body)) {
                    
                    foreach ($body as $aColumns) {
                        // p($aColumns);
                        echo "<tr>";
                        foreach ($aColumns as $value) {
                            // p($value);
                            if (is_array($value)):
                                
                                $align = (isset($value['align']) ? 'text-align: ' . $value['align'] .';'  : '');
                                $excel_num = (is_numeric(str_replace(",","",$value['value'])) ? 'x:num="'.$value['value'].'"' : '');
				                $excel_date = (checkIsAValidDate($value['value']) ? ' mso-number-format:"Short Date"; ' : ''  );
                                
                                echo "<td " . $align . " ".$excel_num." style=\"" . $align . $excel_date . "\"  >" . $value['value'] . "</td>";
                            else:
                                
                                $excel_num = (is_numeric(str_replace(",","",$value)) ? 'x:num="'.$value.'"' : '');
				                $excel_date = (checkIsAValidDate($value) ? ' style=\'mso-number-format:"Short Date";\' ' : ''  );
                                
                                
                                echo "<td ".$excel_num." $excel_date >" . $value . "</td>";
                            endif;
                        }
                        
                        echo "</tr>";
                    }
                }
            }
            ?></table>
</body>
</html>

