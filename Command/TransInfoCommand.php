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

class TransInfoCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:info')
            ->setDescription('Get translations messages info from your catalogue files.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('id', InputArgument::REQUIRED, 'Translation Message ID (Ej."label.home").')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:INFO => INFO : Starting Info Process ...');
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
            $output->writeln('TRANS:INFO => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:INFO => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:INFO => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        //Proces
        $output->writeln('');
        $table = new Table($output);
        $table
            ->setHeaders(array('Locale', 'File', 'ID', 'Value'));
        if ($domainfiles['path'] == '') {
            $rows[] = [$domainfiles['locale'], 'Not found!', '', ''];

        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            if (!$defaultdata) {
                $rows[] = [$domainfiles['locale'], 'Invalid format?', '', ''];
            } else {
                if (isset($defaultdata[$input->getArgument('id')])) {
                    $rows[] = [$domainfiles['locale'], $domainfiles['default'], $input->getArgument('id'), $defaultdata[$input->getArgument('id')]];
                } else {
                    $rows[] = [$domainfiles['locale'], $domainfiles['default'], $input->getArgument('id'), 'Not found!!'];
                }
            }
        }

        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {
                $rows[] = new TableSeparator();
                if ($other['path'] == '') {
                    $rows[] = [$other['locale'], 'Not found!', '', ''];
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if (!$otherdata) {
                        $rows[] = [$other['locale'], 'Invalid format?', '', ''];
                    } else {
                        if (isset($otherdata[$input->getArgument('id')])) {
                            $rows[] = [$other['locale'], $other['filename'], $input->getArgument('id'), $otherdata[$input->getArgument('id')]];
                        } else {
                            $rows[] = [$other['locale'], $other['filename'], 'Not found!'];

                        }
                    }
                }
            }
        }

        $table->setRows($rows);
        $table->render();

        $output->writeln('');
        $output->writeln('TRANS:INFO => SUCCESS : Translation message info shown!');

    }


}