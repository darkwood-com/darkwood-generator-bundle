<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Matheu Ledru <matyo91@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darkwood\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as BaseCommand;
use Darkwood\GeneratorBundle\Generator\BundleGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates bundles.
 *
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
class GenerateBundleCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('darkwood:generate:bundle');
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = parent::getSkeletonDirs($bundle);

        array_splice($skeletonDirs, -2, 0, array(
            __DIR__.'/../Resources/skeleton',
            __DIR__.'/../Resources',
        ));

        return $skeletonDirs;
    }
}
