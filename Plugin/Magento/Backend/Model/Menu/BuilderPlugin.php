<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 * 
 */

namespace Risecommerce\Core\Plugin\Magento\Backend\Model\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;
use Risecommerce\Core\Model\Config;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;

class BuilderPlugin
{
    /**
     * @var ItemFactory
     */
    private $menuItemFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var array
     */
    private $configSections;

    /**
     * @var array
     */
    private $risecommerceModules;

    /**
     * BuilderPlugin constructor.
     *
     * @param ItemFactory $menuItemFactory
     * @param Config $config
     * @param Structure $structure
     * @param ModuleListInterface $moduleList
     * @param Manager $moduleManager
     */
    public function __construct(
        ItemFactory $menuItemFactory,
        Config $config,
        Structure $structure,
        ModuleListInterface $moduleList,
        Manager $moduleManager
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->config = $config;
        $this->structure = $structure;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        $this->risecommerceModules = $this->getRisecommerceModules();
    }

    /**
     * @param Builder $subject
     * @param Menu $menu
     * @param $result
     * @return mixed $result
     */
    public function afterGetResult(Builder $subject, Menu $menu, $result)
    {
        $menuEnabled = $this->config->menuEnabled();
        if ($menuEnabled) {
            $item = $this->menuItemFactory->create([
                'data' => [
                    'id' => 'Risecommerce_Core::elements',
                    'title' => 'Risecommerce',
                    'module' => 'Risecommerce_Core',
                    'resource' => 'Risecommerce_Core::elements'
                ]
            ]);
            $menu->add($item, null, 61);
            $subItems = $this->getSubItem($menu->toArray());
            $this->createMenuItem($menu, $subItems, 'Risecommerce_Core::elements');

            $item = $this->menuItemFactory->create([
                'data' => [
                    'id' => 'Risecommerce_Core::extension_and_notification',
                    'title' => 'Extensions &amp; Notifications',
                    'module' => 'Risecommerce_Core',
                    'resource' => 'Risecommerce_Core::elements'
                ]
            ]);
            $menu->add($item, 'Risecommerce_Core::elements', 1000);

            $item = $this->menuItemFactory->create([
                'data' => [
                    'id' => 'Risecommerce_Core::extension_and_notification_view',
                    'title' => 'Manage',
                    'module' => 'Risecommerce_Core',
                    'resource' => 'Risecommerce_Core::elements',
                    'action' => 'adminhtml/system_config/edit/section/rcextension',
                ]
            ]);
            $menu->add($item, 'Risecommerce_Core::extension_and_notification', 1000);

            unset($this->configSections['Risecommerce_Core']);

            foreach ($this->risecommerceModules as $moduleName) {
                $section = $this->getConfigSections($moduleName);

                if (isset($section['id']) && 'rcextension' != $section['id']) {
                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'id' => $section['resource'] . '_custom',
                            'title' => $section['label'],
                            'module' => $moduleName,
                            'resource' => $section['resource']
                        ]
                    ]);
                    $menu->add($item, 'Risecommerce_Core::elements');

                    $item = $this->menuItemFactory->create([
                        'data' => [
                            'id' => $section['resource'] . '_menu',
                            'title' => 'Configuration',
                            'resource' => $section['resource'],
                            'action' => 'adminhtml/system_config/edit/section/' . $section['key'],
                            'module' => $moduleName
                        ]
                    ]);
                    $menu->add($item, $section['resource'] . '_custom', 1000);
                }
            }
        }

        return $result;
    }

    /**
     * @param $moduleName
     * @return mixed|null
     */
    private function getConfigSections($moduleName)
    {
        if (null === $this->configSections) {
            $sections = [];
            $this->configSections = [];
            $tabs = $this->structure->getTabs();

            foreach ($tabs as $tab) {
                if (in_array($tab->getId(), ['risecommerce', 'rc_extensions_list'])) {
                    $sections = array_merge($sections, $tab->getData()['children']);
                }
            }

            foreach ($sections as $key => $section) {
                if (empty($section['resource']) || 0 !== strpos($section['resource'], 'Risecommerce_')) {
                    continue;
                }

                $section['key'] = $key;
                $mName =  $this->getModuleNameByResource($section['resource']);
                $this->configSections[$mName] = $section;
            }
        }

        return isset($this->configSections[$moduleName]) ? $this->configSections[$moduleName] : null;
    }

    /**
     * @param $resource
     * @return string
     */
    private function getModuleNameByResource($resource)
    {
        $moduleName =  explode(':', $resource);
        $moduleName = $moduleName[0];

        return $moduleName;
    }

    /**
     * @param $menu
     * @param $items
     * @param $parentId
     */
    private function createMenuItem($menu, $items, $parentId)
    {
        foreach ($items as $item) {
            $moduleName = isset($item['module']) ? $item['module'] : null;
            $title = preg_replace('/(?<!\ )[A-Z]/', ' $0', $moduleName);
            $title = trim(str_replace('Risecommerce_', '', $title));
            $needCreateMenuItem = ('Risecommerce_Core::elements' == $parentId && !empty($item['action']));
            if ($needCreateMenuItem) {
                $subItem = $this->menuItemFactory->create([
                    'data' => [
                        'id' => $item['id'] . '3',
                        'title' => $title,
                        'resource' => $item['resource'],
                        'module' => isset($item['module']) ? $item['module'] : null,
                    ]
                ]);
                $menu->add($subItem, $parentId);
            }

            $subItem = $this->menuItemFactory->create([
                'data' => [
                    'id' => $item['id'] . '2',
                    'title' => $item['title'],
                    'resource' => $item['resource'],
                    'action' => $item['action'],
                    'module' => isset($item['module']) ? $item['module'] : null,
                ]
            ]);
            if ($needCreateMenuItem) {
                $menu->add($subItem, $item['id'] . '3');
            } else {
                $menu->add($subItem, $parentId);
            }

            if (!empty($item['sub_menu'])) {
                $this->createMenuItem($menu, $item['sub_menu'], $item['id'] . '2');
            }

            if ('Risecommerce_Core::elements' == $parentId) {
                $addConfig = true;
                if (!empty($item['sub_menu'])) {
                    foreach ($item['sub_menu'] as $subItem) {
                        if ('Configuration' == $subItem['title']) {
                            $addConfig = false;
                            break;
                        }
                    }
                }

                if ($addConfig) {
                    $section = $this->getConfigSections($moduleName);
                    if ($section) {
                        $subItem = $this->menuItemFactory->create([
                            'data' => [
                                'id' => $section['resource'] . '_menu',
                                'title' => 'Configuration',
                                'resource' => $section['resource'],
                                'action' => 'adminhtml/system_config/edit/section/' . $section['key'],
                                'module' => $moduleName
                            ]
                        ]);
                        if ($needCreateMenuItem) {
                            $menu->add($subItem, $item['id'] . '3');
                        } else {
                            $menu->add($subItem, $item['id'] . '2');
                        }
                    }
                }
            }
            unset($this->configSections[$moduleName]);
            $index = array_search($moduleName, $this->risecommerceModules);
            if (false !== $index) {
                unset($this->risecommerceModules[$index]);
            }
        }
    }

    /**
     * @param $items
     * @return array
     */
    private function getSubItem($items)
    {
        $subItems = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                if (isset($item['module']) &&  0 === strpos($item['module'], 'Risecommerce_')
                    || !isset($item['module']) && isset($item['id']) && 0 === strpos($item['id'], 'Risecommerce_')
                ) {
                    if ('Risecommerce_Core::elements' != $item['id']) {
                        $subItems[] = $item;
                    }
                } elseif (!empty($item['sub_menu'])) {
                    $subItems = array_merge($subItems, $this->getSubItem($item['sub_menu']));
                }
            }
        }

        return $subItems;
    }

    /**
     * Retrieve Risecommerce modules info
     *
     * @return array
     */
    private function getRisecommerceModules()
    {
        $modules = [];
        foreach ($this->moduleList->getNames() as $moduleName) {
            if (strpos($moduleName, 'Risecommerce_') !== false && $this->moduleManager->isEnabled($moduleName)) {
                $modules[] = $moduleName;
            }
        }
        return $modules;
    }
}
