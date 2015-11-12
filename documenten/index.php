<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: ./bis_login.php");
	exit();
}

include_once("../include_globalVars.php");
?>

<!DOCTYPE html>
<html lang="nl">

    <head>
        <title>Documenten - BIS</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="../css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<?php
  
  include('../includes/navbar.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">
            
            <h1>
                Documenten voor leden
            </h1>
	

<p><em>Alle documenten zijn in het PDF-formaat en openen in een nieuw venster.</em></p>

<strong>Diplomalijst</strong>
<ul>
	<li><a href='Diplomalijst_leden_05-08-2012.pdf' target='_blank'>Diplomalijst per 5 augustus 2012</a></li>
</ul>

<strong>Jaarvergadering 2011</strong>
<ul>
	<li><a href='uitnodiging2011.pdf' target='_blank'>Uitnodiging 2011</a></li>
	<li><a href='VerslagAV2010.pdf' target='_blank'>Verslag jaarvergadering 2010</a></li>
	<li><a href='Begroting2011.pdf' target='_blank'>Begroting 2011</a></li>
	<li><a href='Jaarverslagen2010.pdf' target='_blank'>Jaarverslagen 2010</a></li>
	<li><a href='JaarverslagPenningmeester2010.pdf' target='_blank'>Jaarverslag 2010 door de penningmeester</a></li>
</ul>

<strong>Botenplanvergadering 2011</strong>
<ul>
	<li><a href="NotulenBotenplanAV2011.pdf" target="_blank">Notulen Botenplanvergadering 9 maart 2011</a></li>
	<li><a href="AgendaBotenplanvergadering09-03-2011.pdf" target="_blank">Agenda Botenplanvergadering 9 maart 2011</a></li>
    <li><a href="PresentatieBotenplan2011.ppt" target="_blank">Botenplanpresentatie 2011</a></li>
	<li><a href="Botenplan2011.pdf" target="_blank">Botenplan 2011</a></li>
</ul>

<strong>Botenplanvergadering 2010</strong>
<ul>
	<li><a href="Botenplan2010.pdf" target="_blank">Botenplan 2010</a></li>
	<li><a href="NotulenBotenplanAV09-03-2010.pdf" target="_blank">Notulen botenplanvergadering 2010</a></li>
	<li><a href="VerslagPrebesprekingBotenplan2010.pdf" target="_blank">Verslag pre-bespreking botenplan 2010</a></li>
	<li><a href="NotulenBotenplanAV2009.pdf" target="_blank">Notulen botenplanvergadering 2009</a></li>
</ul>

<strong>Jaarvergadering 2010</strong>
<ul>
	<li><a href="NotulenJaarverg23mrt2009.pdf" target="_blank">Notulen jaarvergadering 2009</a></li>
	<li><a href="agendajaarvergadering.pdf" target="_blank">Agenda jaarvergadering 2010</a></li>
	<li><a href="Begroting2010.pdf" target="_blank">Begroting 2010</a></li>
</ul>

<strong>Jaarverslagen 2009</strong>
<ul>
	<li><a href="jaarverslagcomp2009.pdf" target="_blank">Jaarverslag commissariaat competitieroeien 2009</a></li>
	<li><a href="Jaarverslaginstr2009.pdf" target="_blank">Jaarverslag instructiecommissie 2009</a></li>
	<li><a href="jaarverslagjun2009.pdf" target="_blank">Jaarverslag juniorencommissaris 2009</a></li>
	<li><a href="Jaarverslagmat2009.pdf" target="_blank">Jaarverslag materiaalcommissaris 2009</a></li>
	<li><a href="JaarverslagPenning2009.pdf" target="_blank">Jaarverslag penningmeester 2009</a></li>
	<li><a href="Jaarverslagsecretaris2009.pdf" target="_blank">Jaarverslag secretaris 2009</a></li>
	<li><a href="JaarverslagSociet2009.pdf" target="_blank">Jaarverslag soci&euml;teitscommissaris 2009</a></li>
	<li><a href="jaarverslagtoer2009.pdf" target="_blank">Jaarverslag toercommissie 2009</a></li>
	<li><a href="jaarverslagweds2009.pdf" target="_blank">Jaarverslag wedstrijdcommissaris 2009</a></li>
</ul>

</div>
</div>
</div>
</body>
</html>
