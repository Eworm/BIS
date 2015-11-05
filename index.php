<?php
// check login
session_start();
if (!isset($_SESSION['authorized_bis']) || $_SESSION['authorized_bis'] != 'yes') {
	header("Location: bis_login.php");
	exit();
}

include_once("include_globalVars.php");
include_once("include_helperMethods.php");
if ($toonweer) include_once("xmlnews.php");

setlocale(LC_TIME, 'nl_NL');

$bisdblink = mysql_connect($database_host, $database_user, $database_pass);
if (!mysql_select_db($database, $bisdblink)) {
	echo "Fout: database niet gevonden.<br>";
	exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
    
    <head>
        <title><?php echo $systeemnaam; ?></title>
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
    	<link type="text/css" href="css/bis.css" rel="stylesheet">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    	<link type="text/css" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    	
    </head>
    
<body>
    
<script type="text/javascript" src="scripts/wz_tooltip.js"></script>

<?php
// stop alle bootcategorieŽn in een array
$query = "SELECT DISTINCT Categorie FROM types ORDER BY Categorie;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van categorieŽn mislukt.". mysql_error());
}
$cat_array = array();
while ($row = mysql_fetch_assoc($result)) {
	array_push($cat_array, $row['Categorie']);
}

// stop alle roeigraden in een array
$query = "SELECT Roeigraad FROM roeigraden WHERE ToonInBIS=1 ORDER BY ID;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van roeigraden mislukt.". mysql_error());
}
$grade_array = array();
while ($row = mysql_fetch_assoc($result)) {
	array_push($grade_array, $row['Roeigraad']);
}

$date_to_show = 0;
if (isset($_GET['date_to_show'])) {
	$date_to_show = $_GET['date_to_show'];
}
if ($date_to_show == 0 || !CheckTheDate($date_to_show)) { // altijd sanity check
	$date_to_show = $today;
}
$date_to_show_db = DateToDBdate($date_to_show);

$start_hrs_to_show = -1;
$start_mins_to_show = -1;
if (isset($_GET['start_time_to_show'])) {
	$start_time_to_show = $_GET['start_time_to_show'];
	$start_time_fields = explode(":", $start_time_to_show);
	$start_hrs_to_show = $start_time_fields[0];
	$start_mins_to_show = $start_time_fields[1];
}
if ($start_hrs_to_show == -1 || $start_mins_to_show == -1 ||
    !($start_hrs_to_show >= 6 && $start_hrs_to_show <= 23) ||
	!($start_mins_to_show == 0 || $start_mins_to_show == 15 || $start_mins_to_show == 30 || $start_mins_to_show == 45)
) { // sanity check
	if ($date_to_show == $today) {
		if ($thehour_q < 6) {
			$start_hrs_to_show = 6;
			$start_mins_to_show = 0;
		} else {
			$start_hrs_to_show = $thehour_q;
			$start_mins_to_show = $theminute_quarts;
		}
	} else {
		$start_hrs_to_show = 9;
		$start_mins_to_show = 0;
	}
}
if ($start_mins_to_show == 0) $start_mins_to_show = "00";
$start_time_to_show = $start_hrs_to_show.":".$start_mins_to_show;
$start_block = TimeToBlocks($start_time_to_show);

$cat_to_show = $standaardcategorie;
if (isset($_GET['cat_to_show'])) {
	$cat_to_show = $_GET['cat_to_show'];
}
if (!in_array($cat_to_show, $cat_array)) { // sanity check
	$cat_to_show = $standaardcategorie;
}

$grade_to_show = $standaardgraad;
if (isset($_GET['grade_to_show'])) {
	$grade_to_show = $_GET['grade_to_show'];
}
if (!in_array($grade_to_show, $grade_array)) { // sanity check
	$grade_to_show = $standaardgraad;
}

$date_tmp = strtotime($today_db);
$date_sh = strftime('%A %d-%m-%Y', $date_tmp);
?>

<?php
  
  include('includes/navbar.php');
    
?>

