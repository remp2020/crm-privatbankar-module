<?php

namespace Crm\PrivatbankarModule\Seeders;

use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\Repository\ConfigCategoriesRepository;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ApplicationModule\Seeders\ConfigsTrait;
use Crm\ApplicationModule\Seeders\ISeeder;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigsSeeder implements ISeeder
{
    use ConfigsTrait;

    private $configCategoriesRepository;

    private $configsRepository;

    private $configBuilder;

    public function __construct(
        ConfigCategoriesRepository $configCategoriesRepository,
        ConfigsRepository $configsRepository,
        ConfigBuilder $configBuilder
    ) {
        $this->configCategoriesRepository = $configCategoriesRepository;
        $this->configsRepository = $configsRepository;
        $this->configBuilder = $configBuilder;
    }

    public function seed(OutputInterface $output)
    {
        $category = $this->configCategoriesRepository->findBy('name', 'payments.config.category');
        $sorting = 3001;

        $this->addConfig(
            $output,
            $category,
            'privatbankar_source',
            ApplicationConfig::TYPE_STRING,
            'privatbankar.config.source.name',
            'privatbankar.config.source.description',
            null,
            $sorting++
        );

        $this->addConfig(
            $output,
            $category,
            'privatbankar_mode',
            ApplicationConfig::TYPE_STRING,
            'privatbankar.config.mode.name',
            'privatbankar.config.mode.description',
            'test',
            $sorting++
        );
    }
}
