<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

function doc_ajout_jours_manquant($data, $methode){
	$nb_jours = 0;
	$cycle = [];
	$date_cursor = new DateTime($data[0]["date_obs"]);
	$empty_line = array("?" => '1',"gommette" => '',"sensation" => '',"sommet" => '',"unions" => '',"commentaire" => '');
	if ($methode == 1) $empty_line["temperature"] = '';
	if ($methode == 3) {
		$empty_line["note_fc"] = '';
		$empty_line["fleche_fc"] = '';
		unset($empty_line["sensation"]);
	}
	foreach ($data as $line){
		while ($date_cursor->format('Y-m-d') != $line["date_obs"]) {
			$empty_line["date_obs"] = $date_cursor->format('Y-m-d');
			array_push($cycle, $empty_line);
			$date_cursor->modify('+1 day');
		}
		if ($methode != 1) unset($line["temperature"]);
		if ($methode != 3) {
			unset($line["note_fc"]);
			unset($line["fleche_fc"]);
		}
		if ($methode == 3) unset($line["sensation"]);
		array_push($cycle, $line);
		$date_cursor->modify('+1 day');
		$nb_jours += 1;
	}
	return $cycle;
}


function doc_cycle_vers_csv ($out, $cycle, $methode) {
	$i = 1;
	fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	if ($methode == 2) fputcsv($out,["jour","date","?","gommette","sensation","sommet", "unions", "commentaires"], CSV_SEP);
	elseif ($methode == 3) fputcsv($out,["jour","date","?","température","sommet", "unions", "commentaires"], CSV_SEP);
	else fputcsv($out,["jour","date","?","gommette","température","sensation","sommet", "unions", "commentaires"], CSV_SEP);
	foreach ($cycle as $line){
		fputcsv($out,array_merge([$i], $line), CSV_SEP);
		$i += 1;
	}
}



