<?php

namespace zyware\AnyVersion;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\PlayerNetworkSessionAdapter;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class AnyVersion extends PluginBase implements Listener{

	public const PROTOCOL_12 = [134, 135, 136, 137, 140, 141, 150, 160, 200, 201, 223];

	public const PACKET_12 = [
		"LoginPacket" => 0x01,
		"PlayStatusPacket" => 0x02,
		"ServerToClientHandShakePacket" => 0x03,
		"ClientToServerHandShakePacket" => 0x04,
		"DisconnectPacket" => 0x05,
		"ResourcePacksInfoPacket" => 0x06,
		"ResourcePackStackPacket" => 0x07,
		"ResourcePackClientResponsePacket" => 0x08,
		"TextPacket" => 0x09,
		"SetTimePacket" => 0x0a,
		"StartGamePacket" => 0x0b,
		"AddPlayerPacket" => 0x0c,
		"AddEntityPacket" => 0x0d,
		"RemoveEntityPacket" => 0x0e,
		"AddItemEntityPacket" => 0x0f,
		"AddHangingEntityPacket" => 0x10,
		"TakeItemEntityPacket" => 0x11,
		"MoveEntityPacket" => 0x12,
		"MovePlayerPacket" => 0x13,
		"RiderJumpPacket" => 0x14,
		"UpdateBlockPacket" => 0x15,
		"AddPaintingPacket" => 0x16,
		"ExplodePacket" => 0x17,
		"LevelSoundEventPacket" => 0x18,
		"LevelEventPacket" => 0x19,
		"BlockEventPacket" => 0x1a,
		"EntityEventPacket" => 0x1b,
		"MobEffectPacket" => 0x1c,
		"UpdateAttributesPacket" => 0x1d,
		"InventoryTransctionPacket" => 0x1e,
		"MobEquipmentPacket" => 0x1f,
		"MobArmorEquipmentPacket" => 0x20,
		"InteractPacket" => 0x21,
		"BlockPickRequestPacket" => 0x22,
		"EntityPickRequestPacket" => 0x23,
		"PlayerActionPacket" => 0x24,
		"EntityFallPacket" => 0x25,
		"HurtArmorPacket" => 0x26,
		"SetEntityDataPacket" => 0x27,
		"SetEntityMotionPacket" => 0x28,
		"SetEntityLinkPacket" => 0x29,

		"SetHealthPacket" => 0x2a,
		"SetSpawnPositionPacket" => 0x2b,
		"AnimatePacket" => 0x2c,
		"RespawnPacket" => 0x2d,
		"ContainerOpenPacket" => 0x2e,
		"ContainerClosePacket" => 0x2f,
		"PlayerHotBarPacket" => 0x30,
		"InventoryContentPacket" => 0x31,
		"InventorySlotPacket" => 0x32,
		"ContainerSetDataPacket" => 0x33,
		"CraftingDataPacket" => 0x34,
		"CraftingEventPacket" => 0x35,
		"GuiDataPickItemPacket" => 0x36,
		"AdventureSettingsPacket" => 0x37,
		"BlockEntityDataPacket" => 0x38,
		"PlayerInputPacket" => 0x39,
		"FullChunkDataPacket" => 0x3a,
		"SetCommandSenabledPacket" => 0x3b,
		"SetDifficultyPacket" => 0x3c,
		"ChangedImensionPacket" => 0x3d,
		"SetPlayerGameTypePacket" => 0x3e,
		"PlayerListPacket" => 0x3f,
		"SimpleEventPacket" => 0x40,
		"EventPacket" => 0x41,
		"SpawnExperienceOrbPacket" => 0x42,
		"ClientBoundMapItemDataPacket" => 0x43,
		"MapInfoRequestPacket" => 0x44,
		"RequestChunkRadiusPacket" => 0x45,
		"ChunkRadiusUpdatedPacket" => 0x46,

		"ItemFrameDropItemPacket" => 0x47,
		"GameRulesChangedPacket" => 0x48,
		"CameraPacket" => 0x49,
		"BossEventPacket" => 0x4a,
		"ShowCreditsPacket" => 0x4b,
		"AvailableCommandsPacket" => 0x4c,
		"CommandRequestPacket" => 0x4d,
		"CommandBlockUpdatePacket" => 0x4e,
		"CommandOutPutPacket" => 0x4f,
		"UpdateTradePacket" => 0x50,

		"UpdateEquipPacket" => 0x51,
		"ResourcePackDataInfoPacket" => 0x52,
		"ResourcePackChunkDataPacket" => 0x53,
		"ResourcePackChunkRequestPacket" => 0x54,
		"TransferPacket" => 0x55,
		"PlaySoundPacket" => 0x56,
		"StopSoundPacket" => 0x57,
		"SetTitlePacket" => 0x58,
		"AddBehaviorTreePacket" => 0x59,
		"StructureBlockUpdatePacket" => 0x5a,
		"ShowStoreOfferPacket" => 0x5b,
		"PurchaseReceiptPacket" => 0x5c,
		"PlayerSkinPacket" => 0x5d,
		"SubClientLoginPacket" => 0x5e,
		"WSConnectPacket" => 0x5f,
		"SetLastHurtByPacket" => 0x60,
		"BookEditPacket" => 0x61,
		"NpcRequestPacket" => 0x62,
		"PhotoTransferPacket" => 0x63,
		"ModalFormRequestPacket" => 0x64,
		"ModalFormResponsePacket" => 0x65,
		"ServerSettingsRequestPacket" => 0x66,
		"ServerSettingsResponsePacket" => 0x67,
		"ShowProfilePacket" => 0x68,
		"SetDefaultGameTypePacket" => 0x69
	];

