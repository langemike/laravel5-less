<?php namespace Langemike\Laravel5Less;

use lessc;
use File;
use Cache;
use Illuminate\Contracts\Config\Repository as Config;

class Less {

	protected $config;
	protected $jobs = array();
	public static $cache_key = 'less_cache';

	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Compile CSS
	 * @param string $file CSS filename without extension
	 * @param array $options Compile options
	 * @return array parsed files
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
		File::put($output_file, $parser->getCss());
		return $parser->allParsedFiles();
	}

	/**
	 * Get configuration
	 * @param array $options 
	 * @return array Less configuration
	 */
	private function prepareConfig($options = array()) {
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
		return $this;
	}

	/**
	 * Set values of LESS variables
	 * @param array $variables
	 * @return \Less
	 */
	public function modifyVars($variables = array()) {
		$this->jobs[] = array('ModifyVars', $variables);
		return $this;
	}

	/**
	 * Return output CSS url. Recompile CSS as configured  
	 * @param  string $file
	 * @return string 
	 */
	public function url($file) {
		switch(env('LESS_RECOMPILE')) {
			case 'always' : // Always recompile
				$this->compile($file);
				break;
			case 'change' :
			case 'update' : // When modification is detected
				$config = $this->prepareConfig();
				$input_file = $config['less_path'] . DIRECTORY_SEPARATOR . $file . '.less';
				$cache_value = \Less_Cache::Get(array($input_file => asset('/')), $config); //@todo Less_Cache variables parameter support
				if (Cache::get(self::$cache_key) !== $cache_value) {
					$this->compile($file);
					Cache::put(self::$cache_key, $cache_value, 0);
				}
				break;
			case 'none' :
			default:
				// Do nothing
		}
		$path = $this->config->get('less.link_path', '/css');
		return asset($path . '/' . $file . '.css');
	}
}
