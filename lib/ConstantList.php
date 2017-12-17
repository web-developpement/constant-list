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

namespace WebDeveloppement\ConstantList;

use phpFastCache\CacheManager;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class ConstantList.
 *
 * Allow to use constant annotations to sort classes constants in list and to easily retrieve constant's labels.
 *
 * @author Vincent Vaur <contact@web-developpement.com>
 */
class ConstantList
{
    /**
     * @var CacheItemPoolInterface The cache manager. Must implements PSR-6 cache interfaces.
     *                             Use phpfastcache file cache by default.
     */
    private static $cache;

    /**
     * @var bool Debug mode. If true, disabling the cache manager.
     */
    private static $debug = false;


    /**
     * Set the cache manager.
     *
     * The given cache must implements PSR-6 CacheItemPoolInterface.
     *
     * @see http://www.php-fig.org/psr/psr-6/
     *
     * @param CacheItemPoolInterface $cache
     */
    public static function setCache(CacheItemPoolInterface $cache): void
    {
        self::$cache = $cache;
    }


    /**
     * Clear the cache
     */
    public static function clearCache()
    {
        self::getCache()->clear();
    }


    /**
     * Set the debug mode. If true, disabling the cache system.
     *
     * @param bool $debug
     */
    public static function setDebug(bool $debug): void
    {
        self::$debug = $debug;
    }


    /**
     * Returns the cache manager instance.
     *
     * If no cache has been set, use phpfastcache files cache as default. Writes files in the system temp directory.
     *
     * @return CacheItemPoolInterface
     */
    private static function getCache(): CacheItemPoolInterface
    {
        if (empty(self::$cache)) {
            CacheManager::setDefaultConfig([
                "securityKey"      => 'constant-list',
                "path"             => sys_get_temp_dir(),
                "itemDetailedDate" => false
            ]);

            self::setCache(CacheManager::getInstance('files'));
        }

        return self::$cache;
    }


    /**
     * Returns all constants list found in the given class
     *
     * @param string $className The class to get constants
     *
     * @return array
     */
    public static function get(string $className): array
    {
        // Deactivate the cache in debug mode
        if (self::$debug) {
            return self::parse($className);
        }

        // Normalize class name to use it as cache item key
        $normalizedClassName = mb_strtolower(preg_replace('/\\\/', '-', $className));

        $cacheItem = self::getCache()->getItem($normalizedClassName);

        // Cache item if necessary
        if (!$cacheItem->isHit()) {
            $cacheItem->set(self::parse($className));
            self::getCache()->save($cacheItem);
        }

        return $cacheItem->get();
    }


    /**
     * Returns the given constant list
     *
     * @param string $className The class to get constants
     * @param string $list      The constant list name
     *
     * @return array
     */
    public static function getList(string $className, string $list): array
    {
        $constants = self::get($className);

        return array_key_exists($list, $constants) ? $constants[$list] : [];
    }


    /**
     * Returns the constant label for the given constant value
     *
     * @param string $className The class to get constants
     * @param string $list      The constant list name
     * @param string $value     The constant value
     *
     * @return mixed
     */
    public static function getLabel(string $className, string $list, string $value)
    {
        $constantList = self::getList($className, $list);

        return array_key_exists($value, $constantList) ? $constantList[$value] : null;
    }


    /**
     * Returns whether the given constant value exists in the given constant list
     *
     * @param string $className
     * @param string $list
     * @param mixed  $value
     *
     * @return bool
     */
    public static function exists(string $className, string $list, $value): bool
    {
        return !empty(self::getLabel($className, $list, $value));
    }


    /**
     * Parses the given class for constant's comments using the Zend engine's lexical scanner.
     *
     * @param string $className
     *
     * @see token_get_all
     *
     * @return array
     */
    private static function parse(string $className): array
    {
        $reflection = new \ReflectionClass($className);
        $content = file_get_contents($reflection->getFileName());
        $tokens = token_get_all($content);

        $constantList = [];
        $comment = null;
        $isConst = false;

        foreach ($tokens as $token) {
            if (count($token) <= 1) {
                continue;
            }

            list($tokenType, $tokenValue) = $token;

            switch ($tokenType) {
                case T_WHITESPACE:
                case T_COMMENT:
                    break;

                case T_DOC_COMMENT:
                    $comment = $tokenValue;
                    break;

                // We are parsing a constant doc block
                case T_CONST:
                    $isConst = true;
                    break;

                case T_STRING:
                    // If we are parsing a constant doc block and if we match the @ConstantList annotation in it,
                    // extract the constant list data.
                    if ($isConst && !empty($comment) && preg_match('/\@ConstantList ([A-Za-z_0-9-]+)/', $comment, $matches)) {
                        $constantListName = $matches[1];

                        // Initialize the constant list if necessary
                        if (!array_key_exists($constantListName, $constantList)) {
                            $constantList[$constantListName] = [];
                        }

                        $commentLines = preg_split('/\R/', $comment);
                        $constantLabels = [];

                        foreach ($commentLines as $line) {
                            $line = trim($line, "/* \t\x0B\0");

                            if (!empty($line) && !preg_match('/\@ConstantList/', $line)) {
                                $constantLabels[] = $line;
                            }
                        }

                        $constantList[$constantListName][$reflection->getConstant($tokenValue)] = implode(' ', $constantLabels);
                    }

                    $comment = null;
                    $isConst = false;
                    break;

                default:
                    $comment = null;
                    $isConst = false;
                    break;
            }
        }

        return $constantList;
    }
}