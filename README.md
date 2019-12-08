# TeamSpeak Query  bot - Channel Deleter [![GitHub license](https://img.shields.io/github/license/Ondra3211/ts-channel-deleter)](https://github.com/Ondra3211/ts-channel-deleter/blob/master/LICENSE)

## What is this?

TeamSpeak query bot that deletes unused channels after specific time.  
Delete time is saved in channel topic so the deletion time will not be reseted after server restart.  
Channel icons if channel is too close to delete.

## Features

- It's query bot so does not use any slots.
- Fully configurable
- Build in most powerful ts3 php framework [TeamSpeak 3 PHP Framework](https://github.com/planetteamspeak/ts3phpframework)
- Works on Linux, macOS and Windows
- Open Source

## Installation
**Requirements**
* PHP 7.x, `mbstring`, `xml`
* TeamSpeak Server - v3.4.0 (build >= 1536564584) or higher.
* Install the TS3 PHP Framework by [manually downloading](https://github.com/ronindesign/ts3phpframework/archive/master.zip) it or using Composer:
```
composer require planetteamspeak/ts3-php-framework
```  
* Channels **must be** as subsubchannels!

**Setup server permissions**  
* Disable for users this permission `b_channel_modify_topic`
## Configuration
<details>
    <summary>config.php</summary>
  
```php
$cf["framework"] = "libraries/TeamSpeak3/TeamSpeak3.php";
$cf["connect"]   = [
    "username"        => "serveradmin",
    "password"        => "2lM3Nop6",
    "host"            => "127.0.0.1",
    "qport"           => "10011",
    "vport"           => "9987",
    "nickname"        => "Channel Deleter",
    "default_channel" => false, //channel id of channel / false to disable
];
$cf["deleter"] = [
    "update_interval" => 120, //Check channels every x seconds
    "timezone"        => "Europe/Prague", //timezones https://www.php.net/manual/en/timezones.php
    "parent_channel"  => 685, //parent channel of sub channels
    "delete_time"     => 604800, //In seconds! (1 week)
    "warning_time"    => [
        "enabled"      => true, //false to disable
        "time"         => 432000, //In seconds! (2 days before delete)
        "suffix"       => [
            "enabled" => true,
            "suffix"  => 4145625407, // IMPORTANT!!! add space before the suffix
        ],
        "info_channel" => [ //avaiable variables [COUNT], [HOURS], [MINUTES], [SECONDS]
            "enabled"      => true, //eneble info channel, false to disable all feauters
            "cid"          => 685, //channel id
            "channel_name" => [
                "enabled"      => true, //false to disable
                "channel_name" => "[cspacer]Number of rooms [COUNT]/50",
            ],
            "description"  => [ // IMPORTANT!!! Only if warning time is enabled!
                "enabled"            => true, //false to disable //if warning time is disabled this is disabled also
                "description_prefix" => "[SIZE=10]list of rooms that will be deleted[/SIZE]", // "\n" for new line
                "description_suffix" => "", // "\n" for new line
                "description"        => "Channel [B][CHANNEL][/B] will be deleted in [HOURS] hours, [MINUTES] minutes and [SECONDS] seconds\n", // "\n" for new line
                "description_empty"  => "No channel will be deleted", // "\n" for new line
            ],
        ],
    ],
];
```
  
</details>

## Usage
```
screen -AmdSL tsbot php bot.php
```

## Screenshosts  
<details>
    <summary>Show Images</summary>

![](https://i.zerocz.eu/ja/werg2jL9JG.png "Info Channel Description")
![](https://i.zerocz.eu/ja/nKD1Go4GyH.gif "When you switch to channel that is in warning mode")
![](https://i.zerocz.eu/ja/BBRR1uo5Qf.gif "Channel topic edit. Preview how the script works")
![](https://i.zerocz.eu/ja/RFlHSRH6o4.gif "Count of channels")
</details>

## License
```
MIT License

Copyright (c) 2019 Ondřej Niesner

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
