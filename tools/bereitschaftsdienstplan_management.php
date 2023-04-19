<?php

function get_list_of_all_bd_types($mysqli, $ShowDeleted=false){

    $BDtypes = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM bereitschaftsdienst_typen ORDER BY rank, kuerzel ASC";
    }else{
        $sql = "SELECT * FROM bereitschaftsdienst_typen WHERE delete_time IS NULL ORDER BY reihung,kuerzel ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDtypes[] = $row;
        }
    }

    return $BDtypes;

}

function get_list_of_all_freiegegebene_bd_monate($mysqli){

    $BDtypes = [];
    $sql = "SELECT * FROM bereitschaftsdienstplan_freigeschaltete_monate WHERE delete_user IS NULL";

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDtypes[] = $row;
        }
    }

    return $BDtypes;

}

function bd_monat_freigeben($mysqli, $Month, $Year){

    $Antwort = [];
    $CurrentUser = get_current_user_id();
    $AllUsers = get_sorted_list_of_all_users($mysqli);
    $StatusMonat = lade_bd_freigabestatus_monat($Month, $Year);
    if(sizeof($StatusMonat)==0) {

        // Prepare statement & DB Access
        $sql = "INSERT INTO bereitschaftsdienstplan_freigeschaltete_monate (month, year, freigegeben_von) VALUES (?,?,?)";
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("iii", $Month, $Year, $CurrentUser);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Return success + new users ID + Users Password
                $Antwort['success'] = true;
                $Antwort['meldung'] = "Bereitschaftsdienstplan " . $Month . "-" . $Year . " erfolgreich freigegeben!";
            } else {
                $Antwort['success'] = false;
                $Antwort['meldung'] = "Fehler beim Datenbankzugriff";
            }

            // Close statement
            $stmt->close();
        }
    } else {
        $FreigabeUser = get_user_infos_by_id_from_list($StatusMonat[0]['freigegeben_von'], $AllUsers);
        $Antwort['success'] = false;
        $Antwort['meldung'] = "Der ausgewählte Monat wurde bereits am ".date('d.m.Y, G:i', strtotime($StatusMonat[0]['timestamp']))." Uhr von ".$FreigabeUser['vorname']." ".$FreigabeUser['nachname']." freigegeben!";
    }
    return $Antwort;

}

function bd_monat_freigabe_zuruecknehmen($mysqli, $Month, $Year, $DeleteComment = ""){

    $Antwort = [];
    $CurrentUser = get_current_user_id();
    $DeleteTime = date('Y-m-d G:i:s');

    // Prepare statement & DB Access
    $sql = "UPDATE bereitschaftsdienstplan_freigeschaltete_monate SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE month = ? AND year = ? AND delete_user IS NULL";
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issii", $CurrentUser, $DeleteTime, $DeleteComment, $Month, $Year);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success + new users ID + Users Password
            $Antwort['success']=true;
            $Antwort['meldung'] = "Freigabe für Bereitschaftsdienstplan ".$Month."-".$Year." zurückgenommen!";
        } else {
            $Antwort['success']=false;
            $Antwort['meldung']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }

    return $Antwort;

}

function get_list_of_all_bd_matrixes($mysqli, $ShowDeleted=false){

    $BDtypes = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM bereitschaftsdienst_matrix";
    }else{
        $sql = "SELECT * FROM bereitschaftsdienst_matrix";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDtypes[] = $row;
        }
    }

    return $BDtypes;

}

function get_bd_assignment_info($mysqli, $assignmentID){

    $sql = "SELECT * FROM user_bereitschaftsdienst_assignments WHERE id = ".$assignmentID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }
}

function get_users_with_bd_assignment_type($mysqli, $assignmentID){

    $sql = "SELECT * FROM user_bereitschaftsdienst_assignments WHERE bd_type = ".$assignmentID;
    if($stmt = $mysqli->query($sql)){
        return $stmt->fetch_assoc();
    }
}

function get_all_users_bd_assignments($mysqli, $includeDeleted=false){

    $BDeinteilungen = [];
    if($includeDeleted){
        $sql = "SELECT * FROM user_bereitschaftsdienst_assignments";
    } else {
        $sql = "SELECT * FROM user_bereitschaftsdienst_assignments WHERE delete_time IS NULL";
    }
    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDeinteilungen[] = $row;
        }
    }

    return $BDeinteilungen;
}

