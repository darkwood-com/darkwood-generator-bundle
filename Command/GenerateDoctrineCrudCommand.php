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

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand as BaseCommand;
use Darkwood\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Matheu Ledru <matyo91@gmail.com>
 */
class GenerateDoctrineCrudCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('darkwood:generate:doctrine:crud');
    }

    protected function createGenerator()
    {
        return new DoctrineCrudGenerator($this->getContainer()->get('filesystem'));
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = parent::getSkeletonDirs($bundle);

        $skeletonDirs[] = __DIR__.'/../Resources/skeleton';
        $skeletonDirs[] = __DIR__.'/../Resources';

        return $skeletonDirs;
    }
}
