<?php

function any_null(...$values) {
    foreach($values as $v) {
        if (is_null($v)) {
            return true;
        }
    }

    return false;
}