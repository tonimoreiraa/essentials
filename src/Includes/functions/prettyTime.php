<?php
/**
 * Formata uma timestamp amigávelmente
 * @param timestamp $date Timestamp com tempo
 * @return string Retorna a timestamp amigavelmente, ex. "Hoje11:37", "Quarta-Feira17:05"
 */
function prettyTime($date, $show_hour = true):string{

    $date = !is_integer($date) ? strtotime($date) : $date;
    $now = strtotime('now');
    $diff = $now - $date;
    $hour = $show_hour ? ' \à\s H:i:s' : '';

    // caso seja menor que um minuto
    if($date >= $now){
        $return = date('d/m/Y'.$hour, $date);
    } else

    if($diff < 60){
        $return = 'Agora mesmo';
    } else

    // caso seja menor que uma hora
    if($diff < 60*59){
        $val = intval($diff/60);
        $return = $val. ' minuto';
        $return .= ($val > 1) ? 's': '';
        $return .= ' atrás';
    } else

    // caso seja menor que um dia
    if($diff < 60*60*24){
        $val = intval($diff/60/60);
        $return = $val.' hora';

        $return .= ($val > 1) ? 's' : '';
        $return .= ' atrás';
    } else

    if($diff <= 60*60*24*1){
        $return = 'Ontem';
    } else

    if($diff <= 60*60*24*2){
        $return = 'Anteontem';
    } else

    if($diff <= 60*60*24*7){
        $days = [
            1 => 'Segunda',
            2 => 'Terça-Feira',
            3 => 'Quarta-Feira',
            4 => 'Quinta-Feira',
            5 => 'Sexta-Feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $return = $days[date('N', $diff)] . (date($hour, $diff) ?? '');
    }

    return $return ?? date('d/m/Y'.$hour, $date);
}