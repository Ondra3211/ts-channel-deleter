<?php
$cf["framework"] = "libraries/TeamSpeak3/TeamSpeak3.php";
$cf["connect"] = [
	"username" => "serveradmin",
	"password" => "2lM3Nop6",
	"host" => "127.0.0.1",
	"qport" => "10011",
	"vport" => "9987",
	"nickname" => "Channel Deleter",
	"default_channel" => false //channel id of channel / false to disable
];
$cf["deleter"] = [
	"update_interval" => 120, //Check channels every x seconds
	"timezone" => "Europe/Prague", //timezones https://www.php.net/manual/en/timezones.php
	"parent_channel" => 685, //parent channel of sub channels
	"delete_time" => 604800, //In seconds! (1 week)
	"warning_time" => [
		"enabled" => true, //false to disable
		"time" => 432000, //In seconds! (2 days before delete)
		"suffix" => [
			"enabled" => true,
			"suffix" => " ❗" // IMPORTANT!!! add space before the suffix 
		],
		"info_channel" => [ //avaiable variables [COUNT], [HOURS], [MINUTES], [SECONDS]
			"enabled" => true, //eneble info channel, false to disable all feauters
			"cid" => 685, //channel id
			"channel_name" => [
				"enabled" => true, //false to disable
				"channel_name" => "[cspacer]Number of rooms [COUNT]/50"
			],
			"description" => [ // IMPORTANT!!! Only if warning time is enabled!
				"enabled" => true, //false to disable //if warning time is disabled this is disabled also
				"description_prefix" => "[SIZE=10]list of rooms that will be deleted[/SIZE]", // "\n" for new line
				"description_suffix" => "", // "\n" for new line
				"description" => "Channel [B][CHANNEL][/B] will be deleted in [HOURS] hours, [MINUTES] minutes and [SECONDS] seconds\n", // "\n" for new line
				"description_empty" => "No channel will be deleted" // "\n" for new line
			]
		]
	]
];
return $cf;
?>
