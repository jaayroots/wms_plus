<?php
print_r($error);
if (preg_match('|en-US,|', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matched)) :
    $text_resend = 'Resend';
else:
    $text_resend = utf8_to_tis620('ส่งซ้ำ');
endif;
?>
<!doctype html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> 	
<html lang="en"> <!--<![endif]-->
    <head>

        <!-- General Metas -->
        <meta charset="TIS-620" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">	<!-- Force Latest IE rendering engine -->
        <title>Login Form</title>
        <meta name="description" content="">
        <meta name="author" content="">
        <!--[if lt IE 9]>
                <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Mobile Specific Metas -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> 

        <!-- Stylesheets -->
        <link rel="stylesheet" href='http://localhost:1008/ecolab/WebAppForPC/css/base.css'; ?>
        <link rel="stylesheet" href='http://localhost:1008/ecolab/WebAppForPC/css/skeleton.css'; ?>
        <link rel="stylesheet" href='http://localhost:1008/ecolab/WebAppForPC/css/layout_login.css'; ?>
       <!-- <link rel="stylesheet" href="http://localhost:1008/ecolab/WebAppForPCcss/bootstrap.min.css"> -->
        <? echo link_tag(base_url() . "css/error_message.css"); ?>

        <?php
        echo link_tag(base_url("css/debug_kit.css")); // Add By Akkarapol, 14/01/2014, เพิ่มการโหลด CSS ใช้กับ profiler(debug_kit)
        ?>

        <?php if ($this->config->item('node_js_active')): ?>
            <script src="<?php echo $this->config->item('node_js_url'); ?>/socket.io/socket.io.js"></script>
            <script>
                var socket = io.connect('<?php echo $this->config->item('node_js_url'); ?>');
                var db_config = {
                    server: '<?php echo str_replace(',1433', '', str_replace('tcp:', '', @$db_config['hostname'])); ?>',
                    user: '<?php echo @$db_config['username']; ?>',
                    password: '<?php echo @$db_config['password']; ?>',
                    database: '<?php echo @$db_config['database']; ?>'
                };
                var uniqid = '<?php echo uniqid() ?>';


            </script>
        <?php endif; ?>

    </head>
    <body>
        <!-- Primary Page Layout -->
        <?
        $validate_text = validation_errors();
        if ($validate_text !== "") {
        ?>
        <div class="warning message" style="top:0px;" id="warning">
            <h2>Warning !</h2>
            <p>
                <? echo $validate_text; ?>
            </p>
        </div>
        <?
        }
        if ($error != "") {
        ?>
        <div class="error message" style="top:0px;" id="warning">
            <h2>Warning !</h2>
            <p>
                <? echo $error; ?>
            </p>
        </div>
        <? } ?>
        <div class="container">
            <div class="form-bg" >
                <?php echo form_open('authen/login'); ?>
                <div>
                    <h2>WMSPlus+ Login</h2>
                </div>
                <p>
                    <?php
                    echo form_label('User Name : ', 'user_label');
                    echo form_input('user', set_value('user'), 'id="user" autofocus placeholder="Username" onKeyPress="clear_input_password();"');
                    ?>
                </p>
                <p>
                    <?php
                    echo form_label('Password :', 'password_label');
                    echo form_password('password', '', 'id="password" placeholder="Password"');
                    ?>
                </p>
                <p>
                    <?php
                    echo form_label('Renter :', 'renter_label');
                    echo form_dropdown('renter', $renter, set_value('renter'), 'id="renter"');
                    ?>
                </p>                
                <p>
                    <?php
                    echo form_label('Department :', 'brunch_label');
                    echo form_dropdown('branch', array(), set_value('branch'), 'id="branch"');
                    ?>
                </p>
                <!--                <label for="remember">
                                    <input type="checkbox" id="remember" value="remember" />
                                    <span>Remember me on this computer</span>
                                </label>-->                               
                <p style="margin:40px 5px 5px 80%">
                    <?php echo form_submit('submit', 'Login'); ?>
                </p>
                <?php echo form_close(); ?>

            </div>
                <!-- <p class="forgot">��� Password ? <a href="">Click �����.</a></p>-->
        </div><!-- container -->

        <!-- JS  -->
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-1.9.1.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/app.js") ?>"></script>        
        <script type="text/javascript">
            $(document).ready(function () {

                // Check Protocol http to https 9/8/2016 (New Login)
                // if (window.location.protocol !== 'https:') {
                //     window.location = 'https://' + window.location.hostname + window.location.pathname + window.location.hash;
                // }

                // Add By Akkarapol, 14/01/2014, เพิ่มการเซ็ตค่าให้กับ DebugKit ว่าให้ย่อขยายตามการกดปุ่ม DEBUG(#debug_kit_text_title) ที่มุมขวาของจอ
                $('#div_debug_kit').hide();
                $('#debug_kit_text_title').click(function () {
                    $('#div_debug_kit').fadeToggle(300);
                });
                // END Add By Akkarapol, 14/01/2014, เพิ่มการเซ็ตค่าให้กับ DebugKit ว่าให้ย่อขยายตามการกดปุ่ม DEBUG(#debug_kit_text_title) ที่มุมขวาของจอ


                $("#renter").change(function ()
                {
                    $.get("<?php echo site_url(); ?>/authen/get_department", {renter_id: $(this).val()}, function (data) {
                        $('#branch').find('option').remove();
                        $.each(data, function (k, v) {
                            var opt = $("<option>").text(v).val(k);
                            $('#branch').append(opt);
                        });
                    }, 'json');
                });

                $("#renter").trigger('change');


                if (<?php echo ($this->config->item('node_js_active') ? 1 : 0); ?>) {
                    if (<?php echo (@$call_node_check_user_online ? 1 : 0); ?>) {
                        socket.emit('check_user_login_already', db_config, uniqid, $('#user').val());
                        socket.on('check_user_login_already', function (data) {
                            if (data.uniqid == uniqid) {
                                $('#warning').removeClass('error');
                                $('#warning').addClass('success');
                                $('#warning').html("<H2>Waiting</H2>" + "<P>System Re Loging In, Please click '<?php echo $text_resend; ?>' for Submit.</P>");
                                location.reload(false);
                            }
                        });
                    }
                }

            });

            function clear_input_password() { // function for clear textbox password when keypress in textbox username
                $('#password').val('');
            }

        </script>
        <!-- End Document -->
    </body>
</html>