<?php

function dashboard_view_abwesenheiten_user($mysqli, $userID, $Nutzergruppen){

    $AbwesenheitenTable = table_abwesenheiten_user($mysqli, $Nutzergruppen, $DashboardMode=true);
    return card_builder('Abwesenheiten dieses Jahr', '', $AbwesenheitenTable, true);

}

function dashboard_view_bereitschaftsdienstplan_user($mysqli, $userID){

    return '<div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Bereitschaftsdienste</h5>
        <p class="card-text">Hier folgt eine Auflistung geplanter Dienste diesen Monat...</p>
      </div>
    </div>
  </div>';

}