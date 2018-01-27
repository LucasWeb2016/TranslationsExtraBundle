<?php

namespace Lucasweb\TranslationsExtraBundle;

use Lucasweb\TranslationsExtraBundle\Command\TransAddCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransCheckCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransCreateCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransEditCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransImportCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransInfoCommand;
use Lucasweb\TranslationsExtraBundle\Command\TranslationsExtraCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransRemoveCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransRepairCommand;
use Lucasweb\TranslationsExtraBundle\Command\TransSyncCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Lucasweb/TranslationsExtraBundle.
 *
 * @author LucasWeb <https://github.com/LucasWeb2016>
 */

class TranslationsExtraBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        $application->add(new TransCheckCommand());
        $application->add(new TransAddCommand());
        $application->add(new TransEditCommand());
        $application->add(new TransRemoveCommand());
        $application->add(new TransInfoCommand());
        $application->add(new TransImportCommand());
        $application->add(new TransSyncCommand());
        $application->add(new TransCreateCommand());
    }
}