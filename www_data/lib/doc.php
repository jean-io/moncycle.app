<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

use Fpdf\Fpdf;

function doc_preparation_jours_pour_affichage($data, $nfp_method){
	$cycle = [];
	$date_cursor = new DateTime($data[0]["date_obs"]);
	$today = new DateTime();
	$empty_line = array("date_obs" => '', "cycle_1st_day" => "", "?" => '1',"stamp" => '',"sensation" => '',"sommet" => '', "counter_start" => '',"unions" => '', "pregnancy" => 0,"comment" => '');
	if ($nfp_method == 1 || $nfp_method == 4) {
		$empty_line["temperature"] = '';
		$empty_line["time_temp_taken"] = '';
	}
	if ($nfp_method == 3 || $nfp_method == 4) {
		$empty_line["fc_score"] = '';
		$empty_line["fc_arrow"] = '';
		unset($empty_line["sensation"]);
	}
	foreach ($data as $line){
		while ($date_cursor->format('Y-m-d') !== trim($line["date_obs"]) && $date_cursor < $today) {
			$empty_line["date_obs"] = $date_cursor->format('Y-m-d');
			$cycle[] = $empty_line;
			$date_cursor->modify('+1 day');
		}
		if ($line["cycle_1st_day"]) $line["cycle_1st_day"] = 1;
		else $line["cycle_1st_day"] = "";
		if ($nfp_method != 1 && $nfp_method != 4) unset($line["temperature"]);
		if ($nfp_method != 3 && $nfp_method != 4) {
			unset($line["fc_score"]);
			unset($line["fc_arrow"]);
		}
		$empty_line["date_obs"] = $date_cursor->format('Y-m-d');
		if ($nfp_method != 1 && $nfp_method != 2) unset($line["sensation"]);
		if ($line["pregnancy"]) {
			$comment = $line["comment"];
			$line = $empty_line;
			$line["pregnancy"] = 1;
			$line["?"] = 0;
			$line["comment"] = $comment;
		}
		else {
			if (($nfp_method == 1 || $nfp_method == 4) && empty(trim($line["stamp"])) && empty(trim($line["temperature"]))) $line = $empty_line;
			if (($nfp_method == 2 || $nfp_method == 3) && empty(trim($line["stamp"]))) $line = $empty_line;
		}
		array_push($cycle, $line);
		$date_cursor->modify('+1 day');
	}
	return $cycle;
}

function doc_parse_fc_note ($str_fc_note) {
	$fc_note = [
		'10DL' => false,
		'10SL' => false,
		'10WL' => false,
		'RAP' => false,
		'LAP' => false, 
		'X1' => false,
		'X2' => false,
		'X3' => false,
		'AD' => false,
		'AP' => false,
		'VL' => false,
		'VH' => false,
		'2W' => false,
		'10' => false,
		'H' => false,
		'M' => false,
		'L' => false,
		'Lsaignement' => false,
		'B' => false,
		'0' => false,
		'2' => false,
		'4' => false,
		'6' => false,
		'8' => false,
		'C' => false,
		'G' => false,
		'K' => false,
		'P' => false,
		'Y' => false,
		'R' => false
	];
	$str_fc_note = trim($str_fc_note);
	if (strlen($str_fc_note)>0) {
		$str_fc_note = strtoupper($str_fc_note);
		if (str_starts_with($str_fc_note, 'L') && !str_starts_with($str_fc_note, 'LAP')) {
			$fc_note['Lsaignement'] = true;
			$str_fc_note = substr($str_fc_note, 1);
		}
		foreach ($fc_note as $note => $is_present) {
			if (str_contains($str_fc_note, $note)) $fc_note[$note] = true;
			$str_fc_note = str_ireplace($note, '', $str_fc_note);
		}
		$str_fc_note = trim($str_fc_note);
	}
	$fc_note['extra'] = strlen($str_fc_note)>0;
	$fc_note['extra_str'] = $str_fc_note;
	return $fc_note;
}

function doc_txt($txt) {
	if (is_null($txt)) return '';
	$encoding = mb_detect_encoding($txt, "auto");
	if (!$encoding) return '';
	$txt = mb_convert_encoding($txt, 'windows-1252', $encoding);
	if ($txt) return $txt;
	return '';
}

