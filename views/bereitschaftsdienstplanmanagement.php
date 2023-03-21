<?php

function bereitschaftsdienstplan_funktionsbuttons_management($Month,$Year){

    $FORMhtml = '<div class="row">';
    $FORMhtml .= "<div class='col'>".form_dropdown_months('month',$Month)."</div>";
    $FORMhtml .= "<div class='col'>".form_dropdown_years('year', $Year)."</div>";
    $FORMhtml .= "<div class='col'>".form_group_continue_return_buttons(true, 'Reset', 'reset_calendar', 'btn-primary', true, 'Zeitraum wählen', 'action_change_date', 'btn-primary')."</div>";
    $FORMhtml .= "</div>";

    $HTML = container_builder(form_builder($FORMhtml, 'self', 'POST'));

    return $HTML;

}

function bereitschaftsdienstplan_table_management($Month,$Year){

    //Initialze & fetch stuff
    $mysqli = connect_db();
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
    $FirstDayOfSelectedMonthString = $Year."-".$Month."-01";
    $FirstDayOfSelectedMonth = strtotime($FirstDayOfSelectedMonthString);

    // Initialize Table
    $Table = "<table class='table table-bordered table-sm table-condensed'>";

    //Setup Table Header
    $TableHeader = "<thead><tr>";
    $TableHeader .= "<th>Datum</th>";
    foreach ($AllBDTypes as $BDType) {
        $TableHeader .= "<th>".$BDType['kuerzel']."</th>";
    }
    $TableHeader .= "</tr></thead>";

    //Populate Table rows
    $TableRows = "";
    $Tabindex = 0;
    for($a=0;$a<=30;$a++){
        $CommandDate = "+" . $a . " days";
        $DateConcerned = strtotime($CommandDate, $FirstDayOfSelectedMonth);
        if(date("m", $DateConcerned)==$Month) {
            $Populate = populate_day_bd_plan_management($DateConcerned, $AllBDTypes, $AllBDmatrixes, $Tabindex);
            $Tabindex = $Populate['tabindex'];
            $TableRows .= $Populate['HTML'];
        }
    }

    //Setup Table Body
    $TableBody = '<tbody class="table-group-divider">'.$TableRows.'</tbody>';

    // Build that calendar
    $Table .= $TableHeader;
    $Table .= $TableBody;
    $Table .= "</table>";
    $Table .= "<script>
  $(function () {
    $('.popper').popover({
        placement: 'bottom',
        container: 'body',
        html: true,
        content: function () {
            return $(this).next('.popper-content').html();
        }
    })
})

  </script>";

    return $Table;

}

function populate_day_bd_plan_management($DateConcerned, $AllBDTypes, $AllBDmatrixes, $Tabindex){

    if(day_is_a_weekend_or_holiday($DateConcerned)){
        $Holidayweekend = true;
        $searchedDayType = 'weekend';
    } else {
        $Holidayweekend = false;
        $searchedDayType = 'normal';
    }

    $Matrix = [];
    foreach ($AllBDmatrixes as $BDmatrix){
        if($BDmatrix['type_of_day']==$searchedDayType){
            $Matrix = $BDmatrix;
        }
    }

    //Deconstruct BD Matrix
    $MatrixUnpacked = explode(',', $Matrix['matrix']);

    $Row = "<tr>";
    if($Holidayweekend){
        $Row .= "<td class='table-secondary'>".date('d.m.Y', $DateConcerned)."</td>";
    } else {
        $Row .= "<td>".date('d.m.Y', $DateConcerned)."</td>";
    }

    foreach ($AllBDTypes as $BDType) {

        foreach ($MatrixUnpacked as $item){

            $Exploded = explode(':', $item);

            if($Exploded[0]==$BDType['id']){

                if($Exploded[1]==0){
                    $Row .= "<td class='text-center align-middle table-secondary'></td>";
                } else {
                    $Row .= "<td class='text-center align-middle table-success'>".build_modal_popup_bd_planung($Tabindex, $DateConcerned)."</td>";
                    $Tabindex++;
                }
            }
        }
    }

    $Row .= "<tr>";

    $ReturnVals['HTML'] = $Row;
    $ReturnVals['tabindex'] = $Tabindex;

    return $ReturnVals;
}

