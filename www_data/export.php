<?php

require_once "config.php";
require_once "lib/date.php";
require_once "lib/db.php";
require_once "fpdf/fpdf.php";

session_start();

try {

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

	$db = db_open();
	$methode = $_SESSION["compte"]["methode"];

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
		while ($date_cursor->format('Y-m-d') != $line["date_obs"]) {
			if ($methode == 2) array_push($cycle, array("date_obs" => $date_cursor->format('Y-m-d'),"?" => '1',"gommette" => '',"sensation" => '',"sommet" => '',"unions" => '',"commentaire" => ''));
			elseif ($methode == 3) array_push($cycle, array("date_obs" => $date_cursor->format('Y-m-d'),"?" => '1',"temperature" => '',"sommet" => '',"unions" => '',"commentaire" => ''));
			else array_push($cycle, array("date_obs" => $date_cursor->format('Y-m-d'),"?" => '1',"gommette" => '',"temperature" => '',"sensation" => '',"sommet" => '',"unions" => '',"commentaire" => ''));
			$date_cursor->modify('+1 day');
		}
		if ($methode == 2) unset($line["temperature"]);
		elseif ($methode == 3) {
			unset($line["gommette"]);
			unset($line["sensation"]);
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
		$out = fopen('php://output', 'w');
		fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		if ($methode == 2) fputcsv($out,["jour","date","?","gommette","sensation","sommet", "unions", "commentaires"], CSV_SEP);
		elseif ($methode == 3) fputcsv($out,["jour","date","?","température","sommet", "unions", "commentaires"], CSV_SEP);
		else fputcsv($out,["jour","date","?","gommette","température","sensation","sommet", "unions", "commentaires"], CSV_SEP);
		foreach ($cycle as $line){
			fputcsv($out,array_merge([$i], $line), CSV_SEP);
			$i += 1;
		}
		fclose($out);
	}
	elseif ($_GET['type'] == "pdf") {
	
		$pdf = new FPDF('P','mm','A4');
		$pdf->SetTitle('bill_cycle_'. date_humain($date, '_') . '.pdf');
		$pdf->AddPage();
		$pdf->SetFont('Courier','B',16);
		//$pdf->Link($pdf->GetX(), $pdf->GetY(), $pdf->GetStringWidth("MONCYCLE.APP "), 10, "https://moncycle.app");
		//$pdf->Cell($pdf->GetStringWidth("MON"),10,"MON");
		//$pdf->SetTextColor(30, 130, 76);
		//$pdf->Cell($pdf->GetStringWidth("CYCLE"),10,"CYCLE");
		//$pdf->SetTextColor(0,0,0);
		//$pdf->Cell(0,10,sprintf(".APP %s", utf8_decode($_SESSION["compte"]["nom"])));
		$pdf->Cell($pdf->GetPageWidth()-35,10,utf8_decode($_SESSION["compte"]["nom"]), 0, 0, 'C');
		$pdf->SetFont('Courier','',10);
		$pdf->Ln();
		$pdf->Cell($pdf->GetPageWidth()-35,5,sprintf("Cycle de %d jours du %s au %s", $nb_jours, date_humain($result["cycle_debut"]), date_humain($result["cycle_fin"])), 0, 0, 'C');
		$pdf->Ln();
		$pdf->SetTextColor(30, 130, 76);
		$pdf->Link($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth()-25, 6, "https://www.moncycle.app");
		$pdf->Cell($pdf->GetPageWidth()-35,5,"suivi avec MONCYCLE.APP", 0, 0, 'C');
		$pdf->SetTextColor(0,0,0);
		$pdf->Ln();
		$pdf->Ln();

		$i = 1;
		$s = -1;
		$prev_temp_x = 0;
		$prev_temp_y = 0;
		foreach ($cycle as $line){
			$pdf->SetFont('Courier','',8);
			$pdf->SetTextColor(150,150,150);
			$pdf->Cell(8,5,$i, 0, 0, 'C');
			$pdf->SetFont('Courier','',10);
			$pdf->SetTextColor(0,0,0);
			if (isset($line["gommette"])) {
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
			}	
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
			if (isset($line["sensation"]) && !empty($line["sensation"])){
				$pdf->SetFont('Courier','',10);
				$w = $pdf->GetStringWidth(utf8_decode($line["sensation"]))+1;
				$pdf->Cell($w,5,utf8_decode($line["sensation"]));
				$pdf->SetFont('Courier','',10);
			}
			$pdf->SetFont('Courier','I',7);
			$pdf->Cell(10,5,utf8_decode($line["commentaire"]?? ""));
			$pdf->SetFont('Courier','',10);
			if (isset($line["temperature"]) && !empty($line["temperature"])) {
				$temp = floatval($line["temperature"]);
				$largeur = 65;
				$mini = 36;
				$maxi = 38;
				$disptemp = $temp;
				if ($temp>$maxi) $disptemp = $maxi; 
				if ($temp<$mini) $disptemp = $mini; 
				$pdf->SetX($pdf->GetPageWidth()/2-12);
				$pdf->SetFont('Courier','',9);
				$pdf->SetTextColor(135, 67, 176);
				$pdf->Cell(12,5,strval($temp) . utf8_decode("°"),0,0,'R');
				$pdf->SetFont('Courier','',10);
				$pdf->SetTextColor(0,0,0);
				$pdf->SetDrawColor(200,200,200);
				$pdf->SetX($pdf->GetPageWidth()/2);
				$pdf->Line($pdf->GetX(),$pdf->GetY()+2.5,$pdf->GetX()+$largeur,$pdf->GetY()+2.5);
				$trace = (($disptemp-$mini)/($maxi-$mini))*$largeur;
				$pdf->SetFillColor(135, 67, 176);
				$pdf->Rect($pdf->GetX()+$trace,$pdf->GetY()+2,1,1,"F");
				if ($prev_temp_x!=0 && $prev_temp_y!=0) {
					$pdf->SetDrawColor(135, 67, 176);
					$pdf->Line($prev_temp_x,$prev_temp_y,$pdf->GetX()+$trace+0.5,$pdf->GetY()+2.5);
				}
				$prev_temp_x = $pdf->GetX()+$trace+0.5;
				$prev_temp_y = $pdf->GetY()+2.5;
			}
			else {
				$prev_temp_x = 0;
				$prev_temp_y = 0;
			}
			$pdf->SetFont('Courier','I',7);
			$pdf->SetTextColor(100,100,100);
		 	$pdf->Text($pdf->GetPageWidth()-35,$pdf->GetY()+4,date_humain(new DateTime($line["date_obs"])));
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('Courier','',10);
			if($pdf->GetPageHeight()-$pdf->GetY()<=30){
				$pdf->AddPage();
				$prev_temp_x = 0;
				$prev_temp_y = 0;
			}
			else {
				$pdf->Ln();
				$pdf->SetY($pdf->GetY()+0.5);
			} 
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
