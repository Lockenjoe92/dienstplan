<?php

function tage_differenz_berechnen($TimestampAnfang, $TimestamoEnde){
    $datetime1 = date_create($TimestampAnfang);
    $datetime2 = date_create($TimestamoEnde);
    $interval = date_diff($datetime1, $datetime2);
    $DifferenzTage = $interval->format('%a');
    return $DifferenzTage;
}