<?php
function mail_senden($mysqli, $NameVorlage, $UserObj, $Bausteine, $ProtocolTyp='', $ProtocolDetails='')
{
    //Vorlage laden
    $Vorlage = lade_mailvorlage($NameVorlage);

    //Vorlagentext generieren
    $Mailtext = html_entity_decode(str_replace(array_keys($Bausteine), array_values($Bausteine), $Vorlage['text']));
    $MailBetreff = $Vorlage['betreff'];

    $from = "MIME-Version: 1.0" . "\r\n";
    $from .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $from .= "From: Dienstplantool UKT - Anästhesie und Intensivmedizin <noreply_dp.anaesthesie@med.uni-tuebingen.de>\r\n";
    $from .= "Reply-To: noreply_dp.anaesthesie@med.uni-tuebingen.de\r\n";
    $from .= 'X-Mailer: PHP/' . phpversion();

    if(mail($UserObj['mail'], $MailBetreff, $Mailtext, $from)){
        add_protocol_entry($mysqli, $ProtocolTyp, 'mail_success', $UserObj['id'], get_current_user_id(), $ProtocolDetails);
        return true;
    } else {
        $ProtocolDetails = "SENDEN DER MAIL FEHLGESCHLAGEN: ".$ProtocolDetails;
        add_protocol_entry($mysqli, $ProtocolTyp, 'mail_err', $UserObj['id'], get_current_user_id(), $ProtocolDetails);
        return false;
    }
}

function lade_mailvorlage($name){

    $xml = simplexml_load_file("./config/mailvorlagen.xml");
    $Betreff = $xml->$name->betreff;
    $Text = $xml->$name->text;

    $StrBetreff = (string) $Betreff;
    //$StrBetreff = htmlentities($StrBetreff);
    $StrText = (string) $Text;
    $StrText = htmlentities($StrText);

    $Antwort['betreff'] = $StrBetreff;
    $Antwort['text'] = $StrText;

    return $Antwort;
}
