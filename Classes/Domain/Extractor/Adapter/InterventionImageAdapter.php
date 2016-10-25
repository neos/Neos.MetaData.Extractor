<?php
namespace Neos\MetaData\Extractor\Domain\Extractor\Adapter;

/*
 * This file is part of the Neos.MetaData.Extractor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Intervention\Image\ImageManager;
use Neos\MetaData\Domain\Collection\MetaDataCollection;
use Neos\MetaData\Domain\Dto;
use Neos\MetaData\Extractor\Converter\ColorSpaceConverter;
use Neos\MetaData\Extractor\Converter\GpsConverter;
use Neos\MetaData\Extractor\Converter\NumberConverter;
use Neos\MetaData\Extractor\Domain\Extractor\AbstractExtractor;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Resource\Exception as ResourceException;
use TYPO3\Flow\Resource\Resource as FlowResource;

/**
 * Intervention/Image Adapter
 */
class InterventionImageAdapter extends AbstractExtractor
{
    /**
     * @var array
     */
    protected static $compatibleMediaTypes = [
        'image/jpeg',
        'video/jpeg',
    ];

    /**
     * @param FlowResource $resource
     * @param MetaDataCollection $metaDataCollection
     *
     * @throws ResourceException
     */
    public function extractMetaData(FlowResource $resource, MetaDataCollection $metaDataCollection)
    {
        $manager = new ImageManager(['driver' => 'gd']);
        $image = $manager->make($resource->createTemporaryLocalCopy());

        $exifData = $image->exif();
        if (is_array($exifData)) {
            $metaDataCollection->set('exif', $this->buildExifDto($exifData));
        }
    }

    /**
     * @param FlowResource $resource
     *
     * @return bool
     */
    public function canHandleExtraction(FlowResource $resource)
    {
        return class_exists(ImageManager::class);
    }

    /**
     * @param $exifData
     *
     * @return Dto\Exif
     */
    protected function buildExifDto($exifData)
    {
        $exifData['Aperture'] = isset($exifData['FNumber']) ? NumberConverter::convertRationalToFloat($exifData['FNumber']) : 0.0;
        $exifData['FocalLength'] = isset($exifData['FocalLength']) ? (int)NumberConverter::convertRationalToFloat($exifData['FocalLength']) : 0;
        $exifData['XResolution'] = isset($exifData['XResolution']) ? (int)NumberConverter::convertRationalToFloat($exifData['XResolution']) : 0;
        $exifData['YResolution'] = isset($exifData['YResolution']) ? (int)NumberConverter::convertRationalToFloat($exifData['YResolution']) : 0;
        $exifData['ColorSpace'] = isset($exifData['ColorSpace']) ? ColorSpaceConverter::translateColorSpaceId($exifData['ColorSpace']) : '';
        $exifData['Description'] = isset($exifData['ImageDescription']) ? $exifData['ImageDescription'] : '';

        if (isset($exifData['GPSLongitude'], $exifData['GPSLongitudeRef'], $exifData['GPSLatitude'], $exifData['GPSLatitudeRef'])) {
            $exifData['GPSLongitude'] = GpsConverter::convertRationalArrayAndReferenceToFloat($exifData['GPSLongitude'], $exifData['GPSLongitudeRef']);
            $exifData['GPSLatitude'] = GpsConverter::convertRationalArrayAndReferenceToFloat($exifData['GPSLatitude'], $exifData['GPSLatitudeRef']);
        }

        return new Dto\Exif($exifData);
    }
}
