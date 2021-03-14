<?php
function render_select_by_arr(array $arr, $field = 'completename', $value = 'id'): string
{
    $select = (string) '';
    foreach($arr ?? [] as $option){
        $select .= "<option value='{$option->$value}'>{$option->$field}</option>";
    }
    return $select;
}