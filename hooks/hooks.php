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
		$this->config = $conf;
		$this->branch = $branch;
	}

	/**
	 * Do the thing
	 **/
	public function run() {

		// First are there any vars? If so, set them
		$yaml_vars = array();
		if (is_array($this->config['hooks']['config_vars'])) {
			foreach ($this->config['hooks']['config_vars']['execute'] AS $var => $val) {
				$yaml_vars[$var] = str_replace("\n", "", `$val`);
			}
			foreach ($this->config['hooks']['config_vars']['plain'] AS $var => $val) {
				$yaml_vars[$var] = str_replace("\n", "", $val);
			}
		}

		// Get the branch specific config
		if (is_array($this->config['hooks']['branches'][$this->branch]['config_vars'])) {
			foreach ($this->config['hooks']['branches'][$this->branch]['config_vars'] AS $var => $val) {
				$yaml_vars[$var] = str_replace("\n", "", $val);
			}
		}			

		echo "**************\n**** Running ". $this->stage ." hooks\n**************\n";
		// Now, loop through the branch steps
		foreach($this->config['hooks']['branches'][$this->branch][$this->stage] AS $stepname) {

			foreach($this->config['hooks']['steps'][$stepname] AS $hook) {
				// Now, see if there are any replacements
				foreach($yaml_vars AS $var => $val) {
					$hook = str_replace($var, $val, $hook);
				}	
				//echo $hook."\n";
				echo passthru($hook);	
			}
		}
		
		echo "**************\n**** Ended ". $this->stage ." hooks\n**************\n";

	}
}