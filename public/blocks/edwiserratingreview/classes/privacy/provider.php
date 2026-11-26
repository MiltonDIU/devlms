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
 * Privacy provider for block_edwiserratingreview.
 *
 * @package    block_edwiserratingreview
 * @copyright  2022 WisdmLabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_edwiserratingreview\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider implementing metadata, plugin provider and userlist provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns metadata about the data stored by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'block_edwiserratingreview',
            [
                'user_id' => 'privacy:metadata:block_edwiserratingreview:user_id',
                'star_ratings' => 'privacy:metadata:block_edwiserratingreview:star_ratings',
                'review' => 'privacy:metadata:block_edwiserratingreview:review',
                'date_created' => 'privacy:metadata:block_edwiserratingreview:date_created',
                'date_updated' => 'privacy:metadata:block_edwiserratingreview:date_updated',
                'for_id' => 'privacy:metadata:block_edwiserratingreview:for_id',
                'approved' => 'privacy:metadata:block_edwiserratingreview:approved',
            ],
            'privacy:metadata:block_edwiserratingreview'
        );

        $collection->add_database_table(
            'block_ernr_likedlike',
            [
                'reviewid' => 'privacy:metadata:block_ernr_likedlike:reviewid',
                'user_id' => 'privacy:metadata:block_ernr_likedlike:user_id',
                'liked' => 'privacy:metadata:block_ernr_likedlike:liked',
            ],
            'privacy:metadata:block_ernr_likedlike'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Reviews submitted by the user.
        $sql = "SELECT ctx.id
                  FROM {block_edwiserratingreview} r
                  JOIN {context} ctx ON ctx.instanceid = r.for_id AND ctx.contextlevel = :courselevel
                 WHERE r.user_id = :userid";
        $contextlist->add_from_sql($sql, ['userid' => $userid, 'courselevel' => CONTEXT_COURSE]);

        // Likes/dislikes by the user.
        $sql = "SELECT ctx.id
                  FROM {block_ernr_likedlike} l
                  JOIN {block_edwiserratingreview} r ON r.id = l.reviewid
                  JOIN {context} ctx ON ctx.instanceid = r.for_id AND ctx.contextlevel = :courselevel
                 WHERE l.user_id = :userid";
        $contextlist->add_from_sql($sql, ['userid' => $userid, 'courselevel' => CONTEXT_COURSE]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT user_id
                  FROM {block_edwiserratingreview}
                 WHERE for_id = :courseid";
        $userlist->add_from_sql('user_id', $sql, ['courseid' => $context->instanceid]);

        $sql = "SELECT l.user_id
                  FROM {block_ernr_likedlike} l
                  JOIN {block_edwiserratingreview} r ON r.id = l.reviewid
                 WHERE r.for_id = :courseid";
        $userlist->add_from_sql('user_id', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Export personal data for the given approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            // Export reviews.
            $reviews = $DB->get_records('block_edwiserratingreview', ['user_id' => $userid, 'for_id' => $context->instanceid]);
            if (!empty($reviews)) {
                $reviewdata = [];
                foreach ($reviews as $review) {
                    $reviewdata[] = [
                        'star_ratings' => $review->star_ratings,
                        'review' => $review->review,
                        'date_created' => transform::datetime($review->date_created),
                        'date_updated' => transform::datetime($review->date_updated),
                        'approved' => transform::yesno($review->approved),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('privacy:reviews', 'block_edwiserratingreview')],
                    (object) ['reviews' => $reviewdata]
                );
            }

            // Export likes/dislikes.
            $sql = "SELECT l.*, r.for_id
                      FROM {block_ernr_likedlike} l
                      JOIN {block_edwiserratingreview} r ON r.id = l.reviewid
                     WHERE l.user_id = :userid AND r.for_id = :courseid";
            $likes = $DB->get_records_sql($sql, ['userid' => $userid, 'courseid' => $context->instanceid]);
            if (!empty($likes)) {
                $likedata = [];
                foreach ($likes as $like) {
                    $likedata[] = [
                        'reviewid' => $like->reviewid,
                        'liked' => transform::yesno($like->liked),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('privacy:likes', 'block_edwiserratingreview')],
                    (object) ['likes' => $likedata]
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        // Delete likes for reviews in this course first.
        $DB->execute(
            "DELETE FROM {block_ernr_likedlike}
              WHERE reviewid IN (
                  SELECT id FROM {block_edwiserratingreview}
                   WHERE for_id = :courseid
              )",
            ['courseid' => $context->instanceid]
        );

        // Delete reviews for this course.
        $DB->delete_records('block_edwiserratingreview', ['for_id' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            // Delete user's likes in this course.
            $DB->execute(
                "DELETE FROM {block_ernr_likedlike}
                  WHERE user_id = :userid
                    AND reviewid IN (
                        SELECT id FROM {block_edwiserratingreview}
                         WHERE for_id = :courseid
                    )",
                ['userid' => $userid, 'courseid' => $context->instanceid]
            );

            // Delete user's reviews in this course.
            $DB->delete_records('block_edwiserratingreview', ['user_id' => $userid, 'for_id' => $context->instanceid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['courseid' => $context->instanceid]);

        // Delete likes.
        $DB->execute(
            "DELETE FROM {block_ernr_likedlike}
              WHERE user_id {$insql}
                AND reviewid IN (
                    SELECT id FROM {block_edwiserratingreview}
                     WHERE for_id = :courseid
                )",
            $params
        );

        // Delete reviews.
        $DB->execute(
            "DELETE FROM {block_edwiserratingreview}
              WHERE user_id {$insql} AND for_id = :courseid",
            $params
        );
    }
}
