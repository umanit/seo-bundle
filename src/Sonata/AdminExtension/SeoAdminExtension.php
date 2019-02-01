<?php

namespace Umanit\SeoBundle\Sonata\AdminExtension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;

/**
 * Class SeoAdminExtension
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoAdminExtension extends AbstractAdminExtension
{
    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    public function __construct(MutableTemplateRegistryInterface $templateRegistry)
    {
        $this->templateRegistry = $templateRegistry;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        if ($listMapper->has('_action')) {
            $actions = $listMapper->get('_action')->getOption('actions');
            if ($actions && isset($actions['show'])) {
                // Overrides show action to use SeoBundle system
                $actions['show'] = ['template' => '@UmanitSeo/admin/CRUD/list__action_show.html.twig'];
                $listMapper->get('_action')->setOption('actions', $actions);
            }
        }
    }

    public function getPersistentParameters(AdminInterface $admin)
    {
        $admin->setTemplate('button_show', '@UmanitSeo/admin/Button/show_button.html.twig');
        return parent::getPersistentParameters($admin);
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('seoMetadata');
        parent::configureShowFields($showMapper);
    }
}
