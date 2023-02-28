<?php

function form_builder($FormHTML='', $action='self', $method='post'){

    $HTML = '';

    if($action=='self'){
        $HTML .= '<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="'.$method.'">';
        $HTML .= $FormHTML;
        $HTML .= '</form>';
    } else {
        $HTML .= '<form action="'.$action.'" method="'.$method.'">';
        $HTML .= $FormHTML;
        $HTML .= '</form>';
    }

    return $HTML;
}

function form_group_input_text($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false) {

    $HTML = '<div class="form-group">';
    $HTML .= '<label class="form-label">'.$Label.'</label><br>';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($HasFormControl){
        if(!empty($FieldError)){
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }
        $HTML .= '<input type="text" name="'.$name.'" class="form-control '.$InValid.'" value="'.$Value.'" '.$disbledHTML.'>';
        $HTML .= '<span class="invalid-feedback">'.$FieldError.'</span>';
    } else {
        $HTML .= '<input type="text" name="'.$name.'" class="" value="'.$Value.'" '.$disbledHTML.'>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_input_password($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<div class="form-group">';
    $HTML .= '<label>'.$Label.'</label>';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($HasFormControl){
        if(!empty($FieldError)){
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }
        $HTML .= '<input type="password" name="'.$name.'" class="form-control '.$InValid.'" value="'.$Value.'" '.$disbledHTML.'>';
        $HTML .= '<span class="invalid-feedback">'.$FieldError.'</span>';
    } else {
        $HTML .= '<input type="password" name="'.$name.'" class="" value="'.$Value.'" '.$disbledHTML.'>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_input_date($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false) {

    $HTML = '<div class="form-group">';
    $HTML .= '<label>'.$Label.'</label>';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($HasFormControl){
        if(!empty($FieldError)){
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }
        $HTML .= '<input type="date" name="'.$name.'" class="form-control '.$InValid.'" value="'.$Value.'" '.$disbledHTML.'>';
        $HTML .= '<span class="invalid-feedback">'.$FieldError.'</span>';
    } else {
        $HTML .= '<input type="date" name="'.$name.'" class="" value="'.$Value.'" '.$disbledHTML.'>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_checkbox($Label, $name, $Checked=false, $Disabled=false){

    if($Checked){$Checked='checked';}
    if($Disabled){$Disabled='disabled';}

    $HTML = '<div class="form-check"><input class="form-check-input" type="checkbox" name="'.$name.'" id="flexCheck-'.$name.'" '.$Checked.' '.$Disabled.'>
            <label class="form-check-label" for="flexCheck-'.$name.'">'.$Label.'</label></div>';

    return $HTML;
}

function form_group_switch($Label, $name, $Checked=false, $Disabled=false){

    if($Checked){$Checked='checked';}
    if($Disabled){$Disabled='disabled';}

    $HTML = '<div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="'.$name.'" id="flexCheck-'.$name.'" '.$Checked.' '.$Disabled.'>
            <label class="form-check-label" for="flexCheck-'.$name.'">'.$Label.'</label>
            </div>';

    return $HTML;
}

function form_group_continue_return_buttons($Continue=true, $ContinueValue='', $ContinueName='', $ContinueClass='btn-primary', $GoBack=true, $GoBackValue='', $GoBackName='', $GoBackClass='btn-primary'){

    $HTML = '<div class="form-group">';

    if($GoBack){
        $HTML .= '<input type="submit" class="btn '.$GoBackClass.'" value="'.$GoBackValue.'" name="'.$GoBackName.'">';
    }

    $HTML .= " ";

    if($Continue){
        $HTML .= '<input type="submit" class="btn '.$ContinueClass.'" value="'.$ContinueValue.'" name="'.$ContinueName.'">';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_sondereinteilungen_buttons($Add=true, $AddValue='', $AddName='', $AddClass='btn-primary', $Edit=true, $EditValue='', $EditName='', $EditClass='btn-primary', $Delete=true, $DeleteValue='', $DeleteName='', $DeleteClass='btn-danger'){

    $HTML = '<div class="form-group">';

    if($Add){
        $HTML .= '<input type="submit" class="btn '.$AddClass.'" value="'.$AddValue.'" name="'.$AddName.'">';
    }

    $HTML .= " ";

    if($Edit){
        $HTML .= '<input type="submit" class="btn '.$EditClass.'" value="'.$EditValue.'" name="'.$EditName.'">';
    } else {
        $HTML .= '<input type="submit" class="btn '.$EditClass.'" value="'.$EditValue.'" name="'.$EditName.'" disabled>';
    }

    $HTML .= " ";

    if($Delete){
        $HTML .= '<input type="submit" class="btn '.$DeleteClass.'" value="'.$DeleteValue.'" name="'.$DeleteName.'">';
    } else {
        $HTML .= '<input type="submit" class="btn '.$DeleteClass.'" value="'.$DeleteValue.'" name="'.$DeleteName.'" disabled>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_mitarbeitertypen($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $Options = explode(',', MITARBEITERGRUPPEN);
    foreach ($Options as $option){

        if($Value==$option){
            $OptionsHTML .= '<option value="'.$option.'" selected>'.$option.'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$option.'">'.$option.'</option>';
        }

    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Mitarbeitergruppe auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.'>Mitarbeitergruppe auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_abwesenheitentypen($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false, $mode='user'){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $Options = explode(',', ABWESENHEITENTYPEN);
    foreach ($Options as $option){

        $OptionDetails = explode(':',$option);

        if($mode=='user'){
            if($OptionDetails[3]!='false'){
                if($Value==$OptionDetails[0]){
                    $OptionsHTML .= '<option value="'.$OptionDetails[0].'" selected>'.$OptionDetails[0].'</option>';
                } else {
                    $OptionsHTML .= '<option value="'.$OptionDetails[0].'">'.$OptionDetails[0].'</option>';
                }
            }
        } else {
            if($Value==$OptionDetails[0]){
                $OptionsHTML .= '<option value="'.$OptionDetails[0].'" selected>'.$OptionDetails[0].'</option>';
            } else {
                $OptionsHTML .= '<option value="'.$OptionDetails[0].'">'.$OptionDetails[0].'</option>';
            }
        }

    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Abwesenheitstyp auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.'>Abwesenheitstyp auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_bearbeitungsstati($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $Options = explode(',', ABWESENHEITENBEARBEITUNGSSTATI);
    foreach ($Options as $option){

        $optionUsable = explode(':', $option);

        if($Value==$optionUsable[0]){
            $OptionsHTML .= '<option value="'.$optionUsable[0].'" selected>'.$optionUsable[0].'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$optionUsable[0].'">'.$optionUsable[0].'</option>';
        }

    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Bearbeitungsstatus auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Bearbeitungsstatus auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_abwesenheiten_dringlichkeiten_typen($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $Options = explode(',', ABWESENHEITENDRINGLICHKEITEN);
    foreach ($Options as $option){

        if($Value==$option){
            $OptionsHTML .= '<option value="'.$option.'" selected>'.$option.'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$option.'">'.$option.'</option>';
        }

    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Dringlichkeit angeben</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.'>Dringlichkeit angeben</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_dienstwunschtypen($mysqli, $Label, $name, $Value='',$HasFormControl=true, $FieldError='', $disbled=false, $ManagementMode = false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $Departments = get_list_of_all_departments($mysqli);
    $Options = get_list_of_all_dienstplanwunsch_types($mysqli);

    foreach ($Departments as $department){

        $OptionsHTML .= '<option disabled>-- '.$department['name'].' --</option>';

        foreach ($Options as $option){

            if($option['belongs_to_depmnt']==$department['id']){

                if($ManagementMode){
                    if($Value==$option['id']){
                        $OptionsHTML .= '<option value="'.$option['id'].'" selected>'.$option['name'].'</option>';
                    } else {
                        $OptionsHTML .= '<option value="'.$option['id'].'">'.$option['name'].'</option>';
                    }
                } else {
                    if($option['visible_for_users']==1){
                        if($Value==$option['id']){
                            $OptionsHTML .= '<option value="'.$option['id'].'" selected>'.$option['name'].'</option>';
                        } else {
                            $OptionsHTML .= '<option value="'.$option['id'].'">'.$option['name'].'</option>';
                        }
                    }
                }
            }
        }
    }


    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select '.$InValid.'" name="'.$name.'" id="'.$name.'" '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Dienstwunschtyp auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Dienstwunschtyp auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;

}

function form_group_dropdown_unterabteilungen($Label, $name, $Value='',$HasFormControl=true, $FieldError='', $disbled=false, $primaryName = ''){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    if($primaryName==''){
        $primaryName = "Primäre Unterabteilung auswählen";
    }

    //Build Options List
    $OptionsHTML = "";
    $Departments = get_list_of_all_departments(connect_db());

    foreach ($Departments as $department){

        if($Value==$department['id']){
            $OptionsHTML .= '<option value="'.$department['id'].'" selected>'.$department['name'].'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$department['id'].'">'.$department['name'].'</option>';
        }
    }


    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">'.$primaryName.'</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">'.$primaryName.'</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;

}

function form_group_dropdown_dienstgruppen($Label, $name, $Value='',$HasFormControl=true, $FieldError='', $disbled=false, $primaryName = ''){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    if($primaryName==''){
        $primaryName = "Primäre Unterabteilung auswählen";
    }

    //Build Options List
    $OptionsHTML = "";
    $Departments = get_list_of_all_bd_types(connect_db());

    foreach ($Departments as $department){

        if($Value==$department['id']){
            $OptionsHTML .= '<option value="'.$department['id'].'" selected>'.$department['kuerzel'].' - '.$department['name'].'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$department['id'].'">'.$department['kuerzel'].' - '.$department['name'].'</option>';
        }
    }


    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">'.$primaryName.'</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">'.$primaryName.'</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;

}

function form_group_dropdown_sondereinteilungen_unterabteilungen($Label, $name, $Values='',$HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    //Build Options List
    $OptionsHTML = "";
    $Departments = get_list_of_all_departments(connect_db());
    $DepartmentName = '';

    foreach ($Values as $Value){
        foreach ($Departments as $department){
            if ($department['id']==$Value['department']){
                $DepartmentName = $department['name'];
            }
        }

        $OptionsHTML .= '<option value="'.$Value['id'].'">'.$Value['begin'].' bis '.$Value['end'].': '.$DepartmentName.'</option>';
    }

    if (sizeof($Values)==0){
        $OptionsHTML .= '<option value="">Bislang keine Sondereinteilungen erfasst!</option>';
    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }
        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {
        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;

}

function form_group_dropdown_dienstgruppenzugehörigkeiten($Label, $name, $Values='',$HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    //Build Options List
    $OptionsHTML = "";
    $Dienste = get_list_of_all_bd_types(connect_db());
    $Dienstname = "";

    foreach ($Values as $value) {
        foreach ($Dienste as $Dienst) {
            if($value['bd_type']==$Dienst['id']){
                $Dienstname = $Dienst['name'];
            }
        }
        if($value['end']=='2099-12-31'){

            if(strtotime($value['begin'])<time()){
                $TimeString = "seit";
            } else {
                $TimeString = "ab";
            }

            $OptionsHTML .= '<option value="'.$value['id'].'">'.$TimeString.' '.$value['begin'].': '.$Dienstname.'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$value['id'].'">'.$value['begin'].' bis '.$value['end'].': '.$Dienstname.'</option>';
        }
    }

    if (sizeof($Values)==0){
        $OptionsHTML .= '<option value="">Bislang keine Dienstgruppenzugehörigkeit erfasst!</option>';
    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }
        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {
        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;

}

function form_group_dropdown_toolrollen($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $Options = explode(',', TOOLGRUPPEN);
    foreach ($Options as $option){

        if(in_array($option, $Value)){
            $OptionsHTML .= '<option value="'.$option.'" selected>'.$option.'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$option.'">'.$option.'</option>';
        }

    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" multiple name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled>Planungstoolrollen auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" multiple name="'.$name.'" id="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.'>Planungstoolrollen auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dorpdown_arbeitstage($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';

    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    if(!is_array($Value)){
        $Value = [];
    }

    //Build Options List
    $OptionsHTML = "";
    for($a=1;$a<=7;$a++){

        if(in_array($a, $Value)){
            $OptionsHTML .= '<option value="'.$a.'" selected>'.getDay($a).'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$a.'">'.getDay($a).'</option>';
        }

    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" multiple name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Arbeitsfreie Tage auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" multiple name="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Arbeitsfreie Tage auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_all_users($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<label class="form-label" for="'.$name.'">'.$Label.'</label>';
    $HTML .= '<div class="form-group">';

    if($disbled){
        $disbledHTML = 'disabled';
    } else {
        $disbledHTML = '';
    }

    if($Value==""){
        $PrimSelected = "selected";
    } else {
        $PrimSelected = "";
    }

    //Build Options List
    $OptionsHTML = "";
    $AllUsers = get_sorted_list_of_all_users(connect_db());
    foreach($AllUsers as $User){

        if($User['id'] == $Value){
            $OptionsHTML .= "<option value='".$User['id']."' selected>".$User['nachname'].", ".$User['vorname']."</option>";
        } else {
            $OptionsHTML .= "<option value='".$User['id']."'>".$User['nachname'].", ".$User['vorname']."</option>";
        }
    }

    if($HasFormControl) {
        if (!empty($FieldError)) {
            $InValid = "is-invalid";
        } else {
            $InValid = "";
        }

        $HTML .= '<select class="form-select" name="'.$name.'" id="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Mitarbeiter/in auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {
        $HTML .= '<select class="form-select" name="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Mitarbeiter/in auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</select>';
    }

    $HTML .= '</div>';
    return $HTML;

}

function form_hidden_input_generator($Name, $Value){
    return "<input type='hidden' name='".$Name."' value='".$Value."'</input>";
}

function getDay($dow){
    $dowMap = array('Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
    return $dowMap[$dow-1];
}

function form_dropdown_months($Name, $Value){

    $OptionsHTML = $HTML = "";

    // Render Month Options
    for($a=1;$a<=12;$a++){
        $format = new IntlDateFormatter('de_DE', IntlDateFormatter::NONE,
            IntlDateFormatter::NONE, NULL, NULL, "MMM");
        $monthName = datefmt_format($format, mktime(0, 0, 0, $a));
        if($Value==$a){
            $OptionsHTML .= '<option value="'.$a.'" selected>'.$monthName.'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$a.'">'.$monthName.'</option>';
        }
    }

    $HTML .= '<select class="form-select" name="'.$Name.'" id="'.$Name.'">';
    $HTML .= $OptionsHTML;
    $HTML .= '</select>';

    return $HTML;
}

function form_dropdown_years($Name, $Value){
    $initialYear = 2022;
    $maxYears = date('Y',strtotime('+5 years'));
    $OptionsHTML = $HTML = "";

    for($a=$initialYear;$a<=$maxYears;$a++){
        if($Value==$a){
            $OptionsHTML .= '<option value="'.$a.'" selected>'.$a.'</option>';
        } else {
            $OptionsHTML .= '<option value="'.$a.'">'.$a.'</option>';
        }
    }

    $HTML .= '<select class="form-select" name="'.$Name.'" id="'.$Name.'">';
    $HTML .= $OptionsHTML;
    $HTML .= '</select>';

    return $HTML;
}