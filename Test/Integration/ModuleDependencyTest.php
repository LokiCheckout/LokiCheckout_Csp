<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Integration;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfo;
use PHPUnit\Framework\TestCase;

class ModuleDependencyTest extends TestCase
{
    public function testComposerPackageRequiresMagentoCsp(): void
    {
        $packageInfo = ObjectManager::getInstance()->get(PackageInfo::class);

        $this->assertContains('Magento_Csp', $packageInfo->getRequire('LokiCheckout_Csp'));
    }

    public function testModuleIsLoadedAfterMagentoCsp(): void
    {
        $module = ObjectManager::getInstance()->get(ModuleList::class)->getOne('LokiCheckout_Csp');

        $this->assertIsArray($module);
        $this->assertContains('Magento_Csp', $module['sequence']);
    }
}