function build_modal_popup_bd_planung($Tabindex, $DateConcerned){

    if($Tabindex % 2 == 0){

        if(time()>$DateConcerned){
            $buildPopup = '<a class="disabled">
  unbesetzt
</a>';
        } else {
            $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">
  besetzen
</a>';
        }

    } elseif($Tabindex % 7 == 0) {
        $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">
  Heinzelmann, M.
</a>';
    } elseif($Tabindex % 3 == 0) {
        $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">
  Smith, A.
</a>';
    } else {
        $buildPopup = '<a class="" data-bs-toggle="modal" data-bs-target="#myModal'.$Tabindex.'">
  Mustermann, M.
</a>';
    }



    $buildPopup .= '<!-- The Modal -->
<div class="modal fade" id="myModal'.$Tabindex.'">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Dienst besetzen</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        Mockup der Auswahltabelle
		<table class="table">
			<thead><th></th><th>Name</th><th>Verfügbarkeit</th><th>Kommentar</th></thead>
			<tbody>
			<tr class="table-success"><td><input type="checkbox" class="form-check-input" id="exampleCheck1"></td><td>Dampf, Hans</td><td>Gewünscht</td><td>Bitte Nachtdienst</td></tr>
			<tr><td><input type="checkbox" class="form-check-input" id="exampleCheck1"></td><td>Mustermann, Max</td><td>Verfügbar</td><td></td></tr>
			<tr class="table-danger"><td><input type="checkbox" class="form-check-input" id="exampleCheck1"></td><td>Müller, Florian</td><td>Ausschluss</td><td>Bin auf einer Hochzeit eingeladen...</td></tr>
			</tbody>
		</table>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
	  	<button type="button" class="btn btn-primary" name="action_add_bd_zuteilung">Zuteilen</button>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Schließen</button>
      </div>

    </div>
  </div>
</div>';

    return $buildPopup;
}

function table_bereitschaftsdienstplan_user($mysqli,$CurrentUser,$SelectedMonth='',$SelectedYear=''){

    // deal with stupid "" and '' problems
    $bla = '"{"key": "value"}"';
    $Einteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $BDtypen = get_list_of_all_bd_types($mysqli);
    #$Wuensche = get_sorted_list_of_all_dienstplanwünsche($mysqli, false);
    #$Wunschtypen = get_list_of_all_dienstplanwunsch_types($mysqli);

    if(($SelectedMonth=='') && ($SelectedYear=='')){
        $BeginDate = "01-".date('m-Y');
    } else {
        $BeginDate = "01-".$SelectedMonth."-".$SelectedYear;
    }
    $EndDate = date("Y-m-t", strtotime($BeginDate));

    // Initialize Table
    $HTML = '<table data-toggle="table" 
data-locale="de-DE"
data-show-columns="true" 
  data-multiple-select-row="true"
  data-click-to-select="true"
  data-pagination="true">';

    // Setup Table Head
    $HTML .= '<thead>
                <tr class="tr-class-1">
                    <th data-field="day" data-sortable="true">Tag</th>
                    <th data-field="type" data-sortable="true">Diensttyp</th>
                    <th>Optionen</th>
                </tr>
              </thead>';

    // Fill table body
    $HTML .= '<tbody>';
    $counter = 1;
    foreach ($Einteilungen as $Einteilung){

        if($Einteilung['user'] == $CurrentUser){
            if(($Einteilung['day']>=$BeginDate) && ($Einteilung['day']<=$EndDate)){
                // Build rows
                if($counter==1){
                    $HTML .= '<tr id="tr-id-1" class="tr-class-1" data-title="bootstrap table" data-object='.$bla.'>';
                } else {
                    $HTML .= '<tr id="tr-id-'.$counter.'" class="tr-class-'.$counter.'">';
                }


                // Build edit/delete Buttons
                $Options = '';

                $DienstType = get_bereitschaftsdiensttype_details_by_type_id($BDtypen, $Einteilung['bd_type']);

                if($counter==1){
                    $HTML .= '<td id="td-id-1" class="td-class-1" data-title="bootstrap table">'.$Einteilung['day'].'</td>';
                    $HTML .= '<td>'.$DienstType['name'].'</td>';
                    $HTML .= '<td> '.$Options.'</td>';
                } else {
                    $HTML .= '<td id="td-id-'.$counter.'" class="td-class-'.$counter.'"">'.$Einteilung['day'].'</td>';
                    $HTML .= '<td>'.$DienstType['name'].'</td>';
                    $HTML .= '<td> '.$Options.'</td>';
                }

                // close row and count up
                $HTML .= "</tr>";
                $counter++;
            }
        }

    }
    $HTML .= '</tbody>';
    $HTML .= '</table>';

    return $HTML;

}