<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: bis_login.php");
	exit();
}

include_once("include_globalVars.php");

?>

<!DOCTYPE html>
<html lang="nl">

    <head>
        <title>Over BIS</title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link type="text/css" href="css/bis.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<?php
  
  include('includes/navbar.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
        
        <div class="col-md-8">
            
            <h1>Veelgestelde vragen</h1>
            
            <hr>
            
            <h2>Hoe zit het met BIS en het spitsrooster?</h2>
            <p>
            Het spitsrooster wordt door het bestuur vantevoren in BIS ingevoerd. U hoeft de aan u toegewezen (oranjegekleurde) blokken alleen nog te bevestigen door ze aan te klikken en op 'Bevestigen' te drukken. Dit kan van drie dagen tot een dag vantevoren. Daarna komt een spitsblok te vervallen.
            </p>
            
            <h2>Waarom loopt de kalender maar tot het einde van de maand?</h2>
            <p>
            Als u op het symbooltje naast een datum-invoerveld klikt, opent in een venster een kalendertje waarin u makkelijk de juiste datum aan kunt klikken. Deze kalender opent in de huidige maand. Bovenaan de kalender bevinden zich vier knoppen, waarvan '>' de belangrijkste is. Hiermee gaat de kalender namelijk een maand vooruit. U kunt dan dus data uit de volgende maand selecteren.
            </p>
            
            <h2>Waarom kan ik een e-mailadres opgeven?</h2>
            <p>
            In principe kan iemand anders een door u gemaakte inschrijving wijzigen of wissen, maar als u een e-mailadres opgeeft bij inschrijving, zult u hiervan altijd via e-mail bericht krijgen. U kunt dan zelf controleren wat er met uw inschrijving gebeurd is. NB: ook al wist iemand anders in het inschrijfscherm uw e-mailadres, u krijgt toch een e-mail, omdat BIS uw adres uit de database haalt.
            </p>
            
            <h2>Waarom kan ik meer dan 3 dagen vooruit inschrijven?</h2>
            <p>
            U kunt maximaal 10 dagen vooruit inschrijven. Dit in verband met het tijdig kunnen reserveren van examens, toertochten en wedstrijden. Let wel: inschrijvingen die meer dan 3 dagen vantevoren gedaan worden, dienen van een MPB voorzien te zijn en worden ter controle aan het opgegeven bestuurslid gemeld. 
            </p>
            
            <h2>Kan iemand zomaar mijn inschrijving wissen?</h2>
            <p>
            In principe kan dit, maar als u een e-mailadres opgeeft bij inschrijving, zult u hiervan altijd bericht krijgen. U kunt dan zelf controleren wat er met uw inschrijving gebeurd is.
            </p>

        </div>

                
        <div class="col-md-4">
            
            <div class="well">
            
                <h3>
                    Over BIS
                </h3>
                
                <p>Voor meer informatie, opmerkingen, vragen en/of suggesties kunt u ook contact opnemen met Erik Roos door te mailen naar het volgende adres:
                <? echo "<a href='mailto:".$mailadres."'>".$mailadres."</a>"; ?>
                </p>
                
                <h4>API en app</h4>
                <p>Voor meer informatie over de BIS-API, zie <a href="api/doc.html">de documentatie</a>.<br>
                Download <a href="http://hunze.nl/sites/default/files/Hunze%20BIS%20v120820.apk" target="_blank">hier</a> de Android-app van BIS.</p>
                
                <h4>Algemeen - hoofdscherm</h4>
                <ul>
                  <li>Klik in het schema ter hoogte van de gewenste boot en tijd om een inschrijving te maken. NB: grijsgekleurde boten zijn uit de vaart.</li>
                  <li>Klik op een ingeschreven blok om dit te bevestigen, te bekijken of te wijzigen. NB: grijze blokken kunnen niet (meer) gewijzigd worden.</li>
                  <li>Oranje blokken zijn vooraf door het bestuur ingevoerde spitsblokken. Deze dienen uiterlijk een dag vantevoren bevestigd te worden.</li>
                </ul>
                
                <h4>Algemeen - inschrijfscherm</h4>
                <ul>
                  <li>Voer de juiste gegevens in en druk tot slot op 'inschrijven' of 'opslaan'.</li>
                  <li>Bij een spitsblok de gegevens controleren en op 'bevestigen' drukken.</li>
                  <li>Met een druk op 'sluiten' rechtsbovenin gaat u terug naar het inschrijfblad zonder de inschrijving te hebben gemaakt of gewijzigd.</li>
                  <li>Kies, in het geval van een bestaande inschrijving, eventueel 'verwijderen' om deze te wissen.</li>
                </ul>
                
            </div>
            
        </div>
            
    </div>
    
</div>

</body>
</html>
