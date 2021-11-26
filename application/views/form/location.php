<?php // Create by Ton! 20130425  ?>
<SCRIPT>
    $(document).ready(function() {
        var type = $('#type').val();
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();
        }
    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        if ($('#Warehouse_Id').val() <= 0) {
            alert("Please enter the warehouse.");
            $('#Warehouse_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if ($('#Zone_Id').val() <= 0) {
            alert("Please enter the zone.");
            $('#Zone_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
//        if ($('#Category_Id').val()<=0) {
//            alert("Please enter the category.");
//            $('#Category_Id').focus();
//            return false;   
//        }
        if ($('#Storage_Id').val() <= 0) {
            alert("Please enter the storage.");
            $('#Storage_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if ($('#Storage_Detail_Id').val() <= 0) {
            alert("Please enter the storage code.");
            $('#Storage_Detail_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }
        if ($('#Location_Code').val() == '') {
            alert("Please enter the location code.");
            $('#Location_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        submitFrm();
    }

    function submitFrm() {// save & edit location (call location/saveLocation)
        $("#btn_save").attr("disabled", "disabled");
        $.post('<?php echo site_url() . "/location/saveLocation" ?>', $('#frmLocation').serialize(), function(data) {
            if (data == '1') {
                if (confirm("Save successfully.")) {
//                    window.location ="<?php //echo site_url() ?>/location";
                }
                return true;
            } else if (data == '2') {
                alert("Location already !!");
                $("#btn_save").removeAttr("disabled");
                return false;
            } else if (data == '0') {
                alert("Save unsuccessfully.");
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }, "html");
    }

    function clearData() {// define input = "".
        $('#Warehouse_Id option[value=""]').attr('selected', 'selected');
        $('#Zone_Id option[value=""]').attr('selected', 'selected');
        $('#Category_Id option[value=""]').attr('selected', 'selected');
        $('#Storage_Id option[value=""]').attr('selected', 'selected');
        $('#Storage_Detail_Id option[value=""]').attr('selected', 'selected');
        $('#Location_Code').val('');
    }

    function backToList() {// back to list location page.
        window.location = "<?php echo site_url() ?>/location";
    }

    function setZone() {
        $.post('<?php echo site_url() . "/location/get_zone_by_warehouse_ID" ?>', $('#frmLocation').serialize(), function(html) {
            $('#Zone_Id').html(html);
        }, 'html');
        setStorage();
    }

    function setCategory() {
        $.post('<?php echo site_url() . "/location/getCategoryByZoneID" ?>', $('#frmLocation').serialize(), function(html) {
            $('#Category_Id').html(html);
        }, 'html');
        setStorage();
    }

    function setStorage() {
        $.post('<?php echo site_url() . "/location/getStorageByWarehouseID" ?>', $('#frmLocation').serialize(), function(html) {
            $('#Storage_Id').html(html);
        }, 'html');
        setStorageDetail();
        $('#Location_Code').val('');
    }

    function setStorageDetail() {
        $.post('<?php echo site_url() . "/location/getStorageDetailByStorageID" ?>', $('#frmLocation').serialize(), function(html) {
            $('#Storage_Detail_Id').html(html);
        }, 'html');
    }
</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Location </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmLocation" NAME="frmLocation" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Location_Id" name="Location_Id" value="<?php echo $Id ?>"/>
            <TABLE>
                <TR>
                    <TD>Warehouse </TD>
                    <TD><?php echo $WHList; ?></TD>
                </TR>
                <TR>
                    <TD>Zone </TD>
                    <TD><?php echo $ZONEList; ?></TD>
                </TR>
                <TR>
                    <TD>Category </TD>
                    <TD><?php echo $CateList; ?></TD>
                </TR>
                <TR>
                    <TD>Storage </TD>
                    <TD><?php echo $STORList; ?></TD>
                </TR>
                <TR>
                    <TD>Storage Code </TD>
                    <TD><?php echo $StorDetailList; ?></TD>
                </TR>
                <TR>
                    <TD>Location Code </TD>
                    <TD><INPUT TYPE="text" ID="Location_Code" NAME="Location_Code" VALUE="<?php echo $Location_Code ?>" disabled="disabled"></TD>
                </TR>   
            </TABLE>
        </FORM>
    </BODY>
</HTML>
