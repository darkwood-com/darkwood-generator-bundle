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

use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator as BaseGenerator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Generates a CRUD controller.
 *
 * @author Matheu Ledru <matyo91@gmail.com>
 */
class DoctrineCrudGenerator extends BaseGenerator
{
    protected $entityLink;
    protected $bundleService;
    protected $toBundle;
    protected $toBundleService;
    protected $templatePrefix;

    protected function parseBundleNamespace($namespace)
    {
        $namespace = Validators::validateBundleNamespace($namespace);

        $pos = strpos($namespace, '\\');

        return array(substr($namespace, 0, $pos), substr($namespace, $pos + 1));
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface   $bundle           A bundle object
     * @param string            $entity           The entity relative class name
     * @param ClassMetadataInfo $metadata         The entity class metadata
     * @param string            $format           The configuration format (xml, yaml, annotation)
     * @param string            $routePrefix      The route name prefix
     * @param array             $needWriteActions Wether or not to generate write actions
     *
     * @throws \RuntimeException
     */
    public function generateToBundle(BundleInterface $bundle, $entity, BundleInterface $toBundle, ClassMetadataInfo $metadata, $format, $routePrefix, $needWriteActions, $forceOverwrite)
    {
        $this->routePrefix = $routePrefix;
        $this->routeNamePrefix = str_replace('/', '_', $routePrefix);
        $this->actions = $needWriteActions ? array('index', 'show', 'new', 'edit', 'delete') : array('index', 'show');

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The CRUD generator does not support entity classes with multiple primary keys.');
        }

        if (!in_array('id', $metadata->identifier)) {
            throw new \RuntimeException('The CRUD generator expects the entity object has a primary key field named "id" with a getId() method.');
        }

        $this->entity   = $entity;
        $this->entityLink = Container::underscore(str_replace('\\', ':', $entity));
        $this->bundle   = $bundle;
        list($bundleNamespace, $bundleEntity) = $this->parseBundleNamespace($this->bundle->getNamespace());
        $this->bundleService = lcfirst(Container::camelize(substr($bundleEntity, 0, -6)));
        $this->toBundle = $toBundle;
        list($toBundleNamespace, $toBundleEntity) = $this->parseBundleNamespace($this->toBundle->getNamespace());
        $this->toBundleService = lcfirst(Container::camelize(substr($toBundleEntity, 0, -6)));
        $this->metadata = $metadata;
        $this->templatePrefix = (preg_match('/AdminBundle$/', $this->toBundle->getName())) ? 'admin_' : '';
        $this->setFormat($format);

        $this->generateControllerClass($forceOverwrite);

        $dir = sprintf('%s/Resources/views/%s', $this->toBundle->getPath(), str_replace('\\', '/', $this->entity));

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }

        $this->generateIndexView($dir);

        if (in_array('show', $this->actions)) {
            $this->generateShowView($dir);
        }

        if (in_array('new', $this->actions)) {
            $this->generateNewView($dir);
        }

        if (in_array('edit', $this->actions)) {
            $this->generateEditView($dir);
        }

        $this->generateTestClass();
        $this->generateConfiguration();
    }

    /**
     * Sets the configuration format.
     *
     * @param string $format The configuration format
     */
    private function setFormat($format)
    {
        switch ($format) {
            case 'yml':
            case 'xml':
            case 'php':
            case 'annotation':
                $this->format = $format;
                break;
            default:
                $this->format = 'yml';
                break;
        }
    }

    /**
     * Generates the controller class only.
     *
     */
    protected function generateControllerClass($forceOverwrite)
    {
        $dir = $this->toBundle->getPath();

        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Controller/%s/%sController.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        if (!$forceOverwrite && file_exists($target)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists.');
        }

        $this->renderFile('crud/controller.php.twig', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'template_prefix'   => $this->templatePrefix,
            'bundle'            => $this->bundle->getName(),
            'bundleService'     => $this->bundleService,
            'toBundle'          => $this->toBundle->getName(),
            'toBundleService'   => $this->toBundleService,
            'entity'            => $this->entity,
            'entityLink'        => $this->entityLink,
            'entity_class'      => $entityClass,
            'namespace'         => $this->bundle->getNamespace(),
            'toNamespace'       => $this->toBundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'format'            => $this->format,
        ));
    }

    /**
     * Generates the functional test class only.
     *
     */
    protected function generateTestClass()
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $dir    = $this->toBundle->getPath() .'/Tests/Controller';
        $target = $dir .'/'. str_replace('\\', '/', $entityNamespace).'/'. $entityClass .'ControllerTest.php';

        $this->renderFile('crud/tests/test.php.twig', $target, array(
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'entity'            => $this->entity,
            'bundle'            => $this->bundle->getName(),
            'entity_class'      => $entityClass,
            'namespace'         => $this->bundle->getNamespace(),
            'toNamespace'       => $this->toBundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'actions'           => $this->actions,
            'form_type_name'    => strtolower(str_replace('\\', '_', $this->bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.$entityClass.'Type'),
        ));
    }

    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateIndexView($dir)
    {
        $this->renderFile('crud/views/index.html.twig.twig', $dir.'/index.html.twig', array(
                'bundle'            => $this->bundle->getName(),
                'toBundle'          => $this->toBundle->getName(),
                'entity'            => $this->entity,
                'fields'            => $this->metadata->fieldMappings,
                'actions'           => $this->actions,
                'record_actions'    => $this->getRecordActions(),
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
            ));
    }

    /**
     * Generates the show.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateShowView($dir)
    {
        $this->renderFile('crud/views/show.html.twig.twig', $dir.'/show.html.twig', array(
                'bundle'            => $this->bundle->getName(),
                'toBundle'          => $this->toBundle->getName(),
                'entity'            => $this->entity,
                'fields'            => $this->metadata->fieldMappings,
                'actions'           => $this->actions,
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
            ));
    }

    /**
     * Generates the new.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateNewView($dir)
    {
        $this->renderFile('crud/views/new.html.twig.twig', $dir.'/new.html.twig', array(
                'bundle'            => $this->bundle->getName(),
                'toBundle'          => $this->toBundle->getName(),
                'entity'            => $this->entity,
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'actions'           => $this->actions,
            ));
    }

    /**
     * Generates the edit.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateEditView($dir)
    {
        $this->renderFile('crud/views/edit.html.twig.twig', $dir.'/edit.html.twig', array(
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'entity'            => $this->entity,
                'bundle'            => $this->bundle->getName(),
                'toBundle'          => $this->toBundle->getName(),
                'actions'           => $this->actions,
            ));
    }
}
