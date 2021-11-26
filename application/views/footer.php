<script>// Add by Ton! 20131225 For user can edit password by myself.
    $(document).ready(function() {
        $('#Current_Password').val('');
        $('#New_Password').val('');
        $('#Confirm_New_Password').val('');

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('#Current_Password').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('#Current_Password').blur(function() {
            $.post("<?php echo site_url() . "/contact_user/check_current_password" ?>", {Current_Password: $(this).val()}, function(dataCheck) {
                if (dataCheck === "1") {
                    alert("Current password is incorrect. Please check.");
                    $('#Current_Password').focus();
                    return;
                }
            }, "html");
        });

        $('[name="New_Password"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Confirm_New_Password"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    $('#myModalPassword').modal('toggle').css({// make width 90% of screen
        'width': function() {
            return ($(document).width() * 0.95) + 'px';
        }, // center model
        'margin-left': function() {
            return -($(this).width() / 2);
        }
    });

    function save_change_password() {
        var userId = '<?php echo $this->session->userdata('user_id') ?>';
        var current_pass = $('#Current_Password').val();
        var new_pass = $('#New_Password').val();
        var confirm_new_pass = $('#Confirm_New_Password').val();

        if (current_pass === '') {
            alert("Please input Current Password.");
            $('#Current_Password').focus();
            return;
        }

        if (new_pass === '') {
            alert("Please input New Password.");
            $('#New_Password').focus();
            return;
        }

        if (current_pass === new_pass) {
            alert("New Password must be identical to the Current Password. Please check.");
            $("#New_Password").focus();
            return;
        }

        if (new_pass !== confirm_new_pass) {
            alert("Confirm New Password must be identical to the New Password. Please check.");
            $("#Confirm_New_Password").focus();
            return;
        }

        $.post("<?php echo site_url() . "/contact_user/check_old_password" ?>", {Password: new_pass, UserLogin_Id: userId}, function(chk_pw) {
            if (chk_pw === "1") {
                alert("New Password ever used. Not available.");
                $("#New_Password").val("");
                $("#Confirm_New_Password").val("");
                $("#New_Password").focus();
//                $('#Current_Password').val("");
                return;
            } else {
                // --- Save
                $.post("<?php echo site_url() . "/contact_user/change_password" ?>", {Password: new_pass, UserLogin_Id: userId}, function(dataSave) {
                    if (dataSave !== null) {
                        $('#myModalPassword').modal('hide');
                        $('#Current_Password').val('');
                        $('#New_Password').val('');
                        $("#Confirm_New_Password").val('');
                        alert("Change password successfully.");
                    }
                }, "html");
            }
        }, "html");
    }
    
    
    socket.on('show_user_online', function(data){
        if (JSON.stringify(data.db_config) == JSON.stringify(db_config)) {
            var users = data.users;
            var all_user_online = [];
            for(var socket_id in users){
                if(users[socket_id] != null){
                    if(all_user_online.indexOf(users[socket_id]) < 0){
                        all_user_online.push(users[socket_id]);
                    }
                }
            }
            
            all_user_online = all_user_online.sort().join(',');
            if('<?php echo $this->session->userdata('user_id'); ?>' == '1'){
                $('#show_user_online').html(all_user_online);
            }
        }
    });
    
    
</script>
</TABLE>

<div id="show_user_online"></div>

<!-- Modal Change Password -->
<div style="min-height:200px;padding:5px 10px;display:none;" id="myModalPassword" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalPasswordLabel" aria-hidden="true" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalPasswordLabel">Change password</h3>
        <h6 id="myModalPasswordLabel" style="color: red;">*** Recommended to change the password every 30 days at least.</h6>
    </div>
    <div class="modal-body">
        <TABLE width='100%' align='center'>
            <TR><TD>
                    <FIELDSET class="well" >
                        <TABLE>
                            <TR>
                                <TD>Current Password :</TD>
                                <TD><INPUT TYPE="password" class="required" ID="Current_Password" NAME="Current_Password"></TD>
                            </TR>
                            <TR>
                                <TD>New Password :</TD>
                                <TD><INPUT TYPE="password" class="required" ID="New_Password" NAME="New_Password"></TD>
                            </TR>
                            <TR>
                                <TD>Confirm New Password :</TD>
                                <TD><INPUT TYPE="password" class="required" ID="Confirm_New_Password" NAME="Confirm_New_Password"></TD>
                            </TR>
                        </TABLE>
                    </FIELDSET>
                </TD></TR>
        </TABLE>
    </div>
    <div class="modal-footer">
        <div style="float:right;">
            <input class="btn btn-primary" value="Save" type="submit" id="save_change_pass" ONCLICK='save_change_password();'>
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        </div>
    </div>
    <!--    </form>-->
</div>
<p id="back-top">
    <a href="#top"><span></span>Top</a>
</p>