function doc_cycle_vers_pdf ($cycle, $methode, $nom) {	
		$pdf = new FPDF('P','mm','A4');
		$pdf->SetTitle('bill_cycle_'. date_humain(new Datetime($cycle[0]["date_obs"]), '_') . '.pdf');
		$pdf->AddPage();
		$pdf->SetFont('Courier','B',16);
		$pdf->Cell($pdf->GetPageWidth()-35,10,utf8_decode($nom), 0, 0, 'C');
		$pdf->SetFont('Courier','',10);
		$pdf->Ln();
		$pdf->Cell($pdf->GetPageWidth()-35,5,sprintf("Cycle de %d jours du %s au %s", count($cycle), date_humain(new Datetime($cycle[0]["date_obs"])), date_humain(new Datetime(end($cycle)["date_obs"]))), 0, 0, 'C');
		$pdf->Ln();
		$pdf->SetTextColor(30, 130, 76);
		$pdf->Link($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth()-25, 6, "https://www.moncycle.app");
		$pdf->Cell($pdf->GetPageWidth()-35,5,"suivi avec MONCYCLE.APP", 0, 0, 'C');
		$pdf->SetTextColor(0,0,0);
		$pdf->Ln();

		$temp_max = 0;
		$temp_mini = 100;
		if ($methode==1) {
			foreach ($cycle as $line){
				if (isset($line["temperature"]) && !empty($line["temperature"])) {
					$temp = floatval($line["temperature"]);
					if ($temp>$temp_max) $temp_max = $temp;
					if ($temp<$temp_mini) $temp_mini = $temp;	
				}
			}
			if ($temp_max == $temp_mini) {
				$temp_max = 38;
				$temp_mini = 36;
			}
		}

		$i = 1;
		$s = -1;
		$prev_temp_x = 0;
		$prev_temp_y = 0;
		$com_long = false;
		foreach ($cycle as $line){
			if($pdf->GetPageHeight()-$pdf->GetY()<=30){
				$pdf->AddPage();
				$prev_temp_x = 0;
				$prev_temp_y = 0;
			}
			elseif (!$com_long) {
				$pdf->Ln();
			} 
			$com_long = false;
			$pdf->SetFont('Courier','',8);
			$pdf->SetTextColor(150,150,150);
			$pdf->Cell(8,5,$i, 0, 0, 'C');
			$pdf->SetFont('Courier','',10);
			$pdf->SetTextColor(0,0,0);
			if (isset($line["gommette"]) && !boolval($line["?"])) {
				if(str_contains($line["gommette"], ".")) {
					$pdf->SetFillColor(172,36,51);
					$pdf->SetDrawColor(172,36,51);
				}
				elseif(str_contains($line["gommette"], "I")){
					$pdf->SetFillColor(30,130,76);
					$pdf->SetDrawColor(30,130,76);
				}
				elseif(str_contains($line["gommette"], "?")){
					$pdf->SetFillColor(220,220,220);
					$pdf->SetDrawColor(220,220,220);
				}
				elseif(str_contains($line["gommette"], "=")) {
					$pdf->SetFillColor(251,202,11);
					$pdf->SetDrawColor(251,202,11);
				}
				else {
					$pdf->SetFillColor(255,255,255);
					$pdf->SetDrawColor(255,255,255);
				}
				if ($line["gommette"] == ":)") {
					$pdf->SetTextColor(30, 130, 76);
					$pdf->SetDrawColor(150,150,150);
					$pdf->Cell(5,5,"(:",1,0,'C', true);
					$pdf->SetTextColor(0,0,0);
				}
				elseif (str_contains($line["gommette"], ":)")) {
					$pdf->SetTextColor(255,255,255);
					$pdf->Cell(5,5,"(:",1,0,'C', true);
					$pdf->SetTextColor(0,0,0);
				}
				else $pdf->Cell(5,5,"",1,0,'C', true);
			}
			if (boolval($line["?"])) {
				$pdf->SetFont('Courier','I',8);
				$pdf->SetTextColor(100,100,100);
				$pdf->Cell($pdf->GetStringWidth("jour non observé"),5,utf8_decode("jour non observé"));
				$pdf->SetFont('Courier','',10);
				$pdf->SetTextColor(0,0,0);
			}
			if(intval($line["unions"])) {
				$pdf->SetTextColor(172,36,51);
				if ($methode == 3) { 
					$pdf->SetFont("Arial");	
					$pdf->Cell(4,5,"U");
				}
				else {
					$pdf->SetFont("ZapfDingbats");	
					$pdf->Cell(4,5,chr(164)); // <3
				}
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			else $pdf->Cell(4,5,"");
			if(intval($line["sommet"]) || $s>0) {
				$pdf->SetTextColor(139,69,19);
				if(intval($line["sommet"])) {
					if ($methode == 3) {
						$pdf->SetFont("Arial");	
						$pdf->Cell(4,5,"P");
					}
					else {
						$pdf->SetFont("ZapfDingbats");	
						$pdf->Cell(8,5,chr(115).chr(115)); // /\/\
					}
					$s = 1;
				}
				elseif ($s<=3) {
					if ($methode==3) $pdf->Cell(3,5,"p");
					else {
						$pdf->SetFont("ZapfDingbats");	
						$pdf->Cell(3,5,chr(115)); // /\
					}
					$pdf->SetFont('Courier','',10);
					$pdf->Cell(5,5,"+". $s); 
					$s += 1;
				}
				else $s = -1;
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			if ($methode==3 && isset($line["note_fc"]) && !empty($line["note_fc"])) {
				$pdf->SetFont('Arial','',10);
				$w = $pdf->GetStringWidth(utf8_decode($line["note_fc"]))+1;
				$pdf->Cell($w,5,utf8_decode($line["note_fc"]));
				$pdf->SetFont('Courier','',10);
			}
			if ($methode==3 && isset($line["fleche_fc"]) && !empty($line["fleche_fc"])) {
				$fleche = array("↑" => chr(173), "↓" => chr(175), "→" => chr(174), "←" => chr(172));
				$pdf->SetFont("Symbol");	
				$pdf->SetTextColor(135, 67, 176);
				$pdf->Cell(4,5,$fleche[$line["fleche_fc"]] ?? ""); // <3
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);

			}
			if ($methode !=3 && isset($line["sensation"]) && !empty($line["sensation"])){
				if ($methode==1) $pdf->SetFont('Courier','',8.5);
				else $pdf->SetFont('Courier','',10);
				$w = $pdf->GetStringWidth(utf8_decode($line["sensation"]))+1;
				$pdf->Cell($w,5,utf8_decode($line["sensation"]));
				$pdf->SetFont('Courier','',10);
			}
			$com_debut_x = $pdf->GetX();
			$com_fin_x = -1;	
			if (isset($line["temperature"]) && !empty($line["temperature"]) && !boolval($line["?"])) {
				$temp = floatval($line["temperature"]);
				$largeur = 65;
				$disptemp = $temp;
				$pdf->SetX($pdf->GetPageWidth()/2-12);
				$pdf->SetFont('Courier','',9);
				$pdf->SetTextColor(135, 67, 176);
				$w = strval($temp) . utf8_decode("°");
				$com_fin_x = $pdf->GetPageWidth()/2 - $pdf->GetStringWidth($w);
				$pdf->SetX($com_fin_x);
				$pdf->Cell($pdf->GetStringWidth($w),5,$w,0,0,'R');
				$pdf->SetFont('Courier','',10);
				$pdf->SetTextColor(0,0,0);
				$pdf->SetDrawColor(200,200,200);
				$pdf->SetX($pdf->GetPageWidth()/2);
				$pdf->Line($pdf->GetX(),$pdf->GetY()+2.5,$pdf->GetX()+$largeur,$pdf->GetY()+2.5);
				$trace = (($disptemp-$temp_mini)/($temp_max-$temp_mini))*$largeur;
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
			if ($com_fin_x<=0) {
				$com_fin_x = $pdf->GetPageWidth()-35;
			}
			$pdf->SetFont('Courier','I',7);
			$pdf->SetTextColor(100,100,100);
		 	$pdf->Text($pdf->GetPageWidth()-35,$pdf->GetY()+3.5,date_humain(new DateTime($line["date_obs"])));
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('Courier','',10);
			$pdf->SetY($pdf->GetY()+0.5);
			if (isset($line["commentaire"]) && $line["commentaire"]) {
				$pdf->SetFont('Arial','I',7);
				$w = $pdf->GetStringWidth(utf8_decode($line["commentaire"]));
				if ($w < ($com_fin_x-$com_debut_x)) {
					$pdf->SetX($com_debut_x);
					$pdf->Cell($w,5,utf8_decode($line["commentaire"]));
				}
				else {
					$pdf->Ln();
					$pdf->SetX($pdf->GetX()+16.5);
					$pdf->MultiCell($pdf->GetPageWidth()-50,3,utf8_decode($line["commentaire"]));
					$com_long = true;
				}
				$pdf->SetFont('Courier','',10);
			}
			$i += 1;
		}
		return $pdf;
	}


