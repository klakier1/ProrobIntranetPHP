<?php

namespace Klakier;

use Symfony\Component\Yaml\Tag\TaggedValue;

class YamlUtils {

    public static function taggedValueToArray ($in){
        $ret = null;
        if(is_array($in)){
            foreach ($in as $key => &$item) {
                $ret[$key] = YamlUtils::taggedValueToArray($item);
            }
        }else if($in instanceof TaggedValue){
            $ret = $in->getValue();
        }else{
            $ret = $in;
        }
        return $ret;
    }
}

?>