<div class="container-fluid">
            
    <div class="row">
                
        <div class="col-md-9">
            
            <div class="panel panel-default">
                
                <div class="panel-body">
            
                    <form>
                        
                        <div class="row">
                            
                            <div class="col-md-2">
                        
                                <div class="form-group">
                                    
                                    <label for="date_to_show">Datum</label>
                                    
                                    <input type="text" name="date_to_show" maxlength="10" value="<?php echo $date_to_show ?>" onchange="changeInfo();" id="date_to_show" class="form-control datepicker">
                                    
<!--
                                    <a href="javascript:show_calendar('adjust.date_to_show');" onmouseover="window.status='Kalender';return true;" onmouseout="window.status='';return true;"><img src='res/kalender.gif' width='19' height='17' border='0' alt='Kalender'></a>
                                	<input class='btn btn-default btn-sm' type="button" name="change_date" value="&lt;" onclick="changeDate(-1); changeInfo();">
                                	<input class='btn btn-default btn-sm' type="button" name="reset_date" value="Vandaag" onclick="resetDate(); changeInfo();">
                                	<input class='btn btn-default btn-sm' type="button" name="change_date" value="&gt;" onclick="changeDate(1); changeInfo();">
-->
                                    
                                </div>
                        
                            </div>
                            
                            <div class="col-md-2">
                        
                                <div class="form-group">
                                    
<!--                                     <input type="time" class="form-control" min="6:00" max="17:00" step="900"> -->
                                    
                                    <label for="start_hrs_to_show">Vanaf</label>
                                    
                                    <div class="row">
                                        
                                        <div class="col-md-12">
                                            
                                            
                                            <input type="range" min="6" max="17" step="1" value="8" name='start_hrs_to_show' onchange="changeInfo();" id='start_hrs_to_show' list="powers">
                                    
                                            <datalist id="powers">
                                              <option value="6">
                                              <option value="7">
                                              <option value="8">
                                              <option value="9">
                                              <option value="10">
                                              <option value="11">
                                              <option value="12">
                                              <option value="13">
                                              <option value="14">
                                              <option value="15">
                                              <option value="16">
                                              <option value="17">
                                            </datalist>
                                    
<!--
                                            <select name='start_hrs_to_show' onchange="changeInfo();" id='start_hrs_to_show' class="form-control">
                                        	<?php for ($t = 6; $t < 18; $t++): ?>
                                        		<option value="<?php echo $t ?>"<?php if ($start_hrs_to_show == $t): ?> selected="selected" <?php endif; ?>><?php echo $t; ?></option>
                                        	<?php endfor; ?>
                                        	</select>
-->
                                        	
                                        </div>
                                        
                                        <div class="col-md-6" style="display: none;">
                                        	
                                        	<select name='start_mins_to_show' onchange="changeInfo();" id='start_mins_to_show' class="form-control">
                                        		<option value="00"<?php if ($start_mins_to_show == 0): ?> selected="selected" <?php endif; ?>>00</option>
                                        		<option value="15"<?php if ($start_mins_to_show == 15): ?> selected="selected" <?php endif; ?>>15</option>
                                        		<option value="30"<?php if ($start_mins_to_show == 30): ?> selected="selected" <?php endif; ?>>30</option>
                                        		<option value="45"<?php if ($start_mins_to_show == 45): ?> selected="selected" <?php endif; ?>>45</option>
                                        	</select>
                                        	
                                        </div>
                                        
                                    </div>
        
                                </div>
                            
                            </div>
                            
                            <div class="col-md-8">
                                
                                <div class="form-group">
                                    
                                    <label for="cat_to_show">Categorie</label>
                                    
<!--
                                    <select name='cat_to_show' onchange="changeInfo();" id='cat_to_show' class="form-control">
                                        
                                    	<?php foreach($cat_array as $cat_db): ?>
                                    		<option value="<?php echo $cat_db; ?>"<?php if ($cat_to_show == $cat_db): ?> selected="selected" <?php endif; ?>><?php echo $cat_db; ?></option>
                                    	<?php endforeach; ?>
                                    	
                                	</select>
