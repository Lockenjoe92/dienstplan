<?php

function lade_xml_einstellung($NameEinstellung, $mode='global'){

    if($mode == 'global'){
        $xml = @simplexml_load_file("./config/xml_settings.xml");
    }

    if (false === $xml) {
        // throw new Exception("Cannot load xml source.\n");
        $StrValue = false;

    } else {

        $Value = $xml->$NameEinstellung;
        $StrValue = (string) $Value;
    }

    return $StrValue;

}

function update_xml_einstellung($NameEinstellung, $WertEinstellung, $mode='global'){

    $WertEinstellung = utf8_encode($WertEinstellung);

    #Catch Error when trying to save empty field
    if(strlen($WertEinstellung)==0){
        $WertEinstellung = utf8_encode(' ');
    }

    if($mode == 'global'){
        $xml = simplexml_load_file("./config/xml_settings.xml");
        $xml->$NameEinstellung = $WertEinstellung;
        $xml->asXML("./config/xml_settings.xml");
    } elseif ($mode == 'cdata'){
        $xml = simplexml_load_file("./config/xml_settings.xml");
        $Einstellung = $xml->$NameEinstellung;
        $xmlDoc = new DOMDocument();
        $xmlDoc->load("./config/xml_settings.xml");
        $y=$xmlDoc->getElementsByTagName($NameEinstellung)[0];
        $cdata = $y->firstChild;
        $cdata->replaceData(0,strlen($Einstellung),utf8_decode($WertEinstellung));
        $xmlDoc->save("./config/xml_settings.xml");
    }

}