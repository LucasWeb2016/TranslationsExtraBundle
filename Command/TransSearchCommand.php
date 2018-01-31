<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransSearchCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:search')
            ->setDescription('Search for a string in all translation files of a domain.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/TranslationsExtraBundle')
            ->addArgument('searchterm', InputArgument::REQUIRED, 'Translation Message ID (Ej."label.home").')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:SEARCH => INFO : Starting Search Process ...');
        $filesystem = new Filesystem();

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:SEARCH => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:SEARCH => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:SEARCH => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }


        //Proces
        $output->writeln('');
        $table = new Table($output);
        $rows = [];
        $table
            ->setHeaders(array('Locale', 'File', 'ID', 'Value'));
        if ($domainfiles['path'] == '') {
            $rows[] = [$domainfiles['locale'], 'Not found!', '', ''];

        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            if (is_array($defaultdata)) {
                foreach ($defaultdata as $key => $value) {
                    $posvalue = strripos($value, $input->getArgument('searchterm'));
                    $poskey = strripos((string)$key, $input->getArgument('searchterm'));
                    if ($posvalue !== false || $poskey !== false) {
                        $rows[] = [$domainfiles['locale'], $domainfiles['default'], $key, $value];
                    }

                }
            }
        }

        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {
                if ($other['path'] != '') {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if (is_array($otherdata)) {
                        foreach ($otherdata as $key => $value) {
                            $pos = strripos($value, $input->getArgument('searchterm'));
                            if ($pos !== false) {
                                $rows[] = [$other['locale'], $other['filename'], $key, $value];

                            }

                            $posvalue = strripos($value, $input->getArgument('searchterm'));
                            $poskey = strripos((string)$key, $input->getArgument('searchterm'));
                            if ($posvalue !== false || $poskey !== false) {
                                $rows[] = [$other['locale'], $other['filename'], $key, $value];
                            }

                        }

                    }

                }
            }
        }

        $table->setRows($rows);
        if (count($rows) >= 1) {
            $table->render();
        } else {
            $output->writeln('TRANS:SEARCH => Nothing found !!!');

        }

        $output->writeln('');

    }


}