function doc_get_initials($string) {
	$string = trim($string);
	$words = explode(' ', $string);
	$initials = '';
	foreach ($words as $word) {
		if (!empty($word)) {
			$initials .= strtoupper($word[0]);
		}
	}
	return $initials;
}

function doc_cycle_vers_csv ($out, $cycle, $nfp_method) {
	$i = 1;
	fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	$csv_partition = [];
	if ($nfp_method == 1) $csv_partition = ["date_obs","cycle_1st_day","?","stamp", "temperature", "time_temp_taken", "sensation", "sommet", "counter_start", "unions", "pregnancy", "comment"];
	if ($nfp_method == 2) $csv_partition = ["date_obs","cycle_1st_day","?","stamp", "sensation", "sommet", "counter_start", "unions", "pregnancy", "comment"];
	if ($nfp_method == 3) $csv_partition = ["date_obs","cycle_1st_day","?","fc_score","fc_arrow","stamp", "sommet", "unions", "pregnancy", "comment"];
	if ($nfp_method == 4) $csv_partition = ["date_obs","cycle_1st_day","?","fc_score","fc_arrow","stamp", "temperature", "time_temp_taken", "sommet", "unions", "pregnancy", "comment"];
	fputcsv($out,array_merge(["no"], $csv_partition), CSV_SEP);
	foreach ($cycle as $line){
		fputs($out, $i);
		fputs($out, CSV_SEP);
		foreach($csv_partition as $key) {
			if (isset($line[$key])) fputs($out, $line[$key]);
			fputs($out, CSV_SEP);
		}
		fputs($out, PHP_EOL);
		$i += 1;
	}
}

