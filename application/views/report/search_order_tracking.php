<!--Create By Joke 27/10/57  --> 



<link href="<?php echo base_url(); ?>css/bootstrap.min.css" rel="stylesheet">

<script>
    var column = $.parseJSON('<?php echo $show_hide; ?>');   //ADD BY POR 2014-06-25 กำหนด column ทั้งหมดที่มีในรายงานนี้
    $(document).ready(function () {
        var area_width = $('#frmReceive').width() - 20;
        $('#table-wrapper').width(area_width);

        $("#scroll_div").scroll(function () {
            $('#header_title').scrollLeft($(this).scrollLeft());
        });

        //ADD BY POR 2014-06-25 จัดการเรื่อง show hide column
        $.each(column, function (idx, val) {
            if (!val) { //ตรวจสอบ value จาก xml
                //                $("."+idx).show(); //กรณีให้แสดงค่า
                //            }else{
                $("." + idx).hide(); //กรณีไม่ให้แสดงค่า
            }
        });
        //END ADD

    });
    
    </script>
<style>
    td.group{
        background:#E6F1F6;
    }
    tr.reject_row{
        color:red;
    }


</style>
<style>
    .Tables_wrapper{
        clear: both;
        height: auto;
        position: relative;
        margin: 15px;
        border: 1px solid gainsboro;
        width: 97%;
    }
    .table_report{
        table-layout: fixed;
        //border: 1px solid gainsboro;
        margin-left: 20px;
        max-width: none;
        width: 97%;
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-top-right-radius: 0 !important;
        margin-bottom: 0 !important;
    }

    .table_report tbody {
        width: 1000px;
        overflow: auto;
    }

    .table_report th {
        padding: 3px 5px;
        background: -moz-linear-gradient(center top , #013953, #002232) repeat scroll 0 0 transparent !important;
        background: -webkit-gradient(linear, center top, center bottom, from(#A64B00), to(#FF7400)) !important;
        border-left: 1px solid #D0D0D0 !important;
        border-radius: 0 0 0 0 !important;
        color : white !important;
    }

    table.table_report tr:nth-child(odd) td{
        background-color: #E2E4FF;
    }

    table.table_report tr:nth-child(even) td{
        background-color: #FFFFFF;
    }

    table.table_report td {
        //border-right: 1px solid #D0D0D0;
        padding: 3px 5px;
    }

    table.table_report td {
        // border-right: 1px solid #D0D0D0;
        padding: 3px 5px;
    }
    .Tables_wrapper .ui-toolbar {
        padding: 5px 5px 0;
        overflow: hidden;
    }





</style>

</head>
<div style='height:100% ; margin-right: 30px'>
    <form  method="post" target="_blank" id="form_Order_tracking_report" name="form_Order_tracking_report">

        <div class="Tables_wrapper " >
            <fieldset style="margin: 5px; background-color: #ededed; padding-bottom: 10px">
                <legend>Document Result</legend>
                <table class="table_report">
                    <thead>
                        <tr>
                            <td style = "width: 110px;background-color: #ededed ">Document No. :</td>
                            <td style="background-color: #ededed"><input type = "text" disabled  value ="<?php echo $data['Document_No'] ?> "></td>
                            <td style = "width: 110px; margin: 10px ; background-color: #ededed">Refer Ext. :</td>
                            <td style="background-color: #ededed"><input type = "text" disabled   value = "<?php echo $data['Doc_Refer_Ext'] ?>"></td>
                        </tr>
                        <tr>
                            <td style = "width: 110px; margin: 10px; background-color: #ededed">Doc Refer Int. :</td>
                            <td style="background-color: #ededed"><input type = "text" disabled  value = " <?php echo $data['Doc_Refer_Int'] ?> "></td>
                            <td style = "width: 110px; margin: 10px ;background-color: #ededed">Doc Refer Inv. :</td>
                            <td style="background-color: #ededed"><input type = "text" disabled  value = " <?php echo $data['Doc_Refer_Inv'] ?> "></td>
                        </tr>
                        <tr>
                            <td style = "width: 110px;background-color: #ededed ">Doc Refer CE. :</td>
                            <td style="background-color: #ededed"><input type = "text" disabled  value ="<?php echo $data['Doc_Refer_CE'] ?>"></td>
                            <td style = "width: 110px;background-color: #ededed ">Doc Refer BL. :</td>
                            <td style="background-color: #ededed" ><input type = "text" disabled  value ="<?php echo $data['Doc_Refer_BL'] ?> "></td>
                        </tr>
                    </thead>
                </table>
            </fieldset>

            <fieldset style="margin: 5px; background-color: white; padding-bottom: 10px">
                <legend>Search Result</legend>
                <table class="table_report">
                    <thead>
                        <?php
                        if (!empty($data1)) {
                            foreach ($data1 as $key => $value1) {
                                $value_data = $value1['Process_NameEn'];
                            }
                        } else {
                            $value_data = 'No Data';
                        }
                        ?>
                        <tr>
                            <td style = "width: 80px;padding-bottom: 15px ; padding-top: 15px ;background-color: white ;font-weight:bold ;text-align: center; font-size: 16px"
                                colspan="3">Process : <?php echo $value_data ?></td>
                        </tr>
                        <tr>
                            <th style="width: 60px;padding-left: 1px; border: 1px solid lightsteelblue">Number</th>
                            <th style="padding-left: 1px; border: 1px solid lightsteelblue">Description</th>
                            <th style="padding-left: 1px; border: 1px solid lightsteelblue">Name</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if (!empty($data1)) {
                            $i = 0;
                            foreach ($data1 as $key => $value) {
//                                
                                $i++;
                                echo "<tr> ";
                                echo "<td style=\"width: 50px ;text-align: center ; padding-left: 1px; border: 1px solid lightsteelblue \">" . $i . "</td>";
                                echo "<td style=\"text-align: center ; padding-left: 1px; border: 1px solid lightsteelblue \">" . $value ['Description'] . "</td>";
                                echo "<td style=\"text-align: center ; padding-left: 1px; border: 1px solid lightsteelblue \">" . $value ['First_NameTH'] . "    " . $value ['Last_NameTH'] . "</td>";
//                                        echo "<td>" . $value['Last_NameTH'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr>';
                            echo '<td  style="color: red ; border: 1px solid gainsboro; text-align: center" colspan="3"> No Data </td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </fieldset>
            <div style="width:100% ; height:100% ; text-align:center ; margin-bottom: 10px ;">
                <input type="button" name="back" value="Back" id="back" class="button dark_blue" onclick=" window.location = '<?php echo site_url(); ?>/order_tracking/OrderTracking';"  />
            </div>
            
        </div>
    </form>
</div>









