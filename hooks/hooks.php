<?php

/**
 * Git hook handler
 *
 * Run pre- and post-receive hooks based on a json config file for remote
 * pushes and deployments.
 *
 * PHP version 5
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    Git-hooks
 * @author     Mike Pearce <mike@mikepearce.net>
 * @license    GNU GPL
 * @link       http://github.com/mikepearce/git-hooks
 * @since      File available since Release 1.2.0 (01/11/12)
 */
class gitHooks {

	/**
	 * Where is this class called from?
	 **/
	public $stage;

	/**
	 * Config array
	 **/
	public $conf;

	/**
	 * Set the stage (gegddit?)
	 * @param string $stage What stage of the hook?
	 * @param array $conf A config array
	 */
	public function __construct($stage, $branch, array $conf) {
		$this->stage = $stage;
		$this->branch = $branch;
		$this->config = $conf;
	}

	/**
	 * The reason I hate PHP so much
	 * A function to gracefully return an empty array
	 * @param array $array The array to work on
	 * @param string $arg subsequent array indexes
	 * @return array Array
	 */
	private static function _a() {
		$args = func_get_args();
		$array = array_shift($args);
		foreach ($args as $index) {
			if (!isset($array[$index])) return array();
			if (!is_array($array[$index])) return array();
			$array = $array[$index];
		}
		return $array;
	}

	private function _buildYamlVars() {
		$yaml_vars = array();
		foreach (self::_a($this->config, 'hooks', 'config_vars', 'execute') AS $var => $val) {
			$yaml_vars[$var] = str_replace("\n", "", shell_exec($val));
		}
		foreach (self::_a($this->config, 'hooks', 'config_vars', 'plain') AS $var => $val) {
			$yaml_vars[$var] = str_replace("\n", "", $val);
		}

		// Get the branch specific config
		foreach (self::_a($this->config, 'hooks', 'branches', $this->branch, 'config_vars') AS $var => $val) {
			$yaml_vars[$var] = str_replace("\n", "", $val);
		}
		return $yaml_vars;
	}

	/**
	 * Do the thing
	 **/
	public function run() {
		// First are there any vars? If so, set them
		$yaml_vars = $this->_buildYamlVars();

		// Now, loop through the branch stepsi
		foreach(self::_a($this->config, 'hooks', 'branches', $this->branch, $this->stage) AS $stepname) {
			foreach(self::_a($this->config, 'hooks', 'steps', $stepname) AS $hook) {
				// Now, see if there are any replacements
				foreach($yaml_vars AS $var => $val) {
					$hook = str_replace($var, $val, $hook);
				}
				echo passthru($hook);
			}
		}
	}
}
