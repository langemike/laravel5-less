<?php namespace Langemike\Laravel5Less;

use lessc;
use File;
use Cache;
use Illuminate\Contracts\Config\Repository as Config;

class Less {

	const RECOMPILE_ALWAYS = 'always';
	const RECOMPILE_CHANGE = 'change';
	const RECOMPILE_NONE = 'none';

	protected $config;
	protected $jobs = array();
	protected $modified_vars = array();
	protected $parsed_less = '';
	public static $cache_key = 'less_cache';

	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Compile CSS
	 * @param string $file CSS filename without extension
	 * @param array $options Compile options
	 * @return bool true on succes, false on failure
	 */
	public function compile($file, $options = array()) {
		$config = $this->prepareConfig($options);
		$input_file = $config['less_path'] . DIRECTORY_SEPARATOR . $file . '.less';
		$output_file = $config['public_path'] . DIRECTORY_SEPARATOR . $file . '.css';
		$parser = new \Less_Parser($config);
		$parser->parseFile($input_file, asset('/'));
		// Iterate through jobs
		foreach($this->jobs as $i => $job) {
			call_user_func_array(array($parser, array_shift($job)), $job);
			unset($this->jobs[$i]);
		}
		return $this->writeCss($output_file, $parser->getCss());
	}

	/**
	 * Write CSS file to disk
	 * @param  string $output_file CSS filepath
	 * @param  string $css CSS
	 * @return bool true on succes, false on failure
	 */
	protected function writeCss($output_file, $css) {
		$this->modified_vars = array();
		$this->parsed_less = '';
		return File::put($output_file, $css);
	}

	/**
	 * Recompile CSS if needed
	 * @param string $file CSS filename without extension
	 * @param string $recompile CSS always (RECOMPILE_ALWAYS), when changed (RECOMPILE_CHANGE) or never (RECOMPILE_NONE)
	 * @param array $options Extra compile options
	 * @return bool true on recompiled, false when not
	 */
	public function recompile($file, $recompile = null, $options = array()) {
		if (is_null($recompile)) {
			$recompile = env('LESS_RECOMPILE');
		}
		switch($recompile) {
			case self::RECOMPILE_ALWAYS :
				return $this->compile($file, $options);
			case self::RECOMPILE_CHANGE :
				$config = $this->prepareConfig($options);
				$input_file = $config['less_path'] . DIRECTORY_SEPARATOR . $file . '.less';
				$cache_key = $this->getCacheKey($file);
				$cache_value = \Less_Cache::Get(array($input_file => asset('/')), $config, $this->modified_vars);
				if (Cache::get($cache_key) !== $cache_value || !empty($this->parsed_less)) {
					Cache::put($cache_key, $cache_value, 0);
					return $this->compile($file);
				}
				return false;
			case self::RECOMPILE_NONE :
			case null:
				return false;
			default:
				throw new \Exception('Unknown \'' . $recompile . '\' LESS_RECOMPILE setting');
		}
		return false;
	}

	/**
	 * Get filename-based cache key
	 * @param string $file
	 * @return  string Cache key
	 */
	protected function getCacheKey($file) {
		return self::$cache_key . '_' . $file;
	}

	/**
	 * Get configuration
	 * @param array $options 
	 * @return array Less configuration
	 */
	protected function prepareConfig($options = array()) {
		$defaults = array(
			'compress' => false,
			'sourceMap' => false,
			'cache_dir' => storage_path('framework/cache/lessphp'),
			'public_path' => public_path('css'),
			'less_path' => base_path('resources/assets/less'),
			// 'cache_method' => function() {}
		);
		return array_merge($defaults, $this->config->get('less', array()), $options);
	}

	/**
	 * Append custom CSS/LESS to CSS output
	 * @param string $less 
	 * @return \Less
	 */
	public function parse($less) {
		$this->jobs[] = array('parse', $less);
		$this->parsed_less .= $less . PHP_EOL;
		return $this;
	}

	/**
	 * Set values of LESS variables
	 * @param array $variables
	 * @return \Less
	 */
	public function modifyVars($variables = array()) {
		$this->jobs[] = array('ModifyVars', $variables);
		$this->modified_vars = array_merge($this->modified_vars, $variables);
		return $this;
	}

	/**
	 * Return output CSS url. Recompile CSS as configured  
	 * @param  string $file
	 * @return string 
	 */
	public function url($file) {
		$recompiled = $this->recompile($file);
		$path = $this->config->get('less.link_path', '/css');
		return asset($path . '/' . $file . '.css');
	}
}
