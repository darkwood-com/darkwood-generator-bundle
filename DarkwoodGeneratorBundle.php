<?php

namespace Darkwood\GeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DarkwoodGeneratorBundle extends Bundle
{
    public function getParent()
    {
        return 'SensioGeneratorBundle';
    }
}
