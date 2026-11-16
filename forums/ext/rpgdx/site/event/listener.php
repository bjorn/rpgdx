<?php

namespace rpgdx\site\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    protected $template;
    protected $path_helper;
    protected $request;
    protected $user;
    protected $in_ucp = false;

    public function __construct(\phpbb\template\template $template, \phpbb\path_helper $path_helper, \phpbb\request\request_interface $request, \phpbb\user $user)
    {
        $this->template = $template;
        $this->path_helper = $path_helper;
        $this->request = $request;
        $this->user = $user;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'core.page_header_after' => 'navigation',
            'core.ucp_display_module_before' => 'ucpNavigation',
            'core.generate_forum_nav' => 'breadcrumbs',
            'core.viewtopic_cache_user_data' => 'profileDate',
        );
    }

    public function ucpNavigation($event)
    {
        // This event runs for control-panel modules, not login or registration.
        $this->in_ucp = true;
    }

    public function navigation($event)
    {
        // Relative to phpBB's configured root, including when using a subdirectory.
        $site_root = $this->path_helper->get_web_root_path() . '../';
        $page_title = basename($this->request->server('SCRIPT_NAME', '')) === 'index.php' ? 'Forums' : $event['page_title'];
        if ($this->in_ucp && $this->user->style['style_path'] === 'rpgdx') {
            $page_title = $this->user->lang('UCP');
            $this->template->destroy_block_vars('navlinks');
            $this->template->assign_block_vars('navlinks', array(
                'BREADCRUMB_NAME' => $page_title,
                'U_BREADCRUMB' => append_sid($this->path_helper->get_web_root_path() . 'ucp.php'),
            ));
        }
        $this->template->assign_vars(array(
            'U_RPGDX_HOME' => $site_root,
            'U_SITE_HOME' => $site_root,
            'L_SITE_HOME' => 'RPGDX',
            'L_INDEX' => 'Forums',
            'RPGDX_PAGE_TITLE' => $page_title,
            'S_RPGDX_ANALYTICS' => in_array(strtolower($this->request->server('SERVER_NAME', '')), array('rpgdx.net', 'www.rpgdx.net'), true),
        ));
    }

    public function profileDate($event)
    {
        if ($this->user->style['style_path'] !== 'rpgdx' || empty($event['row']['user_regdate'])) {
            return;
        }
        $profile = $event['user_cache_data'];
        $profile['joined'] = $this->user->format_date($event['row']['user_regdate'], 'd M Y');
        $event['user_cache_data'] = $profile;
    }

    public function breadcrumbs($event)
    {
        $parents = $event['navlinks_parents'];
        foreach ($parents as &$parent) {
            if (!empty($parent['S_IS_CAT'])) {
                $parent['MICRODATA'] .= ' data-rpgdx-category="true"';
            }
        }
        $event['navlinks_parents'] = $parents;
    }
}
