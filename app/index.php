<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>PHP Image Upload</title>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    </head>
  <body>

<!-- TITLE -->
    <div class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="index.php">Upload File</a>
        </div>
      </div>
    </div>

<!-- LIST UPLOADED FILES -->
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
				       		 <p><a href="remove.php?name='.$result.'" class="btn btn-danger btn-xs" role="button">Remove</a>
                   <a href="crop.php?name='.$result.'" class="btn btn-danger btn-xs" role="button">Crop</a></p>
			       		 </div>
		       	   </div>
	       	   </div>';
  	       }
	       }
        ?>
  	  </div>

<!-- FILE UPLOAD SECTION -->
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

  </body>
</html>