function get_list_of_users_bd_assignments_with_active_bd_type_on_certain_day($Type, $allAssignments, $Day){

    $List = [];

    foreach ($allAssignments as $Assignment){

        if ($Assignment['bd_type']==$Type){

            if((strtotime($Assignment['begin'])<=$Day) && (strtotime($Assignment['end'])>=$Day)){
                $List[] = $Assignment;
            }

        }

    }

    return $List;

}

function get_sorted_list_of_all_bd_einteilungen($mysqli, $ShowDeleted=false){
    $BDeinteilungen = [];

    if($ShowDeleted){
        $sql = "SELECT * FROM bereitschaftsdienstplan ORDER BY day ASC";
    }else{
        $sql = "SELECT * FROM bereitschaftsdienstplan WHERE delete_time IS NULL ORDER BY day ASC";
    }

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDeinteilungen[] = $row;
        }
    }

    return $BDeinteilungen;
}

function get_list_of_all_public_bd_plans($mysqli){

    $BDfreigegeben = [];

    $sql = "SELECT * FROM bereitschaftsdienstplan_freigeschaltete_monate ORDER BY year,month ASC";

    if($stmt = $mysqli->query($sql)){
        while ($row = $stmt->fetch_assoc()) {
            $BDfreigegeben[] = $row;
        }
    }

    return $BDfreigegeben;

}

function get_bereitschaftsdiensttype_details_by_type_id($Wunschtypen, $WunschTypeID){

    foreach ($Wunschtypen as $wunschtyp){
        if($wunschtyp['id']==$WunschTypeID){
            return $wunschtyp;
        }
    }

}

function get_bereitschaftsdienstwuenschetype_details_by_type_id($Wunschtypen, $WunschTypeID){

    foreach ($Wunschtypen as $wunschtyp){
        if($wunschtyp['id']==$WunschTypeID){
            return $wunschtyp;
        }
    }

}

function get_bereitschaftsdienst_einteilungen_on_day($day, $BDeinteilungen, $certainType=0){

    $counter = [];
    foreach ($BDeinteilungen as $BDeinteilung){

        if(strtotime($BDeinteilung['day'])==$day){

            if($certainType>0){
                if($BDeinteilung['bd_type'] == $certainType){
                    $counter[] = $BDeinteilung;
                }
            } else {
                $counter[] = $BDeinteilung;
            }

        }

    }

    return $counter;
}

function compare_lastname($a, $b)
{
    return strnatcmp($a['surname'], $b['surname']);
}

function compare_bd_type_rank($a, $b)
{
    return strnatcmp($a['reihung'], $b['reihung']);
}

function compare_bd_candidates_based_on_rank_and_bd_auslastung($a, $b)
{
    // sort by last name
    $retval = strnatcmp($a['highest_bd_rank'], $b['highest_bd_rank']);
    // if last names are identical, sort by first name
    if(!$retval) $retval = strnatcmp($a['dienstbelastung'], $b['dienstbelastung']);
    return $retval;
}

function get_users_highest_ranking_bd_assignments_on_certain_day($User, $AllBDassignments, $AllBDTypes, $DateConcerned){

    $List = [];

    //First get all Users Assignments
    foreach ($AllBDassignments as $Assignment){
        if($Assignment['user']==$User){
            if((strtotime($Assignment['begin'])<=$DateConcerned) && (strtotime($Assignment['end'])>=$DateConcerned)){
                $BDtypeInfo = get_bd_type_infos_from_list($Assignment['bd_type'], $AllBDTypes);
                $List[] = $BDtypeInfo;
            }
        }
    }

    //Now Sort by rank
    usort($List, 'compare_bd_type_rank');

    return $List;

}

function get_bd_type_infos_from_list($type, $AllBDTypes){

    foreach ($AllBDTypes as $AllBDType){
        if($AllBDType['id']==$type){
            return $AllBDType;
        }
    }

}

function calculate_users_dienstbelastung_points_on_given_day($DateConcerned, $UserConcerned, $AllBDeinteilungen, $WeeksPast=3, $WeeksFuture=3, $PenaltyWeekDay = 10, $PenaltyWeekend = 20){

    $Counter = 0;

    //Calculate SearchDates
    $FirstDayToConcider = date('Y-m-d', strtotime('- '.$WeeksPast.' weeks', $DateConcerned));
    $LastDayToConcider = date('Y-m-d', strtotime('+ '.$WeeksFuture.' weeks', $DateConcerned));

    //Now parse all Einteilungen
    foreach ($AllBDeinteilungen as $Einteilung){
        if($UserConcerned == $Einteilung['user']){

            //Check if Einteilung is within search-parameters
            if(($Einteilung['day']>=$FirstDayToConcider) && ($Einteilung['day']<=$LastDayToConcider)){

                //Check if Date is weekend or holiday
                if(day_is_a_weekend_or_holiday($DateConcerned)){
                    $Counter += $PenaltyWeekend;
                } else {
                    $Counter += $PenaltyWeekDay;
                }
            }
        }
    }

    return $Counter;

}