function doc_cycle_bill_vers_pdf ($cycle, $nfp_method, $name, $pdf_anonymous=false) {
	$week_days = ["D", "L", "M", "M", "J", "V", "S"];
	
	$pdf = new Fpdf('P','mm','A4');
	$pdf->SetTitle('MONCYCLE.APP tableau du '. date_humain(new Datetime($cycle[0]["date_obs"])));
	$pdf->AddPage();
	$pdf->SetFont('Courier','B',12);
	if ($pdf_anonymous) $name = doc_get_initials($name) . " (anonyme)";
	$pdf->Cell($pdf->GetPageWidth()-35,10,doc_txt($name), 0, 0, 'C');
	$pdf->SetFont('Courier','',10);
	$pdf->Ln();
	if ($pdf_anonymous)$pdf->Cell($pdf->GetPageWidth()-35,5,sprintf("Tableau de %d jours", count($cycle)), 0, 0, 'C');
	else $pdf->Cell($pdf->GetPageWidth()-35,5,sprintf("Tableau de %d jours du %s au %s", count($cycle), date_humain(new Datetime($cycle[0]["date_obs"])), date_humain(new Datetime(end($cycle)["date_obs"]))), 0, 0, 'C');
	$pdf->Ln();
	$pdf->SetTextColor(30, 130, 76);
	$pdf->Link($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth()-25, 6, "https://www.moncycle.app");
	$pdf->Cell($pdf->GetPageWidth()-35,5,"MONCYCLE.APP", 0, 0, 'C');
	$pdf->SetTextColor(0,0,0);
	$pdf->Ln();
	$pdf->Ln();
	
	$temp_max = 0;
	$temp_mini = 100;
	if ($nfp_method==1) {
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
	
	$i = -1;
	$s = -1;
	$counter_start_n = 100;
	$counter_start_max = -1;
	$prev_temp_x = 0;
	$prev_temp_y = 0;
	$com_long = false;
	$col = 0;
	$top_y = $top_y = $pdf->GetY();
	foreach ($cycle as $line){
		if (boolval($line["cycle_1st_day"])) $i = 1;
		if ($col == 0) $col = 1;
		elseif($pdf->GetPageHeight()-$pdf->GetY()<=30 || boolval($line["cycle_1st_day"])){
			$prev_temp_x = 0;
			$prev_temp_y = 0;
			if ($nfp_method == 2 && $col == 1) {
				$col = 2;
				$pdf->SetXY($pdf->GetPageWidth()/2,$top_y);
			}
			else {
				$pdf->AddPage();
				$top_y = $pdf->GetY();
				$col = 1;
			}
		}
		elseif (!$com_long) $pdf->Ln();
		if ($col == 2) $pdf->SetX($pdf->GetPageWidth()/2);
		$com_long = false;
		$date_obs = new DateTime($line["date_obs"]);
		$date_obs_human = date_humain_week_day($date_obs, $week_days);
		if (intval(date_format($date_obs, 'w'))==0) $pdf->SetFont('Courier','B',6);
		else $pdf->SetFont('Courier','',6);
		if ($pdf_anonymous) $date_obs_human = $week_days[date_format($date_obs, 'w')];
		if (boolval($line["cycle_1st_day"])) {
			$pdf->SetFillColor(0,0,0);
			$pdf->SetDrawColor(0,0,0);
			$pdf->SetTextColor(255,255,255);
			$pdf->Cell(11, 5, $date_obs_human, 1, 0, 'R', true);
			$pdf->SetFont('Courier','',8);
			$pdf->Cell(8,5,"1erJ", 1, 0, 'C', true);
		}
		else {
			$pdf->SetTextColor(200,200,200);
			if (intval(date_format($date_obs, 'w'))==0) $pdf->SetTextColor(145,145,145);
			$pdf->Cell(11, 5, $date_obs_human, 0, 0, 'R');
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('Courier','',8);
			$pdf->Cell(8,5,$i>0 ? $i : "?", 0, 0, 'C');
		}
		if ($line["pregnancy"]) {
			$pdf->SetTextColor(130, 21, 33);
			$pdf->SetFillColor(255, 236, 238);
			$pdf->SetDrawColor(255, 236, 238);
			$pdf->SetFont('Courier','',12);
			$pdf->Cell($pdf->GetStringWidth("GROSSESSE")+5,5,"GROSSESSE", 1, 0, 'C', true);
			$com_debut_x = $pdf->GetX();
		}
		else {
			$pdf->SetX($pdf->GetX()+0.5);
			$pdf->SetFont('Courier','',10);
			$pdf->SetTextColor(0,0,0);
			if (isset($line["stamp"]) && !boolval($line["?"])) {
				if(str_contains($line["stamp"], "R")) {
					$pdf->SetFillColor(172,36,51);
					$pdf->SetDrawColor(172,36,51);
				}
				elseif(str_contains($line["stamp"], "G")){
					$pdf->SetFillColor(30,130,76);
					$pdf->SetDrawColor(30,130,76);
				}
				elseif(str_contains($line["stamp"], "?")){
					$pdf->SetFillColor(220,220,220);
					$pdf->SetDrawColor(220,220,220);
				}
				elseif(str_contains($line["stamp"], "Y")) {
					$pdf->SetFillColor(251,202,11);
					$pdf->SetDrawColor(251,202,11);
				}
				else {
					$pdf->SetFillColor(255,255,255);
					$pdf->SetDrawColor(255,255,255);
				}
				if ($line["stamp"] == "BB") {
					$pdf->SetTextColor(30, 130, 76);
					$pdf->SetDrawColor(220,220,220);
					$xx = $pdf->GetX();
					$yy = $pdf->GetY();
					$pdf->Cell(5,5,"",1,0,'C', true);
					$pdf->Image("../img/baby.png", $xx+0.25, $yy+0.25, 4.5, 4.5);
					$pdf->SetTextColor(0,0,0);
				}
				elseif (str_contains($line["stamp"], "BB")) {
					$pdf->SetTextColor(255,255,255);
					$xx = $pdf->GetX();
					$yy = $pdf->GetY();
					$pdf->Cell(5,5,"",1,0,'C', true);
					$pdf->Image("../img/baby.png", $xx+0.25, $yy+0.25, 4.5, 4.5);
					$pdf->SetTextColor(0,0,0);
				}
				else $pdf->Cell(5,5,"",1,0,'C', true);
			}
			if (boolval($line["?"])) {
				$pdf->SetFont('Arial','I',8);
				$pdf->SetTextColor(100,100,100);
				$pdf->Cell($pdf->GetStringWidth(doc_txt("jour non observé "))+0.25,5,doc_txt("jour non observé "));
				$pdf->SetFont('Courier','',10);
				$pdf->SetTextColor(0,0,0);
			}
			if(intval($line["sommet"]) || $s>0) {
				$pdf->SetTextColor(139,69,19);
				if(intval($line["sommet"])) {
					$pdf->SetFont("ZapfDingbats");
					$pdf->Cell(5,5,chr(115)); // /\/\
					$s = 1;
				}
				elseif ($s<=3) {
					$pdf->SetFont('Courier','',10);
					$pdf->Cell(5,5,"+". $s); 
					$s += 1;
				}
				else $s = -1;
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			if(intval($line["counter_start"]) || $counter_start_n>0) {
				$pdf->SetTextColor(30,130,76);
				if(intval($line["counter_start"])) {
					$counter_start_n = 1;
					$counter_start_max = $line["counter_start"];
				}
				if ($counter_start_n<=$counter_start_max) {
					$pdf->SetFont('Courier','',10);
					$pdf->Cell(5,5,"+". $counter_start_n); 
					$counter_start_n += 1;
				}
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			if (isset($line["sensation"]) && !empty($line["sensation"]) && !boolval($line["?"])){
				$pdf->SetFont('Arial','',8.5);
				$w = $pdf->GetStringWidth(doc_txt($line["sensation"]))+1;
				$pdf->Cell($w,5,doc_txt($line["sensation"]));
				$pdf->SetFont('Courier','',10);
			}
			if(intval($line["unions"])) {
				$pdf->SetTextColor(172,36,51);
				$pdf->SetFont("ZapfDingbats");	
				$pdf->Cell(4,5,chr(164)); // <3
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('Courier','',10);
			}
			$com_debut_x = $pdf->GetX();
			$com_fin_x = -1;	
			if (isset($line["temperature"]) && !empty($line["temperature"]) && !boolval($line["?"])) {
				$temp = floatval($line["temperature"]);
				$largeur = 70;
				$disptemp = $temp;
				$pdf->SetX($pdf->GetPageWidth()/2-12);
				$pdf->SetFont('Courier','',9);
				$pdf->SetTextColor(135, 67, 176);
				$w = strval($temp) . doc_txt("°");
				if ($line["time_temp_taken"]) $w .= doc_txt(" à ") .  str_replace(':', 'h', substr($line["time_temp_taken"],0,-3));
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
				if ($col == 2 || $nfp_method ==1) $com_fin_x = $pdf->GetPageWidth();
				else $com_fin_x = $pdf->GetPageWidth()/2;
			}
		}
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('Courier','',10);
		$pdf->SetY($pdf->GetY()+0.5);
		if (isset($line["comment"]) && $line["comment"]) {
			$pdf->SetFont('Arial','I',7);
			$w = $pdf->GetStringWidth(doc_txt($line["comment"]));
			if ($w < ($com_fin_x-$com_debut_x)) {
				$pdf->SetX($com_debut_x);
				$pdf->Cell($w,5,doc_txt($line["comment"]));
			}
			else {
				$pdf->Ln();
				if ($col == 2) $pdf->SetX($pdf->GetPageWidth()/2);
				$pdf->SetX($pdf->GetX());
				$pdf->MultiCell($pdf->GetPageWidth()/2-11,3,doc_txt($line["comment"]));
				$com_long = true;
			}
			$pdf->SetFont('Courier','',10);
		}
		if ($i>0) $i += 1;
	}
	return $pdf;
}


function doc_cycle_fc_vers_pdf($cycle, $nfp_method, $name, $pdf_anonymous=false) {
	$week_days = ["D", "L", "M", "M", "J", "V", "S"];
	$first_col_width = 16;
	$nb_days_per_line = 35;
	$nb_lines_per_page = 8;
	$top_margin = 10;
	$left_margin = 10;
	$line_height = 3.5;
	$stamp_height = 7.3;
	$color_coef = 1.1;
	$comment_max_char_per_line = 15;
	$grid_gray = 60;
	
	$symbol_convert_table = [
		"" => ["", 255, 255, 255],
		"?" => ["???", 210, 210, 210],
		"R" => ['R', 190, 0, 4],
		"G" => ['V', 45, 102, 23],
		"Y" => ['J', 255, 255, 9],
		"BB" => ['BBB', 255, 255, 255],
		'RBB' => ['BBR', 190, 0, 0],
		'GBB' => ['BBV', 130, 187, 106],
		'YBB' => ['BBJ', 255, 255, 9],
		'Gi' => ['G', 255, 236, 238] // TODO changer G pour grossesse (confondu avec Green)
	];
	
	if ($pdf_anonymous) $name = doc_get_initials($name);
	
	if ($nfp_method == 4) $nb_lines_per_page -= 1;
	
	$h_start_date = date_humain(new Datetime($cycle[0]["date_obs"]));
	$h_end_date = date_humain(new Datetime(end($cycle)["date_obs"]));
	$h_current_date = date_humain(new Datetime());
	
	$pdf = new Fpdf('L','mm','A3');
	$pdf->SetMargins($left_margin, $top_margin);
	$pdf->SetTitle('MONCYCLE.APP tableau du '. date_humain(new Datetime($cycle[0]["date_obs"])));
	
	
	$cell_width = ($pdf->GetPageWidth()-$first_col_width-2*$left_margin)/$nb_days_per_line;
	$img_seize = min($cell_width, $stamp_height)-2;
	
	
	// CALCUL DU NB DE PAGE
	$nb_line = 1;
	$current_line_size = 0;
	foreach ($cycle as $index => $day) {
		if ($index>0 && (boolval($day["cycle_1st_day"] ?? false) || $current_line_size > $nb_days_per_line)) {
			$nb_line += 1;
			$current_line_size = 0;
		}
		$current_line_size +=1;
	}
	$total_nb_page = ceil($nb_line/$nb_lines_per_page);
	
	// counter_start DE CELLLES
	$num_cell = 0;
	
	// BOUCLE PAGE PAR PAGE
	for ($page_no=1; $page_no <= $total_nb_page; $page_no++) { 
		
		$pdf->AddPage();
		
		$pdf->SetDrawColor($grid_gray,$grid_gray,$grid_gray);
		
		$page_title = "";
		if ($pdf_anonymous) $page_title .= " (anonyme)";
		else $page_title .= doc_txt(" - observations du $h_start_date au $h_end_date");
		$page_title .= doc_txt(" - document créé le $h_current_date - page $page_no sur $total_nb_page - ");
		$pdf->SetFont('Courier','B',10);
		$pdf->Cell($pdf->GetStringWidth(doc_txt($name)),$line_height, doc_txt($name), 0, 0, 'L');
		$pdf->SetFont('Courier','',10);
		$pdf->Cell($pdf->GetStringWidth($page_title),$line_height, $page_title, 0, 0, 'L');
		$pdf->SetTextColor(30, 130, 76);
		$pdf->SetFont('Courier','B',10);
		$pdf->Cell($pdf->GetStringWidth(" MONCYCLE.APP"),$line_height, "MONCYCLE.APP", 0, 0, 'L', false, "https://www.moncycle.app/");
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Courier','',8);
		$pdf->Ln();
		
		$pdf->SetY($pdf->GetY()+2);
		
		$pdf->SetFillColor(220,220,220);
		
		$pdf->Cell($first_col_width,$line_height,doc_txt(""), "LTR", 0, 'L');
		for ($j=0; $j < $nb_days_per_line; $j++) { 
			$pdf->Cell($cell_width,$line_height,doc_txt($j+1), "TR", 0, 'C', True);
		}
		
		// BOUCLE LIGNE PAR LIGNE
		for ($ligne=0; $ligne < $nb_lines_per_page; $ligne++) { 
			
			$pdf->Ln();
			
			// LIGNE DE SEPARATION ENTRE LES CELLULES
			$pdf->SetFillColor($grid_gray,$grid_gray,$grid_gray);
			$pdf->Cell($pdf->GetPageWidth()-2*$left_margin,0.25, '', 'TLBR', 0, '', true);
			
			$pdf->Ln();
			
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			
			// 1ERE CELLULE / LEGENDE
			$pdf->SetFont('Courier','',6);
			$pdf->SetDrawColor(255/$color_coef,255/$color_coef,255/$color_coef);
			$pdf->Cell($first_col_width,$line_height,doc_txt("TAMPON"), "B", 0, 'R');
			$pdf->Ln();
			$pdf->Cell($first_col_width,$stamp_height,doc_txt(""), "B", 0, 'R');
			$pdf->Ln();
			$pdf->Cell($first_col_width,$line_height,doc_txt("PIC"), "B", 0, 'R');
			$pdf->Ln();
			$pdf->Cell($first_col_width,$line_height,doc_txt("DATE"), "B", 0, 'R');
			$pdf->Ln();
			$pdf->Cell($first_col_width,$line_height,doc_txt("SAIGNEMENT"), "B", 0, 'R');
			$pdf->Ln();
			$pdf->Cell($first_col_width,$line_height,doc_txt("GLAIRE"), "B", 0, 'R');
			$pdf->Ln();
			$pdf->Cell($first_col_width,$line_height,doc_txt("AUTRE INFO"), "B", 0, 'R');
			$pdf->Ln();
			if ($nfp_method == 4) {
				$pdf->Cell($first_col_width,$line_height,doc_txt("TEMPERATURE"), "B", 0, 'R');
				$pdf->Ln();
			}
			$pdf->Cell($first_col_width,$line_height,doc_txt("COMMENTAIRE"), "", 0, 'R');
			$pdf->Ln();
			
			$pdf->SetXY($x+$first_col_width, $y);
			$x = $pdf->GetX();
			
			$peak = -1;
			
			// BOUCLE CELLULE PAR CELLULE
			for ($j=0; $j < $nb_days_per_line; $j++) {
				
				$first_day_of_cycle = boolval($cycle[$num_cell]["cycle_1st_day"] ?? false);
				$obs_index = $num_cell;
				
				if (isset($cycle[$obs_index]["pregnancy"]) && $cycle[$obs_index]["pregnancy"]) $pdf->SetTextColor(130, 21, 33);
				else $pdf->SetTextColor(0, 0, 0);
				
				if ($first_day_of_cycle && $num_cell>0 && $j>0) $obs_index = null;
				
				$obs_forgotten = boolval($cycle[$num_cell]["?"] ?? false);
				if ($obs_forgotten) $cycle[$obs_index]["stamp"] = '?';
				$cell_carac = $symbol_convert_table[$cycle[$obs_index]["stamp"] ?? ""];
				if (isset($cycle[$obs_index]["pregnancy"]) && $cycle[$obs_index]["pregnancy"]) $cell_carac =  $symbol_convert_table["G"];
				$peak_text = "";
				$date_exploded = explode('-', $cycle[$obs_index]["date_obs"] ?? "");
				$fc_score = doc_parse_fc_note($cycle[$obs_index]["fc_score"] ?? "");
				
				$fc_saignement = '';
				foreach (['H', 'M', 'Lsaignement', 'VL', 'VH', 'B'] as $s) {
					if ($fc_score[$s] && $s == 'Lsaignement') $fc_saignement .= 'L';
					else $fc_score[$s] ? $fc_saignement .= $s : null;
				}
				
				$fc_glaire = '';
				foreach (['0', '2', '2W', '4', '6', '8', '10', '10DL', '10SL', '10WL', 'C', 'G', 'K', 'P', 'Y', 'R', 'L', 'X1', 'X2', 'X3', 'AD'] as $s) $fc_score[$s] ? $fc_glaire .= $s : null;
				$fc_glaire = str_ireplace('X', '*', $fc_glaire);
				
				$arraow = $cycle[$obs_index]["fc_arrow"] ?? '';
				$all_arraows = array("↑" => chr(173), "↓" => chr(175), "→" => chr(174), "←" => chr(172), "" => '');
				
				$fc_tiers = '';
				foreach (['AP', 'RAP', 'LAP'] as $s) if ($fc_score[$s]) $fc_tiers .= $s;
				
				if ($obs_forgotten) {
					$fc_saignement = '';
					$fc_glaire = '';
					$arraow = '';
					$fc_tiers = '';
				}
				
				if (strlen($fc_tiers)>0) $fc_tiers .= ' ';
				if (boolval($cycle[$obs_index]["unions"] ?? false)) $fc_tiers .= "G";
				
				$pdf->SetDrawColor($cell_carac[1]/$color_coef,$cell_carac[2]/$color_coef,$cell_carac[3]/$color_coef);
				
				if (boolval($cycle[$obs_index]["sommet"] ?? 0)) {
					$peak = 0;
					$peak_text = 'PIC';
				}
				elseif ($peak >= 0) {
					$peak += 1;
					$peak_text = $peak;
				}
				
				$comment_width = $pdf->GetStringWidth(doc_txt($cycle[$obs_index]["comment"] ?? ""))+1;
				
				// CELLULE TAMPON
				$pdf->SetFont('Courier','',6);
				$pdf->SetFillColor($cell_carac[1],$cell_carac[2],$cell_carac[3]);
				$pdf->SetXY($x+$cell_width*$j, $y);
				$pdf->Cell($cell_width,$line_height,doc_txt($cell_carac[0]), 'B', 0, 'C', true);
				$pdf->SetFont('Courier','',8);
				$pdf->SetXY($x+$cell_width*$j, $y+$line_height);
				
				// CELLULE baby
				$pdf->SetFont('Courier','',5);
				$xx = $pdf->GetX();
				$yy = $pdf->GetY();
				$text = "";
				if (isset($cycle[$obs_index]["pregnancy"]) && $cycle[$obs_index]["pregnancy"]) $text = "GROSSESSE";
				$pdf->Cell($cell_width,$stamp_height,doc_txt($text), 'B', 0, 'C', true);
				if (str_contains($cell_carac[0], 'BB')) $pdf->Image("../img/baby.png", $xx+$img_seize/2, $yy+1, $img_seize, $img_seize);
				$pdf->SetXY($x+$cell_width*$j, $y+$line_height+$stamp_height);
				
				// CELLULE PIC
				$pdf->SetFont('Courier','',6);
				$pdf->Cell($cell_width,$line_height,doc_txt(isset($date_exploded[2]) ? $peak_text : ''), 'B', 0, 'C', true);
				$pdf->SetXY($x+$cell_width*$j, $y+$line_height*2+$stamp_height);
				$pdf->SetDrawColor(255/$color_coef,255/$color_coef,255/$color_coef);
				
				// CELLULE DATE
				$date_obs = new DateTime($cycle[$obs_index]["date_obs"] ?? "");
				$date_txt = ($date_exploded[2] ?? "") . (isset($date_exploded[2]) ? '/' : '') . ($date_exploded[1] ?? "");
				if (strlen($date_txt)>0) {
					if ($pdf_anonymous) $date_txt = $week_days[date_format($date_obs, 'w')];
					else $date_txt = $week_days[date_format($date_obs, 'w')] . " " . $date_txt;
				}
				if (intval(date_format($date_obs, 'w'))==0) $pdf->SetFont('Courier','B',6);
				else $pdf->SetFont('Courier','',6);
				$pdf->Cell($cell_width,$line_height,doc_txt($date_txt), 'B', 0, 'C');
				$pdf->SetXY($x+$cell_width*$j, $y+$line_height*3+$stamp_height);
				$pdf->SetFont('Courier','',6);
				
				// CELLULE SAIGNEMENT
				$pdf->Cell($cell_width,$line_height,doc_txt($fc_saignement), 'B', 0, 'C');
				
				// CELLULE GLAIRE
				$pdf->SetXY($x+$cell_width*$j, $y+$line_height*4+$stamp_height);
				$pdf->Cell($cell_width,$line_height,doc_txt($fc_glaire), 'B', 0, 'C');
				
				// CELLULE INFO TIERS
				if (strlen($fc_tiers)>0 && strlen($arraow)>0) {
					$pdf->SetFont("Symbol");
					$pdf->SetXY($x+$cell_width*$j, $y+$line_height*5+$stamp_height);
					$pdf->Cell($cell_width/4,$line_height,$all_arraows[$arraow], 'B', 0, 'R');
					$pdf->SetFont("Courier");
					$pdf->Cell(($cell_width/4)*3,$line_height,doc_txt($fc_tiers), 'B', 0, 'L');
				}
				elseif (strlen($fc_tiers)>0 && strlen($arraow)<=0) {
					$pdf->SetXY($x+$cell_width*$j, $y+$line_height*5+$stamp_height);
					$pdf->Cell($cell_width,$line_height,doc_txt($fc_tiers), 'B', 0, 'C');
				}
				elseif (strlen($fc_tiers)<=0 && strlen($arraow)>0) {
					$pdf->SetFont("Symbol");
					$pdf->SetXY($x+$cell_width*$j, $y+$line_height*5+$stamp_height);
					$pdf->Cell($cell_width,$line_height,$all_arraows[$arraow], 'B', 0, 'C');
				}
				else {
					$pdf->SetXY($x+$cell_width*$j, $y+$line_height*5+$stamp_height);
					$pdf->Cell($cell_width,$line_height,'', 'B', 0, 'C');
				}
				
				// CELLULE TEMPERATURE
				if ($nfp_method == 4) {
					$pdf->SetXY($x+$cell_width*$j, $y+$line_height*6+$stamp_height);
					$pdf->SetFont('Courier','',3.8);
					$temp = "";
					if (isset($cycle[$obs_index]["temperature"]) && !empty($cycle[$obs_index]["temperature"])) $temp = $cycle[$obs_index]["temperature"];
					if (isset($cycle[$obs_index]["time_temp_taken"]) && !empty($cycle[$obs_index]["time_temp_taken"])) $temp .= " à " . substr($cycle[$obs_index]["time_temp_taken"], 0, 5);
					$pdf->Cell($cell_width,$line_height,doc_txt($temp), 'B', 0, 'C');
				}
				
				// CELLULE COMMENTAIRE
				$pdf->SetXY($x+$cell_width*$j, $y+$line_height*(6 + intval($nfp_method == 4))+$stamp_height);
				$pdf->SetFont('Courier','',3);
				$xx = $pdf->GetX();
				$yy = $pdf->GetY();
				$comment = trim($cycle[$obs_index]["comment"] ?? "");
				if (strlen($comment)<$comment_max_char_per_line/2) {
					$pdf->SetFont('Courier','',6);
					$pdf->Cell($cell_width,$line_height,doc_txt($comment), 0, 0, 'L');
				}
				else if (strlen($comment)<$comment_max_char_per_line) {
					$pdf->Cell($cell_width,$line_height,doc_txt($comment), 0, 0, 'L');
				}
				else if (strlen($comment)<$comment_max_char_per_line*2) {
					$pdf->MultiCell($cell_width,$line_height/3,doc_txt($comment), 0, 'L');
				}
				else {
					$comment = substr($comment, 0, $comment_max_char_per_line*5);
					$pdf->MultiCell($cell_width,$line_height/5,doc_txt($comment), 0, 'L');
				}
				$pdf->SetXY($xx+$cell_width, $yy);
				$pdf->SetDrawColor($grid_gray,$grid_gray,$grid_gray);
				
				if (!is_null($obs_index)) $num_cell += 1;
			}
			
			// LIGNE DE SEPARATION ENTRE LES CELLULES
			$pdf->SetDrawColor($grid_gray,$grid_gray,$grid_gray);
			$pdf->Line($x-$first_col_width,$y,$x-$first_col_width,$pdf->GetY()+$line_height);
			$pdf->Line($x,$y,$x,$pdf->GetY()+$line_height);
			for ($j=0; $j < $nb_days_per_line; $j++) {
				$pdf->Line($x+$cell_width*$j+$cell_width,$y,$x+$cell_width*$j+$cell_width,$pdf->GetY()+$line_height);
			}
			
		}
		
		// LA DERNIERE LIGNE
		$pdf->Ln();
		$pdf->SetFillColor($grid_gray,$grid_gray,$grid_gray);
		$pdf->Cell($pdf->GetPageWidth()-2*$left_margin,0.25, '', 'TLBR', 0, '', true);
		
		
	}
	
	return $pdf;
}
