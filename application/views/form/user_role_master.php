<SCRIPT>
    $(document).ready(function () {
        $('#defDataTableUserRole').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bPaginate": false,
            "bFilter": true,
            "bInfo": false,
            "bRetrieve": true,
            "bDestroy": true,
            "bAutoWidth": false,
            "sScrollY": "300px",
            "bScrollCollapse": true,
            "sPaginationType": "full_numbers"
        });

        $('#check_all').click(function () {
            var oTable = $('#defDataTableUserRole').dataTable();
            var checked = ($(this).is(':checked') === true ? 'checked' : false);
            $('input', oTable.fnGetNodes()).each(function () {
                $('input', oTable.fnGetNodes()).attr('checked', checked);
            });

            count_member_checked();
        });

        $('.chk_member').live('change', function () {
            count_member_checked();
        });

<?php if ($mode == 'A'): ?>
            clearData();
            $('#btn_clear').show();
<?php elseif ($mode == 'E'): ?>
            $('#btn_save').show();
            $('#btn_clear').hide();
<?php else: ?>
            $(':text').each(function () {
                $(this).attr('readonly', true);
            });
            $('textarea#UserRole_Desc').attr('readonly', true);
            $('#Active').prop("checked", true);

            $(":checkbox").each(function () {
                $(this).attr("disabled", "disabled");
            });
            $('#btn_clear').hide();
