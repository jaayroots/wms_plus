
      <input type="button" class="button dark_blue" value="Add" onclick="openForm('product_master','replenishment_/replenishment_adding/','A','')">
      <table cellpadding="0" cellspacing="0" border="0" class="display dataTable" id="defDataTable" aria-describedby="defDataTable_info">
         
         <thead>
            <tr role="row">
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 132px;">
                  <div class="DataTables_sort_wrapper unuse_sort">No<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 376px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Location Code<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 376px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Product Code<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 368px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Re-Order Point<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 307px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Max<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 307px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Create By<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 307px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Active<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 307px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Generate<span class="DataTables_sort_icon"></span></div>
               </th>
               <th class="ui-state-default" role="columnheader" rowspan="1" colspan="1" style="width: 307px;">
                  <div class="DataTables_sort_wrapper unuse_sort">Delete<span class="DataTables_sort_icon"></span></div>
               </th>
            </tr>
         </thead>
         <tbody role="alert" aria-live="polite" aria-relevant="all">
            <?php 
                    foreach ($list_view as $key => $value) {
                     $no = $key+1;
                        if($key % 2 == 0){
                           //  $class_tr = "list_row_click odd";
                            }
                            else{
                           //  $class_tr = "list_row_click even";
                            }
                        if($value['status'] == 'YES'){
                           $style = 'style="color:green"';
                        }else{
                           $style = 'style="color:red"';
                        }

            echo '<tr class="'.$class_tr.'">';
             echo '<td class=" ">'.$no.'</td>';
             echo '<td class=" ">'.$value['location_code'].'</td>';
             echo '<td class=" ">'.$value['product_code'].'</td>';
             echo '<td style="color:red" class=" ">'.$value['re_order_point'].'</td>';
             echo '<td class=" ">'.$value['max'].'</td>';
             echo '<td class=" ">'.$value['UserAccount'].'</td>';
             echo '<td '.$style.'class=" ">'.$value['status'].'</td>';
            //  echo '<td class=" ">Delete</td>';
             echo '<td  id =list'.$value['id'].' onclick="generate('.$value['id'].');"><button>Generate</button></td>';
             echo '<td  id =list'.$value['id'].' onclick="edit_list('.$value['id'].');"><img src="'.base_url().'css/images/icons/del.png" alt=""></td>';
             echo '</tr>';
         }
            ?>

         </tbody>
      </table>

   </div>
   <form id="after_gen" action="<?php echo base_url(); ?>index.php/replenishment/openActionForm" method="POST">
       <input  id="id" name="id" type="hidden" value="" id="id">
   </form>
<script>

            function edit_list(id){
               if (confirm('Are you want to delete ?')) {
                  var settings = {
            "url": "<?php echo base_url(); ?>index.php/replenishment_/delete_list",
            "method": "POST",
            "timeout": 0,
            "headers": {
               "Content-Type": "text/plain"
            },
            "data": {"id":id},
            };
            $.ajax(settings).done(function (response) {

            alert('Successfully Updated'); window.location = '<?php echo base_url(); ?>index.php/replenishment_/replenishment_form'
            });
            
               }
      
            }

            function generate(id){
//                console.log(id);
                var settings = {
                    "method": "POST",
                    "url": "<?php echo base_url(); ?>index.php/c_bypass/call_from_master_list",                 
                    "timeout": 0,
                    "headers": {
                       "Content-Type": "text/plain"
                    },
                    "data": {"id":id},
                };
                
                $.ajax(settings).done(function (response) {
//                    console.log(response);
                     $("#id").val(response);
                     $( "#after_gen" ).submit();
//                    alert('Successfully Updated'); window.location = '<?php echo base_url(); ?>index.php/replenishment_/replenishment_form'
                });
            }
            
            
</script>
