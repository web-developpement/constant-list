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

namespace WebDeveloppement\ConstantList\Tests;

use PHPUnit\Framework\TestCase;
use WebDeveloppement\ConstantList\Cache\InvalidArgumentException;
use WebDeveloppement\ConstantList\Cache\MemCache;

/**
 * Class MemCacheTest.
 *
 * Test class for MemCache class
 *
 * @author Vincent Vaur <contact@web-developpement.com>
 */
final class MemCacheTest extends TestCase
{
    /**
     * @test
     * @testdox Check item key
     */
    public function checkItemKey()
    {
        $cache = new MemCache();

        $this->expectException(InvalidArgumentException::class);
        $cache->set("0123456789012345678901234567890123456789012345678901234567890123456789", []);
    }


    /**
     * @test
     * @testdox set method
     */
    public function set()
    {
        $cache = new MemCache();

        $this->assertTrue($cache->set("my_key", "my-value", 60));
        $this->assertTrue($cache->set("my_key", "my-value", new \DateInterval("PT60S")));
        $this->assertFalse($cache->set("my_key", function () {

        }));
    }


    /**
     * @test
     * @testdox get method
     */
    public function get()
    {
        $cache = new MemCache();

        $cache->set("my_key", "my-value", 2);

        $this->assertEquals($cache->get("my_key"), "my-value");
        $this->assertEquals($cache->get("unknown_key", "default_value"), "default_value");

        sleep(2);

        $this->assertEquals($cache->get("my_key", "default_value"), "default_value");
    }


    /**
     * @test
     * @testdox delete method
     */
    public function delete()
    {
        $cache = new MemCache();

        $cache->set("my_key", "my-value");

        $this->assertTrue($cache->delete("my_key"));
        $this->assertFalse($cache->has("my_key"));
    }


    /**
     * @test
     * @testdox setMultiple method
     */
    public function setMultiple()
    {
        $cache = new MemCache();

        $this->assertTrue($cache->setMultiple(["key_1" => "value1", "key_2" => "value2"]));
        $this->assertEquals("value1", $cache->get("key_1"));
        $this->assertEquals("value2", $cache->get("key_2"));

        $this->expectException(InvalidArgumentException::class);
        $cache->setMultiple(10);
    }


    /**
     * @test
     * @testdox getMultiple method
     */
    public function getMultiple()
    {
        $cache = new MemCache();

        $values = ["key_1" => "value1", "key_2" => "value2"];
        $cache->setMultiple($values);

        $this->assertEquals($cache->getMultiple(["key_1", "key_2"]), $values);

        $this->expectException(InvalidArgumentException::class);
        $cache->getMultiple(10);
    }


    /**
     * @test
     * @testdox deleteMultiple method
     */
    public function deleteMultiple()
    {
        $cache = new MemCache();

        $values = ["key_1" => "value1", "key_2" => "value2"];
        $cache->setMultiple($values);

        $this->assertTrue($cache->deleteMultiple(["key_1", "key_2"]));
        $this->assertFalse($cache->has("key_1"));
        $this->assertFalse($cache->has("key_2"));

        $this->expectException(InvalidArgumentException::class);
        $cache->deleteMultiple(10);
    }
}
