<?php
if (!defined('IN_RPGDX')) {
	die("Hacking attempt?");
}


/* Configuration
 */

$rpgdx_config['use_bbcode']              = true;
$rpgdx_config['screenshots_limit']       = 5;
$rpgdx_config['enable_gzip_compression'] = false;


/*
// New table for downloads

rpgdx_project_downloads     (download_id, download_project, download_title, download_url)

// New tables for competitions

rpgdx_contests              (contest_id, contest_creator, contest_creation_time, contest_name, contest_desc, contest_start, contest_end, contest_status)
rpgdx_contest_entries       (entry_id, entry_contest, entry_project, entry_votes)
rpgdx_contests_votes        (vote_user, vote_contest, vote_project)

// New tables for assigning awards to games

rpgdx_awards                (award_id, award_name, award_icon, award_url)
rpgdx_projects_awards       (praw_id, praw_project, praw_award)

*/

$table_prefix = 'rpgdx_';

define('ARTICLE_TYPES_TABLE',         $table_prefix.'article_types');
define('ARTICLES_TABLE',              $table_prefix.'articles');
define('NEWS_TABLE',                  $table_prefix.'news');
define('OPERATING_SYSTEMS_TABLE',     $table_prefix.'operating_systems');
define('PROGRAMMING_LANGUAGES_TABLE', $table_prefix.'programming_languages');
define('PROJECT_SCREENSHOTS_TABLE',   $table_prefix.'project_screenshots');
define('PROJECT_STATUSSES_TABLE',     $table_prefix.'project_statusses');
define('PROJECT_TYPES_TABLE',         $table_prefix.'project_types');
define('PROJECTS_TABLE',              $table_prefix.'projects');
define('REVIEWS_TABLE',               $table_prefix.'reviews');
define('SESSIONS_TABLE',              $table_prefix.'sessions');
define('RPGDX_THEMES_TABLE',          $table_prefix.'themes');
define('LOG_TABLE',                   $table_prefix.'log');
define('UPLOADS_TABLE',               $table_prefix.'uploads');
define('CONTESTS_TABLE',              $table_prefix.'contests');
define('CONTEST_ENTRIES_TABLE',       $table_prefix.'contest_entries');
define('CONTEST_VOTES_TABLE',         $table_prefix.'contest_votes');
define('CONTEST_CATEGORIES_TABLE',    $table_prefix.'contest_categories');


// Contest statusses

define('CON_OPEN',   0);
define('CON_ACTIVE', 1);
define('CON_VOTING', 2);
define('CON_CLOSED', 3);


// Projects sort methods

define('PSORT_USE_LAST', 0);
define('PSORT_NAME',     1);  // Sort by project name
define('PSORT_RATING',   2);  // Sort by project rating
define('PSORT_UPDATE',   3);  // Sort by time of last update
define('PSORT_USER',     4);  // Sort by username of project author


// Expressions that can be used in MySQL statements

define('UPLOAD_FILENAME', 'CONCAT(\'uploads/\', rpgdx_uploads.upload_type, rpgdx_uploads.upload_id, rpgdx_uploads.upload_ext)');
//define('DATE_FORMAT', '');
//define('DATE_FORMAT_EXT', '');


?>
