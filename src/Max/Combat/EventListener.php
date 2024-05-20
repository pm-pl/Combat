<?php

declare(strict_types=1);

namespace Max\Combat;

use Max\Combat\events\CombatCooldownStartEvent;
use Max\Combat\events\CombatCooldownStopEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class EventListener implements Listener {
    private Combat $plugin;

    public function __construct(Combat $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @priority MONITOR
     */
    public function onHit(EntityDamageByEntityEvent $event): void {
        $victim = $event->getEntity();
        $attacker = $event->getDamager();
        if (!$victim instanceof Player || !$attacker instanceof Player || $victim === $attacker) return;
        foreach([$victim, $attacker] as $player) {
            if ($player->isCreative()) break;
            $session = $this->plugin->getSession($player);
            $combatStartEvent = new CombatCooldownStartEvent($player, !$session->isInCombat(), $this->plugin->getCooldown());
            $combatStartEvent->call();
            if ($combatStartEvent->isCancelled()) break;
            if ($combatStartEvent->isStart()) {
                $this->plugin->getScheduler()->scheduleRepeatingTask(new CombatTask($player), 1);
                $player->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.combat-start", "combat-start")));
            }
            $session->startCombatCooldown($combatStartEvent->getCooldown());
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onCommand(CommandEvent $event): void {
        $player = $event->getSender();
        if (!$player instanceof Player || !$this->plugin->getSession($player)->isInCombat()) return;
        $args = explode(" ", preg_replace(['/"/', "/:/"], "", trim($event->getCommand())));
        $realCommand = $this->plugin->getServer()->getCommandMap()->getCommand(strtolower($args[0]));
        if ($realCommand !== null) {
            $args[0] = $realCommand->getName();
        }
        $command = strtolower(implode(" ", $args));
        foreach ($this->plugin->getBannedCommands() as $bannedCommand) {
            if (str_starts_with($command, strtolower($bannedCommand))) {
                $event->cancel();
                $player->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.banned-command", "banned-command")));
                return;
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        (new CombatCooldownStopEvent($player))->call();
        $this->plugin->getSession($player)->stopCombatCooldown();
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if ($this->plugin->getQuitKill() && $this->plugin->getSession($player)->isInCombat()) $player->kill();
        $this->plugin->removeSession($player);
    }
}