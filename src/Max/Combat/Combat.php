<?php

declare(strict_types=1);

namespace Max\Combat;

use Max\Combat\addons\scorehud\ScoreHudListener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class Combat extends PluginBase {
    private static Combat $instance;

    /** @var Session[] */
    private array $sessions = [];

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(): void {
            $this->getConfig()->set("commands", array_map(function($commands) {
                $args = explode(" ", $commands);
                $realCommand = $this->getServer()->getCommandMap()->getCommand(strtolower($args[0]));
                if ($realCommand !== null) {
                    $args[0] = $realCommand->getName();
                }
                return strtolower(implode(" ", $args));
            }, $this->getConfig()->get("commands", [])));
        }), 1);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        if ($this->getServer()->getPluginManager()->getPlugin("ScoreHud") !== null) {
            $this->getServer()->getPluginManager()->registerEvents(new ScoreHudListener(), $this);
        }
    }

    public static function getInstance(): Combat {
        return self::$instance;
    }

    public function getSession(Player $player): Session {
        return $this->sessions[$player->getUniqueId()->getBytes()] ??= new Session();
    }

    public function removeSession(Player $player): void {
        unset($this->sessions[$player->getUniqueId()->getBytes()]);
    }

    public function getCooldown(): int {
        return $this->getConfig()->get("cooldown", 600);
    }

    /** @return String[] */
    public function getBannedCommands(): array {
        return $this->getConfig()->get("commands", []);
    }
}