<?php

namespace mod_biblereader\external;

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

// \core_external\external_api
class create_groups extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'groups' => new external_multiple_structure(
                new external_single_structure([
                    'courseid'  => new external_value(PARAM_INT, 'The course to create the group for'),
                    'idnumber'    => new external_value(
                        PARAM_RAW,
                        'An arbitrary ID code number perhaps from the institution',
                        VALUE_DEFAULT,
                        null
                    ),
                    'name' => new external_value(
                        PARAM_RAW,
                        'The name of the group'
                    ),
                    'description' => new external_value(
                        PARAM_TEXT,
                        'A description',
                        VALUE_OPTIONAL
                    ),
                ]),
                'A list of groups to create'
            ),
        ]);
    }

    public static function execute(array $groups): array {
        // Validate all of the parameters.
        [
            'groups' => $groups,
        ] = self::validate_parameters(self::execute_parameters(), [
            'groups' => $groups,
        ]);

        // Perform security checks, for example:
        $coursecontext = \context_course::instance($courseid);
        self::validate_context($coursecontext);
        require_capability('moodle/course:creategroups', $coursecontext);

        // Create the group using existing Moodle APIs.
        $createdgroups = \mod_biblereader\util::create_groups($groups);

        // Return a value as described in the returns function.
        return [
            'groups' => $createdgroups,
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'groups' => new external_multiple_structure([
                'id' => new external_value(PARAM_INT, 'Id of the created user'),
                'name' => new external_value(PARAM_RAW, 'The name of the group'),
            ])
        ]);
    }
}
