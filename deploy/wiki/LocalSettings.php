<?php
$wgSitename = 'RPGDX';
$wgServer = 'https://rpgdx.net';
$wgScriptPath = '/wiki';
$wgArticlePath = '/wiki/index.php/$1';
$wgLanguageCode = 'en';
$wgDBtype = 'mysql';
$wgDBprefix = 'wiki_';
require '/home/protected/rpgdx-wiki-archive/database.php';
$wgSecretKey = trim(file_get_contents('/home/protected/rpgdx-wiki-archive/secret'));
$wgCacheDirectory = '/home/protected/rpgdx-wiki-archive/cache';
$wgLocalisationCacheConf['store'] = 'files';
$wgMainCacheType = CACHE_NONE;
$wgReadOnly = 'The RPGDX wiki is a read-only archive.';
$wgReadOnlyWatchedItemStore = true;
$wgEnableEmail = false;
$wgEnableUploads = false;
$wgUseDatabaseMessages = false;
$wgUseSiteJs = false;
$wgUseSiteCss = false;
$wgAllowUserJs = false;
$wgAllowUserCss = false;
$wgJobRunRate = 0;
foreach ( [ 'edit', 'createpage', 'createtalk', 'createaccount', 'writeapi' ] as $right ) {
    $wgRevokePermissions['*'][$right] = true;
}

$wgRCMaxAge = 10950 * 86400;
$wgRCLinkDays[] = 10950;
$wgDefaultUserOptions['rcdays'] = 10950;
$wgRCLinkLimits = [ 50, 100, 250, 500, 1000, 2500, 5000 ];
$wgDefaultUserOptions['rclimit'] = 2500;

wfLoadSkin( 'Vector' );
