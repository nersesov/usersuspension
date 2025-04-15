<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * this file contains the task to suspend inactive users.
 *
 * File         mark.php
 * Encoding     UTF-8
 *
 * @package     tool_usersuspension
 *
 * @copyright   Sebsoft.nl
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_usersuspension\task\suspend;

use tool_usersuspension\config;

/**
 * Description of mark
 *
 * @package     tool_usersuspension
 *
 * @copyright   Sebsoft.nl
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mark extends \core\task\scheduled_task {

    /**
     * Return the localised name for this task
     *
     * @return string task name
     */
    public function get_name() {
        return get_string('task:mark', 'tool_usersuspension');
    }

    /**
     * Executes the task
     *
     * @return void
     */
    public function execute() {
        if (!(bool)config::get('enabled')) {
            mtrace(get_string('config:tool:disabled', 'tool_usersuspension'));
            return;
        }
        if (!(bool)config::get('enablesmartdetect')) {
            mtrace(get_string('config:smartdetect:disabled', 'tool_usersuspension'));
            return;
        }
        
        mtrace('User suspension task starting...');
        
        // Ensure task runs without checking intervals
        \tool_usersuspension\util::set_lastrun_config('smartdetect', 0);
        
        $result = false;
        
        // First, email any users in the warning period.
        mtrace('Looking for users to warn...');
        $warningresult = \tool_usersuspension\util::warn_users_of_suspension();
        mtrace('Warning result: ' . ($warningresult ? 'warnings sent' : 'no warnings sent'));
        
        // Then suspend users who have reached the threshold.
        mtrace('Looking for users to suspend...');
        $suspendresult = \tool_usersuspension\util::mark_users_to_suspend();
        mtrace('Suspension result: ' . ($suspendresult ? 'users suspended' : 'no users suspended'));
        
        $result = $warningresult || $suspendresult;

        if ($result) {
            \tool_usersuspension\util::set_lastrun_config('smartdetect');
            mtrace('Task completed with actions taken');
        } else {
            mtrace('Task completed with no actions taken');
        }
    }

}
