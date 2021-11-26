<?php
if (empty($data)) {
    echo 'no result';
} else {
?>

<script>
        
var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';

    $(document).ready(function() {
        //#2236 แก้ไขกรณีที่ next page ทำงานไม่ถูกต้อง เนื่องจากเดิมไม่ได้ใส่ส่วนนี้ เลยทำให้แสดงผิดพลาด
        //#DATE:2013-09-05
        //#BY:POR

        var oTable = $('#tbreport').dataTable({
            "sScrollY": "100%",
            "sScrollX": "100%",
            "bAutoWidth": false,
            "bSort": false,
            "sScrollXInner": "<?php echo $s_scroll_x_inner; ?>", // Add By Akkarapol, 04/11/2013, รับค่าความกว้างของ Inner มาจาก controller
            "iDisplayLength": 100,
            "sPaginationType": "full_numbers",
            // Add By Akkarapol, 04/11/2013, เพิ่มการแสดง สี background ที่ก่อนหน้านี้เห็นใช้การกำหนดค่าเข้าไปเลยตั้งแต่ต้น พอเมื่อทำการ sort มันจะมีปัญหาว่า สี background นั้น เละเทะ มากๆ จึงเพิ่ม script นี้ขึ้นมาเพื่อกำหนดค่าสีอีกที
            "fnRowCallback": function() {
                $('tr').each(function() {
                    if ($(this).hasClass('inactive')) {
                        $(this).css('background', '#fccfcf');
                        $(this).find('.sorting_1').each(function() {
                            $(this).css('background', '#fccfcf');
                        });
                    }
                });
            },
            // END Add By Akkarapol, 04/11/2013, เพิ่มการแสดง สี background ที่ก่อนหน้านี้เห็นใช้การกำหนดค่าเข้าไปเลยตั้งแต่ต้น พอเมื่อทำการ sort มันจะมีปัญหาว่า สี background นั้น เละเทะ มากๆ จึงเพิ่ม script นี้ขึ้นมาเพื่อกำหนดค่าสีอีกที

            // Add By Akkarapol, 04/11/2013, เพิ่มส่วนของ aoColumns ที่ใช้จัดการค่าของ Column ต่างๆใน dataTable เพื่อให้ทำการ sorting ได้ และทำให้การแสดงผลนั้นสวยงามขึ้นด้วย
             "aoColumns": [
                {"sWidth": "3%","sClass": "center", "aTargets": [0]},
                {"sWidth": "10%","sClass": "center", "aTargets": [0]},
                {"sWidth": "27%", "sClass": "left_text", "aTargets": [0]},
                {"sWidth": "8%","sClass": "left_text", "aTargets": [0]},
                {"sWidth": "8%","sClass": "left_text", "aTargets": [0]},
                {"sWidth": "8%","sClass": "center", "aTargets": [0]},
                {"sWidth": "8%","sClass": "center", "aTargets": [0]},
                {"sWidth": "8%","sClass": "center td_pallet_code", "aTargets": [0]},
                {"sWidth": "20%","sClass": "right_text", "aTargets": [0]},
                <?php echo $parser_ao_column; ?>
            ]
            // END Add By Akkarapol, 04/11/2013, เพิ่มส่วนของ aoColumns ที่ใช้จัดการค่าของ Column ต่างๆใน dataTable เพื่อให้ทำการ sorting ได้ และทำให้การแสดงผลนั้นสวยงามขึ้นด้วย

        });
        new FixedColumns(oTable, {
            "iLeftColumns": 9,
            "iLeftWidth": 1000,
            "aiWidths":[35,150,215,100,100,100,100,100,100] // Add By Akkarapol, 05/11/2013, เพิ่ม "aiWidths" เพื่อการจัดการความกว้างของแต่ละ column ที่อยู่ใน FixedColumns
        });
        
        if(!built_pallet){  // check config built_pallet if it is false then hide a column Pallet Code
            $('.td_pallet_code').hide();
        }

    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportInventoryToExcel")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportInventoryPdf")
        }
        $("#form_report").submit();
    }
