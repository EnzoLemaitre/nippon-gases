<?php
namespace App;

class Acf {

	private $componentsFolder = '/views/components/';

	public function __construct()
	{
		add_action('init', [$this, 'addOptionsPage']);
		add_action('acf/init', [$this, 'scanAcfFields']);
	}

	/**
	 * Add options page
	 * 
	 * @return void
	 */
	public function addOptionsPage(): void
	{
		if (function_exists('acf_add_options_page')) {
			acf_add_options_page([
				'post_id' => 'options',
				'page_title' => 'Options',
				'menu_slug'  => 'acf-options', 
				'menu_title' => 'Options', 
				'redirect'   => false
			]);
		}
	}

	/**
	 * Detect JSON files and load ACF fields
	 * 
	 * @return void
	 */
	public function scanAcfFields(): void
	{
		$files = $this->detectFileJson();
		$this->checkCache($files);
	}

	/**
	 * Cache-aware ACF field loading
	 * 
	 * @param array $files
	 * @return void
	 */
	private function checkCache(array $files): void
	{
		$cacheFile = get_stylesheet_directory() . '/acf_cache.json';
		$cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];

		$dir = get_stylesheet_directory() . $this->componentsFolder;
		$hash = md5(json_encode(array_map('filemtime', glob($dir . '*.json', GLOB_NOSORT))));
		$cacheHash = $cache['_global_hash'] ?? null;

		$changedFiles = [];

		if ($hash !== $cacheHash) {
			foreach ($files as $file) {
				$relative = str_replace(get_stylesheet_directory() . '/', '', $file);
				$mtime = filemtime($file);
				$cache[$relative] = $mtime;
				$changedFiles[] = $file;
			}
			$cache['_global_hash'] = $hash;
		}
		
		// Toujours ajouter les champs ACF
		$this->addFields($files);

		// Si changement -> met à jour le cache + push Git
		if (!empty($changedFiles)) {
			file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			$this->gitPush();
		}
	}

	/**
	 * Detect all JSON files in folder (cached for 5min)
	 * 
	 * @return array
	 */
	private function detectFileJson(): array
	{
		$componentsFolder = get_stylesheet_directory() . $this->componentsFolder;
		$cacheKey = 'acf_json_files_cache_' . md5($componentsFolder);

		$cached = get_transient($cacheKey);
		if ($cached !== false) {
			return $cached;
		}

		$files = [];

		if (is_dir($componentsFolder)) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($componentsFolder, \RecursiveDirectoryIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($iterator as $file) {
				if ($file->isFile() && strtolower($file->getExtension()) === 'json') {
					$files[] = $file->getPathname();
				}
			}
		}

		set_transient($cacheKey, $files, 300);

		return $files;
	}

	/**
	 * Add field groups to ACF
	 * 
	 * @param array $files
	 * @return void
	 */
	private function addFields(array $files): void
	{
		$usedKeys = [];

		foreach ($files as $file) {
			$jsonContent = file_get_contents($file);
			$data = json_decode($jsonContent, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				dump(json_last_error_msg());
				dump('Invalid JSON in file: ' . basename($file));
				die;
			}

			if ($data && is_array($data)) {
				$fieldGroup = (isset($data[0]) && is_array($data[0])) ? $data[0] : $data;

				if (empty($fieldGroup['key'])) {
					$fieldGroup['key'] = $this->makeUniqueKey('group');
					$data['key'] = $fieldGroup['key'];
					file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				}

				if (in_array($fieldGroup['key'], $usedKeys, true)) {
					dump('Duplicate group key "' . $fieldGroup['key'] . '" found in "' . basename($file) . '"');
					die;
				}

				if (!empty($fieldGroup['fields'])) {
					$this->checkAndFixFields($fieldGroup['fields'], $usedKeys, $file, $data);
				}

				$usedKeys[] = $fieldGroup['key'];
				acf_add_local_field_group($fieldGroup);
			}
		}
	}

	/**
	 * Push changes to GitHub
	 * 
	 * @return void
	 */
	private function gitPush(): void
	{
		$themeDir = escapeshellarg(get_stylesheet_directory());
		$date = date('Y-m-d H:i:s');

		$cmd = <<<CMD
			cd $themeDir

			echo "=== Starting safe git push sequence ==="

			if [ -d ".git/rebase-apply" ] || [ -d ".git/rebase-merge" ]; then
				echo "Git rebase in progress — aborting push"
				exit 1
			fi

			echo "Fetching latest changes..."
			git fetch origin main

			if ! git merge --ff-only origin/main 2>/dev/null; then
				echo "Divergence detected — resetting to origin/main"
				git reset --hard origin/main
			fi

			git clean -fd

			CHANGED_FILES=\$(git ls-files -m views/components/*.json acf_cache.json 2>/dev/null)
			if [ ! -z "\$CHANGED_FILES" ]; then
				echo "Changes detected — committing..."
				git add views/components/*.json acf_cache.json
				git commit -m "Auto: update ACF JSON ($date)" --no-edit || echo "No new commit created"
				git push origin main
				echo "Git push completed successfully"
			else
				echo "No local JSON changes to commit"
			fi

			echo "=== Git push complete ==="
		CMD;

		exec($cmd . ' 2>&1', $output, $status);

		if ($status !== 0) {
			$message = date('[Y-m-d H:i:s] ') . "Git push failed:\n" . implode("\n", $output);
			dump($message);
			die;
		}
	}

	/**
	 * Check & fix subfield keys
	 * 
	 * @param array &$fields
	 * @param array &$usedKeys
	 * @param string $file
	 * @param array &$rootData
	 * @return void
	 */
	private function checkAndFixFields(array &$fields, array &$usedKeys, string $file, array &$rootData = null): void
	{
		$jsonUpdated = false;

		foreach ($fields as &$field) {
			if (empty($field['key'])) {
				$field['key'] = $this->makeUniqueKey($field['name'] ?? 'field');
				$jsonUpdated = true;
			} elseif (in_array($field['key'], $usedKeys, true)) {
				dump('Duplicate field key "' . $field['key'] . '" detected in ' . basename($file));
				die;
			}

			$usedKeys[] = $field['key'];

			if (!empty($field['sub_fields'])) {
				$this->checkAndFixFields($field['sub_fields'], $usedKeys, $file);
			}

			if (!empty($field['layouts'])) {
				foreach ($field['layouts'] as &$layout) {
					if (!empty($layout['sub_fields'])) {
						$this->checkAndFixFields($layout['sub_fields'], $usedKeys, $file);
					}
				}
			}
		}

		if ($jsonUpdated && $rootData !== null) {
			$rootData['fields'] = $fields;
			file_put_contents($file, json_encode($rootData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		}
	}

	/**
	 * Generate unique key
	 * 
	 * @param string $base
	 * @return string
	 */
	private function makeUniqueKey(string $base): string
	{
		$sanitizedBase = preg_replace('/[^a-zA-Z0-9_]/', '', $base);
		return $sanitizedBase . '_' . uniqid();
	}
}

new Acf();
