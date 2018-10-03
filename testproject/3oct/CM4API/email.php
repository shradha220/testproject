<html>
   
   <head>
      <title>AmazonSES TEST</title>
   </head>
   
   <body>
      
      <?php
         $to = "ab10486@gmail.com";
         $subject = "AWS Mail Server Configuration";
         
         $message = "<b>This is a test email</b>";
        
         $header = "From:abhishek.10486@gmail.com \r\n";
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-type: text/html\r\n";
         
         $retval = mail ($to,$subject,$message,$header);
         
         
         
         if( $retval == true )
         {
            echo "Message sent successfully...";
         }
         else
         {
            echo "Message could not be sent...";
         }
      ?>
      
   </body>
</html>