</script>

    <div id="container" style="width:100%; margin:0 auto;">
        <form  method="post" action="" target="_blank" id="form_report">
            <input type="hidden" name="renter_id" id="renter_id" value="<?php echo $search['renter_id']; ?>">
            <!--<input type="hidden" name="warehouse_id" id="warehouse_id" value="<? //php echo $search['warehouse_id'];   ?>">-->
            <input type="hidden" name="product_id" id="product_id" value="<?php echo $search['product_id']; ?>">
            <input type="hidden" name="as_date" id="as_date" value="<?php echo $search['as_date']; ?>">
            <input type="hidden" name="status_id" id="as_date" value="<?php echo $search['status_id']; ?>">
            <!--<input type="hidden" name="branch_id" id="as_date" value="<? //php echo $search['branch_id'];    ?>">-->
            <table id="tbreport"  cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" class="well" style="max-width: none">
                <thead>
                    <tr>
                        <!--class="border-top"-->
                        <th rowspan="2" class="border-top"><?php echo  _lang('no') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('product_code') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('product_name') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('lot') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('serial') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('product_mfd') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('product_exp') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('pallet_code') ?></th>
                        <th rowspan="2" class="border-top"><?php echo  _lang('total') ?></th> <!--ADD BY POR 2013-11-26 เพิ่ม column แสดงผลรวมของ product (balance-->


                        <?php
                        $sum_balance = array();
                        $sum_estimate = array();
                        $j = 1;
                        foreach ($range as $col) {
                            if (!array_key_exists($j, $sum_balance)) {
                                $sum_balance[$j] = 0;
                            }
                            if (!array_key_exists($j, $sum_estimate)) {
                                $sum_estimate[$j] = 0;
                            }
                            echo "<th colspan=\"2\" class=\"border-top\">$col</th>";
                            $j++;
                        }
                        ?>
                    </tr>
                    <tr bgcolor="#8B8B7A">

                        <?php
                        foreach ($range as $col) {
                            echo "<td bgcolor=\"#EEEED1\" width=150><b>BL</b></td><td bgcolor=\"#CDCDB4\" width=150><b>EST</b></td>";
                        }
                        ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 1;
                    $sum_balance_all=0;
                    $sum_estimate_all=0;
                    foreach ($data as $cols) {
                        // Comment By Akkarapol, 04/11/2013, คอมเม้นต์ทิ้งเพราะไม่ต้องมากำหนดค่าแบบตายตัวแบบนี้ใช้แล้ว เราสามารถจัดการมันด้วยการใช้ ฟังก์ชั่นของ dataTable ดีกว่า และไม่ทำให้การแสดงผลตอนเรา sort เพี้ยนด้วย
                        //        if (($i % 2) != 0) {
                        //            $bg = ' style="background:#D3D6FF;"';
                        //        } else {
                        //            $bg = ' style="background:#EAEBFF;"';
                        //        }
                        // END Comment By Akkarapol, 04/11/2013, คอมเม้นต์ทิ้งเพราะไม่ต้องมากำหนดค่าแบบตายตัวแบบนี้ใช้แล้ว เราสามารถจัดการมันด้วยการใช้ ฟังก์ชั่นของ dataTable ดีกว่า และไม่ทำให้การแสดงผลตอนเรา sort เพี้ยนด้วย
                        ?>
                                <!--<tr <?php //echo $bg;   ?>>-->

                        <tr>
                            <td style="width: 35px;"><?php echo $i; ?></td>                            
                            <td align="center" style="width: 150px;" <?php echo ($is_today)?" class='td_click' ONCLICK='showProductEstInbound(\"{$cols->Product_Code}\",\"{$cols->Product_Lot}\",\"{$cols->Product_Serial}\",\"{$cols->Product_Mfd}\",\"{$cols->Product_Exp}\")'":""?>><?php echo $cols->Product_Code; ?></td>                        
                            <td style="width: 215px; text-align: left;"><?php echo $cols->Product_NameEN; ?></td>
                            <td style="width: 100px;" align="left"><?php echo $cols->Product_Lot; ?></td>
                            <td style="width: 100px;" align="left"><?php echo $cols->Product_Serial; ?></td>
                            <td style="width: 100px;" align="center"><?php echo $cols->Product_Mfd; ?></td>
                            <td style="width: 100px;" align="center"><?php echo $cols->Product_Exp; ?></td>
                            <td style="width: 100px;" align="center"><?php echo $cols->Pallet_Code; ?></td>
                            <td style="text-align:right;"><b><?php echo set_number_format($cols->totalbal); ?></b></td>
                            <?php
                            $name = "counts";
                            $name2 = "estimate";
                            // for ($j = 1; $j <= count($range); $j++) { // Comment By Akkarapol, 04/11/2013, คอมเม้นต์ทิ้งเพราะการเขียน loop แบบนี้ ทำให้ PHP ทำงานหนักขึ้นซึ่งสิ่งที่ PHP จะทำคือ มันจะ count $range ทุกๆการวน loop ซึ่งไม่จำเป็นเลย เขียนตัวแปรรับค่าไว้ใช้ดีกว่าเยอะ!!!

                            $count_range = count($range); // Add By Akkarapol, 04/11/2013, เพิ่มตัวแปร $count_range ไว้รับค่าของ count($range); ซึ่งจะนำไปใช้ต่อใน loop for
                            for ($j = 1; $j <= $count_range; $j++) { // Add By Akkarapol, 04/11/2013, เปลี่ยนจาก $j <= count($range) ให้เป็น $j <= $count_range ที่ได้เก็บค่าไว้ซะ เพื่อลดการทำงานของ PHP 
                                $balance = 'counts_' . $j;
                                $estimate = 'estimate_' . $j;

                                $sum_balance[$j]+=$cols->{$balance};
                                $sum_estimate[$j]+=$cols->{$estimate};
                                $sum_balance_all+=$cols->{$balance}; //ADD BY POR 2013-11-12 เพิ่ม BALANCE ทุก product
                                $sum_estimate_all+=$cols->{$estimate}; //ADD BY POR 2013-11-12 เพิ่ม ESTIMATE ทุก product
                                
                                // Add By Akkarapol, 28/11/2013, เช็คว่าถ้า balance กับ est. ไม่เท่ากัน ให้เปลี่ยน text เป็นสีแดง
                                $style_text = '';
                                if(set_number_format($cols->{$balance})!=set_number_format($cols->{$estimate})):
                                    $style_text = 'color:red;';
                                endif;
                                // END Add By Akkarapol, 28/11/2013, เช็คว่าถ้า balance กับ est. ไม่เท่ากัน ให้เปลี่ยน text เป็นสีแดง
                                ?>
                                <td style="text-align:right; <?php echo $style_text;?> "><!--Add By Akkarapol, 28/11/2013, เช็คว่าถ้า balance กับ est. ไม่เท่ากัน ให้เปลี่ยน text เป็นสีแดง-->
                                    <?php
                                    if ($cols->{$balance} == 0) {
                                        $balance = "";
                                    } else {
                                        $balance = "<b>" . set_number_format($cols->{$balance}) . "</b>";
                                    }
                                    echo $balance;
                                    ?>
                                </td>
                                <td style="text-align:right; <?php echo $style_text;?> "> <!--Add By Akkarapol, 28/11/2013, เช็คว่าถ้า balance กับ est. ไม่เท่ากัน ให้เปลี่ยน text เป็นสีแดง-->
                                    <?php
                                    if ($cols->{$estimate} == 0) {
                                        $estimate = "";
                                    } else {
                                        $estimate = "<b>" . set_number_format($cols->{$estimate}) . "</b>";
                                    }
                                    echo $estimate;
                                    ?>
                                </td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>  
                </tbody>
                <tfoot>
                    <tr bgcolor="#EEEED1">
                        <td colspan="7" align="center"><b>Total Balance</b></td>
                        <td colspan="2" align="right"><b><?php echo set_number_format($sum_balance_all); ?></b></td>
                        <?php
                        foreach ($sum_balance as $sb) {
                            ?>
                            <td colspan="2"><b><?php echo set_number_format($sb); ?></b></td>
                            <?php
                        }
                        ?>
                    </tr>
                    <tr bgcolor="#CDCDB4">
                        <td colspan="7" align="center"><b>Total Estimate</b></td>
                        <td colspan="2" align="right"><b><?php echo set_number_format($sum_estimate_all); ?></b></td>
                        <?php
                        foreach ($sum_estimate as $se) {
                            ?>
                            <td colspan="2"><b><?php echo set_number_format($se); ?></b></td>
                            <?php
                        }
                        ?>
                    </tr>
                </tfoot>
            </table>
            <!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
            <div align="center" style="margin-top:10px;">
                <input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
                &emsp;&emsp;
                <input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
            </div>
            -->
        </form>
    </div>
<?php } ?>



<!--call element Product Est. balance Detail modal : add by kik : 14-11-2013-->
<?php $this->load->view('element_showEstBal_inventory'); ?>