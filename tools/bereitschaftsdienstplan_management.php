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

function parse_bd_candidates_on_day_for_certain_bd_type($DateConcerned, $BDType, $AllBDeinteilungen, $Allwishes, $AllBDassignments, $AllAbwesenheiten, $AllWishTypes, $AllUsers, $AllBDTypes){

    $Answer = [];
    $GoodCandidateCounter = 0;

    //1. Get Candidates based on assigned BD Group
    $firstListOfCandidates = get_list_of_users_bd_assignments_with_active_bd_type_on_certain_day($BDType, $AllBDassignments, $DateConcerned);

    //2. Build 3 sublists based on abwesenheiten, wishes and prior assignments
    // Array is built as follows: 'userID', 'userName', 'table-color', 'reason'
    $GreenList = [];
    $BlankList = [];
    $RedList = [];

    foreach ($firstListOfCandidates as $firstListOfCandidate){

        $CandidateInfos = [];
        $CandidatePersonalInfos = get_user_infos_by_id_from_list($firstListOfCandidate['user'], $AllUsers);
        $CandidateInfos['userName'] = $CandidatePersonalInfos['nachname'].', '.$CandidatePersonalInfos['vorname'];
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
            //Check if User is unavailable due to wish
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
                $GreenList[] = $CandidateInfos;
                $GoodCandidateCounter++;
            } else {
                $CandidateInfos['userID'] = $firstListOfCandidate['user'];
                $CandidateInfos['table-color'] = '';
                $CandidateInfos['reason'] = '';
                $CandidateInfos['verfuegbarkeit'] = 'Verfügbar';
                $BlankList[] = $CandidateInfos;
                $GoodCandidateCounter++;
            }
        }
    }

    $CombinedList = array_merge($GreenList,$BlankList,$RedList);

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