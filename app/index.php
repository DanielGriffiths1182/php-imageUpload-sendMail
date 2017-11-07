<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>PHP File Uploader</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    </head>
    <body>

        <div class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.php">Upload File</a>
                </div>
            </div>
        </div>

        <div class="container">
        	 <div class="row">
      	       <?php
      	       $folder = "uploads";
      	       $results = scandir('uploads');
      	       foreach ($results as $result) {
          	       	if ($result === '.' or $result === '..') continue;

          	       	if (is_file($folder . '/' . $result)) {
            	       		echo '
            	       		<div class="col-md-3">
            		       		<div class="thumbnail">
            			       		<img src="'.$folder . '/' . $result.'" alt="...">
            				       		<div class="caption">
            				       		<p><a href="remove.php?name='.$result.'" class="btn btn-danger btn-xs" role="button">Remove</a></p>
            			       		</div>
            		       		</div>
            	       		</div>';
            	       	}
        	       }
      	       ?>
        	  </div>

    	      <div class="row">
            	  <div class="col-lg-12">
        	          <form class="well" action="upload.php" method="post" enctype="multipart/form-data">
              				  <div class="form-group">
                  				  <label for="file">Select an image to upload</label>
                  				  <input type="file" name="file">
                  				  <p class="help-block">Only jpg, jpeg, png and gif file with maximum size of 1 MB is allowed.</p>
              				  </div>
              				  <input type="submit" class="btn btn-lg btn-primary" value="Upload">
          				  </form>
                </div>
        	  </div>
        </div>
        <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {	// Form was submitted, do some checks and if it passes send the email
              	$errors = 0;		// Keep track of the number of validation errors
              	$errorLog = "";		// Get some details on what didn't validate
              	$message = "";		// Starter email message
              	$request = array();
              	$required = array("First_Name", "Middle_Initial", "Last_Name" );
              	$successLog = "";		// Get some details on what went through

              	// Sanitize the inputs
              	foreach ( $_POST as $formName => $formValue )
              	{	if ( is_array($formValue) )
                		{	foreach ( $formValue as $formName2 => $formValue2 )	{	$request[$formName][$formName2] = htmlspecialchars(strip_tags($formValue2));	}	}
                	else
                		{	$request[$formName] = htmlspecialchars(strip_tags($formValue));	}
              	}

            	// Validate required form fields
            	foreach ( $request as $name => $value )
            	{	$displayName = str_replace("_", " ", $name);
            		// Is this a required value, if so and it's empty track the errors
            		if ( !is_array($value) )
            		{	if ( in_array( $name, $required ) && strlen($value) < 1 )
            			{	$errors++;
            				$errorLog .= "The field <strong>".$displayName."</strong> is required.<br />";
            			}
            		}
            		if ( $name == "Email" && !filter_var($value, FILTER_VALIDATE_EMAIL) )
            		{	$errors++;
            			$errorLog .= "The field <strong>".$displayName."</strong> must be a valid email address.<br />";
            		}
            	}

            	if ( $errors == 0 )
            	{	// Validation passed, get the email ready to send
            		$toEmail = "danielgriffiths.coding@yahoo.com";
            		$subject = "Form Submission from ".$request["First_Name"]." ".$request["Last_Name"];

            		unset ( $request["submit"] );

            		$message .= "Following is an application submitted<br /><br />\n\n<table>";
            		foreach ( $request as $name => $value )
            		{	$displayName = str_replace("_", " ", $name);
            			if ( is_array($value) ) {	$value = implode(", ", $value);	}
            			$message .= "\n<tr><th align='right' valign='top'><strong>".$displayName."</strong>:</th><td valign='top'>".$value."</td></tr>";
            		}
            		$message .= "</table><br /><br />\n\nSubmitted on ".date("F j, Y, g:i a");

            		//Normal headers
            		$num = md5(time());
            		$headers  = "From: Mailer <noreply@sample.com>
            ";
            		$headers .= "Reply-to: ".$request["First_Name"]." ".$request["Last_Name"]." <".$request["Email"].">
            ";
            		$headers .= "MIME-Version: 1.0
            ";
            		$headers .= "Content-Type: multipart/alternative; boundary=".$num."
            ";
            		// These two steps to help avoid spam
            		$headers .= "Message-ID: <".gettimeofday()." TheSystem@".$_SERVER['SERVER_NAME'].">
            ";
            		$headers .= "X-Mailer: PHP v".phpversion()."
            ";
            		// Text version
            		$headers .= "\n--".$num."\n";
            		$headers .= "Content-Type: text/plain; charset=iso-8859-1
            ";
            		$headers .= "Content-Transfer-Encoding: 8bit
            ";
            		$headers .= "".strip_tags($message)."\n";
            		// HTML message
            		$headers .= "\n--".$num."\n";
            		$headers .= "Content-Type: text/html; charset=iso-8859-1
            ";
            		$headers .= "".$message."\n";

            		if ( strlen($_FILES['File']['name']) > 0 )
            		{	// If a file was uploaded, do some processing
            			$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['File']['name']);
            			$filetype = $_FILES["File"]["type"];
            			$filesize = $_FILES["File"]["size"];
            			$filetemp = $_FILES["File"]["tmp_name"];
            			$ext = substr($filename, strpos($filename,'.'), strlen($filename)-1);

            			if ( !preg_match('/\.(jpg|jpeg|png|gif)/i', $ext) )
            			{	$errors++;
            				$errorLog .= "Upload filetype not supported.";
            			}
            			else if ( $filesize > 2000000 )
            			{	$errors++;
            				$errorLog .= "File size too high, up to 2MB is allowed.";
            			}
            			else
            			{	// Looks like the file is good, send it with the email
            				$fp = fopen($filetemp, "rb");
            				$file = fread($fp, $filesize);
            				$file = chunk_split(base64_encode($file));

            				// Attachment headers
            				$headers .= "\n--".$num."\n";
            				$headers .= "Content-Type:".$filetype." ";
            				$headers .= "name=\"".$filename."\"r\n";
            				$headers .= "Content-Disposition: attachment; ";
            				$headers .= "filename=\"".$filename."\"
            \n";
            				$headers .= "".$file."
            ";

            			}
            		}
            		if ( $errors == 0 )
            		{	if ( mail( $toEmail, $subject, "", $headers ))
            			{	$successLog .= "<b>Thank you for your interest<br />Your form has been submitted.</b>";	}
            			else
            			{	$errorLog .= "Message failed to send, please refresh to try again";	}
            			// Send email
            		}
            	}
           }
        ?>
        <div class="container">
            <div class="row">
          	<form method="post" class="well" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'];?>" name="frmApplication" id="frmApplication">
            		<fieldset><legend>Personal Information</legend>
            		<table cellpadding="0" cellspacing="6" border="0" class="appFormTable">
              			<tr>
                				<td valign="top" colspan="3">
                  					<label for="First_Name" class="required">First Name*</label>
                  					<input type="text" name="First_Name" id="First_Name" maxlength="40" value="<?php echo @$request['First_Name']; ?>" />
                				</td>
                				<td valign="top" colspan="2">
                  					<label for="Middle_Initial" class="required">Middle Initial*</label>
                  					<input type="text" name="Middle_Initial" id="Middle_Initial" maxlength="1" value="<?php echo @$request['Middle_Initial']; ?>" />
                				</td>
                				<td valign="top" colspan="3">
                  					<label for="Last_Name" class="required">Last Name*</label>
                  					<input type="text" name="Last_Name" id="Last_Name" maxlength="40" value="<?php echo @$request['Last_Name']; ?>" />
                				</td>
              			</tr>
              			<tr>
                				<td valign="top" colspan="8">
                  					<label for="Email" class="required">Email Address*</label>
                  					<input type="text" name="Email" id="Email" maxlength="100" value="<?php echo @$request['Email']; ?>" />
                				</td>
              			</tr>
              			<tr>
                				<td valign="top" colspan="8">
                  					<div id="Resume_Format_Attach">
                    						<p><small>Supported file types are 'jpg','jpeg','png','gif'</small></p>
                    						<input type="hidden" name="max_file_size" value="2000000" />
                    						<input type="file" name="Resume_File" id="Resume_File" />
                  					</div>
                				</td>
              			</tr>
            		</table>
            		</fieldset>
            		<div align="center"><input type="submit" class="btn btn-lg btn-primary" name="submit" value="Submit Form" /></div>
          	</form>
            </div>
        </div>

    </body>
</html>
