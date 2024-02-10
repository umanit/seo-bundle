<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Sonata\AdminExtension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class SeoAdminExtension extends AbstractAdminExtension
{
    public function configureListFields(ListMapper $list): void
    {
        if ($list->has('_action')) {
            $actions = $list->get('_action')->getOption('actions');

            if ($actions && isset($actions['show'])) {
                // Overrides show action to use SeoBundle system
                $actions['show'] = ['template' => '@UmanitSeo/sonata/admin/CRUD/list__action_show.html.twig'];
                $list->get('_action')->setOption('actions', $actions);
            }
        }
    }

    public function configurePersistentParameters(AdminInterface $admin, array $parameters): array
    {
        $admin->setTemplate('button_show', '@UmanitSeo/sonata/admin/Button/show_button.html.twig');

        return parent::configurePersistentParameters($admin, $parameters);
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $show->add('seoMetadata');

        parent::configureShowFields($show);
    }
}
