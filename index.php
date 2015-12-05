<?php

function sanitize_output($buffer) {

    $search = array(
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
    );

    $replace = array(
        '>',
        '<',
        '\\1'
    );

    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}

ob_start("sanitize_output");

?>

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
        
        <!-- Microsoft. Delete if not required -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    	<link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    	<link rel="stylesheet" href="css/bis.css?v=1">
    	
    </head>
    
<body>

<?php
// stop alle bootcategorieën in een array
$query = "SELECT DISTINCT Categorie FROM types ORDER BY Categorie;";
$result = mysql_query($query);
if (!$result) {
	die("Ophalen van categorieën mislukt.". mysql_error());
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
                
        <div class="col-md-12">
            
            <div class="row">
                        
                <div class="col-md-7">
            
                    <div class="panel panel-default schedule-filter">
                        
                        <div class="panel-body">
            
                            <form>
                                
                                <div class="row">
                                    
                                    <div class="col-md-2">
                                
                                        <div class="form-group">
                                            
                                            <label for="date_to_show" for="date_to_show">Datum</label>
                                            
                                            <input type="text" name="date_to_show" maxlength="10" value="<?php echo $date_to_show ?>" onchange="changeInfo();" id="date_to_show" class="form-control datepicker">
                                            
                                            <div class="datepicker-change">
                                            	<input class='datepicker-change-button' type="button" name="change_date" value="&lt;" onclick="changeDate(-1); changeInfo();">
                                            	<input class='datepicker-change-button' type="button" name="reset_date" value="Nu" onclick="resetDate(); changeInfo();">
                                            	<input class='datepicker-change-button' type="button" name="change_date" value="&gt;" onclick="changeDate(1); changeInfo();">
                                        	</div>
                                            
                                        </div>
                                
                                    </div>
                                    
                                    <div class="col-md-2">
                                
                                        <div class="form-group">
                                            
                                            <label for="start_hrs_to_show" for="start_hrs_to_show">Tijd</label>
                                            
                                            <div class="row">
                                                
                                                <div class="col-md-12">
                                                                                                
                                                    <input type="range" min="6" max="20" step="2" value="8" name='start_hrs_to_show' onchange="changeInfo();" id='start_hrs_to_show' list="powers">
                                            
                                                    <span class="datalist-legend">6:00</span>
                                                    <datalist id="powers" class="datalist">
                                                        <?php for ($t = 6; $t < 20; $t++): ?>
                                                            <option value="<?php echo $t ?>">  
                                                        <?php endfor; ?>
                                                    </datalist>
                                                    <span class="datalist-legend last-legend">20:00</span>
                                                	
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
                                        	
                                        	<div class="form-group">
                                            	
                                            	<div class="row">
                                        	
                                            	<?php foreach($cat_array as $cat_db): ?>
                                            	
                                            	    <div class="col-md-4">
                                            	
                                                	    <label class="radio-inline">
                                                    	    
                                                    		<input type="radio" value="<?php echo str_replace(' ', '%20', $cat_db); ?>" <?php if ($cat_to_show == $cat_db): ?> checked <?php endif; ?> name='cat_to_show' value="<?php echo $cat_db; ?>" onchange="changeInfo();">
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
                    
                </div>
                
                <div class="col-md-3 boardmessages">
                    
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
                    			echo "<strong>" . $summary . "</strong><br>$note<br><span class='text-muted'>" . $note_datum . ", $bestuurslid</span><br><a href=\"$mededelingenpagina\">Alle bestuursmededelingen";
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
                    
                </div>
                
                <div class="col-md-2">            
                    <?php if ($toonweer): ?>
                		<?php echo xmlnews('https://www.gyas.nl/media/output/weer.rss',3,'_blank','br', 0); ?>
                    <?php endif; ?>
                    
                </div>
            
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
    
</div>
            
<footer id="mainscreen_footer" class="text-center text-muted">
    
    <hr>
    
	<p>
    	BIS&nbsp;&copy;2008-<?php echo $theyear; ?>&nbsp;Erik Roos, contact: <a href='mailto:<?php echo $mailadres; ?>'><?php echo $mailadres; ?></a>, open source: <a href="https://github.com/erikroos/BIS" target="_blank">GitHub</a>
    </p>
    
    <br>
    
</footer>

<div id='index_overlay'></div>
<div id='inschrijving'></div>

<div class="modal fade bs-example-modal-lg" id="inschrijvingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Boot reserveren</h4>
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
    <script src="scripts/dates_and_ajax.js?v1"></script>
	
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>
