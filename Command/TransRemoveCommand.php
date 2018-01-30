<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class TransRemoveCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:remove')
            ->setDescription('Remove translations messages from your catalogue files.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('id', InputArgument::REQUIRED, 'Translation Message ID (Ej."label.home").')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:REMOVE => INFO : Starting Remove Process ...');
        $helper = $this->getHelper('question');
        $filesystem = new Filesystem();

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:REMOVE => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:REMOVE => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:REMOVE => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        // Process
        if ($domainfiles['path'] == '') {
            $output->writeln('TRANS:REMOVE => ERROR : Default locale file "' . $domainfiles['default'] . '" required and not found. Run "trans:repair ' . $input->getArgument('domain') . '" command to help you solve it.');
            die();
        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            if (!is_array($defaultdata)) {
                $output->writeln('TRANS:REMOVE => WARNING : Default locale file "' . $domainfiles['default'] . '" cant´t be opened. Incorrect format?. Process will continue without this file.');

            } else {
                if (!isset($defaultdata[$input->getArgument('id')])) {
                    $output->writeln('TRANS:REMOVE => INFO : ID="' . $input->getArgument('id') . '" not found in file "' . $domainfiles['default'] . '".');
                } else {
                    $output->writeln('TRANS:REMOVE => INFO : ID="' . $input->getArgument('id') . '" found in default file "' . $domainfiles['default'] . '".');
                    $question = new ConfirmationQuestion('TRANS:REMOVE => WARNING : Translation ID="' . $input->getArgument('id') . '" will be deleted from default file "' . $domainfiles['default'] . '", even if it is being used in the project. Continue? (y/n) : ', false);

                    if ($helper->ask($input, $output, $question)) {
                        unset($defaultdata[$input->getArgument('id')]);
                        $common->putArrayInFile($domainfiles['path'], $domainfiles['format'], $defaultdata);
                        $output->writeln('TRANS:REMOVE => SUCCESS : Translation message ID="' . $input->getArgument('id') . '" removed from default file "' . $domainfiles['default'] . '"!');
                    } else {
                        $output->writeln('TRANS:REMOVE => INFO : Remove ID="' . $input->getArgument('id') . '" from default file "' . $domainfiles['default'] . '" cancelled.');
                    }
                }
            }
        }
        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {
                if ($other['path'] == '') {
                    $output->writeln('TRANS:REMOVE => WARNING : File "' . $other['filename'] . '" not found. Run "trans:repair ' . $input->getArgument('domain') . '" command to help you solve it. Process will continue without working in this file.');
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if (!is_array($otherdata)) {
                        $output->writeln('TRANS:REMOVE => WARNING : File "' . $other['filename'] . '" cant´t be opened. Incorrect format?. Process will continue without this file.');
                    } else {
                        if (!isset($otherdata[$input->getArgument('id')])) {
                            $output->writeln('TRANS:REMOVE => INFO : ID="' . $input->getArgument('id') . '"not found in file "' . $other['filename'] . '".');
                        } else {
                            $output->writeln('TRANS:REMOVE => INFO : ID="' . $input->getArgument('id') . '" found in file "' . $other['filename'] . '".');
                            $question = new ConfirmationQuestion('TRANS:REMOVE => WARNING : Translation ID="' . $input->getArgument('id') . '" will be deleted from file "' . $other['filename'] . '", even if it is being used in the project. Continue? (y/n) : ', false);
                            if ($helper->ask($input, $output, $question)) {
                                unset($otherdata[$input->getArgument('id')]);
                                $common->putArrayInFile($other['path'], $other['format'], $otherdata);
                                $output->writeln('TRANS:REMOVE => SUCCESS : Translation message ID="' . $input->getArgument('id') . '" removed from file "' . $other['filename'] . '"!');

                            } else {
                                $output->writeln('TRANS:REMOVE => INFO : Remove ID="' . $input->getArgument('id') . '" from file "' . $other['filename'] . '" cancelled.');
                            }
                        }
                    }
                }
            }
        }


    }


}