<?php

namespace Wdb\Dependencies\Service;

/**
 * Service to analyze Composer dependencies and return a structured tree for the backend module
 */
class DependencyAnalyzerService
{
    public function getDependencyTree($maxDepth = 2, $categoryFilter = '', $tagFilter = '')
    {
        $composerJsonPath = PATH_site . 'composer.json';
        $composerLockPath = PATH_site . 'composer.lock';
        $defaultMapPath = PATH_site . 'typo3conf/ext/dependencies/default-dependency-categories.json';
        $rulesDir = PATH_site . 'typo3conf/ext/dependencies/dependency-rules/';

        if (!file_exists($composerJsonPath) || !file_exists($composerLockPath)) {
            return [];
        }

        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        $composerLock = json_decode(file_get_contents($composerLockPath), true);
        $defaultMap = file_exists($defaultMapPath) ? json_decode(file_get_contents($defaultMapPath), true) : [];

        $required = $composerJson['require'] ?? [];
        $packages = $composerLock['packages'] ?? [];
        $packageMap = [];
        foreach ($packages as $pkg) {
            $packageMap[$pkg['name']] = $pkg;
        }

        $results = [];
        foreach ($required as $pkgName => $reqVersion) {
            if (!isset($packageMap[$pkgName])) continue;
            $pkg = $packageMap[$pkgName];
            $visited = [];
            [$category, $tags] = $this->loadRule($pkgName, $defaultMap, $rulesDir);
            if ($categoryFilter && $category !== $categoryFilter) continue;
            if ($tagFilter && !in_array($tagFilter, $tags)) continue;
            $results[] = [
                'manager' => 'composer',
                'name' => $pkgName,
                'category' => $category,
                'tags' => $tags,
                'required' => $reqVersion,
                'installed' => $pkg['version'],
                'latest' => $this->getLatestVersion($pkgName),
                'subdependencies' => $this->getSubdependencies($pkgName, $packageMap, $defaultMap, $rulesDir, $visited, 2, $maxDepth)
            ];
        }
        return $results;
    }
    
    protected function getLatestVersion($pkg)
    {
        // Use TYPO3 Caching Framework
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cache = $cacheManager->getCache('dependencies_version_cache');
        $key = 'packagist_' . md5($pkg);

        if ($cache->has($key)) {
            return $cache->get($key);
        }

        $packagistUrl = "https://repo.packagist.org/p2/{$pkg}.json";
        $data = @json_decode(@file_get_contents($packagistUrl), true);
        $latest = '';
        if ($data && isset($data['packages'][$pkg])) {
            $versions = array_keys($data['packages'][$pkg]);
            $stable = array_filter($versions, fn($v) => strpos($v, 'dev') === false);
            $latest = $stable ? reset($stable) : reset($versions);
        }
        $cache->set($key, $latest, [], 86400); // Cache for 1 day
        return $latest;
    }
    /*
    protected function getLatestVersion($pkg)
    {
        $packagistUrl = "https://repo.packagist.org/p2/{$pkg}.json";
        $data = @json_decode(@file_get_contents($packagistUrl), true);
        if ($data && isset($data['packages'][$pkg])) {
            $versions = array_keys($data['packages'][$pkg]);
            $stable = array_filter($versions, fn($v) => strpos($v, 'dev') === false);
            return $stable ? reset($stable) : reset($versions);
        }
        return '';
    }
    */

    protected function slugify($packageName)
    {
        return str_replace('/', '-', strtolower($packageName));
    }

    protected function loadRule($packageName, $defaultMap, $rulesDir, $manager = 'composer')
    {
        $slug = $this->slugify($packageName);
        $ruleFile = $rulesDir . $slug . '.json';
        if (file_exists($ruleFile)) {
            $data = json_decode(file_get_contents($ruleFile), true);
            $category = $data['category'] ?? '';
            $tags = $data['tags'] ?? [];
        } else {
            $categoriesKey = $manager === 'npm' ? 'npm_categories' : 'categories';
            $tagsKey = $manager === 'npm' ? 'npm_tags' : 'tags';
            $category = $defaultMap[$categoriesKey][$packageName] ?? '';
            $tags = $defaultMap[$tagsKey][$packageName] ?? [];
        }
        return [$category, $tags];
    }

