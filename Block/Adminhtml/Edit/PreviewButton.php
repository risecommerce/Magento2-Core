<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 * 
 */

namespace Risecommerce\Core\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class PreviewButton
 */
class PreviewButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getObjectId()) {
            $data = [
                'label' => __('Preview'),
                'class' => 'preview',
                'on_click' => 'window.open(\'' . $this->getPreviewUrl() . '\');',
                'sort_order' => 35,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview', ['id' => $this->getObjectId()]);
    }
}
