<?php

namespace OCA\WOL\Service;

class WakeOnLAN
{
    public static function wakeUp($deviceMac, $broadcastSubnetAddress)
    {
        $deviceMac = str_replace(':', '', $deviceMac);
        $deviceMac = str_replace('-', '', $deviceMac);

        // validate $deviceMac
        if (!ctype_xdigit($deviceMac)) {
            throw new \Exception('MAC address invalid, only hex values (0-9, a-f) allowed');
        }

        $deviceMacBinary = pack('H12', $deviceMac);
        $magicPacket = str_repeat(chr(0xff), 6).str_repeat($deviceMacBinary, 16);

        if (!$fp = fsockopen('udp://' . $broadcastSubnetAddress, 7, $errno, $errstr, 2)) {
            throw new \Exception("Cannot open UDP socket: {$errstr}", $errno);
        }

        fputs($fp, $magicPacket);
        fclose($fp);
    }
}