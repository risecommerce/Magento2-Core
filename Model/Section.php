<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 * 
 */

namespace Risecommerce\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Section
 * @package Risecommerce\Core\Model
 */
final class Section
{
    const MODULE = 'rcmodule';

    const ENABLED = 'enabled';

    const KEY = 'key';

    const TYPE = 'rctype';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $key;

    /**
     * @var ProductMetadataInterface
     */
    protected $metadata;


    /**
     * Section constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $metadata
     * @param null $name
     * @param null $key
     */
    final public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $metadata,
        $name = null,
        $key = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->metadata = $metadata;
        $this->name = $name;
        $this->key = $key;
    }

    /**
     * @return bool
     */
    final public function isEnabled()
    {
        return (bool) $this->getConfig(self::ENABLED);
    }

    /**
     * @return string
     */
    final public function getModule()
    {
        $module = (string) $this->getConfig(self::MODULE);
        $url = $this->scopeConfig->getValue(
            'web/unsecure/base' . '_' . 'url',
            ScopeInterface::SCOPE_STORE,
            0
        );

        if (\Risecommerce\Core\Model\UrlChecker::showUrl($url)) {
            if ($module
                && (!$this->getConfig(self::TYPE)
                    || $this->getConfig(self::TYPE) && $this->metadata->getEdition() != 'C' . 'omm' . 'un' . 'ity'
                )
            ) {
                return $module;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    final public function getKey()
    {
        if (null !== $this->key) {
            return $this->key;
        } else {
            return $this->getConfig(self::KEY);
        }
    }

    /**
     * @return string
     */
    final public function getName()
    {
        return (string) $this->name;
    }

    /**
     * @param $data
     * @param null $k
     * @return bool
     */
    final public function validate($data)
    {
        if (isset($data[$this->getModule()])) {
            return !empty($data[$this->getModule()]);
        }

        $id = $this->getModule();
        $k = $this->getKey();

        $result = $this->validateIDK($id, $k);
        if (!$result) {
            $id .= 'Plus';
            $result = $this->validateIDK($id, $k);
        }

        return $result;
    }

    /**
     * @param string $id
     * @param string $k
     * @return bool
     */
    private function validateIDK($id, $k)
    {
        $l = substr($id, 1, 1);
        $d = (string) strlen($id);

        return (strlen($k) >= '3' . '2')
            && (strpos($k, $l, 5) == 5)
            && (strpos($k, $d, 19) == 19);
    }

    /**
     * @param string $field
     * @return mixed
     */
    private function getConfig($field)
    {
        $g = 'general';
        return $this->scopeConfig->getValue(
            implode('/', [$this->name, $g, $field]),
            ScopeInterface::SCOPE_STORE,
            0
        );
    }
}
