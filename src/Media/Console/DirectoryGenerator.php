<?php

/**
 * Copyright (c) 2010-2017 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Media\Console;

use Eureka\Eurekon;

/**
 * Console Abstraction class.
 * Must be parent class for every console script class.
 *
 * @author  Romain Cottard
 */
class DirectoryGenerator extends Eurekon\Console
{
    /**
     * @var boolean $executable Set to true to set class as an executable script
     */
    protected $executable = true;

    /**
     * @var boolean $executable Console script description.
     */
    protected $description = 'Orm generator';

    /**
     * Help method.
     *
     * @return void
     */
    public function help()
    {
        $style = new Eurekon\Style(' *** RUN - HELP ***');
        Eurekon\Out::std($style->color('fg', Eurekon\Style::COLOR_GREEN)->get());
        Eurekon\Out::std('');

        $help = new Eurekon\Help('...', true);
        $help->addArgument('d', 'directory',  'Directory path how the script generate 3 level directories for media.', true, true);
        $help->addArgument('p', 'perms', 'Default perms for the subdirectories generated (without leading 0, ie 777)', true, false);
        $help->addArgument('g', 'group', 'User group to apply to the directories generated (unix only)', true, false);

        $help->display();
    }

    /**
     * Run method.
     *
     * @return void
     */
    public function run()
    {
        $argument = Eurekon\Argument::getInstance();

        $directory = (string) $argument->get('d', 'directory');
        $perms     = (int) $argument->get('p', 'perms');
        $perms     = 0 . $perms;

        if (!is_dir($directory)) {
            throw new \RuntimeException('Directory does not exist! (dir: ' . escapeshellarg($directory) . ')');
        }

        $this->generate($directory, $perms);
    }

    /**
     * @param  string $directory
     * @param  string $perms
     * @param  int $level
     * @return void
     */
    private function generate($directory, $perms, $level = 0)
    {
        for($index = 0; $index < 16; $index++) {
            $name = dechex($index);
            $dir  = $directory . DIRECTORY_SEPARATOR . $name;

            if (!is_dir($dir)) {
                mkdir($dir, $perms);
            }

            if ($level < 2) {
                $this->generate($dir, $perms, $level + 1);
            }
        }
    }
}
