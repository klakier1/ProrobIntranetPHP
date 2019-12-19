<?php

namespace Klakier;

class Utils
{
    static function object_to_array($data)
    {
        if ((!is_array($data)) and (!is_object($data)))
            return 'xxx'; // $data;

        $result = array();

        $data = (array) $data;
        foreach ($data as $key => $value) {
            if (is_object($value))
                $value = (array) $value;
            if (is_array($value))
                $result[$key] = Utils::object_to_array($value);
            else
                $result[$key] = $value;
        }
        return $result;
    }
}
