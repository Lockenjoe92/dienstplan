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

function form_group_continue_return_buttons($Continue=true, $ContinueValue='', $ContinueName='', $ContinueClass='btn-primary', $GoBack=true, $GoBackValue='', $GoBackName='', $GoBackClass='btn-primary'){

    $HTML = '<div class="form-group">';

    if($GoBack){
        $HTML .= '<input type="submit" class="btn '.$GoBackClass.'" value="'.$GoBackValue.'" name="'.$GoBackName.'">';
    }

    if($Continue){
        $HTML .= '<input type="submit" class="btn '.$ContinueClass.'" value="'.$ContinueValue.'" name="'.$ContinueName.'">';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_mitarbeitertypen($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<div class="form-group">';
    $HTML .= '<label class="form-label">'.$Label.'</label>';

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

        $HTML .= '<select class="form-select" name="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled value="">Mitarbeitergruppe auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</<select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" name="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.'>Mitarbeitergruppe auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</<select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_group_dropdown_toolrollen($Label, $name, $Value='', $HasFormControl=true, $FieldError='', $disbled=false){

    $HTML = '<div class="form-group">';
    $HTML .= '<label class="form-label">'.$Label.'</label>';

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

        $HTML .= '<select class="form-select" multiple name="'.$name.'" '.$InValid.' '.$disbledHTML.' required>';
        $HTML .= '<option '.$PrimSelected.' disabled>Planungstoolrollen auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</<select>';
        $HTML .= '<div class="invalid-feedback">'.$FieldError.'</div>';
    } else {

        $HTML .= '<select class="form-select" multiple name="'.$name.'" '.$disbledHTML.'>';
        $HTML .= '<option '.$PrimSelected.'>Planungstoolrollen auswählen</option>';
        $HTML .= $OptionsHTML;
        $HTML .= '</<select>';
    }

    $HTML .= '</div>';
    return $HTML;
}

function form_hidden_input_generator($Name, $Value){
    return "<input type='hidden' name='".$Name."' value='".$Value."'</input>";
}