<?php

declare(strict_types=1);

namespace FlatterGnat9702\AnyVersion12;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class AnyVersion12 extends PluginBase implements Listener{

    /** @var int[] */
    private $protocols;

    public function onEnable() : void{
        $this->protocols = [120, 134, 135, 136, 137, 140, 141, 150];
        $this->getLogger()->notice(TextFormat::YELLOW . "Server protocol version: " . ProtocolInfo::CURRENT_PROTOCOL);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onLoginPacket(DataPacketReceiveEvent $event) : void{ 
        $packet = $event->getPacket();
        if($packet instanceof LoginPacket){
            $username = $packet->username ?? "[Unknown player]";
            if($packet->protocol === ProtocolInfo::CURRENT_PROTOCOL){
                $this->getLogger()->notice(TextFormat::GREEN . $username . " uses a server-compatible version of the protocol.");
                return;
            }
            if(!in_array($packet->protocol, $this->protocols)){
                $this->getLogger()->notice(TextFormat::RED . $username . " use a protocol-incompatible version of the server (" . $packet->protocol . ")");
                return;
            }
            $this->getLogger()->notice(TextFormat::YELLOW . $username . " uses the protocol version " . $packet->protocol . " (server version: " . ProtocolInfo::CURRENT_PROTOCOL . ")");
            $this->getLogger()->notice(TextFormat::RED . "Warning! " . TextFormat::YELLOW . "Using an obsolete/new client can damage your server.");
            $this->getLogger()->notice(TextFormat::YELLOW . "Use it at your own peril and risk.");
            $packet->protocol = ProtocolInfo::CURRENT_PROTOCOL;
        }
    }

}