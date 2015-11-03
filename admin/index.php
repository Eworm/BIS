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
        <title>Admin - <?php echo $systeemnaam; ?></title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">

            <div class="row">
                
                <div class="col-md-4">
                
                    <div class="panel panel-default">
                    
                        <div class="panel-heading">
                            Boten      
                        </div>
                    
                        <div class="panel-body">
    
                    		<a href='./admin_vloot.php'>Boten</a>
                    		<br>
                    		<a href='./admin_types.php'>Boottypes</a>
                    		<br>
                    		<a href='./admin_graden.php'>Roeigraden</a>
                    		<br>
                    		<a href='./admin_rappo.php'>Bootgebruikrapportages</a>
            		
                		</div>
        		
            		</div>
        		
            	</div>
            	
            	<div class="col-md-4">
                	
                	<div class="panel panel-default">
                    
                        <div class="panel-heading">
                            Inschrijvingen      
                        </div>
                    
                        <div class="panel-body">
                        	
                    		<a href='./admin_spits.php'>Spitsrooster</a>
                    		<br>
                    		<a href='./admin_blokken.php'>Wedstrijdblokken</a>
                    		
                        </div>
                        
                    </div>
            		
            	</div>
            	
            	<div class="col-md-4">
                	
                	<div class="panel panel-default">
                    
                        <div class="panel-heading">
                            Bestuur      
                        </div>
                    
                        <div class="panel-body">
                	
                    		<a href='./admin_mededeling.php'>Bestuursmededelingen</a>
                    		<br>
                    		<a href='./admin_bestuur.php'>Bestuursleden</a>
                    		
                        </div>
                    
                    </div>
                    		
            	</div>
        	
        	</div>
        	
        </div>
        
        <div class="col-md-3">
            
            <div class="well">
                
                <strong>Welkom in de admin-sectie van BIS</strong>
                <br><br>
                <a href='./admin_logout.php' class="btn btn-primary">Uitloggen</a>
                
            </div>
            
        </div>
        
    </div>

</div>

</body>
</html>