    /*
    protected function loadRule($packageName, $defaultMap, $rulesDir)
    {
        $slug = $this->slugify($packageName);
        $ruleFile = $rulesDir . $slug . '.json';
        if (file_exists($ruleFile)) {
            $data = json_decode(file_get_contents($ruleFile), true);
            $category = $data['category'] ?? '';
            $tags = $data['tags'] ?? [];
        } else {
            $category = $defaultMap['categories'][$packageName] ?? '';
            $tags = $defaultMap['tags'][$packageName] ?? [];
        }
        return [$category, $tags];
    }
    */

    protected function getSubdependencies($pkgName, $packageMap, $defaultMap, $rulesDir, &$visited, $depth, $maxDepth)
    {
        if ($depth > $maxDepth || isset($visited[$pkgName])) return [];
        $visited[$pkgName] = true;
        $pkg = $packageMap[$pkgName] ?? null;
        if (!$pkg || empty($pkg['require'])) return [];
        $results = [];
        foreach ($pkg['require'] as $subName => $subRequiredVersion) {
            if (!isset($packageMap[$subName])) continue;
            $subPkg = $packageMap[$subName];
            [$category, $tags] = $this->loadRule($subName, $defaultMap, $rulesDir);
            $results[] = [
                'manager' => 'composer',
                'name' => $subName,
                'category' => $category,
                'tags' => $tags,
                'required' => $subRequiredVersion,
                'installed' => $subPkg['version'],
                'latest' => $this->getLatestVersion($subName),
                'subdependencies' => $this->getSubdependencies($subName, $packageMap, $defaultMap, $rulesDir, $visited, $depth+1, $maxDepth)
            ];
        }
        return $results;
    }
    
    public function getNpmDependencyTree($maxDepth = 2, $categoryFilter = '', $tagFilter = '')
    {
        $packageJsonPath = PATH_site . 'package.json';
        $packageLockPath = PATH_site . 'package-lock.json';
        $defaultMapPath = PATH_site . 'typo3conf/ext/dependencies/default-dependency-categories.json';
        $rulesDir = PATH_site . 'typo3conf/ext/dependencies/dependency-rules/';

        if (!file_exists($packageJsonPath) || !file_exists($packageLockPath)) {
            return [];
        }

        $packageJson = json_decode(file_get_contents($packageJsonPath), true);
        $packageLock = json_decode(file_get_contents($packageLockPath), true);
        $defaultMap = file_exists($defaultMapPath) ? json_decode(file_get_contents($defaultMapPath), true) : [];

        $required = array_merge($packageJson['dependencies'] ?? [], $packageJson['devDependencies'] ?? []);
        $installed = $packageLock['dependencies'] ?? [];

        $results = [];
        foreach ($required as $pkgName => $reqVersion) {
            if (!isset($installed[$pkgName])) continue;
            $pkg = $installed[$pkgName];
            $visited = [];
            [$category, $tags] = $this->loadRule($pkgName, $defaultMap, $rulesDir, 'npm');
            if ($categoryFilter && $category !== $categoryFilter) continue;
            if ($tagFilter && !in_array($tagFilter, $tags)) continue;
            $results[] = [
                'manager' => 'npm',
                'name' => $pkgName,
                'category' => $category,
                'tags' => $tags,
                'required' => $reqVersion,
                'installed' => $pkg['version'],
                'latest' => $this->getNpmLatestVersion($pkgName),
                'subdependencies' => $this->getNpmSubdependencies($pkgName, $installed, $defaultMap, $rulesDir, $visited, 2, $maxDepth)
            ];
        }
        return $results;
    }

    protected function getNpmLatestVersion($pkg)
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cache = $cacheManager->getCache('dependencies_version_cache');
        $key = 'npm_' . md5($pkg);

