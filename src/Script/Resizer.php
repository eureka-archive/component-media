<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Media\Script;

use Eureka\Component\Media\Image\Image;
use Eureka\Eurekon;

/**
 * Resizer image.
 *
 * @author  Romain Cottard
 */
class Resizer extends Eurekon\AbstractScript
{
    /** @var array $images */
    private $images = [];

    /** @var array $queries */
    private $queries = [];

    /**
     * Resizer constructor.
     */
    public function __construct()
    {
        $this->setDescription('Resizer image script');
        $this->setExecutable(true);
    }

    /**
     * @inheritdoc
     */
    public function help()
    {
        $style = new Eurekon\Style\Style(' *** RUN - HELP ***');
        Eurekon\IO\Out::std($style->color('fg', Eurekon\Style\Color::GREEN)->get());
        Eurekon\IO\Out::std('');

        $help = new Eurekon\Help(self::class);
        $help->addArgument('s', 'source', 'Source', true, true);
        $help->addArgument('w', 'new-width', 'Max width for the image (default: 800)', true, false);
        $help->addArgument('h', 'new-height', 'Max height for the image (default: 600)', true, false);
        $help->addArgument('',  'keep-ratio', 'If the resizing operation must keep the original ratio h/w of the image.', false, false);

        $help->display();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $arguments = Eurekon\Argument\Argument::getInstance();
        $source    = realpath($arguments->get('s', 'source'));
        $target    = rtrim($arguments->get('t', 'target', null), '/\\') ;
        $keepRatio = $arguments->get('keep-ratio', null, false);

        //~ Get source(s)
        if (is_dir($source)) {
            $sources = glob(rtrim($source, '/') . '/*.jpg');
            $sources = array_merge($sources, glob(rtrim($source, '/') . '/*.jpeg'));
            $sources = array_merge($sources, glob(rtrim($source, '/') . '/*.png'));
        } elseif (is_file($source)) {
            $sources = [$source];
        } else {
            throw new \RuntimeException('Source is not a valid dir / file (source: ' . $source . ')');
        }

        foreach ($sources as $file) {
            Eurekon\IO\Out::std('Process image ...', "\r");
            $image = new Image($file);
            $newWidth  = $arguments->get('w', 'new-width', $image->getWidth());
            $newHeight = $arguments->get('h', 'new-height', $image->getHeight());

            echo 'new width: ' . $newWidth . PHP_EOL;
            echo 'new height: ' . $newHeight . PHP_EOL;

            $image->resize($newWidth, $newHeight, $keepRatio);
            $image->saveAsPng($target . DIRECTORY_SEPARATOR . basename($file));

            Eurekon\IO\Out::std('Process image ... done');
        }

        Eurekon\IO\Out::std(implode(PHP_EOL, $this->images));
        Eurekon\IO\Out::std(implode(';' . PHP_EOL, $this->queries));
    }
}
