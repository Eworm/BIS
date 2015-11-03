<?php
// check login
session_start();
if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != 'yes') {
	header("Location: admin_login.php");
	exit();
}

include_once("../include_globalVars.php");
?>

<!DOCTYPE html>
<html lang="nl">
    
    <head>
        <title><?php echo $systeemnaam; ?> - Inloggen</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    	
    </head>
    
<body>

	<div class="container-fluid main-container">
    	
    	<div class="mainbox col-md-4 col-md-offset-4">
        	
        	<br><br><br>
        	
        	<div class="panel panel-default">
            	
            	<main class="panel-body">
    	
                	<h1 class="h3">
                    	U bent uitgelogd
                    </h1>
                	
                	<hr>
                	
<?php
unset($_SESSION['authorized']);
unset($_SESSION['restrict']);
echo "<p><strong>U bent uitgelogd - tot ziens!</strong></p>";
echo "<p><a href='../index.php'>BIS</a><br>";
echo "<a href='admin_login.php'>Opnieuw inloggen</a><br>";
echo "<a href='".$homepage."'>$homepagenaam</a></p>";

?>

                </main>
                    
            </div>
            
        </div>
        
    </div>

</body>
</html>
