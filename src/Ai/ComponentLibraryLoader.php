<?php

namespace Sovic\Cms\Ai;

use Psr\Cache\InvalidArgumentException;
use Sovic\Cms\Ai\Dto\ComponentLibrary;
use Sovic\Cms\Ai\Exception\ComponentLibraryException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Loads the website HTML component snippets and styleguide from disk (paths from sovic_cms.ai config).
 */
class ComponentLibraryLoader
{
    public const MaxFiles = 100;
    public const MaxFileBytes = 65536;
    public const MaxTotalBytes = 524288;

    private const Extensions = ['html', 'htm'];

    public function __construct(
        #[Autowire('%ai_components_dir%')] private readonly ?string   $componentsDir,
        #[Autowire('%ai_styleguide_file%')] private readonly ?string  $styleguideFile,
        private readonly ?CacheInterface                             $cache = null,
    ) {
    }

    public function load(): ComponentLibrary
    {
        $files = $this->listComponentFiles();
        if ($this->cache === null) {
            return $this->read($files);
        }

        $signature = $this->componentsDir . '|' . $this->styleguideFile;
        foreach ($files as $file) {
            $signature .= '|' . $file . ':' . filemtime($file);
        }
        if ($this->styleguideFile && is_file($this->styleguideFile)) {
            $signature .= '|' . filemtime($this->styleguideFile);
        }

        try {
            return $this->cache->get(
                'sovic_cms_ai_library_' . md5($signature),
                fn() => $this->read($files),
            );
        } catch (InvalidArgumentException) {
            return $this->read($files);
        }
    }

    /**
     * @return string[] absolute paths sorted by file name
     */
    private function listComponentFiles(): array
    {
        $dir = $this->componentsDir;
        if (empty($dir) || !is_dir($dir) || !is_readable($dir)) {
            throw new ComponentLibraryException(sprintf('AI components directory "%s" does not exist.', $dir));
        }

        $files = [];
        foreach (scandir($dir) ?: [] as $entry) {
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (!is_file($path) || !in_array($extension, self::Extensions, true)) {
                continue;
            }
            $files[] = $path;
        }
        sort($files, SORT_STRING);

        if (count($files) > self::MaxFiles) {
            throw new ComponentLibraryException(sprintf('Too many AI components, max %d allowed.', self::MaxFiles));
        }

        return $files;
    }

    /**
     * @param string[] $files
     */
    private function read(array $files): ComponentLibrary
    {
        $total = 0;
        $components = [];
        foreach ($files as $file) {
            $html = $this->readFile($file);
            $total += strlen($html);
            $components[pathinfo($file, PATHINFO_FILENAME)] = trim($html);
        }

        $styleguide = '';
        if (!empty($this->styleguideFile)) {
            if (!is_file($this->styleguideFile)) {
                throw new ComponentLibraryException(sprintf('AI styleguide file "%s" does not exist.', $this->styleguideFile));
            }
            $styleguide = trim($this->readFile($this->styleguideFile));
            $total += strlen($styleguide);
        }

        if ($total > self::MaxTotalBytes) {
            throw new ComponentLibraryException(sprintf('AI component library is too large, max %d bytes allowed.', self::MaxTotalBytes));
        }

        return new ComponentLibrary($styleguide, $components);
    }

    private function readFile(string $file): string
    {
        $size = filesize($file);
        if ($size === false || $size > self::MaxFileBytes) {
            throw new ComponentLibraryException(sprintf('AI component file "%s" is too large, max %d bytes allowed.', basename($file), self::MaxFileBytes));
        }
        $content = file_get_contents($file);
        if ($content === false) {
            throw new ComponentLibraryException(sprintf('Unable to read AI component file "%s".', basename($file)));
        }

        return $content;
    }
}
