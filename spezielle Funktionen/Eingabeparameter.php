<?php

function EingabeparameterAlsArray($request, $parameter) {

    if ($request == INPUT_REQUEST) {
        $array = $_REQUEST[$parameter];
    } else {
        $array = filter_input($request, $parameter);
    }
    if ($array == NULL) {
        return array();
    } else {
        return explode(',', $array);
    }
}
