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
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar-admin.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-12">

            <div class="row">
                
                <div class="col-md-4">
                
                    <div class="panel panel-default">
                    
                        <div class="panel-heading">
                            Boten      
                        </div>
                    
                        <div class="panel-body">
    
                    		<a href='./admin_vloot.php'>Boten</a>
                    		<br>
                    		<a href="./admin_boot_toevoegen.php">Boot toevoegen</a>
                    		<hr>
                    		<a href='./admin_types.php'>Boottypes</a>
                    		<br>
                    		<a href="./admin_type_toev.php">Type toevoegen</a>
                    		<hr>
                    		<a href='./admin_graden.php'>Roeigraden</a>
                    		<br>
                    		<a href="./admin_graad_toev.php">Roeigraad toevoegen</a>
                    		<hr>
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
                    		<a href="./admin_spits_toev.php">Spitsblok toevoegen</a>
                    		<hr>
                    		<a href='./admin_blokken.php'>Wedstrijdblokken</a>
                    		<br>
                    		<a href="./admin_blok_toev.php">Wedstrijdblok toevoegen</a>
                    		
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
                    		<a href="./admin_mededeling_toev.php">Mededeling toevoegen</a>
                    		<hr>
                    		<a href='./admin_bestuur.php'>Bestuursleden</a>
                    		<br>
                    		<a href="./admin_bestuur_toev.php">Bestuurslid toevoegen</a>
                    		
                        </div>
                    
                    </div>
                    		
            	</div>
        	
        	</div>
        	
        </div>
        
    </div>

</div>

</body>
</html>
