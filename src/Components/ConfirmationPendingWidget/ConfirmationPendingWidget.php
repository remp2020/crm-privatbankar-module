<?php

namespace Crm\PrivatbankarModule\Components;

use Crm\ApplicationModule\Widget\BaseLazyWidget;

class ConfirmationPendingWidget extends BaseLazyWidget
{
    private $templateName = 'confirmation_pending_widget.latte';

    public function identifier()
    {
        return 'privatbankarconfirmationpendingwidget';
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . $this->templateName);
        $this->template->render();
    }
}
