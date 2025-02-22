<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2023 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . '/../include/cli_check.php');
	array_shift($_SERVER['argv']);
	if (isset($_SERVER['argv'][0]) && $_SERVER['argv'][0] == 'wmi_script') {
		array_shift($_SERVER['argv']);
	}

	print call_user_func_array('wmi_script', $_SERVER['argv']);
}

function wmi_script($hostname, $host_id, $wmiquery, $cmd = '', $arg1 = '', $arg2 = '') {
	global $config;

	include_once($config['base_path'] . '/plugins/wmi/linux-wmi.php');

	$wmi = new Linux_WMI ($host_id);
	$wmi->hostname = $hostname;
	$wmi->binary   = $config['base_path'] . '/plugins/wmi/wmic';

	/* Fetch the info for this WMI query from the database, exit if not found */
	$wmiinfo = db_fetch_row("SELECT * FROM plugin_wmi_queries WHERE queryname = '$wmiquery'", FALSE);
	if (!isset($wmiinfo['queryclass'])) {
		return '';
	}
	$wmi->indexkey = $wmiinfo['indexkey'];
	$wmi->keys = $wmiinfo['querykeys'];
	$wmi->queryclass = $wmiinfo['queryclass'];

	if ($cmd == 'index') {
		$wmi->create_query();
		$results = $wmi->fetch();
		$k = $wmi->fetch_key_index('Name');

		if (isset($results[2])) {
			array_shift($results);
			array_shift($results);
			foreach ($results as $r) {
				print str_replace(array(' ','(', ')'), '', $r[$k]) . "\n";
			}
		}
	} elseif ($cmd == 'query') {
		if ($arg1 == 'index') {
			$wmi->create_query();
			$results = $wmi->fetch();
			$wmi->print_indexes();
		} else {
			$wmi->create_query();
			$results = $wmi->fetch();
			$wmi->print_fetch_key_value_pair($arg1, $arg2);
		}
	} elseif ($cmd == 'get') {
		$wmi->create_query();
		$results = $wmi->fetch();
		echo $wmi->fetch_value($arg1, $arg2);
	}
}

