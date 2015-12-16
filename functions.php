<?php

// D/nsh
$dnsiStr = 'Δ/νση Π.Ε. Ηρακλείου';
$dnsiStrShort = 'ΔΙ.Π.Ε. Ηρακλείου';
$dnsiLink = 'http://dipe.ira.sch.gr/site';
// Espa column headers
// ESPA csv is a mess, with duplicate column names, that's why we keep field numbers in this array...
$hdr = Array (
   'Α/Α' => 0, 'Ονοματεπώνυμο' => 1,'ΕΙΔΟΣ ΑΠΑΣΧ.' => 2, 'ΥΠΟΧ. ΩΡΑΡ.' => 3, 'ΔΕ' => 4, 'ΩΡΕΣ ΑΝΑ ΕΒΔ.' => 5, 'ΗΜΕΡΕΣ' => 6, 'ΚΩΔΙΚ.' => 7, 'ΑΦΜ' => 8, 'Μ.Κ.(BM)' => 9,
   'ΕΙΔ. ΑΠ.' => 10, 'ΗΜ.ΑΣΦ.' => 11, 'Μ.Κ.(ΠΟΣΟ)' => 12, 'Ο.Ε.' => 13, 'ΕΠΠ' => 14, 'ΣΥΝΟΛΟ(ΑΠ)' => 15, 'ΦΟΡΟΣ' => 16, 'ΕΚΤΑΚΤΗ ΕΙΣΦΟΡΑ' => 17,
   'ΟΑΕΔ' => 18, 'ΙΚΑ' => 19, 'ΕΡΓΑΖΟΜ.' => 20, 'ΕΡΓΟΔΟΤΗ' => 21, 'ΣΥΝΟΛΟ(ΙΚΑ)' => 22, 'ΤΑΜΕΙΟ' => 23, 'ΕΡΓΑΖΟΜ.(ΤΑΜ)' => 24, 'ΕΡΓΟΔΟΤΗ(ΤΑΜ)' => 25, 'ΣΥΝΟΛΟ(ΤΑΜ)' => 26,
   'ΑΧΡΕΩΣΤ.' => 27, 'ΑΦ.ΠΟΣΟ' => 28, 'ΕΠΙΔ.ΕΡΓ.' => 29, 'ΑΠΟΖ.ΙΚΑ' => 30, 'ΚΑΘΑΡΑ' => 31, 'ΜΕ ΑΠ.ΤΑΜ.' => 32, 'ΜΕ ΑΠ.ΤΑΜ. 2' => 33, 'ΕΝΑΝΤΙ' => 34, 'ΔΙΑΦΟΡΑ' => 35
);

/**
 * Greek string to uppercase
 * Retrieved from: https://github.com/vdw/Greek-string-to-uppercase
 * Correctly converts greek letters to uppercase.
 */
function grstrtoupper($string) {
		$latin_check = '/[\x{0030}-\x{007f}]/u';
		if (preg_match($latin_check, $string))
		{
			$string = strtoupper($string);
		}
		$letters  								= array('α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω');
		$letters_accent 						= array('ά', 'έ', 'ή', 'ί', 'ό', 'ύ', 'ώ');
		$letters_upper_accent 					= array('Ά', 'Έ', 'Ή', 'Ί', 'Ό', 'Ύ', 'Ώ');
		$letters_upper_solvents 				= array('ϊ', 'ϋ');
		$letters_other 							= array('ς');
		$letters_to_uppercase					= array('Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω');
		$letters_accent_to_uppercase 			= array('Α', 'Ε', 'Η', 'Ι', 'Ο', 'Υ', 'Ω');
		$letters_upper_accent_to_uppercase 		= array('Α', 'Ε', 'Η', 'Ι', 'Ο', 'Υ', 'Ω');
		$letters_upper_solvents_to_uppercase 	= array('Ι', 'Υ');
		$letters_other_to_uppercase 			= array('Σ');
		$lowercase = array_merge($letters, $letters_accent, $letters_upper_accent, $letters_upper_solvents, $letters_other);
		$uppercase = array_merge($letters_to_uppercase, $letters_accent_to_uppercase, $letters_upper_accent_to_uppercase, $letters_upper_solvents_to_uppercase, $letters_other_to_uppercase);
		$uppecase_string = str_replace($lowercase, $uppercase, $string);
		return $uppecase_string;
}

