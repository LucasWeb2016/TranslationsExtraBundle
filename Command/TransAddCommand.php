<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransAddCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:add')
            ->setDescription('Add new translation messages to your translations files.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:ADD => INFO : Starting Add Process ...');
        $helper = $this->getHelper('question');
        $filesystem = new Filesystem();

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));


        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:ADD => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:ADD => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:ADD => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        // Process
        if ($domainfiles['path'] == '') {
            $output->writeln('TRANS:ADD => ERROR : Default locale file "' . $domainfiles['default'] . '" required and not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it.Process can´t continue without this file.');
            die();
        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            $replytarget = null;
            if (!$defaultdata) {
                $output->writeln('TRANS:ADD => ERROR : Default locale file "' . $domainfiles['default'] . '" cant´t be opened. Incorrect format?. Process can´t continue without this file.');
                die();
            } else {
                $a = 0;
                while ($a <= 0) {
                    $question = new Question('TRANS:ADD => QUESTION : Please, enter unique ID for the new message : ', '');
                    $replytarget = $helper->ask($input, $output, $question);
                    if (!$replytarget == '' && is_string($replytarget)) {
                        if (!isset($defaultdata[$replytarget])) {
                            $a = 1;
                            $output->writeln('TRANS:ADD => INFO : The ID you have entered is valid! ');
                        } else {
                            $output->writeln('TRANS:ADD => WARNING : The ID you have entered is already being used. Please enter another one or use trans:edit command.');
                        }
                    }
                }

                $a = 0;
                $replytarget2 = "";
                while ($a <= 0) {
                    $question = new Question('TRANS:ADD => QUESTION : Please, enter value for ID="' . $replytarget . '" in file "' . $domainfiles['default'] . '" : ', '');
                    $replytarget2 = $helper->ask($input, $output, $question);
                    if (!$replytarget2 == '' && is_string($replytarget2)) {
                        $a = 1;
                    }
                }

                $defaultdata[$replytarget] = $replytarget2;
                $common->putArrayInFile($domainfiles['path'], $domainfiles['format'], $defaultdata);
            }
        }
        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {
                if ($other['path'] == '') {
                    $output->writeln('TRANS:ADD => WARNING : File "' . $other['filename'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to help you solve it. Process will continue without working in this file.');
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if (!$otherdata) {
                        $output->writeln('TRANS:ADD => WARNING : File "' . $other['filename'] . '" cant´t be opened. Incorrect format?. Process will continue without this file.');
                    } else {
                        if (isset($otherdata[$replytarget])) {
                            $output->writeln('TRANS:ADD => WARNING : The ID you have entered exists in file "' . $other['filename'] . '" but not in default file "' . $domainfiles['default'] . '". The process will continue without creating this translation in this file.Run "trans:sync ' . $input->getArgument('domain') . '" command to solve it.');
                        } else {
                            $a = 0;
                            $replytarget3 = "";
                            while ($a <= 0) {
                                if ($config['yandex_api_key'] != '') {
                                    $trans = $common->YandexTrans($replytarget2, $domainfiles['locale'], $other['locale'], $config, $output);
                                    if ($trans == '') {
                                        $yandex = '';
                                        $yandexraw = '';
                                    } else {
                                        $yandex = ' (Yandex Translation: ' . $trans . ' )';
                                        $yandexraw = $trans;
                                    }
                                } else {
                                    $yandex = '';
                                    $yandexraw = '';
                                }
                                $question = new Question('TRANS:ADD => QUESTION : Please, enter value for ID="' . $replytarget . '" in file "' . $other['filename'] . '"' . $yandex . ' : ', $yandexraw);
                                $replytarget3 = $helper->ask($input, $output, $question);
                                if (!$replytarget3 == '' && is_string($replytarget3)) {
                                    $a = 1;
                                }
                            }
                            $otherdata[$replytarget] = $replytarget3;
                            $common->putArrayInFile($other['path'], $other['format'], $otherdata);
                        }
                    }
                }
            }
        }

        $output->writeln('TRANS:ADD => SUCCESS : Translation message created!');
    }
}