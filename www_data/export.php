<?php

require_once "config.php";
require_once "lib/date.php";
require_once "lib/db.php";
require_once "fpdf/fpdf.php";

session_start();

try {

	$db = db_open();

	// VERIFICATION DE LA BONNE OUVERTURE DE LA SESSION
	if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
		print("Vous devez etre connecte pour realiser cette action.");
		exit;
	}

	// LECTURE D'UNE DATE DE DEBUT DE CYCLE
	if (isset($_GET['cycle'])) {
		$date = new DateTime($_GET['cycle']);
		$result["date"] = date_sql($date);
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
	$result["cycle_debut"] = new DateTime(db_select_cycle($db, date_sql($date), $_SESSION["no"])[0]["cycle"]);
	$cycle_end = db_select_cycle_end($db, date_sql($date), $_SESSION["no"]);
	if (isset($cycle_end[0]["cycle_end"])) {
		$date_tmp = new DateTime($cycle_end[0]["cycle_end"]);
		$date_tmp->modify('-1 day');
		$result["cycle_fin"] = $date_tmp;
	}
	else $result["cycle_fin"] = new DateTime();

	// RECUPERATION DU CYCLE
	$data = db_select_cycle_complet($db, date_sql($result["cycle_debut"]),date_sql($result["cycle_fin"]), $_SESSION["no"]);

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

	if ($_GET['type'] == "csv") {

		// ECRITURE DU CSV
		header("content-type:application/csv;charset=UTF-8");
		header('Content-Disposition: attachment; filename="moncycle_app_'. date_sql($date) .'.csv"');
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
		$pdf->SetTitle('bill_cycle_'. date_humain($date, '_') . '.pdf');
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
		$pdf->Cell(40,10,sprintf("Cycle de %d jours du %s au %s", $nb_jours, date_humain($result["cycle_debut"]), date_humain($result["cycle_fin"])));
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
		 	$pdf->Text($pdf->GetPageWidth()-35,$pdf->GetY()+4,date_humain(new DateTime($line["date_obs"])));
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('Courier','',10);
			$pdf->Ln();
			$pdf->SetY($pdf->GetY()+0.5);
			$i += 1;
		}

		$pdf->Output('I', 'moncycle_app_'. date_humain($date, '_') . '.pdf');
	
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
	print(json_encode($result));
}
