<?php

// Закрыть смену
return  [
            "uuid"=>fGuid(),
	    "request"=>[
		"type"=>"closeShift",
        	"operator"=>[
            	    "name"=>PHPKKM_OPERATOR_NAME,
            	    "vatin"=>PHPKKM_OPERATOR_INN
		]
            ]
        ];

?>