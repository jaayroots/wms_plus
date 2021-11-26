<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title></title>
    <link rel="stylesheet" href="<?php echo base_url()?>css/style.css" />
     <script src="<?php echo base_url()?>js/jquery-1.9.1.js"></script>
     
    <script type="text/javascript">

        function upload(){

//            $.ajax({
//				url: "<?php echo base_url()?>im_ex/upload",
//				type: "POST",
//				data: formdata,
//				processData: false,
//				contentType: false,
//				success: function (res) {
//                                        alert(res);
//					document.getElementById("response").innerHTML = res; 
//				}
//			});

//            $.post("<?php echo base_url()?>im_ex/upload", 
//                    $('#uploadform').serialize(),
//                    function(data){
//                        alert(data);
//                    }, "html");
                    
//            $.ajax({
//               url: "<?php echo base_url()?>im_ex/upload",
//               type: "POST",
//               data: $('#uploadform').serialize(),
//               success:function(html){
//                  alert(html)
//              }
//           });
        }
    </script>
    
</head>
<body>
	<div id="main">
		<h1>Upload</h1>
		<form method="post" id="uploadform" name="uploadform" enctype="multipart/form-data"  action="<?php echo site_url()?>/im_ex/upload">
    		<input type="file" name="xfile" id="xfile"  />
    		<button type="submit"   id="btn">Upload Files!</button>
    	</form>

  	<div id="response"></div>
		<ul id="image-list">

		</ul>
	</div>
	
<!--
  <script src="<?php echo base_url()?>js/upload.js"></script>
-->

</body>
</html>