	/** @var Player[] */
	private $players12 = [];

	/** @var array */
	private $packeters = [];

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function DataPacketReceive(DataPacketReceiveEvent $ev){
		$pk = $ev->getPacket();
		$pl = $ev->getPlayer();
		if(!in_array($pl->getName(), $this->packeters)){
			if($pk instanceof LoginPacket){
				if(in_array($pk->protocol, self::PROTOCOL_12)){
					array_push($this->players12, $pl);
				}
				$pk->protocol = ProtocolInfo::CURRENT_PROTOCOL;
				$this->packeters[$pl->getName()] = [];

				//For 1.2.0 ~ 1.2.10
			}elseif(in_array($pl, $this->players12) and !in_array(ProtocolInfo::CURRENT_PROTOCOL, self::PROTOCOL_12)){
				foreach(self::PROTOCOL_12 as $p => $id){
					if($pk::NETWORK_ID !== $id) continue;
					$this->PacketChanger12($p, $pk);
					$this->PacketReceiveHandler($pk, $pl);
					$ev->setCancelled();
					array_push($id, $this->packeters[$pl->getName()]);
					return;
				}
				$this->getLogger()->info("Unfound Packet Id : 0x" . $pk::NETWORK_ID - 0x00 . ", Buffer : " . $pk->buffer . ", Offset : " . $pk->offset);
			}// 1.2.0 Done

		}else{
			unset($this->packeters[$pl->getName()]);
		}
	}

	public function DataPacketSend(DataPacketSendEvent $ev){
		$pk = $ev->getPacket();
		$pl = $ev->getPlayer();
		$id = $pk::NETWORK_ID;
		if(!in_array($pl->getName(), $this->packeters) or !in_array($id, $this->packeters[$pl->getName()])){
			if(in_array($pl, $this->players12) and !in_array(ProtocolInfo::CURRENT_PROTOCOL, self::PROTOCOL_12)){ //For 1.2.0
				foreach(self::PACKET_12 as $n => $i){
					if($id !== $i) continue;
					$this->PacketChanger12($n, $pk);
					$pl->sendDataPacket($pk);
					$ev->setCancelled();
					array_push($this->packeters[$pl->getName()], $id);
					return;
				}
				$this->getLogger()->info("Unfound Packet Id : 0x" . $pk::NETWORK_ID - 0x00 . ", Buffer : " . $pk->buffer . ", Offset : " . $pk->offset);
			} // 1.2.0 Done
		}else{
			unset($this->packeters[$pl->getName()] [$id]);
		}
	}


	/**
	 *
	 * @param string     &$n
	 * @param DataPacket &$pk
	 * @return bool
	 */
	public function PacketChanger12(string &$n, DataPacket &$pk) : bool{
		$n = "Packets\\v12\\" . self::PACKET_12[$n];
		$np = new $n;
		if(!$np instanceof DataPacket) return false;
		$np->setBuffer($pk->buffer, $pk->offset);
		$pk = $np;
		return true;
	}

	public function PacketReceiveHandler(DataPacket $pk, Player $pl){
		$a = new PlayerNetworkSessionAdapter($this->getServer(), $pl);
		$a->handleDataPacket($pk);
	}

}
