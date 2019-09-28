<?php
$cf = require __DIR__ . "/config.php";
require_once __DIR__ . "/{$cf["framework"]}";
date_default_timezone_set($cf["deleter"]["timezone"]);

/**
 * TeamSpeak3 Channel Deleter
 */

TeamSpeak3::init();

msg("Bot Started | ChannelDeleter 1.3");
msg("PHP " . phpversion() . " | TS3Lib " . TeamSpeak3::LIB_VERSION);
msg("Bot by Ondra3211 | https://github.com/Ondra3211" . PHP_EOL);
$lastUpdate = time();

if (!$cf["deleter"]["warning_time"]["enabled"]) {
    $cf["deleter"]["warning_time"]["suffix"]["enabled"] = false;
    $cf["deleter"]["warning_time"]["info_channel"]["channel_name"]["enabled"] = false;
    $cf["deleter"]["warning_time"]["info_channel"]["description"]["enabled"] = false;
}

try
{
    TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyServerselected", "onSelect");

    $uri = "serverquery://" . rawurlencode($cf["connect"]["username"]) . ":" . rawurlencode($cf["connect"]["password"]) . "@{$cf["connect"]["host"]}:{$cf["connect"]["qport"]}/?server_port={$cf["connect"]["vport"]}&nickname=" . rawurlencode($cf["connect"]["nickname"] . "&blocking=0&timeout=5");
    $ts3 = TeamSpeak3::factory($uri);
    Deleter();
    while (1) {
        $ts3->getAdapter()->wait();
    }

} catch (Exception $e) {
    msg("Error " . $e->getCode() . ": " . $e->getMessage());
}

function msg($message)
{
    echo "[" . date("d.m.Y H:i:s") . "] {$message}" . PHP_EOL;
}

function onTimeout($seconds, TeamSpeak3_Adapter_ServerQuery $adapter)
{
    global $cf, $lastUpdate, $ts3;
    if ($adapter->getQueryLastTimestamp() < time() - 250) {
        $adapter->request("clientupdate");
    }
    if (time() - $lastUpdate >= $cf["deleter"]["update_interval"]) {
        Deleter();
        $lastUpdate = time();
    }
}

function onSelect(TeamSpeak3_Node_Host $host)
{
    global $cf;
    TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", "onTimeout");
    $host->serverGetSelected()->notifyRegister("server");
    if ($cf["connect"]["default_channel"] != false) {
        $host->serverGetSelected()->clientMove($host->serverGetSelected()->whoamiGet("client_id"), $cf["connect"]["default_channel"]);
    }
    msg("Connected to: " . $host->serverGetSelected()->getProperty("virtualserver_name") . PHP_EOL);
}

function Deleter()
{
    global $ts3, $cf;
    $ts3->channelListReset();
    $parent = $ts3->channelGetById($cf["deleter"]["parent_channel"]);
    $channels = $parent->subChannelList();
    $desc = "";
    foreach ($channels as $channel) {
        /**
         * Time in channel topic
         */
        if ($channel["seconds_empty"] === -1) {
            if (!empty($channel["channel_topic"])) {
                $channel["channel_topic"] = "";
                if ($cf["deleter"]["warning_time"]["suffix"]["enabled"]) {
                    if (!strpos($channel["channel_name"], $cf["deleter"]["warning_time"]["suffix"]["suffix"]) === false) {
                        $channel["channel_name"] = str_replace($cf["deleter"]["warning_time"]["suffix"]["suffix"], "", $channel["channel_name"]);
                    }
                }
            }
        } else {
            if (empty($channel["channel_topic"])) {
                $channel["channel_topic"] = date("d.m.Y H:i:s");
            }
        }

        /**
         * Channel topic time
         */
        if (!empty($channel["channel_topic"])) {
            $time = strtotime($channel["channel_topic"]);
            if ($time) {
                $time = time() - $time;

                /**
                 * Warning time
                 */
                if ($cf["deleter"]["warning_time"]["enabled"]) {
                    if ($time >= $cf["deleter"]["warning_time"]["time"]) {
                        $deltime = $time - $cf["deleter"]["delete_time"];
                        $minutes = floor(abs($deltime) / 60 % 60);
                        $hours = floor(abs($deltime) / 3600);
                        $seconds = floor(abs($deltime) % 60);

                        /**
                         * Prepare description
                         */
                        $desc .= str_replace(["[CHANNEL]", "[HOURS]", "[MINUTES]", "[SECONDS]", "[COUNT]"], ["[URL=channelid://" . $channel["cid"] . "]" . str_replace($cf["deleter"]["warning_time"]["suffix"]["suffix"], "", $channel["channel_name"]) . "[/URL]", $hours, $minutes, $seconds, count($parent->subChannelList())], $cf["deleter"]["warning_time"]["info_channel"]["description"]["description"]);

                        /**
                         * Warning suffix
                         */
                        if ($cf["deleter"]["warning_time"]["suffix"]["enabled"]) {
                            if (!strpos($channel["channel_name"], $cf["deleter"]["warning_time"]["suffix"]["suffix"]) !== false) {
                                if (mb_strlen($channel["channel_name"]) >= 38) {
                                    $channel["channel_name"] = substr($channel["channel_name"], 0, -3) . $cf["deleter"]["warning_time"]["suffix"]["suffix"];
                                } else {
                                    $channel["channel_name"] = $channel["channel_name"] . $cf["deleter"]["warning_time"]["suffix"]["suffix"];
                                }
                            }
                        }
                    }
                }

                /**
                 * Delete time
                 */
                if ($time >= $cf["deleter"]["delete_time"]) {
                    $channel->delete(true);
                }
            }
        }
    }
    $ts3->channelListReset();

    /**
     * Info channel, set channel name and descripiton
     */
    if ($cf["deleter"]["warning_time"]["info_channel"]["enabled"]) {
        $parent_name = $ts3->channelGetById($cf["deleter"]["warning_time"]["info_channel"]["cid"]);
        if ($cf["deleter"]["warning_time"]["info_channel"]["channel_name"]["enabled"]) {
            if ($parent_name["channel_name"] != str_replace("[COUNT]", count($parent->subChannelList()), $cf["deleter"]["warning_time"]["info_channel"]["channel_name"]["channel_name"])) {
                $parent_name["channel_name"] = str_replace("[COUNT]", count($parent->subChannelList()), $cf["deleter"]["warning_time"]["info_channel"]["channel_name"]["channel_name"]);
            }
        }

        if ($cf["deleter"]["warning_time"]["info_channel"]["description"]["enabled"]) {
            if (empty($desc)) {
                $desc = $cf["deleter"]["warning_time"]["info_channel"]["description"]["description_empty"] . PHP_EOL;
            }
            if ($parent_name["channel_description"] != $cf["deleter"]["warning_time"]["info_channel"]["description"]["description_prefix"] . PHP_EOL . $desc . $cf["deleter"]["warning_time"]["info_channel"]["description"]["description_suffix"]) {
                $parent_name["channel_description"] = $cf["deleter"]["warning_time"]["info_channel"]["description"]["description_prefix"] . PHP_EOL . $desc . $cf["deleter"]["warning_time"]["info_channel"]["description"]["description_suffix"];
            }
        }
    }
}
