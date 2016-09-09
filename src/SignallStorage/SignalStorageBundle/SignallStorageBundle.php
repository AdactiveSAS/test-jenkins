<?php

namespace Signall\StorageBundle;

use Knp\Bundle\GaufretteBundle\KnpGaufretteBundle;
use Signall\DataBundle\SignallDataBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * An implementation of BundleInterface that adds a few conventions
 * for DependencyInjection extensions and Console commands.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SignallStorageBundle extends Bundle
{

    /**
     * SignallDataBundle constructor.
     *
     * @param array $bundles
     */
    public function __construct(array &$bundles)
    {
        $this->registerBundle(SignallDataBundle::class, $bundles, [&$bundles]);
        $this->registerBundle(KnpGaufretteBundle::class, $bundles);
    }

    /**
     * @param $bundleName
     * @param array $bundles
     * @param array $arguments
     */
    protected function registerBundle($bundleName, array &$bundles, array $arguments = [])
    {
        if (!$this->isBundleRegistered($bundleName, $bundles)) {
            $bundles[] = new $bundleName(...$arguments);
        }
    }

    /**
     * @param $bundleName
     * @param array $bundles
     * @return bool
     */
    protected function isBundleRegistered($bundleName, array $bundles)
    {
        foreach ($bundles as $bundle) {
            if ($bundle instanceof $bundleName) {
                return true;
            }
        }

        return false;
    }
}
