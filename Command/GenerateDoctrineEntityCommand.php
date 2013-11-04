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

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand as BaseCommand;
use Darkwood\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Matheu Ledru <matyo91@gmail.com>
 */
class GenerateDoctrineEntityCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('darkwood:generate:doctrine:entity');
    }

    protected function createGenerator()
    {
        return new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
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