-->
                                	
                                	<div class="form-group">
                                    	
                                    	<div class="row">
                                	
                                    	<?php foreach($cat_array as $cat_db): ?>
                                    	
                                    	    <div class="col-md-3">
                                    	
                                        	    <label class="radio-inline">
                                            	    
                                            		<input type="radio" id="<?php echo $cat_db; ?>" value="<?php echo $cat_db; ?>" <?php if ($cat_to_show == $cat_db): ?> checked <?php endif; ?> name='cat_to_show' value="<?php echo $cat_db; ?>" onchange="changeInfo();">
                                            		<?php echo $cat_db; ?>
                                            		
                                        		</label>
                                    		
                                    		</div>
                                	
                                    	<?php endforeach; ?>
                                	
                                    	</div>
                                	
                                	</div>
                                	
                                	<input type="hidden" name='grade_to_show' id='grade_to_show' value="alle">
                                	
                                </div>
                                
                            </div>
                        
                        </div>
                
                    </div>
                
                </form>
            
            </div>
                        
        <?php mysql_close($bisdblink); ?>
        
        <div id='ScheduleInfoContainer'>
            
            <div id='ScheduleInfoTransition'>
        
                <div id='ScheduleInfo'>
                    
                	<?php require_once("./show_schedule.php"); ?>
                	
                </div>
                
            </div>
        
        </div>
            
    </div>
                
    <div class="col-md-3">
        
        <div class="well">
            
            <?php if ($toonweer): ?>
        		<?php echo xmlnews('https://www.gyas.nl/media/output/weer.rss',3,'_blank','br', 0); ?>
            <?php endif; ?>

        </div>
        
        <h4>
            Bestuursmededelingen
        </h4>
        
        <?php
        	$query = "SELECT * FROM mededelingen ORDER BY Datum DESC LIMIT 1;"; // alleen recentste
        	$result = mysql_query($query);
        	if (!$result) {
        		echo "Ophalen van bestuursmededelingen mislukt.";
        	} else {
        		$rows_aff = mysql_affected_rows($bisdblink);
        		if ($rows_aff > 0) {
        			$row = mysql_fetch_assoc($result);
        			$note_datum = DBdateToDate($row['Datum']);
        			$bestuurslid = $row['Bestuurslid'];
        			$summary = $row['Betreft'];
        			$note = $row['Mededeling'];
        			echo "Datum: $note_datum<br>Van: $bestuurslid<br><b>Betreft: $summary</b><br>$note<br><br><a href=\"$mededelingenpagina\" target='_blank'>Alle mededelingen";
        			$query2 = "SELECT COUNT(*) AS NrOfNotes FROM mededelingen;"; // alleen recentste
        			$result2 = mysql_query($query2);
        			$row2 = mysql_fetch_assoc($result2);
        			$nr_notes = $row2['NrOfNotes'];
        			if ($nr_notes) echo " (".$nr_notes.") ";
        			echo "</a>";
        		} else {
        			echo "Op dit moment zijn er geen mededelingen.";
        		}
        	}
        	?>
        	
        <hr>
        
        <p>
            <a href='schadeboek/schade_boten_toev.php' class='btn btn-primary'>Schademelding boot  </a>
            <br><br>
            <a href='schadeboek/schade_gebouw_toev.php' class='btn btn-primary'>Schademelding gebouw</a>
        </p>
        
        <hr>
        
        <p class="text-muted">
           Ingelogd als <?php echo $_SESSION['login']; ?>
           <br>
           <a href="bis_logout.php">
               Uitloggen
            </a> 
        </p>
        
    </div>
    
</div>
            
<footer id="mainscreen_footer" class="text-center text-muted">
    
    <hr>
    
	<p>
    	BIS&nbsp;&copy;2008-<?php echo $theyear; ?>&nbsp;Erik Roos, contact: <a href='mailto:<?php echo $mailadres; ?>'><?php echo $mailadres; ?></a>, open source: <a href="https://github.com/erikroos/BIS" target="_blank">GitHub</a>
    </p>
    
</footer>

<div id='index_overlay'></div>
<div id='inschrijving'></div>

<div class="modal fade bs-example-modal-lg" id="inschrijvingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Nieuwe reservering</h4>
            </div>
            <div class="modal-body">
                <p>One fine body&hellip;</p>
            </div>
        </div>
    </div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    
    <script src="bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="scripts/datepicker.js"></script>
	<script src="scripts/Script.js"></script>
    <script src="scripts/dates_and_ajax.js"></script>
	<script src="scripts/kalender.js"></script>
	
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>
