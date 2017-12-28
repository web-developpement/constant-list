<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace WebDeveloppement\ConstantList\Tests;

use phpFastCache\CacheManager;
use PHPUnit\Framework\TestCase;
use WebDeveloppement\ConstantList\ConstantList;
use WebDeveloppement\ConstantList\ConstantListException;

/**
 * @covers \WebDeveloppement\ConstantList\ConstantList
 */
final class ConstantListTest extends TestCase
{
    /**
     * @test
     * @testdox Retrieving all class constants
     */
    public function get(): void
    {
        $constants = ConstantList::get(ClassWithConstants::class);

        $this->assertTrue(
            is_array($constants)
        );

        $this->assertTrue(
            count($constants) == 2
        );
    }


    /**
     * @test
     * @testdox Retrieving a single constant list
     */
    public function getList(): void
    {
        $constants = ConstantList::getList(ClassWithConstants::class, "type");

        $this->assertTrue(
            is_array($constants)
        );

        $this->assertTrue(
            count($constants) == 3
        );

        $constants = ConstantList::getList(ClassWithConstants::class, "unknow");

        $this->assertTrue(
            is_array($constants)
        );

        $this->assertTrue(
            count($constants) == 0
        );

        $this->expectException(ConstantListException::class);
        ConstantList::get(ClassWithBadConstantAnnotation::class);

        $this->expectException(ConstantListException::class);
        ConstantList::getList(ClassWithBadConstantAnnotation::class, "without-label");
    }


    /**
     * @test
     * @testdox Retrieving a constant label
     */
    public function getLabel(): void
    {
        $this->assertEquals(
            'Format PDF in multi line format',
            ConstantList::getLabel(ClassWithConstants::class, 'format', ClassWithConstants::FORMAT_PDF)
        );

        $this->assertNull(
            ConstantList::getLabel(ClassWithConstants::class, 'format', 'unknow')
        );
    }


    /**
     * @test
     * @testdox Checking if a constant exists
     */
    public function exists(): void
    {
        $this->assertTrue(
            ConstantList::exists(ClassWithConstants::class, 'format', ClassWithConstants::FORMAT_PDF)
        );

        $this->assertTrue(
            !ConstantList::exists(ClassWithConstants::class, 'format', 'unknown-constant')
        );
    }


    /**
     * @test
     * @testdox Checking cache
     */
    public function cache(): void
    {
        // phpfastcache is available since PHP 5.6
        if (PHP_VERSION_ID < 50600) {
            return;
        }
        
        CacheManager::setDefaultConfig([
            "securityKey"      => 'constant-list',
            "path"             => sys_get_temp_dir(),
            "itemDetailedDate" => false
        ]);

        ConstantList::setCache(CacheManager::getInstance('files'));

        $defaultCacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'constant-list' . DIRECTORY_SEPARATOR . "Files";
        ConstantList::get(ClassWithConstants::class);

        self::assertDirectoryExists($defaultCacheDir);
        ConstantList::clearCache();
        self::assertDirectoryNotExists($defaultCacheDir);
    }


    /**
     * @test
     * @testdox Checking debug mode
     */
    public function debug(): void
    {
        $defaultCacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'constant-list' . DIRECTORY_SEPARATOR . "Files";

        ConstantList::clearCache();

        ConstantList::setDebug(true);
        ConstantList::get(ClassWithConstants::class);
        self::assertDirectoryNotExists($defaultCacheDir);

        ConstantList::setDebug(false);
        ConstantList::get(ClassWithConstants::class);
        self::assertDirectoryExists($defaultCacheDir);
    }
}
