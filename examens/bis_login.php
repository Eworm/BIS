<?php
include_once("../include_globalVars.php");
include_once("../include_helperMethods.php");

extract ($_REQUEST);
if (isset($login) && isset($password)) {
	if (ValidateLogin($login, $password, $database_host, $login_database_user, $login_database_pass, $login_database)) {
		session_start();
		$_SESSION['authorized_bis'] = 'yes';
		$_SESSION['login'] = $login;
		header("Location: ./index.php");
		exit();
	}
}
?>

<!DOCTYPE html>
<html lang="nl">
    
    <head>
        <title><?php echo $systeemnaam; ?> - Inloggen</title>
        
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
                    	BIS - KGR de Hunze
                    </h1>
                	
                	<hr>
                	
                	<form method="post" action="bis_login.php">
                    	
                		<div class="form-group">
                    		
                            <label for="login">Login-naam</label>
                            <input type="text" name="login" id="login" class="form-control input-lg" autofocus required aria-describedby="login-help">
                            <p class="help-block" id="login-help">
                                'hunzelid' Of uw eigen gebruikersnaam
                            </p>
                		  
                		</div>
                		
                		<div class="form-group">
                    		
                            <label for="password">Wachtwoord</label>
                            <input type="password" name="password" id="password" class="form-control input-lg" required aria-describedby="password-help">
                            <p class="help-block" id="password-help">
                                Afkorting van de vereniging &amp; het jaar van oprichting
                            </p>
                		  
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
