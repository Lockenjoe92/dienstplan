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