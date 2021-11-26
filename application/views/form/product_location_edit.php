<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    //$('#myModal').modal('toggle');
    $(document).ready(function() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": {
                "sLoadingRecords": "Please wait - loading..."
                , "sProcessing": "<img src=\"<?php echo base_url() ?>images/ajax-loader.gif\" />"
            },
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        });

    });

    function confirmDelete(id, location_id) {
        if (confirm("Confirm delete this data?") == true) {
            //redirect("<?php echo site_url(); ?>/putaway/deleteProductToLocation?id="+id+"&location_id="+location_id);
            $.post("<?php echo site_url(); ?>/putaway/deleteProductToLocation", {id: id, location_id: location_id}, function(data) {
                switch (data.status) {
                    case 'C000':
                        message = "Delete Product Complete";
                        alert(message);
                        url = "<?php echo site_url(); ?>/putaway/editProductToLocation?id=" + location_id;
                        redirect(url)
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

    function scancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/putaway";
            redirect(url);
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
        <div align="right" style="padding:10px;">
            <INPUT TYPE='button' class='button dark_blue' VALUE='<?php echo BACK; ?>' ONCLICK="scancel();"> 
            <INPUT TYPE='button' class='button dark_blue' VALUE='Add Product to Location Code : <?php echo $data['location_code']; ?>'	 ONCLICK="openForm('user', 'putaway/addProductByLocation?id=<?php echo $data['location_id']; ?>', 'A', '')">
        </div>
        <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
            <thead>
                <?php
                $show_column = $data['show_column'];
                $str_header = "";
                foreach ($show_column as $column) {
                    $str_header .= "<td>" . $column . "</td>";
                }
                ?>
                <tr><?php echo $str_header; ?></tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach ($data['plist'] as $row) {
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo $row->Product_Code; ?></td>
                        <td><?php echo $row->Product_status; ?></td>
                        <td><?php echo $row->Product_NameEN; ?></td>
                        <td><?php echo $row->Balance_Qty; ?></td>						
                        <td><a ONCLICK="confirmDelete(<?php echo $row->Id; ?>,<?php echo $data['location_id']; ?>)" ><?php echo img("css/images/icons/del.png"); ?></a></td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </tbody>
        </table>
    </TD>
</TR>