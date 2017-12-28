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

use Psr\SimpleCache\CacheInterface;
use WebDeveloppement\ConstantList\Cache\MemCache;

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
     * @var CacheInterface The cache manager. Must implements PSR-16 cache interfaces.
     */
    private static $cache;

    /**
     * @var bool Debug mode. If true, disabling the cache manager.
     */
    private static $debug = false;


    /**
     * Set the cache manager.
     *
     * The given cache must implements PSR-16 CacheInterface.
     *
     * @see http://www.php-fig.org/psr/psr-16/
     *
     * @param CacheInterface $cache
     */
    public static function setCache(CacheInterface $cache)
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
    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }


    /**
     * Returns the cache manager instance.
     *
     * If no cache has been set, uses an in memory cache.
     *
     * @return CacheInterface
     */
    private static function getCache()
    {
        if (empty(self::$cache)) {
            self::setCache(new MemCache());
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
    public static function get($className)
    {
        // Deactivate the cache in debug mode
        if (self::$debug) {
            return self::parse($className);
        }

        // Hash the class name to use it as cache item key
        $key = md5($className);

        // Cache the item if necessary
        if (!self::getCache()->has($key)) {
            self::getCache()->set($key, self::parse($className));
        }

        return self::getCache()->get($key);
    }


    /**
     * Returns the given constant list
     *
     * @param string $className The class to get constants
     * @param string $list      The constant list name
     *
     * @return array
     */
    public static function getList($className, $list)
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
    public static function getLabel($className, $list, $value)
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
    public static function exists($className, $list, $value)
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
    private static function parse($className)
    {
        $reflection = new \ReflectionClass($className);
        $content = file_get_contents($reflection->getFileName());
        $tokens = token_get_all($content);

        $constantList = [];
        $docBlock = null;
        $isConst = false;

        foreach ($tokens as $token) {
            if (is_array($token) && count($token) >= 2) {
                self::parseToken($token, $constantList, $docBlock, $isConst, $reflection);
            }
        }

        return $constantList;
    }


    /**
     * Parse the token details and updates variables if necessary
     *
     * @param array            $token
     * @param array            $constantList
     * @param string           $docBlock
     * @param boolean          $isConst
     * @param \ReflectionClass $reflection
     *
     * @throws ConstantListException
     */
    private static function parseToken($token, &$constantList, &$docBlock, &$isConst, \ReflectionClass $reflection)
    {
        list($tokenType, $tokenValue) = $token;

        switch ($tokenType) {
            case T_WHITESPACE:
            case T_COMMENT:
                break;

            case T_DOC_COMMENT:
                $docBlock = $tokenValue;
                break;

            // We are parsing a constant doc block
            case T_CONST:
                $isConst = true;
                break;

            case T_STRING:
                // If we are parsing a constant doc block and if we match the @ConstantList annotation in it,
                // extracts the constants list data.
                if ($isConst && !empty($docBlock) && preg_match('/\@ConstantList ([A-Za-z_0-9-]+)/', $docBlock, $matches)) {
                    $constantListName = $matches[1];

                    // Initializes constants list if necessary
                    if (!array_key_exists($constantListName, $constantList)) {
                        $constantList[$constantListName] = [];
                    }

                    $constantList[$constantListName][$reflection->getConstant($tokenValue)] = self::parseConstantComment($docBlock);
                }

                $docBlock = null;
                $isConst = false;
                break;

            default:
                $docBlock = null;
                $isConst = false;
        }
    }


    /**
     * Extracts the constant label from the given constant doc block
     *
     * @param string $docBlock
     *
     * @return string
     *
     * @throws ConstantListException
     */
    private static function parseConstantComment($docBlock)
    {
        // Splits comment lines
        $commentLines = preg_split('/\R/', $docBlock);
        $constantLabels = [];

        foreach ($commentLines as $line) {
            $line = trim($line, "/* \t\x0B\0");

            if (!empty($line) && !preg_match('/\@ConstantList/', $line)) {
                $constantLabels[] = $line;
            }
        }

        if (empty($constantLabels)) {
            throw new ConstantListException();
        }

        return implode(' ', $constantLabels);
    }
}