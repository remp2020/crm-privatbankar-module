<?php

namespace Crm\PrivatbankarModule;

use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Authorization\NoAuthorization;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApplicationModule\CrmModule;
use Crm\ApplicationModule\SeederManager;
use Crm\ApplicationModule\Widget\WidgetManagerInterface;
use Crm\PrivatbankarModule\Api\IpnHandler;
use Crm\PrivatbankarModule\Components\ConfirmationPendingWidget;
use Crm\PrivatbankarModule\Seeders\ConfigsSeeder;
use Crm\PrivatbankarModule\Seeders\PaymentGatewaysSeeder;
use Crm\PrivatbankarModule\Seeders\SalesFunnelsSeeder;

class PrivatbankarModule extends CrmModule
{
    public function registerSeeders(SeederManager $seederManager)
    {
        $seederManager->addSeeder($this->getInstance(ConfigsSeeder::class));
        $seederManager->addSeeder($this->getInstance(PaymentGatewaysSeeder::class));
        $seederManager->addSeeder($this->getInstance(SalesFunnelsSeeder::class));
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(
            new ApiRoute(
                new ApiIdentifier('1', 'privatbankar', 'ipn'),
                IpnHandler::class,
                NoAuthorization::class
            )
        );
    }

    public function registerWidgets(WidgetManagerInterface $widgetManager)
    {
        $widgetManager->registerWidget(
            'privatbankar.return.pending',
            $this->getInstance(ConfirmationPendingWidget::class),
            500
        );
    }
}
