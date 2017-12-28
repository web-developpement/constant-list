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

namespace WebDeveloppement\ConstantList\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Class MemCache.
 *
 * Implements the PSR-16 simple cache for a memcache
 *
 * @author Vincent Vaur <contact@web-developpement.com>
 */
class MemCache implements CacheInterface
{
    /**
     * @var CacheItem[] Cache items
     */
    private $items = [];


    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $this->checkKey($key);

        if (array_key_exists($key, $this->items)) {
            $item = unserialize($this->items[$key]);

            if (new \DateTime() < $item->getExpiresAt()) {
                return $item->getValue();
            }
        }

        return $default;
    }


    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->checkKey($key);

        // Handle expiration date. 1 hour by default
        $expiresAt = new \DateTime();

        if (is_int($ttl)) {
            $expiresAt->add(new \DateInterval("PT" . $ttl . "S"));
        } elseif ($ttl instanceof \DateInterval) {
            $expiresAt->add($ttl);
        } else {
            $expiresAt->add(new \DateInterval("PT1H"));
        }

        $item = new CacheItem();

        $item
            ->setValue($value)
            ->setExpiresAt($expiresAt);

        try {
            $this->items[$key] = serialize($item);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $this->checkKey($key);

        unset($this->items[$key]);

        return true;
    }


    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->items = [];

        return true;
    }


    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException("Keys passed to getMultiple must implements the \Traversable interface");
        }

        $returnedKeys = [];

        foreach ($keys as $key) {
            $returnedKeys[$key] = $this->get($key, $default);
        }

        return $returnedKeys;
    }


    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new InvalidArgumentException("Values passed to setMultiple must implements the \Traversable interface");
        }

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException("Keys passed to deleteMultiple must implements the \Traversable interface");
        }

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                return false;
            }
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    public function has($key)
    {
        $this->checkKey($key);

        return array_key_exists($key, $this->items);
    }


    /**
     * Check if the given key is in a valid format
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    private function checkKey($key)
    {
        if (!preg_match('/^[A-Za-z0-9_\.]{1,64}$/', $key)) {
            throw new InvalidArgumentException("The provided key is not valid");
        }
    }
}