function parse_bd_candidates_on_day_for_certain_bd_type($DateConcerned, $BDType, $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes){

    $Answer = [];
    $GoodCandidateCounter = 0;
    $InfosBDType = get_bd_type_infos_from_list($BDType, $AllBDTypes);
    $RankCurrentBDType = $InfosBDType['reihung'];

    //1. Get Candidates based on assigned BD Group
    $firstListOfCandidates = get_list_of_users_bd_assignments_with_active_bd_type_on_certain_day($BDType, $AllBDassignments, $DateConcerned);

    //2. Build 3 sublists based on abwesenheiten, wishes and prior assignments
    // Array is built as follows: 'userID', 'userName', 'table-color', 'reason'
    $GreenList = [];
    $GreenListButTooHighBDrankList = [];
    $BlankList = [];
    $BlankButTooHighBDrankList = [];
    $RedList = [];

    foreach ($firstListOfCandidates as $firstListOfCandidate) {

        $CandidateInfos = [];
        $CandidatePersonalInfos = get_user_infos_by_id_from_list($firstListOfCandidate['user'], $AllUsers);
        if ($CandidatePersonalInfos != FALSE) {
            $CandidateInfos['userName'] = $CandidatePersonalInfos['nachname'].', '.$CandidatePersonalInfos['vorname'];
            $CandidateInfos['surname'] = $CandidatePersonalInfos['nachname'];
            $HRAssignments = get_users_highest_ranking_bd_assignments_on_certain_day($firstListOfCandidate['user'], $AllBDassignments, $AllBDTypes, $DateConcerned);
            $CandidateInfos['highest_bd_rank_kuerzel'] = $HRAssignments[0]['dienstgruppe'];
            $CandidateInfos['highest_bd_rank'] = $HRAssignments[0]['reihung'];
            $CandidateInfos['dienstbelastung'] = calculate_users_dienstbelastung_points_on_given_day($DateConcerned, $firstListOfCandidate['user'], $AllBDeinteilungen);
            $NeedsRed = false;
            $NeedsGreen = false;
            $ReasonNeedsRed = '';
            $ReasonNeedsGreen = '';
            $VerfuegbarkeitRed = '';

            //Check if User is unavailable due to Abwesenheit
            $AbwesenheitCheck = get_abwesenheit_existing_for_user_on_given_day($firstListOfCandidate['user'], $AllAbwesenheiten, $DateConcerned);
            if(sizeof($AbwesenheitCheck)>0){
                $NeedsRed = true;
                $VerfuegbarkeitRed = $AbwesenheitCheck['type'];
                $ReasonNeedsRed = $AbwesenheitCheck['create_comment'];
            }

            //Check if User is unavailable due to wish
            $NegativeWishCheck = get_negative_bd_wishes_user_on_certain_day($firstListOfCandidate['user'], $BDType, $Allwishes, $AllWishTypes, $AllBDTypes, $DateConcerned);
            if(sizeof($NegativeWishCheck)>0){
                $NegativeWishCheck = $NegativeWishCheck[0];
                $NeedsRed = true;
                $WishTypeDetails = get_wunschtype_details_by_type_id($AllWishTypes, $NegativeWishCheck['type']);
                $VerfuegbarkeitRed = $WishTypeDetails['name'];
                $ReasonNeedsRed = $NegativeWishCheck['create_comment'];
            }

            //Check if user already has a FZA today
            if(user_needs_fza_on_certain_date($firstListOfCandidate['user'], $AllBDeinteilungen, $AllBDTypes, $DateConcerned)){
                $NeedsRed = true;
                $VerfuegbarkeitRed = "FZA";
                $ReasonNeedsRed = "FZA";
            }

            //Check if User is already assigned somewhere else on same day
            $AllEinteilungenToday = get_bereitschaftsdienst_einteilungen_on_day($DateConcerned, $AllBDeinteilungen, 0);
            foreach ($AllEinteilungenToday as $EinteilungenToday){
                if($EinteilungenToday['user']==$firstListOfCandidate['user']){
                    $NeedsRed = true;
                    $VerfuegbarkeitRed = "Nicht verfügbar";
                    $ReasonNeedsRed = "Mitarbeiter/in an diesem Tag bereits eingeteilt";
                }
            }

            //Check if giving a user an assignment today will collide FZA with assignment tomorrow
            $InfosOnCurrentBDtype = get_bereitschaftsdiensttype_details_by_type_id($AllBDTypes, $BDType);
            if($InfosOnCurrentBDtype['req_fza']==1){
                $DateTomorrow = strtotime('+1 day',$DateConcerned);
                $AllEinteilungenTomorrow = get_bereitschaftsdienst_einteilungen_on_day($DateTomorrow, $AllBDeinteilungen, 0);
                foreach ($AllEinteilungenTomorrow as $AllEinteilungTomorrow){
                    if($AllEinteilungTomorrow['user']==$firstListOfCandidate['user']){
                        $NeedsRed = true;
                        $VerfuegbarkeitRed = "Nicht verfügbar";
                        $ReasonNeedsRed = "Am Folgetag bereits eingeteilt";
                    }
                }
            }

            if($NeedsRed){
                $CandidateInfos['userID'] = $firstListOfCandidate['user'];
                $CandidateInfos['table-color'] = 'table-danger';
                $CandidateInfos['reason'] = $ReasonNeedsRed;
                $CandidateInfos['verfuegbarkeit'] = $VerfuegbarkeitRed;
                $RedList[] = $CandidateInfos;
            } else {

                //Check for Green
                $PositiveWishCheck = get_positive_bd_wishes_user_on_certain_day($firstListOfCandidate['user'], $BDType, $Allwishes, $AllWishTypes, $AllBDTypes, $DateConcerned);
                if(sizeof($PositiveWishCheck)>0){
                    $PositiveWishCheck = $PositiveWishCheck[0];
                    $NeedsGreen = true;
                    $WishTypeDetails = get_wunschtype_details_by_type_id($AllWishTypes, $PositiveWishCheck['type']);
                    $ReasonNeedsGreen = $PositiveWishCheck['create_comment'];
                }

                if($NeedsGreen){
                    $CandidateInfos['userID'] = $firstListOfCandidate['user'];
                    $CandidateInfos['table-color'] = 'table-success';
                    $CandidateInfos['reason'] = $ReasonNeedsGreen;
                    $CandidateInfos['verfuegbarkeit'] = 'Gewünscht';
                    if($CandidateInfos['highest_bd_rank']>$RankCurrentBDType){
                        $GreenListButTooHighBDrankList[] = $CandidateInfos;
                    } else {
                        $GreenList[] = $CandidateInfos;
                    }
                    $GoodCandidateCounter++;
                } else {
                    $CandidateInfos['userID'] = $firstListOfCandidate['user'];
                    $CandidateInfos['table-color'] = '';
                    $CandidateInfos['reason'] = '';
                    $CandidateInfos['verfuegbarkeit'] = 'Verfügbar';
                    if($CandidateInfos['highest_bd_rank']<$RankCurrentBDType){
                        $BlankButTooHighBDrankList[] = $CandidateInfos;
                    } else {
                        $BlankList[] = $CandidateInfos;
                    }
                    $GoodCandidateCounter++;
                }
            }
        }
    }

    //Sort Lists by highest BD Assignments & past assignments - Red only by Name
    //Sort Red List
    usort($RedList, 'compare_lastname');

    //Sort Green & White List
    usort($GreenList,'compare_bd_candidates_based_on_rank_and_bd_auslastung');
    usort($BlankList,'compare_bd_candidates_based_on_rank_and_bd_auslastung');
    usort($GreenListButTooHighBDrankList,'compare_bd_candidates_based_on_rank_and_bd_auslastung');
    usort($BlankButTooHighBDrankList,'compare_bd_candidates_based_on_rank_and_bd_auslastung');


    $CombinedList = array_merge($GreenList,$GreenListButTooHighBDrankList,$BlankList,$BlankButTooHighBDrankList,$RedList);

    $Answer['num_found_candidates'] = $GoodCandidateCounter;
    $Answer['candidates'] = $CombinedList;
    $Answer['bad_candidates'] = $RedList;
    return $Answer;
}

