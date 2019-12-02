<?php
define('VERSION', 1.6);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/' . $cf['framework'];

if (!$cf['deleter']['warning_time']['enabled']) {
    $cf['deleter']['warning_time']['suffix']['enabled']                       = false;
    $cf['deleter']['warning_time']['info_channel']['channel_name']['enabled'] = false;
    $cf['deleter']['warning_time']['info_channel']['description']['enabled']  = false;
}

date_default_timezone_set($cf['deleter']['timezone']);

TeamSpeak3::init();

msg('Bot Started | ChannelDeleter ' . VERSION);
msg('PHP ' . phpversion() . ' | TS3Lib ' . TeamSpeak3::LIB_VERSION);
msg('Bot by Ondra3211 | https://github.com/Ondra3211' . PHP_EOL);

try {
    $uri = 'serverquery://' . rawurlencode($cf['connect']['username']) . ':' . rawurlencode($cf['connect']['password']) . "@{$cf['connect']['host']}:{$cf['connect']['qport']}/?server_port={$cf['connect']['vport']}&nickname=" . rawurlencode($cf['connect']['nickname'] . '&blocking=0&timeout=3');
    $ts3 = TeamSpeak3::factory($uri);
    
    msg('Connected to: ' . $ts3->getProperty('virtualserver_name') . PHP_EOL);

    if ($cf["connect"]["default_channel"] != false) {
        $ts3->clientMove($ts3->whoamiGet('client_id'), $cf['connect']['default_channel']);
    }

    while (1) deleter($cf['deleter']['update_interval']);

} catch (TeamSpeak3_Exception $e) {
    msg('[ERROR] ' . $e->getCode() . ': ' . $e->getMessage());
}

function deleter($sleep)
{
    global $ts3, $cf;

    $ts3->channelListReset();
    $parent   = $ts3->channelGetById($cf['deleter']['parent_channel']);
    $channels = $parent->subChannelList();
    $desc     = '';

    foreach ($channels as $channel) {

        //Time in channel topic
        if ($channel['seconds_empty'] === -1) {
            if (!empty($channel['channel_topic'])) {
                $channel['channel_topic'] = '';
            }
            delicon($channel);
        } else {
            if (empty($channel['channel_topic'])) {
                $channel['channel_topic'] = date('d.m.Y H:i:s');
            }
        }

        //Channel topic time
        if (!empty($channel['channel_topic'])) {
            $time = strtotime($channel['channel_topic']);
            if ($time) {
                $time = time() - $time;

                if ($time < $cf['deleter']['delete_time'] && $time >= $cf['deleter']['warning_time']['time']) {
                    if ($cf['deleter']['warning_time']['enabled']) {
                        $deltime = $time - $cf['deleter']['delete_time'];
                        $minutes = floor(abs($deltime) / 60 % 60);
                        $hours   = floor(abs($deltime) / 3600);
                        $seconds = floor(abs($deltime) % 60);

                        //Prepare description
                        $desc .= str_replace(['[CHANNEL]', '[HOURS]', '[MINUTES]', '[SECONDS]', '[COUNT]'], ['[URL=channelid://' . $channel['cid'] . ']' . $channel['channel_name'] . '[/URL]', $hours, $minutes, $seconds, count($parent->subChannelList())], $cf['deleter']['warning_time']['info_channel']['description']['description']) . PHP_EOL;

                        //Warning suffix
                        if ($cf['deleter']['warning_time']['suffix']['enabled']) {
                            if (!array_key_exists(143, $channel->permList()) || $channel->permList()[143]['permvalue'] === 0) {
                                $channel->permAssign('i_icon_id', $cf['deleter']['warning_time']['suffix']['suffix']);
                            }
                        }
                    }
                } elseif ($time < $cf['deleter']['warning_time']['time']) {
                    delicon($channel);
                } else {
                    $channel->delete(true);
                    $ts3->channelListReset();
                }
            } else {
                msg('[ERROR] Failed to read time in "' . $channel['channel_name'] . '" ChannelID: ' . $channel['cid']);
            }
        }
    }

    //Info channel, set channel name and descripiton
    if ($cf['deleter']['warning_time']['info_channel']['enabled']) {
        $parent_name = $ts3->channelGetById($cf['deleter']['warning_time']['info_channel']['cid']);
        if ($cf['deleter']['warning_time']['info_channel']['channel_name']['enabled']) {
            if ($parent_name['channel_name'] != str_replace('[COUNT]', count($parent->subChannelList()), $cf['deleter']['warning_time']['info_channel']['channel_name']['channel_name'])) {
                $parent_name['channel_name'] = str_replace('[COUNT]', count($parent->subChannelList()), $cf['deleter']['warning_time']['info_channel']['channel_name']['channel_name']);
            }
        }

        if ($cf['deleter']['warning_time']['info_channel']['description']['enabled']) {
            if (empty($desc)) {
                $desc = $cf['deleter']['warning_time']['info_channel']['description']['description_empty'] . PHP_EOL;
            }
            if ($parent_name['channel_description'] != $cf['deleter']['warning_time']['info_channel']['description']['description_prefix'] . PHP_EOL . $desc . $cf['deleter']['warning_time']['info_channel']['description']['description_suffix']) {
                $parent_name['channel_description'] = $cf['deleter']['warning_time']['info_channel']['description']['description_prefix'] . PHP_EOL . $desc . $cf['deleter']['warning_time']['info_channel']['description']['description_suffix'];
            }
        }
    }
    sleep($sleep);
}

function delicon($channel) {
    global $cf;

    if ($cf['deleter']['warning_time']['suffix']['enabled']) {
        if (array_key_exists(143, $channel->permList())) {
            $channel->permRemove('i_icon_id');
        }
    }
}

function msg($message)
{
    echo '[' . date('d.m.Y H:i:s') . "] {$message}" . PHP_EOL;
}

?>
