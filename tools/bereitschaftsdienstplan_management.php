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

function calculate_users_dienstbelastung_points_on_given_day($DateConcerned, $UserConcerned, $AllBDeinteilungen, $AllBDTypes, $Holidayweekend, $WeeksPast=3, $WeeksFuture=3, $PenaltyWeekDay = 10, $PenaltyWeekend = 20, $MaxDienstePerMonth = 4, $MaxPlannedWeekendsPerMonth = 2, $MaxWeekendHolidayPenalty = 100, $MaxDienstePenalty = 200){

    $Answer = [];
    $Counter = 0;
    $FoundWeekends = array();

    //Calculate SearchDates
    $FirstDayToConcider = date('Y-m-d', strtotime('- '.$WeeksPast.' weeks', $DateConcerned));
    $LastDayToConcider = date('Y-m-d', strtotime('+ '.$WeeksFuture.' weeks', $DateConcerned));

    //Calculate Month Limit Dates for Counting Dienste/Month
    $NumDiensteToConciderThisMonth = 0;
    $FirstDayOfConcideredMonth = date('Y-m-01', $DateConcerned);
    $LastDayOfConcideredMonth = date('Y-m-t', $DateConcerned);

    //Now parse all Einteilungen
    foreach ($AllBDeinteilungen as $Einteilung){
        if($UserConcerned == $Einteilung['user']){

            //Check if Einteilung is within search-parameters
            if(($Einteilung['day']>=$FirstDayToConcider) && ($Einteilung['day']<=$LastDayToConcider)){

                //Check if Date is weekend or holiday
                if($Holidayweekend){

                    //Count num "Used" weekends
                    $date = new DateTime($Einteilung['day']);
                    $week = $date->format("W");
                    if(!in_array($week, $FoundWeekends)){
                        $FoundWeekends[] = $week;
                    }

                    $Counter += $PenaltyWeekend;
                } else {
                    $Counter += $PenaltyWeekDay;
                }
            }

            //Count Anzahl Dienste in general, so we can punish adding more than allowed by regulations/tarifs
            if(($Einteilung['day']>=$FirstDayOfConcideredMonth) && ($Einteilung['day']<=$LastDayOfConcideredMonth)){
                $BDTypeInfos = get_bd_type_infos_from_list($Einteilung['bd_type'], $AllBDTypes);
                if($BDTypeInfos['req_fza']==1){
                    $NumDiensteToConciderThisMonth++;
                } else {
                    //see if there still is a max number of Diensttypes
                    if($BDTypeInfos['max_per_month']>0){
                        $NumDiensteToConciderThisMonth++;
                    }
                }
            }
        }
    }

    //now penalize user if they already have a maximum of assignments in this month
    if($NumDiensteToConciderThisMonth>=$MaxDienstePerMonth){
        $Counter += $MaxDienstePenalty;
    }

    //penalize user if they already have maxed out the number of assigned weekend shifts
    if(sizeof($FoundWeekends)>=$MaxPlannedWeekendsPerMonth){
        $Counter += $MaxWeekendHolidayPenalty;
    }

    $Answer['penalty_points'] = $Counter;
    $Answer['num_dienste_this_month'] = $NumDiensteToConciderThisMonth;

    return $Answer;

}