<?php endif; ?>

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="UserRole_Code"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="UserRole_NameEN"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function count_member_checked() {
        var oTable = $('#defDataTableUserRole').dataTable();
        var countMembers = 0;
        $("input:checked", oTable.fnGetNodes()).each(function () {
            if ($(this).is(':checked')) {
                ++countMembers;
            }
        });

        $('#members').html(countMembers);
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var UserRoleCode = $('#UserRole_Code').val().trim();
        var UserRoleNameEN = $('#UserRole_NameEN').val().trim();

        if (UserRoleCode.length <= 0) {
            alert("Please input UserRole Code.");
            $('#UserRole_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(UserRoleCode)) {
            alert("UserRole Code must not is special Character.");
            $('#UserRole_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (UserRoleNameEN.length <= 0) {
            alert("Please input UserRole Code Name En.");
            $('#UserRole_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(UserRoleNameEN)) {
            alert("UserRole Name EN must not is special Character.");
            $('#UserRole_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {
        $.post('<?php echo site_url('/user_role/validation') ?>', $('#frmUserRole').serialize(), function (data) {
            if (data.result === 1) {
                submitUserRole();
            } else {
                if (data.note === 'ROLE_CODE_ALREADY') {
                    alert('User Role Code duplicate. Please change User Role Code');
                    $('#UserGroup_Code').focus();
                }

                if (data.note === 'ROLE_DEL') {
                    alert('Can not be inactive User Role. User Role is already in used. Do not Inactive!');
                    $('#Active').prop('checked', true);
                }

                $('#btn_save').removeAttr("disabled");
                return;
            }
        }, "JSON");
    }

    function submitUserRole() {
        var data_form = $("#frmUserRole").serialize();
        var oTable = $('#defDataTableUserRole').dataTable();
        var input_checked = [];
        $("input:checked", oTable.fnGetNodes()).each(function () {
            input_checked.push($(this).val());
        });

        if (confirm('You want to save the data User Role?')) {
            $.post('<?php echo site_url('/user_role/save_user_role') ?>', data_form + '&checked=' + input_checked, function (dataSave) {
                if (dataSave === "1") {
                    alert("Save User Role Master successfully.");
                    window.location = '<?php echo site_url('/user_role') ?>';
                } else {
                    alert("Save User Role Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return false;
                }
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function clearData() {
        $(':text').each(function () {
            $(this).val('');
        });

        $('textarea#UserRole_Desc').val('');

        $(':checkbox').each(function () {
            $(this).prop('checked', false);
        });

        count_member_checked();

        $('#Active').prop('checked', true);

        $('#UserRole_Code').addClass('required');
        $('#UserRole_NameEN').addClass('required');
        $('#UserRole_Code').focus();
    }

    function click_selected(id) {
        if (!$("#" + id).is(':checked')) {
            $("#" + id).prop("checked", true);
        }else{
            $("#" + id).prop("checked", false);
        }
        
        count_member_checked();
    }

    function backToList() {// back to list of user group page.
        window.location = '<?php echo site_url('/user_role') ?>';
    }
</SCRIPT>
<STYLE tyle="css/text">
    .w120{ width: 120px;  padding: 5px; }
    .w150{ width: 150px;  padding: 5px; }
    .txt-r{ text-align: right; }
    .txt-l{ text-align: left; }
    .txt-c{ text-align: center; }
</style>
<HTML>
    <HEAD>
        <TITLE> User Role </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmUserRole" NAME="frmUserRole" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="UserRole_Id" name="UserRole_Id" value="<?php echo $UserRole_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $UserRole_Code ?>"/>
            <TABLE width='95%' align='center'>
                <TR><TD>
                        <FIELDSET class="well" ><LEGEND>Role</LEGEND>
                            <TABLE>
                                <TR>
                                    <TD class="w150 txt-r">UserRole Code : </TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="UserRole_Code" NAME="UserRole_Code" VALUE="<?php echo $UserRole_Code ?>"></TD>
                                    <TD>
                                        <LABEL for="Active">
                                            <input type="checkbox" name="Active" id="Active" <?php echo (ISSET($Active) ? (($Active == 'Y' || $Active == '1') ? 'checked' : '') : '') ?>>
                                            &nbsp;Active&nbsp;&nbsp;                                            
                                        </LABEL>
                                    </TD>
                                    <TD class="w150 txt-l">Member in group : <LABEL style="display: inline-block;"><B id="members"><?php echo (ISSET($Members) ? $Members : '0') ?></B></LABEL></TD>
                                </TR>
                                <TR>
                                    <TD class="w150 txt-r">UserRole Name EN :</TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_Eng-f string_special_characters-f" ID="UserRole_NameEN" NAME="UserRole_NameEN" VALUE="<?php echo $UserRole_NameEN ?>"></TD>                    
                                    <TD class="w150 txt-r">UserRole Name TH :</TD>
                                    <TD><INPUT TYPE="text" ID="UserRole_NameTH" class="string_Thai-f string_special_characters-f" NAME="UserRole_NameTH" VALUE="<?php echo $UserRole_NameTH ?>"></TD>
                                </TR>
                                <TR>
                                    <TD class="w150 txt-r">UserRole Desc :</TD>
                                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="UserRole_Desc" NAME="UserRole_Desc" style="resize:none; width:98%;" rows="2"><?php echo $UserRole_Desc ?></TEXTAREA></TD>
                            </TR>
                        </TABLE>
                        </FIELDSET> 
                    </TD></TR>
                
                <TR><TD>
                        <FIELDSET class="well" ><LEGEND>Members Role</LEGEND>
                            <table id="defDataTableUserRole" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                                <thead>
                                    <tr>
                                        <th>Select All <input type="checkbox" name="check_all" id="check_all"></th>
                                        <th>User Account</th>
                                        <th>Name</th>
                                        <th>Company Name</th>
                                    </tr 
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    if (count($memberRoleList) > 0) :
                                        foreach ($memberRoleList as $value) :
                                            ?>
                                                    <tr>
                                                        <td><input type="checkbox" class="chk_member" name="Members_Role_ID[]" id="code-<?php echo $i; ?>" value="<?php echo $value->UserLogin_Id; ?>"<?php if (in_array($value->UserLogin_Id, $memberRoleIDList)) echo 'checked' ?>/></td>
                                                        <td onclick="click_selected('code-<?php echo $i; ?>')"><?php echo $value->UserAccount; ?></td>
                                                        <td onclick="click_selected('code-<?php echo $i; ?>')"><?php echo $value->Contact_Name; ?></td>
                                                        <td onclick="click_selected('code-<?php echo $i; ?>')"><?php echo $value->Company_Name; ?></td>
                                                    </tr>
                                            <?php
                                            ++$i;
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



