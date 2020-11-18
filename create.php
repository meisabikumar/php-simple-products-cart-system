<?php
// Include config file
require_once "dbConfig.php";

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
 
// Define variables and initialize with empty values
$name = $description = $weight = $price =  $status = $img = "";
$name_err = $description_err = $weight_err = $price_err = $status_err = $img_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate name
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } elseif(!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z\s]+$/")))){
        $name_err = "Please enter a valid name.";
    } else{
        $name = $input_name;
    }
    
    // Validate description
    $input_description = trim($_POST["description"]);
    if(empty($input_description)){
        $description_err = "Please enter an description.";     
    } else{
        $description = $input_description;
    }

    $input_weight = trim($_POST["weight"]);
    if(empty($input_weight)){
        $weight_err = "Please enter an description.";     
    } else{
        $weight = $input_weight;
    }
    
    // Validate price
    $input_price = trim($_POST["price"]);
    if(empty($input_price)){
        $price_err = "Please enter the price amount."; 
        //use is_numeric() for float values   
    } elseif(!ctype_digit($input_price)){
        $price_err = "Please enter a positive integer value.";
    } else{
        $price = $input_price;
    }

    

     // Validate status
     $input_status = trim($_POST["status"]);
     if(empty($input_status)){
         $status_err = "Please input.";     
     } elseif(!ctype_digit($input_status)){
         $status_err = "Please enter 1 or 0.";
     } else{
         $status = $input_status;
     }


    // empty($_FILES["fileToUpload"]["name"])
    //Validate image
    $input_image = $_FILES["fileToUpload"]["name"];
    if(empty($input_image)){
        $img_err = "Please select a file.";     
    } else{
        $img = $input_image;
    }

$target_dir = "uploads/";
$fileName = basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check !== false) {
    echo "File is an image - " . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    echo "File is not an image.";
    $uploadOk = 0;
    $img_err = "File is not an image.";    
  }
}

// Check if file already exists
if (file_exists($target_file) && !empty($_FILES["fileToUpload"]["name"])) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
    $img_err = "Sorry, file already exists.";    
  }
  
  // Check file size
  if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
    $img_err = "Sorry, your file is too large.";    
  }
  
  // Allow certain file formats
  if(!empty($_FILES["fileToUpload"]["name"]) && $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
    echo "Sorry, only JPG, JPEG, files are allowed.";
    $uploadOk = 0;
    $img_err = "Sorry, only .jpg, .jpeg, .png files are allowed. (extention name in lowercase)";    
  } 


    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($description_err) && empty($weight_err) && empty($price_err)  && empty($img_err) && empty($status_err) ){

          // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0 && empty($img_err) ) {
                echo "Sorry, your file was not uploaded.";
                $img_err = "Sorry, your file was not uploaded.."; 
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
                } else {
                echo "Sorry, there was an error uploading your file.";
                $img_err = "Sorry, there was an error uploading your file.";   
                }
            }

        // Prepare an insert statement
        $sql = "INSERT INTO products (name, description, weight, price,  file_name, created, status) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssss", $param_name, $param_description, $param_weight, $param_price,  $param_file,$param_status);
            
            // Set parameters
            $param_name = $name;
            $param_description = $description;
            $param_weight = $weight;
            $param_price = $price;
            
            $param_file = $fileName;
            $param_status = $status;

            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records created successfully. Redirect to landing page
                header("location: admin.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<?php




?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Record</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        .wrapper{
            width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h2>Create Record</h2>
                    </div>
                    <p>Please fill this form and submit to add new Bangle record to the database.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <span class="help-block"><?php echo $name_err;?></span>
                        </div>

                        <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                            <label>description</label>
                            <textarea name="description" class="form-control"><?php echo $description; ?></textarea>
                            <span class="help-block"><?php echo $description_err;?></span>
                        </div>

                        <div class="form-group <?php echo (!empty($weight_err)) ? 'has-error' : ''; ?>">
                            <label>weight</label>
                            <textarea name="weight" class="form-control"><?php echo $weight; ?></textarea>
                            <span class="help-block"><?php echo $weight_err;?></span>
                        </div>

                        <div class="form-group <?php echo (!empty($price_err)) ? 'has-error' : ''; ?>">
                            <label>Price</label>
                            <input type="text" name="price" class="form-control" value="<?php echo $price; ?>">
                            <span class="help-block"><?php echo $price_err;?></span>
                        </div>

                        

                        <div class="form-group <?php echo (!empty($status_err)) ? 'has-error' : ''; ?>">
                            <label>status</label>
                            <select class="form-control"name="status"  >                                
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                            <?php //echo $status; ?>
                            <span class="help-block"><?php echo $status_err;?></span>
                        </div>

                        <div class="form-group <?php echo (!empty($img_err)) ? 'has-error' : ''; ?>">
                            <label>Select image to upload:</label>
                            <input type="file" name="fileToUpload" class="form-control" id="fileToUpload" value="<?php// echo $img; ?>">
                            
                            <span class="help-block"><?php echo $img_err;?></span>
                        </div>


                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="admin.php" class="btn btn-default">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>