// Parse CSV file and find employee
// uses https://github.com/parsecsv/parsecsv-for-php
function parseFind($csvFile, $afm, $surname){
     // init vars
     $empOffset = 5;
     $anadrData = [];
     // parse csv & find employee
     $csv = new parseCSV();
     $csv->encoding('iso8859-7','UTF-8');
     $csv->delimiter = ";";
     $csv->heading = false;
     // find employee T.M.
     $csv->offset = $empOffset;
     $condition = '8 is '.$afm.' AND 1 contains '.grstrtoupper($surname);
     $csv->conditions = $condition;
     $csv->parse($csvFile);
     $parsed = $csv->data;

     // find month
     $csv->offset = 1;
     $csv->conditions = '19 contains ΜΙΣΘΟΔΟΣΙΑ';
     $csv->parse($csvFile);
     //$csv->fields =[19];
     $data = $csv->data;
     $tmp = explode(' ',$data[0][19]);
     $month = $tmp[2] . '_' . $tmp[3];

     // find anadromika (if any)
     $csv->offset = $empOffset;
     $csv->conditions = '';
     $csv->parse($csvFile);
     $data = $csv->data;
     $i = $foundFrom = $foundTo = 0;
     foreach ($data as $row) {
       if (array_key_exists('8',$row) && $afm == $row[8] && !$foundFrom) {
         $foundFrom = $i;
       }
       if ($foundFrom && !$foundTo && array_key_exists('8',$row)){
         if ($row[8] != '' && $row[8] != $afm) {
           $foundTo = $i-1;
         }
       }
       $i++;
     }
     $tempData = array_slice($data, $foundFrom, $foundTo-$foundFrom+1);
     foreach ($tempData as $line) {
       if ($line[10] == 'ΑΝΑΔΡΟΜΙΚΑ')
        $anadrData = $line;
     }
     if (count($anadrData)>0)
        array_push($parsed, $anadrData);

    return ['parsed' => $parsed, 'month' => $month];
}

// filterCol: used for csv numbers.
// Returns proper float type, by replacing , (comma) with . (dot)
function filterCol($ar,$hdr,$ind) {
     return preg_replace("/[^-0-9\.]/",".",str_replace('.','',$ar[$hdr[$ind]]));
}

