<?php

require_once "config.php";
require_once "fpdf/fpdf.php";

session_start();
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
$options = ['expires' => time()+$cookieLifetime, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'strict'];
setcookie(session_name(),session_id(),$options);


function format_date($date) {
	$d = $date['day']<10? "0" . $date['day'] : "" . $date['day'];
	$m = $date['month']<10? "0" . $date['month'] : "" . $date['month'];
	return "" . $date['year'] . "-" . $m . "-" . $d;
}

function human_date($date, $sep='/') {
	$d = $date['day'];
	$m = $date['month'];
	return $d . $sep . $m . $sep . $date['year'];
}

function read_observation ($db, $date) {
	$sql = "SELECT * FROM observation WHERE date_obs = :date LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_cycle($db, $date) {
	$sql = "SELECT date_obs AS cycle FROM observation WHERE premier_jour=1 and date_obs<=:date AND no_compte = :no_compte ORDER BY date_obs DESC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_cycle_end($db, $date) {
	$sql = "SELECT date_obs AS cycle_end FROM observation WHERE premier_jour=1 and date_obs>:date AND no_compte = :no_compte ORDER BY date_obs ASC LIMIT 1";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date", format_date($date), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function export_cycle($db, $date_start, $date_end) {
	$sql = "SELECT date_obs, gommette, COALESCE(sensation,'') as sensation, COALESCE(jour_sommet, '') as sommet, COALESCE(union_sex, '') as 'unions', commentaire FROM observation WHERE date_obs>=:date_start AND date_obs<=:date_end AND no_compte = :no_compte ORDER BY date_obs ASC";

	$statement = $db->prepare($sql);
	$statement->bindValue(":date_start", format_date($date_start), PDO::PARAM_STR);
	$statement->bindValue(":date_end", format_date($date_end), PDO::PARAM_STR);
	$statement->bindValue(":no_compte", $_SESSION["no"], PDO::PARAM_INT);
	$statement->execute();

	return $statement->fetchAll(PDO::FETCH_ASSOC);
}


