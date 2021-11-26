<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    //$('#myModal').modal('toggle');
    var response_data = '{response}';
    $(document).ready(function() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "bRetrieve": true,
            "bDestroy": true,
            /*--COMMENT BY POR 2013-11-26 เนื่องจากไม่ได้ใช้
             "aoColumn": [
             { "sClass": "right", "aTargets": [0] },
             { "sClass": "left", "aTargets": [1] },
             { "sClass": "right", "aTargets": [2] },
             { "sClass": "left", "aTargets": [3] },
             ],
             */
            "oLanguage": {
                "sLoadingRecords": "Please wait - loading..."
                , "sProcessing": "<img src=\"<?php echo base_url() ?>images/ajax-loader.gif\" />"
            },
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        });

        if (response_data == "failed") {
            alert('Warning, Putaway Rule in used');
        } else if (response_data == "success") {
            alert('PROCESS SUCCESS');
        }
    });

    function confirmDelete(id, location_id) {
        if (confirm("Confirm delete this data?") == true) {
            //redirect("<?php echo site_url(); ?>/putaway/deleteProductToLocation?id="+id+"&location_id="+location_id);
            $.post("<?php echo site_url(); ?>/putaway/deleteProductToLocation", {id: id, location_id: location_id}, function(data) {
                switch (data.status) {
                    case 'C000':
                        message = "Delete Product Complete";
                        alert(message);
                        url = "<?php echo site_url(); ?>/putaway";
                        redirect(url);
                        break;
                    case 'C001':
                        message = "Not Delete This Product";
                        alert(message);
                        break;
                        //case 'C003':  message = "Approve Pre-Receive Complete";  break;
                        //case 'E001':  message = "Save Pre-Receive Incomplete";	 break;
                }

            }, "json");
        }
    }
</script>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
    #report{
        margin:5px;
        text-align:center;
    }

</style>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <!--Comment By Akkarapol, 14/11/2013, คอมเม้นต์ทิ้งเพราะ ตำแหน่งที่ใส่ไว้ไม่ถูกต้อง มันต้องไป parse ค่าจาก Controller เพื่อให้ template เป็นตัวแสดง แทนที่จะเป็น ไฟล์นี้ ซึ่งเป็นแค่ไฟล์ที่ถูก include เข้าไปเท่านั้น-->
        <!--<div align="right" style="padding:10px;"><INPUT TYPE='button' class='button dark_blue' VALUE='<?php echo ADD; ?>' ONCLICK="openForm('user','putaway/freeLocation','A','')"></div>-->
        <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
            <thead>
                <?php
                $show_column = $data['show_column'];
                $str_header = "";
                foreach ($show_column as $column) :
                    $str_header .= "<th>" . $column . "</th>";
                endforeach;
                ?>

                <?php
                $action_action = FALSE;
                $action_edit = FALSE;
                $action_delete = FALSE;
                foreach ($data['action'] as $row) :
                    if ($row === "Action"):
                        $action_action = TRUE;
                    endif;
                    if ($row === "Edit"):
                        $action_edit = TRUE;
                    endif;
                    if ($row === "Delete"):
                        $action_delete = TRUE;
                    endif;
                endforeach;
                ?>

                <tr><?php echo $str_header; ?></tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach ($data['plist'] as $row) :
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
        <!--                        <td><?php //echo $row->warehouse;                       ?></td> ADD BY POR 2013-11-26 เพิ่ม column 
                        <td><?php //echo $row->Zone_Name;                       ?></td>
                        <td style="text-align: left;"><?php //echo $row->PutAway_Name;                      ?></td> ADD BY POR 2013-11-26 เพิ่ม column 
                        <td><?php //echo $row->Status;                       ?></td>
                        <td><?php //echo $row->Sub_Status;                       ?></td>
                        <td><?php //echo $row->cate_name;                       ?></td>-->

                        <!--Edit by Ton! 20140129-->
                        <td style="text-align: left;"><?php echo $row->PutAway_Name; ?></td>
                        <td><?php echo $row->Product_Status; ?></td>
                        <td><?php echo $row->Product_Sub_Status; ?></td>
                        <td><?php echo $row->Product_Category; ?></td>
                        <!--END Edit by Ton! 20140129-->

                        <td style="text-align: left;"><?php echo $row->Remarks; ?></td>
<!--                        <td style="text-align: left;"><?php echo $row->Active; ?></td>-->
                        <?php if ($action_edit === TRUE): ?>
                            <td><a ONCLICK="openForm('edit_putaway', 'putaway/freeLocation?id=<?php echo $row->Id; ?>', 'E', '')" ><?php echo img("css/images/icons/edit.png"); ?></a></td>    
                        <?php endif; ?>
                        <?php if ($action_delete === TRUE): ?>
                            <td><a ONCLICK="openForm('delete_putaway', 'putaway/flagInactiveLocation?id=<?php echo $row->Id; ?>', 'D', '')" ><?php echo img("css/images/icons/del.png"); ?></a></td>    
                        <?php endif; ?>
                    </tr>
                    <?php
                    $i++;
                endforeach;
                ?>
            </tbody>
        </table>
    </TD>
</TR>