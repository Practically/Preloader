<?php
/**
 * Copyright 2021 Practically.io All rights reserved
 *
 * Use of this source is governed by a BSD-style
 * licence that can be found in the LICENCE file or at
 * https://www.practically.io/copyright/
 */
declare(strict_types=1);

namespace Practically\Preloading;

use Composer\Autoload\ClassLoader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Pre loader for PHP ^7.4 opcache
 *
 * @author Ade Attwood <ade@practically.io>
 */
class Preloader
{

    /**
     * The internal class map
     *
     * @var Array<string, string>
     */
    protected $classMap;

    /**
     * List of regex patterns to ignore when preloading
     *
     * @var Array<string, string>
     */
    protected $noMatch = [];

    /**
     * Set up the preloader
     */
    public function __construct(ClassLoader $loader)
    {
        $this->classMap = $loader->getClassMap();
    }

    /**
     * Pre load a directory of files recursively.
     *
     * This will use `opcache_compile_file` so dependencies will NOT be pre
     * loaded. This is useful for preloading views
     */
    public function preloadDirectory(string $filePath): void
    {
        $di = new RecursiveDirectoryIterator($filePath);
        foreach (new RecursiveIteratorIterator($di) as $file) {
            /** @var SplFileInfo $file */
            $realPath = $file->getRealPath();
            if (preg_match('/.php$/', $realPath)) {
                $this->preloadFile($realPath);
            }
        }
    }

    /**
     * Pre loads a PHP file
     */
    public function preloadFile(string $file): void
    {
        if (is_file($file)) {
            opcache_compile_file($file);
        }
    }

    /**
     * Preloads a class and all of its decencies with `include_once`
     */
    public function preloadClass(string $class): void
    {
        if (class_exists($class, false)) {
            return;
        }

        foreach ($this->noMatch as $pattern) {
            if (preg_match($pattern, $class)) {
                return;
            }
        }

        $file = $this->classMap[$class] ?? null;
        if ($file !== null && is_file($file)) {
            /**
             * @psalm-suppress UnresolvableInclude
             */
            include_once $file;
        }
    }

    /**
     * Defines a regex pattern of classes to
     */
    public function exclude(string $pattern): void
    {
        $this->noMatch[$pattern] = $pattern;
    }

    /**
     * Preloads all of the classes matching a pattern
     */
    public function preload(string $pattern): void
    {
        foreach (array_keys($this->classMap) as $class) {
            if (preg_match($pattern, $class)) {
                $this->preloadClass($class);
            }
        }
    }

}
