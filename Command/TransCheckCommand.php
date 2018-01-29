<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class TransCheckCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:check')
            ->setDescription('Check translation catalogue files.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:CHECK => INFO : Starting Check Process ...');
        $filesystem = new Filesystem();
        $otherdata = [];
        $defaultdata = [];

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:CHECK => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:CHECK => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:CHECK => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        $output->writeln('');
        $table = new Table($output);
        $table
            ->setHeaders(array('Locale', 'File', 'Format', 'Messages', 'Status'));

        if ($domainfiles['path'] == '') {
            $domainfiles['default'] = $input->getArgument('domain') . '.' . $domainfiles['locale'] . '.???';;
            $domainfiles['format'] = "???";
            $defaultdata = [];
            $domainfiles['status'] = 'File not found, Run "trans:create ' . $input->getArgument('domain') . '" to solve it!';
            $rows[] = [$domainfiles['locale'], $domainfiles['default'], $domainfiles['format'], count($defaultdata), $domainfiles['status']];

        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            if ($defaultdata) {
                $domainfiles['status'] = 'Ok!';
                $rows[] = [$domainfiles['locale'], $domainfiles['default'], $domainfiles['format'], count($defaultdata), $domainfiles['status']];
            } else {
                $domainfiles['status'] = 'File can´t be opened. Incorrect format?';
                $rows[] = [$domainfiles['locale'], $domainfiles['default'], $domainfiles['format'], 0, $domainfiles['status']];
            }
        }
        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {

                if ($other['path'] == '') {
                    $other['filename'] = $input->getArgument('domain') . '.' . $other['locale'] . '.???';
                    $other['format'] = "???";
                    $otherdata = [];
                    $other['status'] = 'File not found, Run "trans:create ' . $input->getArgument('domain') . '" to solve it!';
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if ($otherdata) {
                        if (count($defaultdata) == count($otherdata)) {
                            $other['status'] = 'Ok!';
                        } else {
                            $other['status'] = 'Different quantity of messages than default locale, Run "trans:sync ' . $input->getArgument('domain') . '" to solve it!';
                        }

                    } else {
                        $other['status'] = 'File can´t be opened. Incorrect format?';
                        $otherdata = [];
                    }
                }
                $rows[] = new TableSeparator();
                $rows[] = [$other['locale'], $other['filename'], $other['format'], count($otherdata), $other['status']];
            }
        }

        $table->setRows($rows);
        $table->render();

        //Process finished
        $output->writeln('');
        $output->writeln('TRANS:CHECK => SUCCESS : Check process finished!');
    }
}
