<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
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
        if ($domainfiles['path'] == '') {
            $output->writeln('TRANS:INFO => WARNING : Default locale file "' . $domainfiles['default'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it. Process will continue without working in this file.');
        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            if (!$defaultdata) {
                $output->writeln('TRANS:INFO => WARNING : File "' . $domainfiles['default'] . '" cant´t be opened. Incorrect format?. Process will continue without this file.');
            } else {
                if (isset($defaultdata[$input->getArgument('id')])) {
                    $output->writeln('TRANS:INFO => INFO : Value of message ID="' . $input->getArgument('id') . '" in file "' . $domainfiles['default'] . '" is "' . $defaultdata[$input->getArgument('id')] . '"');

                } else {
                    $output->writeln('TRANS:INFO => INFO : Value of message ID="' . $input->getArgument('id') . '" not found in file "' . $domainfiles['default'] . '"');
                }
            }
        }

        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {
                if ($other['path'] == '') {
                    $output->writeln('TRANS:INFO => WARNING : File "' . $other['filename'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it. Process will continue without working in this file.');
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if (!$otherdata) {
                        $output->writeln('TRANS:INFO => WARNING : File "' . $other['filename'] . '" cant´t be opened. Incorrect format?. Process will continue without this file.');
                    } else {
                        if (isset($otherdata[$input->getArgument('id')])) {
                            $output->writeln('TRANS:INFO => INFO : Value of message ID="' . $input->getArgument('id') . '" in file "' . $other['filename'] . '" is "' . $otherdata[$input->getArgument('id')] . '"');

                        } else {
                            $output->writeln('TRANS:INFO => INFO : Value of message ID="' . $input->getArgument('id') . '" not found in file "' . $other['filename'] . '"');
                        }
                    }
                }
            }
        }

        $output->writeln('TRANS:INFO => SUCCESS : Translation message info shown!');

    }


}