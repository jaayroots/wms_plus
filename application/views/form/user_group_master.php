<SCRIPT>
    $(document).ready(function () {
        $('#defDataTableUserGroup').dataTable({
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
            var oTable = $('#defDataTableUserGroup').dataTable();
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
            $('textarea#UserGroup_Desc').attr('readonly', true);
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

        $('[name="UserGroup_Code"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="UserGroup_NameEN"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function count_member_checked() {
        var oTable = $('#defDataTableUserGroup').dataTable();
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
        var UserGroupCode = $('#UserGroup_Code').val().trim();
        var UserGroupNameEN = $('#UserGroup_NameEN').val().trim();

        if (UserGroupCode.length <= 0) {
            alert("Please input UserGroup Code.");
            $('#UserGroup_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!check_special_character(UserGroupCode)) {
            alert("UserGroup Code must not is special Character.");
            $('#UserGroup_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (UserGroupNameEN.length <= 0) {
            alert("Please input UserGroup Code Name En.");
            $('#UserGroup_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!check_special_character(UserGroupNameEN)) {
            alert("UserGroup Name EN must not is special Character.");
            $('#UserGroup_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140228
        $.post('<?php echo site_url('/user_group/validation') ?>', $('#frmUserGroup').serialize(), function (data) {
            if (data.result === 1) {
                submitUserGroup();
            } else {
                if (data.note === 'GROUP_CODE_ALREADY') {
                    alert("User Group Code duplicate. Please change User Group Code");
                    $('#UserGroup_Code').focus();
                }

                if (data.note === 'GROUP_DEL') {
                    alert('Can not be inactive User Group. User Group is already in used. Do not Inactive!');
                    $('#Active').prop("checked", true);
                }

                $('#btn_save').removeAttr('disabled');
                return;
            }
        }, 'JSON');
    }

    function submitUserGroup() {
        var data_form = $("#frmUserGroup").serialize();
        var oTable = $('#defDataTableUserGroup').dataTable();
        var input_checked = [];
        $("input:checked", oTable.fnGetNodes()).each(function () {
            input_checked.push($(this).val());
        });

        if (confirm("You want to save the data User Group?")) {
            $.post('<?php echo site_url('/user_group/save_user_group') ?>', data_form + '&checked=' + input_checked, function (dataSave) {
                if (dataSave === '1') {
                    alert('Save User Group Master successfully.');
                    window.location = '<?php echo site_url('/user_group') ?>';
                } else {
                    alert('Save User Group Master unsuccessfully.');
                    $('#btn_save').removeAttr('disabled');
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
        
        $('textarea#UserGroup_Desc').val('');
        
        $(':checkbox').each(function () {
            $(this).prop('checked', false);
        });    
        
        count_member_checked();
        
        $('#Active').prop('checked', true);

        $('#UserGroup_Code').addClass('required');
        $('#UserGroup_NameEN').addClass('required');
        $('#UserGroup_Code').focus();
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
        window.location = '<?php echo site_url('/user_group') ?>';
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
        <TITLE> User Group </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmUserGroup" NAME="frmUserGroup" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="UserGroup_Id" name="UserGroup_Id" value="<?php echo $UserGroup_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $UserGroup_Code ?>"/>
            <TABLE width='95%' align='center'>
                <TR><TD>
                        <FIELDSET class="well" ><LEGEND>Group</LEGEND>
                            <TABLE>
                                <TR>
                                    <TD class="w150 txt-r">UserGroup Code : </TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="UserGroup_Code" NAME="UserGroup_Code" VALUE="<?php echo $UserGroup_Code ?>"></TD>
                                    <TD>
                                        <LABEL for="Active">
                                            <input type="checkbox" name="Active" id="Active" <?php echo (ISSET($Active) ? (($Active == 'Y' || $Active == '1') ? 'checked' : '') : '') ?>>
                                            &nbsp;Active&nbsp;&nbsp;                                            
                                        </LABEL>
                                    </TD>
                                    <TD class="w150 txt-l">Member in group : <LABEL style="display: inline-block;"><B id="members"><?php echo (ISSET($Members) ? $Members : '0') ?></B></LABEL></TD>
                                </TR>
                                <TR>
                                    <TD class="w150 txt-r">UserGroup Name EN :</TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_Eng-f string_special_characters-f" ID="UserGroup_NameEN" NAME="UserGroup_NameEN" VALUE="<?php echo $UserGroup_NameEN ?>"></TD>                    
                                    <TD class="w150 txt-r">UserGroup Name TH :</TD>
                                    <TD><INPUT TYPE="text" class="string_Thai-f string_special_characters-f" ID="UserGroup_NameTH" NAME="UserGroup_NameTH" VALUE="<?php echo $UserGroup_NameTH ?>"></TD>
                                </TR>
                                <TR>
                                    <TD class="w150 txt-r">UserGroup Desc :</TD>
                                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="UserGroup_Desc" NAME="UserGroup_Desc" style="resize:none; width:98%;" rows="2"><?php echo $UserGroup_Desc ?></TEXTAREA></TD>
                                </TR>
                        </TABLE>
                        </FIELDSET> 
                    </TD></TR>
                <TR><TD>
                        <FIELDSET class="well" ><LEGEND>Members Group</LEGEND>
                            <table id="defDataTableUserGroup" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
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
                                    if (count($memberGroupList) > 0) :
                                        foreach ($memberGroupList as $value) :
                                            ?>
                                    <tr>
                                        <td><input type="checkbox" class="chk_member" name="Members_Group_ID[]" id="code-<?php echo $i; ?>" value="<?php echo $value->UserLogin_Id; ?>"<?php if (in_array($value->UserLogin_Id, $memberGroupIDList)) echo 'checked' ?>/></td>
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


