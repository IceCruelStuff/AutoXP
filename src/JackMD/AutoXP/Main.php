<?php

/**
 *   ___        _       __   ________
 *  / _ \      | |      \ \ / /| ___ \
 * / /_\ \_   _| |_ ___  \ V / | |_/ /
 * |  _  | | | | __/ _ \ /   \ |  __/
 * | | | | |_| | || (_) / /^\ \| |
 * \_| |_/\__,_|\__\___/\/   \/\_|
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * This software is distributed under "GNU General Public License v3.0".
 * This license allows you to use it and/or modify it but you are not at
 * all allowed to sell this plugin at any cost. If found doing so the
 * necessary action required would be taken.
 *
 * AutoXP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 * ------------------------------------------------------------------------
 */

namespace JackMD\AutoXP;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

	public function onEnable(): void {
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents(($this), $this);
		// $this->getLogger()->info("AutoXP Enabled.");
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event) {
		if ($event->isCancelled() || $this->getConfig()->get("disable-autoxp")) {
			return;
		}
		if ($this->getConfig()->get("disable-block-experience")) {
			if ($this->getConfig()->get("drop-normal-xp-for-disabled-options") == false) {
				$event->setXpDropAmount(0);
			}
		} else {
			$players = $this->getConfig()->get("blacklisted-players");
			foreach ($players as $player) {
				if ($event->getPlayer()->getName() === $player) {
					if ($this->getConfig()->get("drop-normal-xp-for-disabled-players") == false) {
						$event->setXpDropAmount(0);
					}
				} else {
					$event->getPlayer()->addXp($event->getXpDropAmount());
					$event->setXpDropAmount(0);
				}
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority HIGHEST
	 */
	public function onPlayerKill(PlayerDeathEvent $event) {
		if ($this->getConfig()->get("disable-autoxp") == true) {
			return;
		}
		$player = $event->getPlayer();
		$cause = $player->getLastDamageCause();
		if ($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if ($this->getConfig()->get("disable-player-experience")) {
				if ($this->getConfig()->get("drop-normal-xp-for-disabled-options") == false) {
					if (!$event->getKeepInventory()) {
						$player->setCurrentTotalXp(0);
					}
				}
			} else {
				$players = $this->getConfig()->get("blacklisted-players");
				foreach ($players as $blacklistedPlayer) {
					if ($damager instanceof Player) {
						if ($damager->getName() === $blacklistedPlayer) {
							if ($this->getConfig()->get("drop-normal-xp-for-disabled-players") == false) {
								if (!$event->getKeepInventory()) {
									$player->setCurrentTotalXp(0);
								}
							}
						} else {
							$damager->addXp($player->getXpDropAmount());
							$player->setCurrentTotalXp(0);
						}
					}
				}
			}
		}
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function onDeath(EntityDeathEvent $event) {
		if ($this->getConfig()->get("disable-autoxp")) {
			return;
		}
		$entity = $event->getEntity();
		$cause = $entity->getLastDamageCause();
		if ($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if ($this->getConfig()->get("disable-mob-experience")) {
				if ($this->getConfig()->get("drop-normal-xp-for-disabled-options") == false) {
					$event->setXpDropAmount(0);
				}
			} else {
				$players = $this->getConfig()->get("blacklisted-players");
				foreach ($players as $player) {
					if ($damager instanceof Player) {
						if ($damager->getName() === $player) {
							if ($this->getConfig()->get("drop-normal-xp-for-disabled-players") == false) {
								$event->setXpDropAmount(0);
							}
						} else {
							$damager->addXp($event->getXpDropAmount());
							$event->setXpDropAmount(0);
						}
					}
				}
			}
		}
	}

}