try {

	$db = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_ID, DB_PASSWORD);

	// VERIFICATION DE LA BONNE OUVERTURE DE LA SESSION
	if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
		print("Vous devez etre connecte pour realiser cette action.");
		exit;
	}

	// LECTURE D'UNE DATE DE DEBUT DE CYCLE
	if (isset($_GET['cycle'])) {
		$date = date_parse($_GET['cycle']);
		$result["date"] = format_date($date);
	}
	else {
		print("Date du cycle non indique.");
		exit;
	}

	// VERIFICATION DU FORMAT DE L'EXPORT
	$available_type = ["pdf", "csv"];
	if (!isset($_GET['type']) || !in_array($_GET['type'], $available_type)) {
		print("Le format de l'export doit être: ");
		print(implode(", ", $available_type));
		exit;
	}

	// RECUPERATION DE LA DATE DE DEBUT ET DE FIN DU CYCLE
	$result["cycle_debut"] = date_parse(get_cycle($db, $date)[0]["cycle"]);
	$cycle_end = get_cycle_end($db, $date);
	if (isset($cycle_end[0]["cycle_end"])) {
		$date_tmp = new DateTime($cycle_end[0]["cycle_end"]);
		$date_tmp->modify('-1 day');
		$result["cycle_fin"] = date_parse($date_tmp->format('Y-m-d'));
	}
	else $result["cycle_fin"] = date_parse(date("Y-m-d"));

	// RECUPERATION DU CYCLE
	$data = export_cycle($db, $result["cycle_debut"],$result["cycle_fin"]);

	// AJOUT DES JOURS MANQUANTS DU CYCLE
	$nb_jours = 0;
	$cycle = [];
	$date_cursor = new DateTime($data[0]["date_obs"]);
	foreach ($data as $line){
		if ($date_cursor->format('Y-m-d') != $line["date_obs"]) {
			array_push($cycle, array("date_obs" => $date_cursor->format('Y-m-d'),"gommette" => '',"sensation" => '',"sommet" => '',"unions" => '',"commentaire" => ''));
			$date_cursor->modify('+1 day');
		}
		array_push($cycle, $line);
		$date_cursor->modify('+1 day');
		$nb_jours += 1;
	}

	// print_r($cycle);
	// exit;

	if ($_GET['type'] == "csv") {

		// ECRITURE DU CSV
		header("content-type:application/csv;charset=UTF-8");
		header('Content-Disposition: attachment; filename="moncycle_app_'. format_date($date) .'.csv"');
		$i = 1;
		print(implode(CSV_SEP,["jour","date","gommette","sensation","sommet", "unions", "commentaires"]));
		print(PHP_EOL);
		foreach (mb_convert_encoding($cycle, 'UTF-16LE', 'UTF-8') as $line){
			print($i . CSV_SEP);
			print(implode(CSV_SEP,$line));
			print(PHP_EOL);
			$i += 1;
		}
	}
	elseif ($_GET['type'] == "pdf") {
	
		$pdf = new FPDF('P','mm','A4');
		$pdf->SetTitle('bill_cycle_'. human_date($date, '_') . '.pdf');
		$pdf->AddPage();
		$pdf->SetFont('Courier','B',16);
		$pdf->Link($pdf->GetX(), $pdf->GetY(), $pdf->GetStringWidth("MONCYCLE.APP "), 10, "https://moncycle.app");
		$pdf->Cell($pdf->GetStringWidth("MON"),10,"MON");
		$pdf->SetTextColor(65,105,255);
		$pdf->Cell($pdf->GetStringWidth("CYCLE"),10,"CYCLE");
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell(0,10,sprintf(".APP %s", utf8_decode($_SESSION["compte"]["nom"])));
		$pdf->SetFont('Courier','',10);
		$pdf->Ln();
		$pdf->Cell(40,10,sprintf("Cycle de %d jours du %s au %s", $nb_jours, human_date($result["cycle_debut"]), human_date($result["cycle_fin"])));
		$pdf->Ln();
		$pdf->Ln();

		$i = 1;
		$s = -1;
		foreach ($cycle as $line){
			$pdf->SetFont('Courier','',8);
			$pdf->SetTextColor(150,150,150);
			$pdf->Cell(8,5,$i, 0, 0, 'C');
			$pdf->SetFont('Courier','',10);
			$pdf->SetTextColor(0,0,0);
			if($line["gommette"]==".")	$pdf->SetFillColor(172,36,51);
			elseif($line["gommette"]=="I")	$pdf->SetFillColor(30,130,76);
			elseif($line["gommette"]=="?")	$pdf->SetFillColor(220,220,220);
			elseif($line["gommette"]=="=")	$pdf->SetFillColor(251,202,11);
			else $pdf->SetFillColor(255,255,255);
			if ($line["gommette"]==":)") {
				$pdf->SetTextColor(75,119,190);
				$pdf->Cell(5,5,$line["gommette"],0,0,'C', true);
				$pdf->SetTextColor(0,0,0);
			}
			elseif ($line["gommette"]=="?") $pdf->Cell(5,5,$line["gommette"],0,0,'C', true);
			else $pdf->Cell(5,5,"",0,0,'C', true);
			if(intval($line["unions"])) {
				$pdf->SetFont("ZapfDingbats");	
				$pdf->SetTextColor(172,36,51);
				$pdf->Cell(4,5,chr(164)); // <3
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			else $pdf->Cell(4,5,"");
			if(intval($line["sommet"]) || $s>0) {
				$pdf->SetFont("ZapfDingbats");	
				$pdf->SetTextColor(139,69,19);
				if(intval($line["sommet"])) {
					$pdf->Cell(8,5,chr(115).chr(115)); // /\/\
					$s = 1;
				}
				elseif ($s<=3) {
					$pdf->Cell(3,5,chr(115)); // /\
					$pdf->SetFont('Courier','',10);
					$pdf->Cell(5,5,"+". $s); 
					$s += 1;
				}
				else $s = -1;
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			if (!empty($line["sensation"])){
				$pdf->SetFont('Courier','',10);
				$w = $pdf->GetStringWidth(utf8_decode($line["sensation"]))+1;
				$pdf->Cell($w,5,utf8_decode($line["sensation"]));
				$pdf->SetFont('Courier','',10);
			}
			$pdf->SetFont('Courier','I',7);
			$pdf->Cell(10,5,utf8_decode($line["commentaire"]?? ""));
			$pdf->SetTextColor(100,100,100);
		 	$pdf->Text($pdf->GetPageWidth()-35,$pdf->GetY()+4,human_date(date_parse($line["date_obs"])));
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('Courier','',10);
			$pdf->Ln();
			$pdf->SetY($pdf->GetY()+0.5);
			$i += 1;
		}

		$pdf->Output('I', 'moncycle_app_'. human_date($date, '_') . '.pdf');
	
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
	print(json_encode($result));
}
