<?php
function regexGen($regexName){

    unset
        (
            $resStealRegex,
            $resBodyImgLinkStealRegex,
            $resBodyAnkaerLinkStealRegex,
            $threadStealRegex
        );

    if($regexName === "resStealRegex"){

        $resStealRegex  = "/<dt>([0-9]{1,4})\s\W<[a-zA-Z]{1,7}\s[a-zA-Z]{1,7}=.+><b>(.+)<\/b><\/[a-zA-Z]{1,7}>";//handle
        $resStealRegex .= "\W([0-9]{4}\/[0-9]{2}\/[0-9]{2}\W.\W)";//date
        $resStealRegex .= "\W([0-9a-zA-Z]{2}\W[0-9a-zA-Z]{2}\W[0-9a-zA-Z]{2}\W[0-9a-zA-Z]{2})";//hms.m
        $resStealRegex .= ".(ID:.{1,15})";//ID
        $resStealRegex .= ".*";//ets
        $resStealRegex .= "<dd>\s(.+)";//body
        $resStealRegex .= "/u";//delmita

        return $resStealRegex;

    }elseif($regexName === "resBodyImgLinkStealRegex"){

        $resBodyImgLinkStealRegex = "";
        $resBodyImgLinkStealRegex .= "/<a\shref=\"http:\/\/[_:a-zA-Z0-9\?\/\.]+\.[jpegpnifJPEGPNIF]{3,4}\"\starget=\"_blank\">";//imgtag
        $resBodyImgLinkStealRegex .= "(http:\/\/[_:a-zA-Z0-9\?\/\.]+(\.[jpegpnifJPEGPNIF]{3,4}))";//imgURL
        $resBodyImgLinkStealRegex .= "<\/a>";//imgURL
        $resBodyImgLinkStealRegex .= "/u";//delimita

        return $resBodyImgLinkStealRegex;

    }elseif($regexName === "resBodyAnkaerLinkStealRegex"){

        $resBodyAnkaerLinkStealRegex = "/<a\shref=\"[-\.a-zA-Z0-9\/]+\"\starget=\"_blank\">(&gt;&gt;([0-9]{1,4}-?[0-9]{0,4}))<\/a>/u";//ankaer

        return $resBodyAnkaerLinkStealRegex;

    }elseif($regexName === "threadStealRegex"){

        $threadStealRegex = "/<a\shref=\"([0-9]+)\/l50\">[0-9]+:\s(.+)\W\(([0-9]{1,4})\)<\/a>/";

        return $threadStealRegex;

    }
}
