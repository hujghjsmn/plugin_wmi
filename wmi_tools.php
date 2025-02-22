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

chdir('../../');
include('./include/auth.php');
include_once('./lib/snmp.php');
include_once('./lib/utility.php');

set_default_action();

process_request_vars();

switch (get_request_var('action')) {
case 'query':
	walk_host();
	break;
case 'queries':
	common_queries_panel();

	break;
case 'assistance':
	assistance_panel();

	break;
default:
	top_header();
	show_tools();
	bottom_footer();

	break;
}

function process_request_vars() {
	/* ================= input validation and session storage ================= */
	$filters = array(
		'username' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => '',
			'options' => array('options' => 'sanitize_search_string')
		),
		'password' => array(
			'filter' => FILTER_DEFAULT,
			'pageset' => true,
			'default' => '',
		),
		'namespace' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => '',
			'options' => array('options' => 'sanitize_search_string')
		),
		'keyname' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => '',
			'options' => array('options' => 'sanitize_search_string')
		),
		'frequency' => array(
			'filter' => FILTER_VALIDATE_INT,
			'pageset' => true,
			'default' => '120'
		),
		'host' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => '',
			'options' => array('options' => 'sanitize_search_string')
		),
		'name' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => 'New Query',
			'options' => array('options' => 'sanitize_search_string')
		)
	);

	validate_store_request_vars($filters, 'sess_wmic');
	/* ================= input validation ================= */
}

