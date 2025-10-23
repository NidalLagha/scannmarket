


<?php

if(isset($_POST['barcode'])){
                    $current_time = time();
                    $DateTime=strftime("%d-%m-%y %H:%M:%S",$current_time);
                    $DateTime;

     $barcode=$_POST['barcode'];
     $barcode=mysqli_real_escape_string($conn,$barcode);
     
     $query_grap="select * from item where barcode='$barcode' ";
     $query_grap_exe=mysqli_query($conn,$query_grap);
     $count=mysqli_num_rows($query_grap_exe);

     if($count>0){
        $error="Data Dublicated!";
     }else{
        $query="INSERT INTO item(barcode,datereg) VALUES('$barcode','$DateTime')";
        $query_exe=mysqli_query($conn,$query);


        if(!$query_exe){
            die(mysqli_error($conn));
        }
     }  
}

?>