function user_needs_fza_on_certain_date($userID, $AllBDeinteilungen, $AllBDTypes, $DateConcerned){

    $Antwort = false;

    foreach ($AllBDeinteilungen as $einteilung){

        if($einteilung['user']==$userID){

            $DienstType = get_bereitschaftsdiensttype_details_by_type_id($AllBDTypes, $einteilung['bd_type']);
            if($DienstType['req_fza']==1){

                if(strtotime('+1 day', strtotime($einteilung['day']))==$DateConcerned){

                    $Antwort = true;

                }

            }

        }

    }

    return $Antwort;
}

function add_bd_entry($mysqli, $User, $Dateconcerned, $BDtype, $comment=''){

    $UserInfos = get_current_user_infos($mysqli, $User);
    $CurrentUserID = get_current_user_id();
    $Date = date('Y-m-d', $Dateconcerned);

    //Load Stuff
    $AllBDTypes = get_list_of_all_bd_types($mysqli);
    $AllBDEinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
    $Allwishes = get_sorted_list_of_all_dienstplanwünsche($mysqli);
    $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
    $AllBDassignments = get_all_users_bd_assignments($mysqli);
    $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
    $AllUsers = get_sorted_list_of_all_users($mysqli);
    $Einteilungen = get_bereitschaftsdienst_einteilungen_on_day($Dateconcerned, $AllBDEinteilungen, $BDtype);

    $Antwort = [];
    $DAUcount = 0;
    $DAUerr = "";

    //Perform last-minute sanity-checks!
    if(!is_numeric($Dateconcerned)){
        $DAUcount++;
        $DAUerr .= "Falsches Datumsformat übermittelt. Bitte Vorgang nochmals probieren, ansonsten mit Entwickler in Verbindung setzen!";
    }

    if(empty($Dateconcerned)){
        $DAUcount++;
        $DAUerr .= "Kein/e Mitarbeiter/in ausgwählt. Bitte Vorgang nochmals probieren, ansonsten mit Entwickler in Verbindung setzen!";
    }

    if(user_needs_fza_on_certain_date($User, $AllBDEinteilungen, $AllBDTypes, $Dateconcerned)){
        $DAUcount++;
        $DAUerr .= $UserInfos['vorname']." ".$UserInfos['nachname']." hat am ".date('d.m.Y', $Dateconcerned)." FZA! Bitte ggf. die bisherigen Einteilungen bearbeiten!";
    }

    //Respect Size limits of BD Type
    $BDtypeInfos = get_bereitschaftsdiensttype_details_by_type_id($AllBDTypes, $BDtype);
    if(sizeof($Einteilungen)>=$BDtypeInfos['req_employees_per_day']){
        $DAUcount++;
        $DAUerr .= "Die maximale Anzahl an MitarbeiterInnen für diesen Bereitschaftsdiensttyp ist bereits erreicht!<br>";
    }

    $ParsedCandidates = parse_bd_candidates_on_day_for_certain_bd_type($Dateconcerned, $BDtype, $AllBDEinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes);
    $UserInRedlist = false;
    $ReasonRedList = "";
    foreach ($ParsedCandidates['bad_candidates'] as $parsedCandidate){
        if($parsedCandidate['userID']==$User){
            $UserInRedlist = true;
            if(empty($parsedCandidate['reason'])){
                $ReasonRedList = $parsedCandidate['verfuegbarkeit'];
            } else {
                $ReasonRedList = $parsedCandidate['verfuegbarkeit'].' - '.$parsedCandidate['reason'];
            }
        }
    }

    if($UserInRedlist){
        $DAUcount++;
        $DAUerr .= $UserInfos['vorname']." ".$UserInfos['nachname']." steht am ".date('d.m.Y', $Dateconcerned)." nicht zur Verfügung! Grund: ".$ReasonRedList;
    }

    if($DAUcount==0){
        // Prepare statement & DB Access
        $sql = "INSERT INTO bereitschaftsdienstplan (day, bd_type, user, create_user, create_comment) VALUES (?,?,?,?,?)";
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("siiis", $Date, $BDtype, $User, $CurrentUserID, $comment);

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Return success + new users ID + Users Password
                $Antwort['success']=true;
                $Antwort['meldung'] = $UserInfos['vorname']." ".$UserInfos['nachname']." erfolgreich am ".date('d.m.Y', $Dateconcerned)." eingeteilt!";
            } else{
                $Antwort['success']=false;
                $Antwort['meldung']="Fehler beim Datenbankzugriff";
            }

            // Close statement
            $stmt->close();
        }
    } else {
        $Antwort['success']=false;
        $Antwort['meldung']=$DAUerr;
    }

    return $Antwort;
}

