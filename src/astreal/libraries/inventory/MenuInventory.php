<?php
/*
 * 
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║   
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║   
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║   
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝   
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 * @Date: 
 */

namespace astreal\libraries\inventory;

use astreal\libraries\inventory\interfaces\FakeBlockInventory;
use astreal\libraries\inventory\interfaces\ListenerInventory;
use astreal\libraries\inventory\interfaces\NameableFakeBlockInventory;
use astreal\libraries\inventory\traits\ListenerInventoryTrait;
use astreal\libraries\inventory\traits\NameableFakeBlockInventoryTrait;;

use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\Tile;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\SimpleInventory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;

class MenuInventory extends SimpleInventory implements NameableFakeBlockInventory, ListenerInventory, BlockInventory, FakeBlockInventory
{
    use NameableFakeBlockInventoryTrait;
    use ListenerInventoryTrait;

    private ?CompoundTag $tile = null;
    protected array $requests = [];

    public static function getConnectableBlocks(string $tile, World $world, Vector3 $position, array $sides): array
    {
        $blocks = [];
        if ($tile instanceof TileChest) {
            foreach ($sides as $side) {
                $pos = $position->getSide($side);
                $chest = $world->getTile($pos);
                if ($chest instanceof TileChest) $blocks[] = $pos;
            }
        }
        return $blocks;
    }

    public static function createTile(string $tile_id): CompoundTag
    {
        return CompoundTag::create()->setString(Tile::TAG_ID, $tile_id);
    }

    public function __construct(int $size, private Block $block, private int $windowType, private bool $double = false)
    {
        parent::__construct($size);
        $this->setTile(self::createTile('Chest'));
    }

    public function get_spawn_holder(Player $player): ?Vector3
    {
        foreach ([[1, -1, 0], [-1, -1, 0], [0, -1, 1], [0, -1, -1], [1, 2, 0], [-1, 2, 0], [0, 2, 1], [0, 2, -1]] as $coords) {
            $vector = $player->getPosition()->floor()->add($coords[0] ?? 0, $coords[1] ?? 0, $coords[2] ?? 0);
            if ($player->getWorld()->isInWorld($vector->getX(), $vector->getY(), $vector->getZ())) return $vector;
        }
        return null;
    }

    public function show_menu(Player $player): void
    {
        if ($this->getMenuDataByPlayer($player) === null) {
            $vector = $this->get_spawn_holder($player);
            if ($vector === null) return;
            $connectable_blocks = [];
            if ($this->getBlock() instanceof Chest) {
                $connectable_blocks = array_merge($connectable_blocks, self::getConnectableBlocks(TileChest::class, $player->getWorld(), $vector, [Facing::NORTH, Facing::SOUTH, Facing::WEST]));
                if ($this->isDouble()) array_merge($connectable_blocks, self::getConnectableBlocks(TileChest::class, $player->getWorld(), $vector->east(), [Facing::NORTH, Facing::SOUTH, Facing::WEST]));
            }
            $this->requests[] = new MenuData($player, Position::fromObject($vector, $player->getWorld()), $connectable_blocks);
        }
    }

    private function getMenuDataByPlayer(Player $player): ?MenuData
    {
        foreach ($this->requests as $request) if ($request->getPlayer()->getName() === $player->getName()) return $request;
        return null;
    }

    private function getLastMenuData(): ?MenuData
    {
        return $this->requests[count($this->requests) - 1] ?? null;
    }

    public function getDefaultName(): string
    {
        return "MenuInvenoty";
    }

    public function getBlock(): Block
    {
        return $this->block;
    }

    public function getWindowType(): int
    {
        return $this->windowType;
    }

    public function getHolder(): Position
    {
        return Position::fromObject(Vector3::zero(), null);
    }

    public function isDouble(): bool
    {
        return $this->double && $this->block instanceof Chest;
    }

    public function setTile(?CompoundTag $tag): void
    {
        $this->tile = $tag;
    }

    public function beforeOpeningInventory(Player $player): void
    {
        $md = $this->getMenuDataByPlayer($player);
        if ($md === null) return;
        $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($md->getHolder()), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->getBlock()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
        if ($this->isDouble()) $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($md->getHolder()->east()), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->getBlock()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
        foreach ($md->getConnectableBlocks() as $pos) $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(blockPosition::fromVector3($pos), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::BARREL()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL), true);
        if (!is_null($this->tile)) {
            $this->tile->setString($this::TAG_CUSTOM_NAME, $this->getName());
            $tile = clone $this->tile;
            $pair_tile = clone $tile;
            if ($this->isDouble()) $tile->setInt(TileChest::TAG_PAIRX, $md->getHolder()->east()->getX());
            if ($this->isDouble()) $tile->setInt(TileChest::TAG_PAIRZ, $md->getHolder()->east()->getZ());
            $pair_tile->setInt(TileChest::TAG_PAIRX, $md->getHolder()->getX());
            $pair_tile->setInt(TileChest::TAG_PAIRZ, $md->getHolder()->getZ());
            $player->getNetworkSession()->sendDataPacket(BlockActorDataPacket::create(BlockPosition::fromVector3($md->getHolder()), new CacheableNbt($tile)));
            if ($this->isDouble()) $player->getNetworkSession()->sendDataPacket(BlockActorDataPacket::create(BlockPosition::fromVector3($md->getHolder()->east()), new CacheableNbt($pair_tile)));
        }
    }

    public function onClose(Player $who): void
    {
        parent::onClose($who);
        $md = $this->getMenuDataByPlayer($who);
        if ($md === null) return;
        $send_data_packet = $who->getNetworkSession()->sendDataPacket(...);
        $send_data_packet(UpdateBlockPacket::create(blockPosition::fromVector3($md->getHolder()), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($md->getHolder()->getWorld()->getBlock($md->getHolder())->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL), true);
        if ($this->isDouble()) $send_data_packet(UpdateBlockPacket::create(blockPosition::fromVector3($md->getHolder()->east()), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($md->getHolder()->getWorld()->getBlock($md->getHolder()->east())->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL), true);
        foreach ($md->getConnectableBlocks() as $pos) $send_data_packet(UpdateBlockPacket::create(blockPosition::fromVector3($pos), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($md->getHolder()->getWorld()->getBlock($pos)->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL), true);
        $tile = $md->getHolder()->getWorld()->getTile($md->getHolder());
        if ($tile instanceof Spawnable) $send_data_packet(BlockActorDataPacket::create(BlockPosition::fromVector3($md->getHolder()), $tile->getSerializedSpawnCompound()), true);
        if ($this->isDouble()) {
            $pair_tile = $md->getHolder()->getWorld()->getTile($md->getHolder()->east());
            if ($pair_tile instanceof Spawnable) $send_data_packet(BlockActorDataPacket::create(BlockPosition::fromVector3($md->getHolder()->east()), $pair_tile->getSerializedSpawnCompound()), true);
        }
        $this->requests = array_filter($this->requests, fn(MenuData $md): bool => $md->getPlayer()->getName() !== $who->getName());
    }

    public function translate(ContainerOpenPacket $packet): void
    {
        $md = $this->getLastMenuData();
        $packet->blockPosition = BlockPosition::fromVector3($md->getHolder());
        $packet->windowType = $this->getWindowType();
    }
}

final class MenuData
{

    public function __construct(private Player $player, private Position $holder, private array $connectable_blocks) {}

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getHolder(): Position
    {
        return $this->holder;
    }

    public function getConnectableBlocks(): array
    {
        return $this->connectable_blocks;
    }
}