// Render a table for the given record ($rec) based on the header array ($hdr)
function renderTable($rec, $hdr, $isAnadr = 0) {
   ob_start();
   ?>
   <div class="row">
     <!-- personal -->
     <?php if (!$isAnadr): ?>
      <table class="table table-bordered table-hover table-condensed table-responsive csv-results">
               <thead>
                 <tr>
                  <th colspan=4 class="info">Προσωπικά Στοιχεία</th>
                 </tr>
               </thead>
               <tbody>
                 <tr>
                  <td>Ονοματεπώνυμο</td>
                  <td><?= $rec[$hdr['Ονοματεπώνυμο']]; ?></td>
                  <td >ΑΦΜ</td>
                  <td><?= $rec[$hdr['ΑΦΜ']]; ?></td>
                 </tr>
                 <tr>
                  <td>Βαθμός-ΜΚ</td>
                  <td><?= $rec[$hdr['Μ.Κ.(BM)']]; ?></td>
                  <td></td>
                  <td></td>
                  </tr>
                  <tr>
                     <td>Είδος Απασχόλησης</td>
                     <td><?= $rec[$hdr['ΕΙΔ. ΑΠ.']]; ?></td>
                     <td>Ημέρες</td>
                     <td><?= $rec[$hdr['ΗΜ.ΑΣΦ.']] ? $rec[$hdr['ΗΜ.ΑΣΦ.']] : ''; ?></td>
                 </tr>
               </tbody>
         </table>
       <?php endif; ?>
         <!-- TM -->
         <table class="table table-bordered table-hover table-condensed table-responsive csv-results">
                 <thead>
                  <tr>
                     <th colspan=4 class="info">Τακτική μισθοδοσία</th>
                  </tr>
                  <tr>
                     <th>A/A</th>
                     <th>Τύπος</th>
                     <th>Ποσό</th>
                  </tr>
                 </thead>
                 <tbody>
                  <tr>
                     <td>1</td>
                     <td>Τακτική Μισθοδοσία</td>
                     <td><?= filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΑΠ)') ?></td>
                  </tr>
                  <?php
                     if (filterCol($rec,$hdr,'Ο.Ε.')>0):
                   ?>
                  <tr>
                     <td>2</td>
                     <td>Οικογενειακό επίδομα</td>
                     <td><?= filterCol($rec,$hdr,'Ο.Ε.') ?></td>
                  </tr>
               <?php endif; ?>
                 </tbody>
         </table>
         <!-- Asfalistika -->
         <table class="table table-bordered table-hover table-condensed table-responsive csv-results">
                 <thead>
                  <tr>
                     <th colspan=4 class="info"><?= $rec[$hdr['ΙΚΑ']]; ?></th>
                  </tr>
                  <tr>
                     <th>A/A</th>
                     <th>Τύπος</th>
                     <th>Ποσό</th>
                  </tr>
                 </thead>
                 <tbody>
                  <tr>
                     <td>1</td>
                     <td>Ασφαλιστικές Εισφορές</td>
                     <td><?= filterCol($rec,$hdr,'ΕΡΓΑΖΟΜ.') ?></td>
                  </tr>
                  <tr>
                     <td>2</td>
                     <td>Εργοδοτικές εισφορές</td>
                     <td><?= filterCol($rec,$hdr,'ΕΡΓΟΔΟΤΗ') ?></td>
                  </tr>
                  <?php
                  // if other TAMEIO
                  if (strlen($rec[$hdr['ΤΑΜΕΙΟ']])>0):
                     $tameio = 1;
                  ?>
                     <tr>
                        <td colspan=4><?= $rec[$hdr['ΤΑΜΕΙΟ']]; ?></td>
                     </tr>
                     <tr>
                        <td>3</td>
                        <td>Ασφαλιστικές Εισφορές</td>
                        <td><?= filterCol($rec,$hdr,'ΕΡΓΑΖΟΜ.(ΤΑΜ)') ?></td>
                     </tr>
                     <tr>
                        <td>4</td>
                        <td>Εργοδοτικές εισφορές</td>
                        <td><?= filterCol($rec,$hdr,'ΕΡΓΟΔΟΤΗ(ΤΑΜ)') ?></td>
                     </tr>
                  <?php endif;
                  ?>
                  <tr>
                     <td colspan = 4>OAEΔ</td>
                  </tr>
                  <tr>
                     <td></td>
                     <td>Υπέρ ΟΑΕΔ</td>
                     <td><?= filterCol($rec,$hdr,'ΟΑΕΔ') ?></td>
                  </tr>
                  <tr>
                     <td colspan=2>ΣΥΝΟΛΟ</td>
                     <td><?= filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΙΚΑ)')+ filterCol($rec,$hdr,'ΟΑΕΔ')+filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΤΑΜ)') ?></td>
                  </tr>
                  <tr><td colspan=4></td></tr>
                  <tr class="info">
                     <td colspan=4><h4><strong>Σύνολα</strong></h4></td>
                  </tr>
                  <tr>
                     <td colspan=2>Σύνολο Αποδοχών</td>
                     <td><?= filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΑΠ)') ?></td>
                  </tr>
                  <tr>
                     <td colspan=2>Σύνολο Ασφαλιστικών Εισφορών</td>
                     <td><?= filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΙΚΑ)')+ filterCol($rec,$hdr,'ΟΑΕΔ')+filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΤΑΜ)') ?></td>
                  </tr>
                  <tr>
                     <td colspan=2>Φόρος</td>
                     <td><?= filterCol($rec,$hdr,'ΦΟΡΟΣ') ?></td>
                  </tr>
                  <tr class="success">
                     <td colspan=2>Καθαρά στο Δικαιούχο</td>
                     <td><?= filterCol($rec,$hdr,'ΚΑΘΑΡΑ') ?></td>
                  </tr>
                 </tbody>
         </table>
   </div> <!-- of row-->
<?php
  $synolo_ap = filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΑΠ)');
  $synolo_asf = filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΙΚΑ)')+ filterCol($rec,$hdr,'ΟΑΕΔ')+filterCol($rec,$hdr,'ΣΥΝΟΛΟ(ΤΑΜ)');
  $synolo_for = filterCol($rec,$hdr,'ΦΟΡΟΣ');
  $synolo_kath = filterCol($rec,$hdr,'ΚΑΘΑΡΑ');
   $ret = ob_get_contents();
   ob_end_clean();
   return ['out'=>$ret, 'apod'=>$synolo_ap, 'asfal'=>$synolo_asf, 'foros'=> $synolo_for, 'kath'=>$synolo_kath];
} // of function

// Render a table containing grand totals
function renderSynola($apod, $asfal, $foros, $kath) {
  ob_start();
  ?>
  <div class="row">
    <table class="table table-bordered table-hover table-condensed table-responsive csv-results">
      <thead>
        <th class="info" colspan=2>
          <h3>Γενικά Σύνολα</h3>
        </th>
      </thead>
      <tbody>
        <tr><td>Αποδοχές</td><td><?= sprintf("%.2f",$apod) ?></td></tr>
        <tr><td>Ασφαλιστικές Εισφορές</td><td><?= sprintf("%.2f",$asfal) ?></td></tr>
        <tr><td>Φόρος</td><td><?= sprintf("%.2f",$foros) ?></td></tr>
        <tr class="success"><td><strong>Καθαρά</strong></td><td><strong><?= sprintf("%.2f",$kath) ?></strong></td></tr>
      </tbody>
    </table>
  </div>
  <?php
  $ret = ob_get_contents();
  ob_end_clean();
  return $ret;
}

 ?>