function delete_bd_entry($mysqli, $User, $Date, $BDtype, $comment){

    $Antwort = [];
    $time = date('Y-m-d G:i:s');
    $day = date("Y-m-d", $Date);
    $currentUserID = get_current_user_id();
    $stmnt = "UPDATE bereitschaftsdienstplan SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE user = ? AND day = ? AND bd_type = ? AND delete_user IS NULL";

    if($stmt = $mysqli->prepare($stmnt)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("issisi", $currentUserID, $time, $comment, $User, $day, $BDtype);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Return success
            $Antwort['success']=true;
            $Antwort['meldung']="Bereitschaftsdiensteinteilung erfolgreich gelöscht!";
        } else{
            $Antwort['success']=false;
            $Antwort['meldung']="Fehler beim Datenbankzugriff";
        }

        // Close statement
        $stmt->close();
    }

    return $Antwort;


}

function parse_add_bd_entry($mysqli){

    $Date = $_POST['date_concerned'];
    $BDtype = $_POST['bd_type'];
    $comment = $_POST['comment'];
    $User = 0;

    for($a=1;$a<=1000;$a++){
        if($User==0){
            if(isset($_POST['chosen_user_'.$a])){
                $User = $a;
            }
        }
    }

    if(empty($comment)){
        $comment = $_POST['comment_chosen_user_'.$User];
    }

    $Parser = add_bd_entry($mysqli, $User, $Date, $BDtype, $comment);

    if($Parser['success']){
        $Answer = '<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Erfolg!</strong> '.$Parser['meldung'].'
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
    } else {
        $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Fehler!</strong> '.$Parser['meldung'].'
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
    }

    return $Answer;

}

