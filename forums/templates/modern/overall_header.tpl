<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="{S_CONTENT_DIRECTION}">

<head>
<meta http-equiv="Content-Type" content="text/html; charset={S_CONTENT_ENCODING}">
<meta name="Keywords" content="forums, forum, rpg, rpgs, rpgdx, indie, indierpg, community, game, games, free, independant, programmers, community, graphics, sound, music, development, projects, resources, graphics, sound, music, qbasic, qb, quickbasic, c, c++">
<meta name="Description" content="A community site for independent RPG programmers, allowing project pages, discussion on forums and resource sharing.">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="templates/modern/{T_HEAD_STYLESHEET}" type="text/css">
<link rel="stylesheet" href="templates/modern/stylesheet.css" type="text/css">
<script defer data-domain="rpgdx.net" src="https://plausible.io/js/script.js"></script>
{META}
{NAV_LINKS}
<title>{SITENAME} &gt; Forums
<!-- BEGIN subtitle -->
&gt; {subtitle.TITLE}
<!-- END subtitle -->
</title>

<!-- BEGIN switch_enable_pm_popup -->
<script language="Javascript" type="text/javascript">
<!--
    if ( {PRIVATE_MESSAGE_NEW_FLAG} )
    {
        window.open('{U_PRIVATEMSGS_POPUP}', '_phpbbprivmsg', 'HEIGHT=225,resizable=yes,WIDTH=400');;
    }
//-->
</script>
<!-- END switch_enable_pm_popup -->

</head>
<body>

<a name="top"></a>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="0" valign="top">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td>
            <a href="{U_RPGDX}"><img src="templates/modern/images/rpgdx_logo.png" style="margin: 0px 0px 0px 4px;" alt="RPGDX" title="" border="0" /></a><img src="templates/modern/images/rpgdx_logotext.png" alt="The center of Indie-RPG gaming" title="" style="margin: 0px 0px 3px 8px;" />
          </td>
          <td style="text-align: right;">
          <!-- Optional image here -->
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td width="100%" class="user_bar">
            <!-- BEGIN switch_user_logged_out -->
            Not logged in.
            <!-- END switch_user_logged_out -->
            <!-- BEGIN switch_user_logged_in -->
            Welcome
            <!-- END switch_user_logged_in -->
            <!-- BEGIN switch_user_is_admin -->
            admin.
            <!-- END switch_user_is_admin -->
            <!-- BEGIN switch_user_logged_in -->
            {USERNAME}
            <!-- END switch_user_logged_in -->
            [<a href="{U_LOGIN_LOGOUT}">{L_LOGIN_LOGOUT}</a>]
            <!-- BEGIN switch_user_logged_out -->
            [<a href="{U_REGISTER}">{L_REGISTER}</a>]
            <!-- END switch_user_logged_out -->
          </td>
          <td class="user_bar" align="right">
            <table class="user_links" cellspacing="0" cellpadding="0">
              <tr>
                <td class="user_link"><a href="{U_FAQ}">{L_FAQ}</a></td>
                <td class="user_link"><a href="{U_SEARCH}">{L_SEARCH}</a></td>
                <td class="user_link"><a href="{U_MEMBERLIST}">{L_MEMBERLIST}</a></td>
                <!--
                <td class="user_link"><a href="{U_GROUP_CP}">{L_USERGROUPS}</a></td>
                -->
                <!-- BEGIN switch_user_logged_in -->
                <td class="user_link"><a href="{U_PROFILE}">{L_PROFILE}</a></td>
                <td class="user_link"><a href="{U_PRIVATEMSGS}">{PRIVATE_MESSAGE_INFO}</a></td>
                <!-- END switch_user_logged_in -->
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="font-size: 0px; background-image: url(templates/modern/images/page_top_gradient.png); background-repeat: repeat-x; height: 6px;">&nbsp;</td>
  </tr>

  <tr>
    <td width="100%" valign="top" align="left" style="padding: 10px; padding-top: 0px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td colspan="3" height="9"><img src="{TEMPL_DIR}images/pixel.gif" height="9" width="1" alt="" /></td>
        </tr>
        <tr>
          <td>
            <div class="thin_shadow">
            <div class="lift_by_one">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="100%" class="body_header">
                  {PAGE_TITLE}
                </td>
              </tr>
              <tr>
                <td class="navigation" colspan="3">
                  <a href="http://www.rpgdx.net/">RPGDX</a> &gt;&gt; <a href="{U_INDEX}">Forums</a>
                  <!-- BEGIN subtitle -->
                  &gt;&gt;
                  <!-- BEGIN url -->
                  <a href="{subtitle.url.URL}">
                  <!-- END url -->
                  {subtitle.TITLE}
                  <!-- BEGIN url -->
                  </a>
                  <!-- END url -->
                  <!-- END subtitle -->
                </td>
              </tr>
            </table>
            </div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="seperation" colspan="3">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3" class="body_main">
