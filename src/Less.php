<?php namespace Langemike\Laravel5Less;

use lessc;
use Storage;
use Illuminate\Contracts\Config\Repository as Config;

class Less {

	protected $config = array();
	protected $jobs = array();
	protected $compiled;

	public function __construct(Config $config) {
		$this->config = $config;
		$this->settings = $config->get('less', array());
	}

	public function compile($file, $options = array()) {
		//@todo add caching
		$default = array(
			'compress' => false,
			'sourceMap' => false,
			'cache_dir' => storage_path('framework/cache/less'),
			// 'cache_method' => function() {}
		);
		$options = array_merge($defaults, $this->settings, $options);
		$parser = new Less_Parser($options);
		$parser->parseFile( 'app/cms/resources/less/cms.less', '/mysite/' );
		// Iterate through jobs
		foreach($this->jobs as $job) {
			call_user_func_array(array($parser, array_shift($job)), $job);
		}
		$this->jobs = array(); // Empty
		$this->compiled = Storage::put('public/cms/css/test.css', $parser->getCss());
		return $this;
	}

	public function parse($less) {
		$this->jobs[] = array('parse', $less);
		return $this;
	}

	public function modifyVars($variables = array()) {
		$this->jobs[] = array('ModifyVars', $variables);
		return $this;
	}

	public function url($file = null) {
		if($file === null) {
			//$file = $this->file;
		}
		return '';
	}
}