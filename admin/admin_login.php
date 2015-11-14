<?php
include_once("../include_globalVars.php");

extract ($_REQUEST);
if ((isset($login) && $login == "admin" && isset($password) && $password == $login_admin_admin_wachtwoord) ||
    (isset($login) && $login == "matcie" && isset($password) && $password == $login_admin_matcie_wachtwoord) ||
	(isset($login) && $login == "excie" && isset($password) && $password == $login_admin_excie_wachtwoord) ||
	(isset($login) && $login == "instrcie" && isset($password) && $password == $login_admin_instrcie_wachtwoord) ||
	(isset($login) && $login == "gebcie" && isset($password) && $password == $login_admin_gebcie_wachtwoord)
) {
	session_start();
	$_SESSION['authorized'] = 'yes';
	if ($login == "matcie") {
		$_SESSION['restrict'] = 'matcie';
		header("Location: admin_schade.php");
	} else {
		if ($login == "excie") {
			$_SESSION['restrict'] = 'excie';
			header("Location: admin_examens.php");
		} else {
			if ($login == "instrcie") {
				$_SESSION['restrict'] = 'instrcie';
				header("Location: admin_cursussen.php");
			} else {
				if ($login == "gebcie") {
					$_SESSION['restrict'] = 'gebcie';
					header("Location: admin_schade_gebouw.php");
				} else {
					header("Location: index.php");
				}
			}
		}
	}
	exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
    
    <head>
        <title><?php echo $systeemnaam; ?> - Inloggen admin</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
            	
    </head>
    
<body class="prominent-bg">

	<div class="container-fluid main-container">
    	
    	<div class="mainbox col-md-4 col-md-offset-4">
        	
        	<br><br><br>
        	
        	<div class="panel panel-default">
            	
            	<main class="panel-body">
                	
                	<h1 class="h3">
                    	Admin login
                    </h1>
                	
                	<hr>
    	
                	<form method="post" action="./admin_login.php">
                    	
                		<div class="form-group">
                    		<label for="login">Login-naam</label>
                            <input type="text" name="login" id="login" class="form-control input-lg" autofocus required>
                        </div>
                        
                		<div class="form-group">
                    		  <label for="password">Wachtwoord</label>
                              <input type="password" name="password" id="password" class="form-control input-lg" required>
                        </div>
                        
                        <div class="form-group">
                		    <input type="submit" value="Inloggen" class="btn btn-primary btn-block btn-lg">
                        </div>
                	
                	</form>
	
            	</main>
	
        	</div>
	
    	</div>
	
	</div>
	
</body>
</html>