        if ($cache->has($key)) {
            return $cache->get($key);
        }

        $npmUrl = "https://registry.npmjs.org/{$pkg}";
        $data = @json_decode(@file_get_contents($npmUrl), true);
        $latest = isset($data['dist-tags']['latest']) ? $data['dist-tags']['latest'] : '';
        $cache->set($key, $latest, [], 86400); // Cache for 1 day
        return $latest;
    }

    protected function getNpmSubdependencies($pkgName, $installed, $defaultMap, $rulesDir, &$visited, $depth, $maxDepth)
    {
        if ($depth > $maxDepth || isset($visited[$pkgName])) return [];
        $visited[$pkgName] = true;
        $pkg = $installed[$pkgName] ?? null;
        if (!$pkg || empty($pkg['dependencies'])) return [];
        $results = [];
        foreach ($pkg['dependencies'] as $subName => $subPkg) {
            [$category, $tags] = $this->loadRule($subName, $defaultMap, $rulesDir, 'npm');
            $results[] = [
                'manager' => 'npm',
                'name' => $subName,
                'category' => $category,
                'tags' => $tags,
                'required' => '', // package-lock doesn't store required version for subdeps
                'installed' => $subPkg['version'] ?? '',
                'latest' => $this->getNpmLatestVersion($subName),
                'subdependencies' => $this->getNpmSubdependencies($subName, $installed, $defaultMap, $rulesDir, $visited, $depth+1, $maxDepth)
            ];
        }
        return $results;
    }

    /**
     * Get Solr extension info (from Composer)
     */
    public function getSolrExtensionInfo()
    {
        $composerLockPath = PATH_site . 'composer.lock';
        if (!file_exists($composerLockPath)) {
            return [];
        }
        $composerLock = json_decode(file_get_contents($composerLockPath), true);
        $packages = $composerLock['packages'] ?? [];
        foreach ($packages as $pkg) {
            if ($pkg['name'] === 'apache-solr-for-typo3/solr') {
                return [
                    'manager' => 'composer',
                    'name' => 'apache-solr-for-typo3/solr',
                    'category' => 'search-extension',
                    'tags' => ['TYPO3', 'Solr', 'PHP'],
                    'required' => $pkg['version'], // no required in lock, but installed
                    'installed' => $pkg['version'],
                    'latest' => $this->getLatestVersion($pkg['name']),
                    'subdependencies' => [],
                ];
            }
        }
        // Not installed
        return [
            'manager' => 'composer',
            'name' => 'apache-solr-for-typo3/solr',
            'category' => 'search-extension',
            'tags' => ['TYPO3', 'Solr', 'PHP'],
            'required' => '',
            'installed' => '',
            'latest' => $this->getLatestVersion('apache-solr-for-typo3/solr'),
            'subdependencies' => [],
        ];
    }

    /**
     * Get Solr server info via HTTP
     */
    public function getSolrServerInfo($solrUrl)
    {
        $infoUrl = rtrim($solrUrl, '/') . '/admin/info/system?wt=json';
        $response = @file_get_contents($infoUrl);
        $version = '';
        if ($response) {
            $data = @json_decode($response, true);
            $version = $data['lucene']['solr-spec-version'] ?? '';
        }
        // Usually, latest version must be checked via Solr release API; here we hardcode or skip
        return [
            'manager' => 'solr',
            'name' => 'solr-server',
            'category' => 'search',
            'tags' => ['Solr', 'Java'],
            'required' => '', // Not applicable
            'installed' => $version,
            'latest' => '', // Optionally fetch from https://solr.apache.org/downloads.html
            'subdependencies' => []
        ];
    }

    /**
     * Example: get Solr servers from config - adapt as needed!
     */
    public function getConfiguredSolrServers()
    {
        // Static for demo; ideally fetch from EXT:solr/TypoScript or site config
        return [
            'http://localhost:8983/solr'
        ];
    }
}
