<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Matheu Ledru <matyo91@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darkwood\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator as BaseGenerator;
use Symfony\Component\DependencyInjection\Container;

/**
 * Generates a bundle.
 *
 * @author Matheu Ledru <matyo91@gmail.com>
 */
class BundleGenerator extends BaseGenerator
{
    public function generate($namespace, $bundle, $dir, $format, $structure)
    {
        parent::generate($namespace, $bundle, $dir, $format, $structure);

        if (preg_match('/CoreBundle$/', $bundle)) {
            //Core bundle

            $dir .= '/'.strtr($namespace, '\\', '/');

            $basename = substr($bundle, 0, -6);
            $parameters = array(
                'namespace' => $namespace,
                'bundle'    => $bundle,
                'format'    => $format,
                'bundle_basename' => $basename,
                'extension_alias' => Container::underscore($basename),
            );

            $this->renderFile('bundle/BaseRepository.php.twig', $dir.'/Repository/BaseRepository.php', $parameters);
            $this->renderFile('bundle/TransactionalService.php.twig', $dir.'/Services/TransactionalService.php', $parameters);
        }
    }
}
