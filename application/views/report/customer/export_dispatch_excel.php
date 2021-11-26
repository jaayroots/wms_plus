<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="' . $file_name . '.xls"');
header("Pragma: no-cache");
header("Expires: 0");
set_time_limit(5000);
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
    <HTML>
        <HEAD>
            <meta http-equiv="Content-type" content="text/html;charset=tis-620" />
        </HEAD>
        <BODY>
            <TABLE  x:str BORDER="1">
                <tr>
                    <?php
                    if (count($header) > 0) {
                        foreach ($header as $head) {
                            echo "<td><b><font face=\"Tahoma\" size=2>" . $head . "</font></b></td>";
                        }
                        if (count($body) > 0) {
                            foreach ($body as $aColumns) {

                                $dimension = (!empty($aColumns->w) && !empty($aColumns->h) && !empty($aColumns->l) ? $aColumns->w . " x " . $aColumns->l . " x " . $aColumns->h : "");

                                echo '<tr>'
                                . '<td style="mso-number-format:\'Short Date\';"><font face=\"Tahoma\" size=2>' . $aColumns->Receive_Date . '</font></td>'
                                . '<td style="mso-number-format:\'Short Date\';"><font face=\"Tahoma\" size=2>' . $aColumns->Dispatch_Date . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $this->conv->tis620_to_utf8($aColumns->Product_Name) . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $this->conv->tis620_to_utf8($aColumns->Doc_Refer_Ext) . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $this->conv->tis620_to_utf8($aColumns->Product_Lot) . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $this->conv->tis620_to_utf8($aColumns->Product_Serial) . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $aColumns->TypeLicense . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $aColumns->Invoice_No . '</font></td>'
                                . '<td align="right"><font face=\"Tahoma\" size=2>' . set_number_format($aColumns->Dispatch_Qty) . '</font></td>'
                                . '<td align="right"><font face=\"Tahoma\" size=2>' . $dimension . '</font></td>'
                                . '<td align="right"><font face=\"Tahoma\" size=2>' . set_number_format($aColumns->cbm) . '</font></td>'
                                . '<td align="right"><font face=\"Tahoma\" size=2>' . set_number_format($aColumns->Dispatch_Qty * $aColumns->cbm) . '</font></td>'
                                . '<td><font face=\"Tahoma\" size=2>' . $this->conv->tis620_to_utf8($aColumns->Unit_Value) . '</font></td>'
                                . '</tr>';
                            }
                        }
                    }
                    ?>
                </tr>