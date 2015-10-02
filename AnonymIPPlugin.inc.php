<?php

/**
 * @file AnonymIPPlugin.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.anonymIP
 * @class AnonymIPPlugin
 *
 * Remove the last octet from the IP addresses before saving them in the database
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class AnonymIPPlugin extends GenericPlugin {

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.anonymIP.name');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.anonymIP.description');
	}

	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			HookRegistry::register('eventlogdao::_insertobject', array($this, 'anonymize'));
			HookRegistry::register('emaillogdao::_insertobject', array($this, 'anonymize'));
			HookRegistry::register('commentdao::_insertcomment', array($this, 'anonymize'));
			HookRegistry::register('commentdao::_updatecomment', array($this, 'anonymize'));
			if (!Config::getVar('security', 'session_check_ip')) {
				HookRegistry::register('sessiondao::_insertsession', array($this, 'anonymize'));
				HookRegistry::register('sessiondao::_updateobject', array($this, 'anonymize'));
			}
		}
		return $success;
	}

	/**
	 * Hook callback: Handle requests.
	 * Anonymize the IP adress -- set the last octet to zero.
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function anonymize($hookName, $args) {
		$params =& $args[1];
		$ipPattern = '/([0-9]+\\.[0-9]+\\.[0-9]+)\\.[0-9]+/';
		foreach ($params as $index => $param) {
			if (preg_match($ipPattern, $param)) {
				$anonymizedIP = preg_replace($ipPattern, '\\1.0', $param);
				$params[$index] = $anonymizedIP;
			}
		}
		return false;
	}

}
?>
