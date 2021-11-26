<!--Original By POR 2013-11-14-->

<!--Create By Joke 13/10/57  --> 

<head>


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
            width: 100%;
        }
        .table_report{
            table-layout: fixed;
            margin-left: 0;
            max-width: none;
            width: 100%;
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
            border-right: 1px solid #D0D0D0;
            padding: 3px 5px;
        }

        table.table_report td {
            border-right: 1px solid #D0D0D0;
            padding: 3px 5px;
        }
        .Tables_wrapper .ui-toolbar {
            padding: 5px 5px 0;
            overflow: hidden;
        }
        .inline{
            display: inline-table;

            margin-left: 30px;
            margin-top: 5px;

        }
        .inline1{
            margin-left: 20px;
            width: 200px;

        }



    </style>

</head> 
<div style='height:100% ; margin-right: 30px'>
    <form  method="post" target="_blank" id="form_Order_tracking_report" name="form_Order_tracking_report">
        <div class="Tables_wrapper ">
            <table class="table_report">
                <thead>
                    <tr>
                        <th style="width: 30px">No.</th>
                        <th>Document No.</th>
                        <!--<th>Doc Relocate.</th>-->
                        <th>Doc Refer Ext.</th>
                        <th>Doc Refer Int.</th>
                        <th>Doc Refer Inv.</th>
                        <th>Doc Refer CE.</th>
                        <th>Doc Refer BL.</th>
                        <th>Process_Name</th>
                        <th>State_Name</th>
                        <th style="width: 60px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                        if (!empty($data)) {
                            $i = 0;
                            foreach ($data as $key => $value) {
                                //p( $value);exit();
                                $i++;

                                echo '<tr style="border: 1px">';
                                echo "<td>" . $i . "</td>";
                                echo "<td>" . $value->Document_No . "</td>";
//                             echo "<td>" . $value->Doc_Relocate . "</td>";
                                echo "<td>" . $value->Doc_Refer_Ext . "</td>";
                                echo "<td>" . $value->Doc_Refer_Int . "</td>";
                                echo "<td>" . $value->Doc_Refer_Inv . "</td>";
                                echo "<td>" . $value->Doc_Refer_CE . "</td>";
                                echo "<td>" . $value->Doc_Refer_BL . "</td>";
                                echo "<td>" . $value->Process_NameEn . "</td>";
                                echo "<td>" . $value->State_NameEn . "</td>";
                                echo "<td>" . "<a href='get_OrderTracking_detail?flow_id=" . $value->Flow_Id . "&type=" . $value->type . "&order_id=" . $value->Order_Id . "'><img src='".base_url()."css/images/icons/view.png'></a> " . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr>';
                            echo '<td  style="color: red ; border: 1px solid gainsboro; text-align: center" colspan="10"> No Data </td>';
                            echo '</tr>';
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>









