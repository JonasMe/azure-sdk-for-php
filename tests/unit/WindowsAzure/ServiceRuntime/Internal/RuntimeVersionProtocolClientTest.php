<?php

/**
 * LICENSE: Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * PHP version 5
 *
 * @category  Microsoft
 *
 * @author    Azure PHP SDK <azurephpsdk@microsoft.com>
 * @copyright 2012 Microsoft Corporation
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @link      https://github.com/windowsazure/azure-sdk-for-php
 */

namespace Tests\unit\WindowsAzure\ServiceRuntime\Internal;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use WindowsAzure\ServiceRuntime\Internal\FileInputChannel;
use WindowsAzure\ServiceRuntime\Internal\RuntimeVersionProtocolClient;

/**
 * Unit tests for class RuntimeVersionProtocolClient.
 *
 * @category  Microsoft
 *
 * @author    Azure PHP SDK <azurephpsdk@microsoft.com>
 * @copyright 2012 Microsoft Corporation
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @version   Release: 0.4.5_2016-09
 *
 * @link      https://github.com/windowsazure/azure-sdk-for-php
 */
class RuntimeVersionProtocolClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \WindowsAzure\ServiceRuntime\Internal\RuntimeVersionProtocolClient::__construct
     */
    public function testConstruct()
    {
        // Setup
        $runtimeVersionProtocolClient =
            new RuntimeVersionProtocolClient(new FileInputChannel());

        // Test
        $this->assertInstanceOf(
            'WindowsAzure\ServiceRuntime\Internal\RuntimeVersionProtocolClient',
            $runtimeVersionProtocolClient);
    }

    /**
     * @covers \WindowsAzure\ServiceRuntime\Internal\RuntimeVersionProtocolClient::getVersionMap
     */
    public function testGetVersionMap()
    {
        // Setup
        $rootDirectory = 'root';

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory($rootDirectory));

        $fileName = 'versionendpoint';
        $fileContent = '<?xml version="1.0" encoding="utf-8"?>'.
            '<RuntimeServerDiscovery xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.
            '<RuntimeServerEndpoints>'.
            '<RuntimeServerEndpoint version="2011-03-08" path="myPath1" />'.
            '<RuntimeServerEndpoint version="2012-03-08" path="myPath2" />'.
            '</RuntimeServerEndpoints>'.
            '</RuntimeServerDiscovery>';

        $file = vfsStream::newFile($fileName);
        $file->setContent($fileContent);

        vfsStreamWrapper::getRoot()->addChild($file);

        $runtimeVersionProtocolClient =
            new RuntimeVersionProtocolClient(new FileInputChannel());

        // Test
        $versions = $runtimeVersionProtocolClient->getVersionMap(
            vfsStream::url($rootDirectory.'/'.$fileName)
        );

        $this->assertEquals('myPath1', $versions['2011-03-08']);
        $this->assertEquals('myPath2', $versions['2012-03-08']);

        // change to a single endpoint
        $fileContent = '<?xml version="1.0" encoding="utf-8"?>'.
            '<RuntimeServerDiscovery xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.
            '<RuntimeServerEndpoints>'.
            '<RuntimeServerEndpoint version="2011-03-08" path="myPath1" />'.
            '</RuntimeServerEndpoints>'.
            '</RuntimeServerDiscovery>';

        $file->setContent($fileContent);

        $versions = $runtimeVersionProtocolClient->getVersionMap(
            vfsStream::url($rootDirectory.'/'.$fileName)
        );

        $this->assertEquals('myPath1', $versions['2011-03-08']);
        $this->assertArrayNotHasKey('2012-03-08', $versions);
    }
}
