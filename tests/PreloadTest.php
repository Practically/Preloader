<?php
/**
 * Copyright 2021 Practically.io All rights reserved
 *
 * Use of this source is governed by a BSD-style
 * licence that can be found in the LICENCE file or at
 * https://www.practically.io/copyright/
 */
declare(strict_types=1);

namespace Practically\Preloading\Tests;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use Practically\Preloading\Preloader;

/**
 * Tests the preloader
 *
 * @author Ade Attwood <ade@practically.io>
 */
class PreloadTest extends TestCase
{

    /**
     * The composer auto loader
     *
     * @var ClassLoader
     */
    protected $autoloader = null;

    /**
     * The opcache status
     *
     * @var array
     */
    protected $opcacheStatus = [];

    /**
     * Set up the test
     */
    public function setUp(): void
    {
        $this->autoloader = require(dirname(__DIR__).'/vendor/autoload.php');
        $this->opcacheStatus = opcache_get_status();

        if ($this->opcacheStatus === false || $this->opcacheStatus['opcache_enabled'] === false) {
            $this->fail('Opcache must be enabled to test the preloader');
        }
    }

    /**
     * Tests we can pre load a file
     */
    public function testPreloadFile(): void
    {
        $preloader = new Preloader($this->autoloader);
        $file = $this->autoloader->getClassMap()['Psalm\\Codebase'];

        $this->assertFalse(opcache_is_script_cached($file));
        $preloader->preloadFile($file);
        $this->assertTrue(opcache_is_script_cached($file));
    }

    /**
     * Tests a class can be loaded and a class excluded by a pattern
     */
    public function testPreloadClassPattern(): void
    {
        $defined = get_declared_classes();
        $class = 'Psalm\Config';
        $preloader = new Preloader($this->autoloader);

        $preloader->exclude('/^Psalm\\\Context/');

        $this->assertNotContains($class, $defined);

        $preloader->preload('/^Psalm/');
        $newlyDefined = array_diff(get_declared_classes(), $defined);

        $this->assertContains($class, $newlyDefined);
        $this->assertNotContains('Psalm\\\Context', $newlyDefined);
    }

}
