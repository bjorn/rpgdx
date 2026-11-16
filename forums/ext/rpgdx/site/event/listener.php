<?php

namespace rpgdx\site\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    protected $template;
    protected $path_helper;
    protected $request;

    public function __construct(\phpbb\template\template $template, \phpbb\path_helper $path_helper, \phpbb\request\request_interface $request)
    {
        $this->template = $template;
        $this->path_helper = $path_helper;
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return array('core.page_header_after' => 'navigation');
    }

    public function navigation()
    {
        // Relative to phpBB's configured root, including when using a subdirectory.
        $site_root = $this->path_helper->get_web_root_path() . '../';
        $this->template->assign_vars(array(
            'U_RPGDX_HOME' => $site_root,
            'U_SITE_HOME' => $site_root,
            'L_SITE_HOME' => 'RPGDX',
            'S_RPGDX_ANALYTICS' => in_array(strtolower($this->request->server('SERVER_NAME', '')), array('rpgdx.net', 'www.rpgdx.net'), true),
        ));
    }
}
