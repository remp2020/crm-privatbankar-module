<?php

namespace Crm\PrivatbankarModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;

class ConfirmationPendingWidget extends BaseWidget
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
