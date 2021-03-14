<?php
/** Seleciona uma opção dentro de um select
 * @param string $select Ex. '\<select>\<option value="1">Opção 1\</option>\</select>'
 * @param $option_to_select Diz qual valor será selecionado, ex. 1
 * @return string Retorna o select com o valor selecionado, ex. '\<select>\<option value="1">Opção 1\</option>\</select>'
 */
function select_option_html(string $select, $option_to_select){

    $select = str_replace('selected', '', $select);
    $arr = array();

    if(!is_array($option_to_select)){
        array_push($arr, $option_to_select);
    } else {$arr = $option_to_select;}

    foreach($arr as $opt){
        $select = str_replace("value=\"$opt\"", "value=\"$opt\" selected", $select);
        $select = str_replace("value='$opt'", "value=\"$opt\" selected", $select);
    }

    return $select;
}