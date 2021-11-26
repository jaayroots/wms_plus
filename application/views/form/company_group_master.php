<SCRIPT>
    $(document).ready(function() {

        $('#defDataTableCompany').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "sPaginationType": "full_numbers"
        });

        $('#check_all').click(function() {
            var oTable = $('#defDataTableCompany').dataTable();
            var checked = ($(this).is(':checked') == true ? 'checked' : false);
            $('input', oTable.fnGetNodes()).each(function() {
                $('input', oTable.fnGetNodes()).attr('checked', checked);
            });
            //return false; // to avoid refreshing the page
        });

<?php if ($Active == true): ?>
            $("#Active").prop("checked", true);
<?php else: ?>
            $("#Active").prop("checked", false);
<?php endif; ?>

<?php if ($mode == 'E'): ?>
            $('#btn_save').show();
            $('#btn_clear').hide();
<?php elseif ($mode == 'A'): ?>
            $('#btn_clear').show();
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("CompanyGroup_Code").readOnly = true;
            document.getElementById("CompanyGroup_NameEN").readOnly = true;
            document.getElementById("CompanyGroup_NameTH").readOnly = true;
            document.getElementById("CompanyGroup_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="CompanyGroup_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="CompanyGroup_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var CompanyGroupCode = $('#CompanyGroup_Code').val();
        var CompanyGroupNameEN = $('#CompanyGroup_NameEN').val();

        if (CompanyGroupCode == "") {
            alert("Please input CompanyGroup Code.");
            $('#CompanyGroup_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(CompanyGroupCode)) {
            alert("CompanyGroup Code must not is special Character.");
            $('#CompanyGroup_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (CompanyGroupNameEN == "") {
            alert("Please input CompanyGroup Name En.");
            $('#CompanyGroup_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

//        check_CompanyGroup_Code();
        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/company_group/validation' ?>', $("#frmCompanyGroup").serialize(), function(data) {
            if (data.result === 1) {
                submitCompanyGroup();
            } else {
                if (data.note === "COM_G_CODE_ALREADY") {
                    alert("Company Group Code already exists.");
                    $('#CompanyGroup_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitCompanyGroup() {
        $("#btn_save").attr("disabled", "disabled");
        var data_form = $("#frmCompanyGroup").serialize();
        var oTable = $('#defDataTableCompany').dataTable();
        var input_checked = [];
        $("input:checked", oTable.fnGetNodes()).each(function() {
            input_checked.push($(this).val());
        });
        if (confirm("You want to save the data Company Group?")) {
            $.post('<?php echo site_url() . "/company_group/save_CompanyGroup" ?>', data_form + '&checked=' + input_checked, function(dataSave) {
                if (dataSave == '1') {
                    alert("Save Company Group Master successfully.");
                    window.location = "<?php echo site_url() ?>/company_group";
                } else {
                    alert("Save Company Group Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return false;
                }
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }
    
    function clearData() {
        $('#CompanyGroup_Code').val('');
        $('#CompanyGroup_Code').addClass('required');
        $('#CompanyGroup_NameEN').val('');
        $('#CompanyGroup_NameEN').addClass('required');
        $('#CompanyGroup_NameTH').val('');
        $('#CompanyGroup_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of company group page.
        window.location = "<?php echo site_url() ?>/company_group";
    }

</SCRIPT>

<HTML>
    <HEAD>
        <TITLE> Company Group </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmCompanyGroup" NAME="frmCompanyGroup" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="CompanyGroup_Id" name="CompanyGroup_Id" value="<?php echo $CompanyGroup_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $CompanyGroup_Code ?>"/>
            <TABLE width='95%' align='center'>
                <TR><TD>
                        <FIELDSET class="well" ><LEGEND>Group</LEGEND>
                            <TABLE>
                                <TR>
                                    <TD>CompanyGroup Code : </TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="CompanyGroup_Code" NAME="CompanyGroup_Code" VALUE="<?php echo $CompanyGroup_Code ?>"></TD>
                                    <TD colspan="2">
                                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                                    </TD>     
                                </TR>
                                <TR>
                                    <TD>CompanyGroup Name EN : </TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="CompanyGroup_NameEN" NAME="CompanyGroup_NameEN" VALUE="<?php echo $CompanyGroup_NameEN ?>"></TD>                    
                                    <TD>CompanyGroup Name TH : </TD>
                                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="CompanyGroup_NameTH" NAME="CompanyGroup_NameTH" VALUE="<?php echo $CompanyGroup_NameTH ?>"></TD>                    
                                </TR>
                                <TR>
                                    <TD>CompanyGroup Desc:</TD>
                                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="CompanyGroup_Desc" NAME="CompanyGroup_Desc" style="resize:none; width:98%;" rows="2"><?php echo $CompanyGroup_Desc ?></TEXTAREA></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>
                <TR><TD>
                        <FIELDSET class="well" ><LEGEND>Members Group</LEGEND>
                            <table id="defDataTableCompany" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                                <thead>
                                    <tr>
                                        <th>Select All <input type="checkbox" name="check_all" id="check_all"></th>
                                        <th>Company Code</th>
                                        <th>Company Name</th>
                                        <th>Business Type Name</th>
                                    </tr>
                                </thead>
                                <tbody>                                            
                                    <?php
                                    $i = 0;
                                    if (count($memberGroupList) > 0):
                                        foreach ($memberGroupList as $value) :
                                            ?>
                                                            <tr>
                                                                <td><input type="checkbox" class="check_box" name="Members_Group_ID[]" id="code-<?php echo $i; ?>" value="<?php echo $value->Company_Id; ?>"<?php if (in_array($value->Company_Id, $memberGroupIDList)) echo 'checked' ?>/></td>
                                                                <td><?php echo $value->Company_Code; ?></td>
                                                                <td><?php echo $value->Company_NameEN; ?></td>
                                                                <td><?php echo $value->BusinessType_NameEN; ?></td>
                                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </FIELDSET>
                    </TD></TR>
            </TABLE>
        </FORM>
    </BODY>
</HTML>