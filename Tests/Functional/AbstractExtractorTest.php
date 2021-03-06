<?php
namespace Neos\MetaData\Extractor\Tests\Functional;

/*
 * This file is part of the Neos.MetaData.Extractor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Tests\Functional\AbstractTest;
use Neos\MetaData\Domain\Dto\AbstractMetaDataDto;
use Neos\Utility\Files;

abstract class AbstractExtractorTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var Asset
     */
    protected $testAsset;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->resourceManager = $this->objectManager->get(ResourceManager::class);
        $this->testAsset = $this->buildTestAsset();
    }

    /**
     * @return Asset
     */
    protected function buildTestAsset() : Asset
    {
        $testImagePath = Files::concatenatePaths([__DIR__, 'Fixtures/Resources/Lighthouse.jpg']);
        $this->assertFileExists($testImagePath);

        $resource = $this->resourceManager->importResource($testImagePath);

        return new Asset($resource);
    }

    /**
     * @param AbstractMetaDataDto $dto
     * @param mixed[] $expectedDtoData
     * @return void
     */
    protected function assertDtoGettersReturnData(AbstractMetaDataDto $dto, array $expectedDtoData)
    {
        foreach ($expectedDtoData as $key => $value) {
            $getter = 'get' . $key;
            $this->assertEquals($value, $dto->$getter(), \sprintf('Value of %s does not match expected.', $key));
        }
    }
}
