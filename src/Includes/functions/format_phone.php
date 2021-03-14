<?php
function format_phone($TEL): string
{
    $tam = strlen(preg_replace("/[^0-9]/", "", $TEL));
    if ($tam == 13) { // COM CÓDIGO DE ÁREA NACIONAL E DO PAIS e 9 dígitos
        return "+".substr($TEL,0,$tam-11)."(".substr($TEL,$tam-11,2).")".substr($TEL,$tam-9,5)."-".substr($TEL,-4);
    }
    if ($tam == 12) { // COM CÓDIGO DE ÁREA NACIONAL E DO PAIS
        return "+".substr($TEL,0,$tam-10)."(".substr($TEL,$tam-10,2).")".substr($TEL,$tam-8,4)."-".substr($TEL,-4);
    }
    if ($tam == 11) { // COM CÓDIGO DE ÁREA NACIONAL e 9 dígitos
        return "(".substr($TEL,0,2).")".substr($TEL,2,5)."-".substr($TEL,7,11);
    }
    if ($tam == 10) { // COM CÓDIGO DE ÁREA NACIONAL
        return "(".substr($TEL,0,2).")".substr($TEL,2,4)."-".substr($TEL,6,10);
    }
    if ($tam <= 9) { // SEM CÓDIGO DE ÁREA
        return substr($TEL,0,$tam-4)."-".substr($TEL,-4);
    }
    return '';
}