function common_queries_panel() {
	$common = array(
		array(
			'key' => 'None',
			'tip' => __('Get Computer Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_ComputerSystem'
		),
		array(
			'key' => 'ProcessId',
			'tip' => __('Get System Processes', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_Process'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Installed Software', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_Product'
		),
		array(
			'key' => 'None',
			'tip' => __('Get Operating System Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_OperatingSystem'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get OD Service Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_Service'
		),
		array(
			'key' => 'None',
			'tip' => __('Get System Enclosure Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_SystemEnclosure'
		),
		array(
			'key' => 'InterleavePosition',
			'tip' => __('Get System Physical Memory Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PhysicalMemory'
		),
		array(
			'key' => 'DeviceID',
			'tip' => __('Get Memory Device Details', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_MemoryDevice'
		),
		array(
			'key' => 'None',
			'tip' => __('Get System BIOS Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_BIOS'
		),
		array(
			'key' => 'None',
			'tip' => __('Get System Baseboard Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_BaseBoard'
		),
		array(
			'key' => 'DeviceID',
			'tip' => __('Get Processor Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_Processor'
		),
		array(
			'key' => 'None',
			'tip' => __('Ping a Known Address from Computer', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PingStatus where Address = "www.google.com"'
		),
		array(
			'key' => 'None',
			'tip' => __('Get Row System OS Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfRawData_PerfOS_System'
		),
		array(
			'key' => 'None',
			'tip' => __('Get Formatted System OS Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfFormattedData_PerfOS_System'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Formatted Phsycal Disk Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfFormattedData_PerfDisk_PhysicalDisk'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Raw Phsycal Disk Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfRawData_PerfDisk_PhysicalDisk'
		),
		array(
			'key' => 'DeviceID',
			'tip' => __('Get Logical Disk Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_LogicalDisk'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Formatted Logical Disk Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfFormattedData_PerfDisk_LogicalDisk'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Raw Logical Disk Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfRawData_PerfDisk_LogicalDisk'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Formatted CPU Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfFormattedData_PerfOS_Processor'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Raw CPU Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfRawData_PerfOS_Processor'
		),
		array(
			'key' => 'None',
			'tip' => __('Get Raw Memory Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfRawData_PerfOS_Memory'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Formatted Network Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfFormattedData_Tcpip_NetworkInterface'
		),
		array(
			'key' => 'Name',
			'tip' => __('Get Raw Network Performance Data', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_PerfRawData_Tcpip_NetworkInterface'
		),
		array(
			'key' => 'DeviceID',
			'tip' => __('Get Network Adapter Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_NetworkAdapter'
		),
		array(
			'key' => 'None',
			'tip' => __('Get Computer Asset Information', 'wmi'),
			'namespace' => 'root\\\\CIMV2',
			'query' => 'SELECT * FROM Win32_ComputerSystemProduct'
		),
	);

	// Common Queries Panel
	print "<div id='common_queries' style='display:none;'>";

	html_start_box('', '100%', '', '3', 'center', '');

	html_header(array(__('Description', 'wmi'), __('Primary Key', 'wmi'), __('Name Space', 'wmi'), __('Query', 'wmi')));

	$i = 0;
	foreach($common as $query) {
		form_alternate_row('line' . $i, true);

		print "<td style='font-weight:bold;'>" . $query['tip'] .
			"</td><td class='keyname'>" . $query['key'] .
			"</td><td class='namespace'>" . $query['namespace'] .
			"</td><td class='query'>" . $query['query'] . "</td>";

		form_end_row();

		$i++;
	}

	print "<tr><td colspan='4' class='odd'><input type='button' id='close_queries' value='" . __('Close', 'wmi') . "'></td></tr>";

	html_end_box(false);

	print "</div>";
}

// Assistance Panel
function assistance_panel() {
	print "<div id='assistance' style='display:none;'>";

	html_start_box('', '100%', '', '3', 'center', '');

	form_alternate_row();
	print '<td>';

	print '<p>' . __('If you need assistance on error codes, use google, or here use the following Link %s.', '<a target="_new" class="linkEditMain" href="https://msdn.microsoft.com/en-us/library/aa394559(v=vs.85).aspx">' . __('Microsoft Common WBEM Errors', 'wmi') . '</a>', 'wmi') . '</p>';
	print '<p>' . __('For WMI to work the user account you are using must be granted Distributed COM permissions, and the Windows Firewall must be configured to allow Distributed COM communications.  You can find a real good document on this procedure at the following Link %s.', '<a target="_new" class="linkEditMain" href="http://www-01.ibm.com/support/docview.wss?uid=swg21678809">' . __('Distributed COM Setup', 'wmi') . '</a>', 'wmi') . '</p>';

	print '</td>';
	form_end_row();

	print "<tr><td colspan='4' class='odd'><input type='button' id='close_help' value='" . __('Close', 'wmi') . "'></td></tr>";

	html_end_box(false);

	print "</div>";
}

function show_tools() {
	global $action, $host, $username, $password, $command, $wmi_frequencies;

	html_start_box(__('WMI Query Tool', 'wmi') , '100%', '', '3', 'center', '');

	print "<tr><td>";

	form_start('wmi_tools.php?action=query&header=false', 'form_wmi');

	print "<table width='100%'>";
	print "<tr>";
	print "<td valign='center' width='50'>" . __('Name', 'wmi') . "</td>";
	print "<td><input type='text' size='40' id='name' value='" . html_escape_request_var('name') . "'></td>";
	print "</tr><tr>";
	print "<td valign='center' width='50'>" . __('Frequency', 'wmi') . "</td>";
	print "<td><select id='frequency'>";
	foreach($wmi_frequencies as $key => $name) {
		print "<option value='$key'" . (get_request_var('frequency') == $key ? ' selected':'') . ">" . $name . "</option>";
	}
	print "</select></td>";
	print "</tr><tr>";
	print "<td valign='center' width='50'>" . __('Host', 'wmi') . "</td>";
	print "<td><input type='text' size='40' id='host' value='" . html_escape_request_var('host') . "'></td>";
	print "</tr><tr>";
	print "<td class='nowrap'>" . __('Username', 'wmi') . "</td>";
	print "<td><input type='text' size='30' id='username' value='" . html_escape_request_var('username') . "'></td>";
	print "</tr><tr>";
	print "<td class='nowrap'>" . __('Password', 'wmi') . "</td>";
	print "<td><input type='password' size='30' id='password' value='" . html_escape_request_var('password') . "'></td>";
	print "</tr><tr>";
	print "<td class='nowrap'>" . __('Namespace', 'wmi') . "</td>";
	print "<td><input type='text' size='30' id='namespace' value='" . html_escape_request_var('namespace') . "'></td>";
	print "</tr><tr>";
	print "<td class='nowrap'>" . __('Command', 'wmi') . "</td>";
	print "<td><textarea class='textAreaNotes' rows='4' cols='80' id='command' value='" . html_escape_request_var('command') . "'></textarea></td>";
	print "</tr><tr>";
	print "<td class='nowrap'>" . __('Primary Key', 'wmi') . "</td>";
	print "<td><input type='text' size='30' id='keyname' value='" . html_escape_request_var('keyname') . "'></td>";
	print "</tr><tr>";
	print "<td colspan='2' style='padding:5px 0px;'><a class='hyperLink' target='_new' href='" . html_escape('https://docs.microsoft.com/en-us/windows/win32/cimwin32prov/win32-provider') ."'>" . __('More Class Information @Microsoft', 'wmi') . "</a></td></tr>";
	print "<tr><td colspan='2'>";
	print "<input type='submit' value='" . __('Run', 'wmi') . "' id='submit' title='" . __('Run the WMI Query against the Device', 'wmi') . "'>";
	print "<input type='button' value='" . __('Clear', 'wmi') . "' id='clear' title='" . __('Clear the results panel.', 'wmi') . "'>";
	print "<input type='button' value='" . __('Queries', 'wmi') . "' id='queries' title='" . __('Pick from a list of common queries.', 'wmi') . "'>";
	print "<input type='button' value='" . __('Help', 'wmi') . "' id='help' title='" . __('Get some help on setting up WMI', 'wmi') . "'>";
	print "<input type='button' value='" . __('Add', 'wmi') . "' id='add' title='" . __('Create a new WMI Query from the existing Query.', 'wmi') . "'>";
	print "</td></tr>";
	print "</table>";

	form_end();

	print "</td></tr>";

	html_end_box();

	// Query Results Panel
	html_start_box(__('Query Results', 'wmi') , '100%', '', '3', 'center', '');

	form_alternate_row();

	print "<td><div class='odd' style='min-height:200px;' id='results'></div></td>";

	form_end_row();

	html_end_box();

	?>
	<script type='text/javascript'>
	$(function() {
		<?php if (get_selected_theme() != 'classic') {?>
		$('#add').button('disable');
		<?php } else {?>
		$('#add').prop('disabled', true);
		<?php }?>

		$('#form_wmi').unbind().submit(function(event) {
			event.preventDefault();
			runQuery();
		});

		$('#queries').click(function() {
			$('#assistance').remove();
			$.get('wmi_tools.php?action=queries', function(data) {
				$('body').append(data);
				$('#common_queries').dialog({
					title: '<?php print __('Common Queries (Click to Select)', 'wmi');?>',
					width: '1024',
				});

				$('tr[id^="line"]').css('cursor', 'pointer').attr('title', 'Click to use this Query').tooltip().click(function() {
					$('#command').val($(this).find('.query').html());
					$('#namespace').val($(this).find('.namespace').html());
					$('#keyname').val($(this).find('.keyname').html());
					$('tr[id^="line"]').not(this).removeClass('selected');
					$(this).addClass('selected');
				});

				<?php if (get_selected_theme() != 'classic') {?>
				$('#close_queries').button().click(function() {
					$('#common_queries').remove();
				});
				<?php } else {?>
				$('#close_queries').click(function() {
					$('#common_queries').remove();
				});
				<?php }?>
			});
		});

		$('#help').click(function() {
			$('#common_queries').remove();
			$.get('wmi_tools.php?action=assistance', function(data) {
				$('body').append(data);
				$('#assistance').dialog({
					title: '<?php print __('WMI Setup Assistance', 'wmi');?>',
					width: '1024',
				});

				<?php if (get_selected_theme() != 'classic') {?>
				$('#close_help').button().click(function() {
					$('#assistance').remove();
				});
				<?php } else {?>
				$('#close_help').click(function() {
					$('#assistance').remove();
				});
				<?php }?>
			});
		});

		$('#wmi_tools1').find('.cactiTableTitle, .cactiTableBottom').css('cursor', 'pointer').click(function() {
			$('#wmi_tools1_child').toggle();
		});

		$('#wmi_tools2').find('.cactiTableTitle, .cactiTableBottom').css('cursor', 'pointer').click(function() {
			$('#wmi_tools2_child').toggle();
		});

		$('#wmi_tools3').find('.cactiTableTitle, .cactiTableBottom').css('cursor', 'pointer').click(function() {
			$('#wmi_tools3_child').toggle();
		});

		$('#clear').click(function() {
			$('#results').empty();
			<?php if (get_selected_theme() != 'classic') {?>
			$('#submit').button('enable');
			<?php } else {?>
			$('#submit').prop('disabled', false);
			<?php }?>
		});

		$('#add').click(function() {
			post = {
				__csrf_magic: csrfMagicToken,
				name: $('#name').val(),
				frequency: $('#frequency').val(),
				namespace: $('#namespace').val(),
				enabled: '',
				query: $('#command').val(),
				primary_key: $('#keyname').val()
			};

			$.post('wmi_queries.php?action=save', post).done(function(data) {
				$('#main').html(data);
				applySkin();
			});
		});
	});

	function runQuery() {
		$.post('wmi_tools.php?action=query&header=false', { host: $('#host').val(), username: $('#username').val(), password: $('#password').val(), namespace: $('#namespace').val(), command: $('#command').val(), __csrf_magic: csrfMagicToken }).done(function(data) {
			$('#results').html(data);
			applySkin();
			<?php if (get_selected_theme() != 'classic') {?>
			$('#submit').button('enable');
			<?php } else {?>
			$('#submit').prop('disabled', false);
			<?php }?>
			if (data.indexOf('ERROR:') == -1) {
				<?php if (get_selected_theme() != 'classic') {?>
				$('#add').button('enable');
				<?php } else {?>
				$('#add').prop('disabled', false);
				<?php }?>
			}
		});
	}

	</script>
	<?php
}

function walk_host() {
	global $config, $host;

	$host      = get_nfilter_request_var('host');
	$username  = get_nfilter_request_var('username');
	$password  = get_nfilter_request_var('password');
	$namespace = get_nfilter_request_var('namespace');

	if (!isset_request_var('command')) {
		$command = 'SELECT * FROM Win32_Process';
	} else {
		$command = get_nfilter_request_var('command');
	}

	$host = strtolower($host);

	if ($username == '' || $password == '' || $host == '') {
		print __('ERROR: You must provide a host, username, password and query', 'wmi');
		exit;
	}

	if ($config['cacti_server_os'] != 'win32') {
		include_once($config['base_path'] . '/plugins/wmi/linux_wmi.php');

		$wmi = new Linux_WMI();
		$wmi->hostname    = $host;
		$wmi->username    = $username;
		$wmi->password    = $password;
		$wmi->querynspace = $namespace;
		$wmi->command     = $command;
		$wmi->binary      = read_config_option('path_wmi');

		if ($wmi->binary == '') {
			$wmi->binary = '/usr/bin/wmic';
		}

		if ($wmi->querynspace == '') {
			$wmi->querynspace = 'root\\\\CIMV2';
		}

		if ($wmi->fetch() !== false) {;
			print "<table style='width:100%'><tr><td class='even'>";

			$indexes = $wmi->fetch_indexes();
			$class   = $wmi->fetch_class();
			$data    = $wmi->fetch_data();

			print "<h4>" . __('WMI Query Results for Device: %s, Class: %s, Columns: %s, Rows: %s', $host, $class, sizeof($indexes), sizeof($data), 'wmi') . "</h4>";

			print "<p>" . __('Showing columns and first one or two rows of data.', 'wmi') . "</p>";

			print "</table>";
			print "<table style='width:100%'>";

			$present = 'columns';

			if ($present == 'columns') {
				if (cacti_sizeof($data[0])) {
					foreach($data[0] as $index => $r) {
						form_alternate_row('line' . $index, true);

						print "<td style='font-weight:bold;'>" . $indexes[$index] . "</td><td>" . $r . "</td>";

						if (isset($data[1][$index])) {
							print "<td style='font-weight:bold;'>" . $indexes[$index] . "</td><td>" . $data[1][$index] . "</td>";
						}

						form_end_row();
					}
				}
			} else {
				foreach($data as $row) {
					$indexes = array_keys($row);
					if (cacti_sizeof($indexes)) {
						print "<tr>";
						foreach($indexes as $col) {
							print "<th>" . $col . "</th>";
						}
						print "</tr>";
					}

					print "<tr>";
					foreach($row as $data) {
						print "<td>" . $data . "</td>";
					}
					print "</tr>";
				}
			}

			print "</table>";
		} else {
			print $wmi->error;
		}
	} else {
		// Windows version
		$wmi  = new COM('WbemScripting.SWwebLocator');
		$wmic = $wmi->ConnectServer($host, $namespace, $username, $password);
		$wmic->Security_->ImpersonationLevel = 3;

		$data = $wmic->ExecQuery($command);

		if (cacti_sizeof($data)) {
			$odata = (array) $data[0];
			$indexes = array_keys($odata);
			if (isset($data[1])) {
				$odata1 = (array) $data[1];
			} else {
				$odata1 = array();
			}

			print "<table style='width:100%'><tr><td>";

			print "<h4>" . __('WMI Query Results for Device: %s, Class: %s, Columns: %s, Rows: %s', $host, $namespace, sizeof($indexes), sizeof($data), 'wmi') . "</h4>";

			print "<p>" . __('Showing columns and first one or two rows of data.', 'wmi') . "</p>";

			print "</table>";
			print "<table style='width:100%'>";

			if (cacti_sizeof($odata)) {
				foreach($odata as $index => $r) {
					form_alternate_row('line' . $index, true);

					print "<td style='font-weight:bold;'>" . $indexes[$index] . "</td><td>" . $r . "</td>";

					if (cacti_sizeof($odata1)) {
						print "<td style='font-weight:bold;'>" . $indexes[$index] . "</td><td>" . $odata1[$index] . "</td>";
					}

					form_end_row();
				}
			}

			print "</table>";
		}
	}
}

function is_valid_host($host) {
	if (preg_match('/^((([0-9]{1,3}\.){3}[0-9]{1,3})|([0-9a-z-.]{0,61})?\.[a-z]{2,4})$/i', $host)) {
		return true;
	}

	return false;
}