function parse_bd_candidates_on_day_for_certain_bd_type($DateConcerned, $BDType, $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes, $Holidayweekend){

    $Answer = [];
    $Assignments = [];
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

        if (($CandidatePersonalInfos != FALSE)) {
            $CandidateInfos['userName'] = $CandidatePersonalInfos['nachname'].', '.$CandidatePersonalInfos['vorname'];
            $CandidateInfos['surname'] = $CandidatePersonalInfos['nachname'];
            $HRAssignments = get_users_highest_ranking_bd_assignments_on_certain_day($firstListOfCandidate['user'], $AllBDassignments, $AllBDTypes, $DateConcerned);
            $CandidateInfos['highest_bd_rank_kuerzel'] = $HRAssignments[0]['dienstgruppe'];
            $CandidateInfos['highest_bd_rank'] = $HRAssignments[0]['reihung'];
            $Dienstbelastung = calculate_users_dienstbelastung_points_on_given_day($DateConcerned, $firstListOfCandidate['user'], $AllBDeinteilungen, $AllBDTypes, $Holidayweekend);
            $CandidateInfos['dienstbelastung'] = $Dienstbelastung['penalty_points'];
            $CandidateInfos['anzahl_dienste_monat'] = $Dienstbelastung['num_dienste_this_month'];
            $NeedsRed = false;
            $NeedsGreen = false;
            $HasExactlyThisAssignment = false;
            $ReasonNeedsRed = '';
            $ReasonNeedsGreen = '';
            $VerfuegbarkeitRed = '';

            //Check if User is unavailable due to Abwesenheit
            $AbwesenheitCheck = get_abwesenheit_existing_for_user_on_given_day($firstListOfCandidate['user'], $AllAbwesenheiten, $DateConcerned);
            if(sizeof($AbwesenheitCheck)>0){
                $NeedsRed = true;
                $VerfuegbarkeitRed = $AbwesenheitCheck['type'];
                $ReasonNeedsRed = htmlspecialchars($AbwesenheitCheck['create_comment']);
            }

            //Check for Teilzeitfrei

            if(in_array(date('w', $DateConcerned), explode(',',$CandidatePersonalInfos['freie_tage']))){
                $NeedsRed = true;
                $VerfuegbarkeitRed = 'TZF';
                $ReasonNeedsRed = 'MitarbeiterIn hat an diesem Tag fest Teilzeitfrei!';
            }

            //Check if User is unavailable due to wish
            $NegativeWishCheck = get_negative_bd_wishes_user_on_certain_day($firstListOfCandidate['user'], $BDType, $Allwishes, $AllWishTypes, $AllBDTypes, $DateConcerned);
            if(sizeof($NegativeWishCheck)>0){
                $NegativeWishCheck = $NegativeWishCheck[0];
                $NeedsRed = true;
                $WishTypeDetails = get_wunschtype_details_by_type_id($AllWishTypes, $NegativeWishCheck['type']);
                $VerfuegbarkeitRed = $WishTypeDetails['name'];
                $ReasonNeedsRed = htmlspecialchars($NegativeWishCheck['create_comment']);
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

                    //Don't show people in red list if they are planned for exactly this bd
                    //Add them to a separate list instead for building modal links in table
                    if($EinteilungenToday['bd_type']==$BDType){
                        $NeedsRed = false;
                        $HasExactlyThisAssignment = true;
                        $GoodCandidateCounter++;
                        $Assignment['userName'] = $CandidatePersonalInfos['nachname'].', '.$CandidatePersonalInfos['vorname'][0];
                        $Assignment['userNameLong'] = $CandidatePersonalInfos['nachname'].', '.$CandidatePersonalInfos['vorname'];
                        $Assignment['highest_bd_rank_kuerzel'] = $CandidateInfos['highest_bd_rank_kuerzel'];
                        $Assignment['dienstbelastung'] = $CandidateInfos['dienstbelastung'];
                        $Assignment['anzahl_dienste_monat'] = $Dienstbelastung['num_dienste_this_month'];
                        $Assignment['assignmentObject'] = $firstListOfCandidate;
                        $Assignment['reason'] = htmlspecialchars($EinteilungenToday['create_comment']);
                        $Assignments[] = $Assignment;
                    }
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
                if(!$HasExactlyThisAssignment){
                    $PositiveWishCheck = get_positive_bd_wishes_user_on_certain_day($firstListOfCandidate['user'], $BDType, $Allwishes, $AllWishTypes, $AllBDTypes, $DateConcerned);
                    if(sizeof($PositiveWishCheck)>0){
                        $PositiveWishCheck = $PositiveWishCheck[0];
                        $NeedsGreen = true;
                        #$WishTypeDetails = get_wunschtype_details_by_type_id($AllWishTypes, $PositiveWishCheck['type']);
                        $ReasonNeedsGreen = htmlspecialchars($PositiveWishCheck['create_comment']);
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

    $Answer['assigned_candidates'] = $Assignments;
    $Answer['num_assigned_candidates'] = sizeof($Assignments);
    $Answer['num_found_candidates'] = $GoodCandidateCounter;
    $Answer['good_for_automatik_list'] = $BlankList;
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

function add_bd_entry($mysqli, $User, $Dateconcerned, $BDtype, $comment='', $byAutomatik=false){

    $UserInfos = get_current_user_infos($mysqli, $User);
    $CurrentUserID = get_current_user_id();
    $Date = date('Y-m-d', $Dateconcerned);

    if($byAutomatik){
        $byAutomatik=1;
    } else {
        $byAutomatik=0;
    }

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

    $ParsedCandidates = parse_bd_candidates_on_day_for_certain_bd_type($Dateconcerned, $BDtype, $AllBDEinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes, day_is_a_weekend_or_holiday($Dateconcerned));
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
        $sql = "INSERT INTO bereitschaftsdienstplan (day, bd_type, user, create_user, create_comment, planned_by_auto_mode) VALUES (?,?,?,?,?,?)";
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("siiisi", $Date, $BDtype, $User, $CurrentUserID, $comment, $byAutomatik);

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
    $comment = htmlspecialchars($_POST['comment']);
    $User = 0;

    for($a=1;$a<=1000;$a++){
        if($User==0){
            if(isset($_POST['chosen_user_'.$a])){
                $User = $a;
            }
        }
    }

    if(empty($comment)){
        $comment = htmlspecialchars($_POST['comment_chosen_user_'.$User]);
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
    $comment = htmlspecialchars($_POST['comment']);
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
    $comment = htmlspecialchars($_POST['comment']);
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
                    $comment = htmlspecialchars($_POST['comment_chosen_user_'.$User]);
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

function bd_automatik(){

    $Antwort = [];
    $Antwort['output'] = '';

    // First some DAU checks:
    if(empty($_POST['automatik_dienstgruppe'])){
        $Antwort['output'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Bitte die zu bearbeitende Dienstgruppe wählen!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        $Antwort['err'] = "Bitte die zu bearbeitende Dienstgruppe wählen!";
        return $Antwort;
    } elseif (empty($_POST['automatik_mode'])){
        $Antwort['output'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Bitte den Automatikmodus wählen!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        return $Antwort;
    } elseif(empty($_POST['automatik_start_date'])){
        $Antwort['output'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Bitte den Zeitraum für die Automatik wählen!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        return $Antwort;
    } elseif(empty($_POST['automatik_end_date'])){
        $Antwort['output'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Bitte den Zeitraum für die Automatik wählen!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        return $Antwort;
    } elseif($_POST['automatik_end_date']<$_POST['automatik_start_date']){
        $Antwort['output'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Das Ende des zu bearbeitenden Zeitraums wurde vor dem Beginn gewählt!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        return $Antwort;
    } else {

        // Mode 1 - fullfill wishes as long as there is only one kandidate
        if($_POST['automatik_mode']==1){
            $mysqli = connect_db();

            $StartDate = strtotime($_POST['automatik_start_date']);
            $EndDate = strtotime($_POST['automatik_end_date']);
            $AllAssignments = get_all_users_bd_assignments($mysqli);
            $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
            $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
            $AllWishes = get_sorted_list_of_all_dienstplanwünsche($mysqli);
            $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
            $AllBDTypes = get_list_of_all_bd_types($mysqli);
            $AllUsers = get_sorted_list_of_all_users($mysqli, 'id ASC', true, $_POST['automatik_end_date']);

            $NumFulfilledWishes = 0;
            $NumTotalWishes = 0;
            $NumImpossibleWishes = 0;
            $ConcernedDates = "";

            for($a=0;$a<=90;$a++){
                $Command = "+ ".$a." days";
                $CurrentDay = strtotime($Command, $StartDate);
                if($CurrentDay<=$EndDate){

                    $UsersWithPositiveWishes = 0;
                    $WishesArray = [];
                    $UsersWithAssignments = get_list_of_users_bd_assignments_with_active_bd_type_on_certain_day($_POST['automatik_dienstgruppe'], $AllAssignments, $CurrentDay);
                    foreach($UsersWithAssignments as $user){
                        $Wuensche = get_positive_bd_wishes_user_on_certain_day($user['user'], $_POST['automatik_dienstgruppe'], $AllWishes, $AllWishTypes, $AllBDTypes, $CurrentDay);
                        if(sizeof($Wuensche)>0){

                            //Only concider this if user is really available on said date! -> he has a einteilung? dont concider him
                            $AllEinteilungenToday = get_bereitschaftsdienst_einteilungen_on_day($CurrentDay, $AllBDeinteilungen, 0);
                            $Count = 0;
                            foreach ($AllEinteilungenToday as $EinteilungenToday){
                                if($EinteilungenToday['user']==$user['user']){
                                    $Count++;
                                }
                            }

                            //Check if user already has a FZA today
                            if(user_needs_fza_on_certain_date($user['user'], $AllBDeinteilungen, $AllBDTypes, $CurrentDay)){
                                $Count++;
                            }

                            //Check if User is unavailable due to Abwesenheit
                            $AbwesenheitCheck = get_abwesenheit_existing_for_user_on_given_day($user['user'], $AllAbwesenheiten, $CurrentDay);
                            if(sizeof($AbwesenheitCheck)>0){
                                $Count++;
                            }

                            //Check if User hay already reached max. amount of assignments this month!
                            $Holidayweekend = day_is_a_weekend_or_holiday($CurrentDay);
                            $Dienstbelastung = calculate_users_dienstbelastung_points_on_given_day($CurrentDay, $user['user'], $AllBDeinteilungen, $AllBDTypes, $Holidayweekend);
                            $PenaltyPoints = $Dienstbelastung['penalty_points'];
                            if($PenaltyPoints>=200){
                                $Count++;
                            }

                            if($Count==0){
                                $UsersWithPositiveWishes++;
                                $WishesArray[] = $Wuensche;
                            }
                        }
                    }

                    // now check if there are colliding wishes -> Add to Error-Output
                    if($UsersWithPositiveWishes>1){
                        $NumImpossibleWishes ++;
                        foreach ($WishesArray as $item) {
                            $WishUser = get_user_infos_by_id_from_list($item[0]['user'], $AllUsers);
                            $ConcernedDates .= date('d.m.Y',$CurrentDay)." ".$WishUser['nachname'].", ".$WishUser['vorname']."; ";
                        }
                        //Continue with fulfilling wish -> Add to success output
                    } elseif($UsersWithPositiveWishes==1) {
                        $DoShit = add_bd_entry($mysqli, $WishesArray[0][0]['user'], $CurrentDay, $_POST['automatik_dienstgruppe'], $WishesArray[0][0]['create_comment'], true);
                        if($DoShit['success']){
                            $NumFulfilledWishes++;
                        }
                    }

                    $NumTotalWishes += $UsersWithPositiveWishes;
                }
            }

        $answer = "";

        if($NumImpossibleWishes>0){
            $answer .= '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> '.$NumImpossibleWishes.' Wünsch(e) konnten nicht erfüllt werden, da sie mit anderen Wünschen kollidieren! Folgende Tage waren betroffen: '.$ConcernedDates.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        if($NumFulfilledWishes>0){
            $answer .= '<br><div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Erfolg!</strong> '.$NumFulfilledWishes.' Wünsch(e) von '.$NumTotalWishes.' konnten erfüllt werden!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        if(($NumImpossibleWishes+$NumFulfilledWishes)==0){
            $answer .= '<br><div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Prozess abgeschlossen!</strong> Keine relevanten Dienstplanwünsche gefunden!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        $Antwort['output'] = $answer;
        return $Antwort;

            // Mode 2 - fill special wishes

        } elseif ($_POST['automatik_mode']==2){

            $answer = '<br><div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Entwicklung in Arbeit!</strong> Funktion in entwicklung!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';

        $Antwort['output'] = $answer;
        return $Antwort;

        } elseif ($_POST['automatik_mode']==3){
        // Mode 3 - autofill days with no explicite user wishes by load-balancing & random choice

            $mysqli = connect_db();

            $StartDate = strtotime($_POST['automatik_start_date']);
            $EndDate = strtotime($_POST['automatik_end_date']);
            $AllBDassignments = get_all_users_bd_assignments($mysqli);
            $AllAbwesenheiten = get_sorted_list_of_all_abwesenheiten($mysqli);
            $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
            $AllBDmatrixes = get_list_of_all_bd_matrixes($mysqli);
            $Allwishes = get_sorted_list_of_all_dienstplanwünsche($mysqli);
            $AllWishTypes = get_list_of_all_dienstplanwunsch_types($mysqli);
            $AllBDTypes = get_list_of_all_bd_types($mysqli);
            $AllUsers = get_sorted_list_of_all_users($mysqli, 'id ASC', false, $_POST['automatik_end_date']);

            // Load BD-Type Infos
            $BDTypeInfos = get_bd_type_infos_from_list($_POST['automatik_dienstgruppe'], $AllBDTypes);

            // Counters and Feedback-Strings
            $NumFilledDays = 0;
            $NumTotalAssignedUsers = 0;
            $NumImpossibleDays = 0;
            $NumIncompleteDays = 0;
            $ConcernedDatesImpossible = "";
            $ConcernedDatesIncomplete = "";
            $OtherErrors = "";

            for($a=0;$a<=90;$a++){
                $Command = "+ ".$a." days";
                $CurrentDay = strtotime($Command, $StartDate);
                if($CurrentDay<=$EndDate){

                    if(day_is_a_weekend_or_holiday($CurrentDay)){
                        $Holidayweekend = true;
                        $searchedDayType = 'weekend';
                    } else {
                        $Holidayweekend = false;
                        $searchedDayType = 'normal';
                    }

                    // Load correct BD Matrix
                    $Matrix = [];
                    foreach ($AllBDmatrixes as $BDmatrix){
                        if($BDmatrix['type_of_day']==$searchedDayType){
                            $Matrix = $BDmatrix;
                        }
                    }

                    //Deconstruct BD Matrix
                    $MatrixUnpacked = explode(',', $Matrix['matrix']);

                    foreach ($MatrixUnpacked as $item) {

                        $Exploded = explode(':', $item);

                        if ($Exploded[0] == $_POST['automatik_dienstgruppe']) {

                            //Only continue operation if Dienstgruppe should be active on this day
                            if ($Exploded[1] > 0) {

                                $Einteilungen = get_bereitschaftsdienst_einteilungen_on_day($CurrentDay, $AllBDeinteilungen, $_POST['automatik_dienstgruppe']);

                                //Firstly: only fill unfilled days!
                                if(sizeof($Einteilungen)<$BDTypeInfos['req_employees_per_day']){
                                    $Diff = $Diffwork = $BDTypeInfos['req_employees_per_day']-sizeof($Einteilungen);
                                    $SavedUser = 0;
                                    for($b=0;$b<$Diff;$b++){
                                        //Now build list of identically able candidates (i.e. run the list from the top down until the amount of penalty points start to increase)
                                        $AllBDeinteilungen = get_sorted_list_of_all_bd_einteilungen($mysqli);
                                        $Candidates = parse_bd_candidates_on_day_for_certain_bd_type($CurrentDay, $_POST['automatik_dienstgruppe'], $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes, $Holidayweekend);

                                        $InitialScore = 0;
                                        $IdenticalCandidates = [];
                                        $Count = 0;
                                        foreach ($Candidates['good_for_automatik_list'] as $Candidate){
                                            if($Candidate['userID']!=$SavedUser){
                                                if($Count==0){
                                                    $IdenticalCandidates[] = $Candidate;
                                                    $InitialScore = $Candidate['dienstbelastung'];
                                                } else {
                                                    if($Candidate['dienstbelastung']<=$InitialScore){
                                                        $IdenticalCandidates[] = $Candidate;
                                                    }
                                                }
                                                $Count++;
                                            }
                                        }

                                        if(sizeof($IdenticalCandidates)==0){
                                            $NumImpossibleDays++;
                                            $ConcernedDatesImpossible .= date('d.m.Y', $CurrentDay).", ";
                                        } else {
                                            //Now lets randomly choose from said List
                                            $RandomChoice = random_int(1, sizeof($IdenticalCandidates))-1;
                                            $EinteilungAnswer = add_bd_entry($mysqli, $IdenticalCandidates[$RandomChoice]['userID'], $CurrentDay, $_POST['automatik_dienstgruppe'], '', true);
                                            if($EinteilungAnswer['success']){
                                                $NumTotalAssignedUsers++;
                                                $SavedUser = $IdenticalCandidates[$RandomChoice]['userID'];
                                                $Diffwork = $Diffwork-1;
                                            } else {
                                                $OtherErrors .= $EinteilungAnswer['meldung']."<br>";
                                            }
                                        }
                                    }

                                    //Now lets check if we filled all items in day
                                    if($Diffwork==0){
                                        $NumFilledDays++;
                                    } else {
                                        $ConcernedDatesIncomplete .= date('d.m.Y', $CurrentDay).", ";
                                        $NumIncompleteDays++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Okay, so now lets build some feedback for the users and then were done:)
            if(!empty($OtherErrors)){
                $Antwort['output'] .= '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> '.$OtherErrors.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }

            if($NumImpossibleDays>0){
                $Antwort['output'] .= '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Achtung!</strong> An '.$NumImpossibleDays.' Tag(en) waren keine MitarbeiterInnen mehr verfügbar! '.$ConcernedDatesImpossible.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }

            if($NumIncompleteDays>0){
                $Antwort['output'] .= '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Warnung!</strong> An '.$NumIncompleteDays.' Tag(en) waren nicht ausreichend MitarbeiterInnen verfügbar! '.$ConcernedDatesIncomplete.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }

            if($NumFilledDays>0){
                $Antwort['output'] .= '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Erfolg!</strong> An '.$NumFilledDays.' Tag(en) konnten insgesamt '.$NumTotalAssignedUsers.' Einträg(e) erfolgreich durchgeführt werden.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }

            if(empty($Antwort['output'])){
                $Antwort['output'] .= '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Prozess abgeschlossen!</strong> Keine zu erfüllenden Aufgaben gefunden.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }

            return $Antwort;

        } elseif ($_POST['automatik_mode']==4){
        // Mode 3 - simply delete all assignments in chosen timeframe & bd_dienstgruppe
            $mysqli = connect_db();
            $StartDate = date('Y-m-d G:i:s', strtotime($_POST['automatik_start_date']));
            $EndDate = date('Y-m-d G:i:s', strtotime($_POST['automatik_end_date']));
            $Dienstgruppe = $_POST['automatik_dienstgruppe'];
            $currentUserID = get_current_user_id();
            $time = date('Y-m-d G:i:s');
            $comment = "Per Automatik gelöscht!";

            $stmnt = "UPDATE bereitschaftsdienstplan SET delete_user = ?, delete_time = ?, delete_comment = ? WHERE day >= ? AND day <= ? AND bd_type = ? AND delete_user IS NULL";

            if($stmt = $mysqli->prepare($stmnt)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("issssi", $currentUserID, $time, $comment, $StartDate, $EndDate, $Dienstgruppe);

                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    // Return success
                    $Antwort['success']=true;
                    $Antwort['output']='<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Erfolg!</strong> Bereitschaftsdiensteinteilung(en) erfolgreich gelöscht!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                } else{
                    $Antwort['success']=false;
                    $Antwort['output']='<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Fehler!</strong> Fehler beim Datenbankzugriff!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }

                // Close statement
                $stmt->close();
            }


            return $Antwort;
        }

    }

}