<script>
    $(document).ready(function() {
        var type = $('#type').val();
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();

            $("#Warehouse_Id").attr("disabled", true);
            document.getElementById("Zone_Code").readOnly = true;
            document.getElementById("Zone_NameEn").readOnly = true;
            document.getElementById("Zone_NameTh").readOnly = true;
            document.getElementById("Zone_Desc").readOnly = true;
        } else if (type === 'A') {
            $('#Warehouse_Id option[value=""]').attr('selected', 'selected');
            $('#btn_clear').show();
        } else if (type === 'E') {
            $('#btn_clear').hide();
        }

        $('#Warehouse_Id').val($('#f_Warehouse_Id').val());

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Zone_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Zone_NameEn"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        if ($('#Warehouse_Id').val() <= 0) {
            $('#Warehouse_Id').addClass('required');
        }

        $('[name="Warehouse_Id"]').change(function() {
            if ($(this).val() > 0) {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        getCategory();
    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");

        if ($('#Warehouse_Id').val() <= 0) {
            alert("Please enter the warehouse.");
            $('#Warehouse_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if ($('#Zone_Code').val() === '') {
            alert("Please enter the zone code.");
            $('#Zone_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

        if (!check_special_character($('#Zone_Code').val())) {
            alert("Zone Code must not is special Character.");
            $('#Zone_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Zone_NameEn').val() === '') {
            alert("Please enter the zone name En.");
            $('#Zone_NameEn').focus();
            $("#btn_save").removeAttr("disabled");
            return false;
        }

//        submitFrm();
        validation_in_controllers();
    }
    function validationCheckBox() {
        var checkvar = document.frmZoneMaster.elements['category_code[]'];
        var checked = false;
        for (var i = 0; i < checkvar.length; i++) {
            if (checkvar[i].checked === true) {
                checked = true;
            }
        }
        if (checked === true) {
            return true;
        } else {
            return false;
            $("#btn_save").removeAttr("disabled");
        }
    }

    function validation_in_controllers() {// Add by Ton! 20140228
        $.post('<?php echo site_url() . '/zone/validation' ?>', $("#frmZoneMaster").serialize(), function(data) {
            if (data.result === 1) {
                submitFrm();
            } else {
                if (data.note === "ZONE_CODE_ALREADY") {
                    alert("Zone Code duplicate. Please change Zone Code");
                    $('#Zone_Code').focus();
                }

                if (data.note === "ZONE_DEL") {
                    alert("Can not be inactive Zone. Zone is already in used. Do not Inactive!");
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitFrm() {// save & edit zone (call zone/saveZone)
        if (confirm("You want to save the data Zone?")) { // Add By Akkarapol, 23/01/2014, Add if(confirm()) for confirm user in action save zone. (Defect : 566)
            $.post('<?php echo site_url() . "/zone/saveZone" ?>', $('#frmZoneMaster').serialize(), function(data) {
                if (data !== 0) {
                    alert("Save successfully.");
                    window.location = "<?php echo site_url() ?>/zone";
                    return true;
                } else {
                    alert("Save unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return false;
                }
            }, "html");
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function clearData() {// define input = "".
        $('#Warehouse_Id option[value=""]').attr('selected', 'selected');
        $('#Zone_Code').val('');
        $('#Zone_NameEn').val('');
        $('#Zone_NameTh').val('');
        $('#Zone_Desc').val('');

        // Add by Ton! 20130814
        $('#check_all').attr('checked', false);
        var checkvar = document.frmZoneMaster.elements['category_code[]'];
        for (var i = 0; i < checkvar.length; i++) {
            $('input[id^=code-]').attr('checked', false);
        }

    }

    function backToList() {// back to list zone page.
        window.location = "<?php echo site_url() ?>/zone";
    }

    function getCategory() {// get Category for Display in view/category_table_form.php 
        $('#category').html('<img src="<?php echo base_url() ?>images/ajax-loader.gif" />');
        $('#frmZoneMaster').serialize()
        $.post('<?php echo site_url('/zone/getCategoryList') ?>', $('#frmZoneMaster').serialize(), function(data) {
            $('#category').html(data);
        });
    }
</script>

<HTML>
    <HEAD>
        <TITLE> Zone </TITLE>
    </HEAD>
    <BODY>
        <form class="form-horizontal" id="frmZoneMaster" name="frmZoneMaster" method='post'>    
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>" readonly/>
            <input type="hidden" id="Zone_Id" name="Zone_Id" value="<?php echo $Id ?>" readonly/>
            <input type="hidden" id="f_Warehouse_Id" name="f_Warehouse_Id" value="<?php echo $WH_Id ?>" readonly/><!-- Initial Warehouse_Id for edit. -->      
            <?php
            $extra_disabled = "";
            $extra_active = "";
            if ($mode === 'V'):
                $extra_disabled = " disabled=disabled ";
            elseif ($mode === 'A'):
                $extra_active = " checked=checked ";
            endif;
            ?>
            <TABLE>
                <TR>
                    <TD>Warehouse Code </TD>
                    <TD><?php echo form_dropdown('Warehouse_Id', $WHList, '1', 'id=Warehouse_Id') ?></TD>
                    <TD>Zone Code </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Zone_Code" NAME="Zone_Code" VALUE="<?php echo $Zone_Code ?>"></TD>
                    <TD>Active </TD>
                    <TD>
                        <?php echo form_checkbox('Active', 1, $Active, $extra_disabled . $extra_active); ?>&nbsp;&nbsp;&nbsp;Active&nbsp;&nbsp;&nbsp;
                    </TD>
                </TR>
                <TR>
                    <TD>Zone Name En </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Zone_NameEn" NAME="Zone_NameEn" VALUE="<?php echo $Zone_NameEn ?>"></TD>
                    <TD>Zone Name Th </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Zone_NameTh" NAME="Zone_NameTh" VALUE="<?php echo $Zone_NameTh ?>"></TD>
                    <TD>Zone Desc </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Zone_Desc" NAME="Zone_Desc" VALUE="<?php echo $Zone_Desc ?>"></TD>
                </TR>
                <TR>
                    <TD valign="top">Category </TD>
                    <TD colspan="5">
                        <div id="category" style="width:100%"><!--for display datatable category_table_form.php(view) @20130426-->
                            No Result.
                        </div>
                    </TD>
                </TR>
            </TABLE>
        </form>
    </BODY>
</HTML>