function parse_delete_bd_entry($mysqli){

    $Date = $_POST['date_concerned'];
    $BDtype = $_POST['bd_type'];
    $comment = $_POST['comment'];
    $User = 0;

    for($a=1;$a<=1000;$a++){
        if($User==0){
            if(isset($_POST['assigned_user_'.$a])){
                $User = $a;
            }
        }
    }

    if($User!=0){
        $Parser = delete_bd_entry($mysqli, $User, $Date, $BDtype, $comment);

        if($Parser['success']){
            $Answer = '<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Erfolg!</strong> '.$Parser['meldung'].'
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        } else {
            $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Fehler!</strong> '.$Parser['meldung'].'
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        }
    } else {
        $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Fehler!</strong> Kein/e Mitarbeiter/in ausgewählt!
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
    }


    return $Answer;

}

function parse_edit_bd_entry($mysqli){

    $Date = $_POST['date_concerned'];
    $BDtype = $_POST['bd_type'];
    $comment = $_POST['comment'];
    $User = $OldUser = 0;

    for($a=1;$a<=1000;$a++){
        if($OldUser==0){
            if(isset($_POST['assigned_user_'.$a])){
                $OldUser = $a;
            }
        }
    }

    for($a=1;$a<=1000;$a++){
        if($User==0){
            if(isset($_POST['chosen_user_'.$a])){
                $User = $a;
            }
        }
    }

    if($OldUser!=0){
        if($User!=0){
            $Parser = delete_bd_entry($mysqli, $OldUser, $Date, $BDtype, $comment);
            if($Parser['success']){
                if(empty($comment)){
                    $comment = $_POST['comment_chosen_user_'.$User];
                }
                $Parser2 = add_bd_entry($mysqli, $User, $Date,  $BDtype, $comment);
                if($Parser2['success']){
                    $Answer = '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Erfolg!</strong> Bereitschaftsdiensteinteilung erfolgreich geändert!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } else {
                    $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> '.$Parser2['meldung'].'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }
            } else {
                $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> '.$Parser['meldung'].'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        } else {
            $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Kein/e Mitarbeiter/in zum Tausch ausgewählt!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }
    } else {
        $Answer = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Kein/e zu entfernende/n Mitarbeiter/in ausgewählt!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }

    return $Answer;

}

function lade_bd_freigabestatus_monat($Month,$Year){

    $mysqli = connect_db();
    $Freigegeben = get_list_of_all_freiegegebene_bd_monate($mysqli);
    $Answer = [];

    foreach ($Freigegeben as $Freigabe) {
        if(($Freigabe['month']==$Month) && ($Freigabe['year']==$Year)){
            $Answer[] = $Freigabe;
        }
    }

    return $Answer;

}