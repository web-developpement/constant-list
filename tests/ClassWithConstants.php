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

/**
 * Class ClassWithConstants.
 *
 * Test class with ConstantList annotations
 *
 * @author Vincent Vaur <contact@web-developpement.com>
 */
class ClassWithConstants
{
    /**
     * Type 1
     *
     * @ConstantList type
     */
    const TYPE_1 = "TYPE_1";

    /**
     * Type 2
     *
     * @ConstantList type
     */
    const TYPE_2 = "TYPE_2";

    /**
     * Type 3
     *
     * @ConstantList type
     */
    const TYPE_3 = "TYPE_3";

    /**
     * Format XML
     *
     * @ConstantList format
     */
    const FORMAT_XML = "XML";

    /**
     * Format PDF
     * in multi line format
     *
     * @ConstantList format
     */
    const FORMAT_PDF = "PDF";


    public function __construct()
    {

    }


    /**
     * Just here to test PHP parser
     *
     * @param null $value
     *
     * @return null
     */
    private function testPrivate($value = null) {
        return $value;
    }
}