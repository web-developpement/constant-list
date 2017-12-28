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

/**
 * Class CacheItem.
 *
 * Represents a cache items
 *
 * @author Vincent Vaur <contact@web-developpement.com>
 */
class CacheItem
{
    /**
     * @var Mixed The item value
     */
    private $value;

    /**
     * @var \DateTime The expiration date
     */
    private $expiresAt;


    /**
     * Returns the item value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Set the item value
     *
     * @param mixed $value
     *
     * @return CacheItem
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }


    /**
     * Returns the expiration date
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }


    /**
     * Set the expiration date
     *
     * @param \DateTime $expiresAt
     *
     * @return CacheItem
     */
    public function setExpiresAt(\DateTime $expiresAt = null)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }


}