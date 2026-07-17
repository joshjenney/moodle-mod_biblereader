<?php

namespace mod_biblereader\external;

class external_api extends \external_api {

    protected static function generate_warning(int $assignmentid, string $warningcode, string $detail): array {
        $warningmessages = [
            'useridnotfound' => 'Unable to save preferences for user.',
        ];

        $message = $warningmessages[$warningcode];
        if (empty($message)) {
            $message = 'Unknown warning type.';
        }

        return [
            'item' => s($detail),
            'itemid' => $assignmentid,
            'warningcode' => $warningcode,
            'message' => $message,
        ];
    }

}
