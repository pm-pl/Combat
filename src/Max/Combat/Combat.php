<?php

declare(strict_types=1);

namespace Max\Combat;

use Max\Combat\addons\scorehud\ScoreHudListener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use WeakMap;

class Combat extends PluginBase {
    private static Combat $instance;

    private Config $config;

    /**
     * @var WeakMap<Player, Session>
     */
    private WeakMap $sessions;

    private int $cooldown;
    private bool $quitKill;

    /** @var string[]  */
    private array $bannedCommands;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->sessions = new WeakMap();

        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->cooldown = is_int($cooldown = $this->config->get("cooldown", 600)) ? $cooldown : 600;
        $this->quitKill = is_bool($quitKill = $this->config->get("quit-kill", true)) ? $quitKill : true;
        $this->bannedCommands = is_array($commands = $this->config->get("commands", [])) ?
            array_filter($commands, function($command): bool {
                return is_string($command);
            }) : [];

        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(): void {
            $this->bannedCommands = array_map(function($commands) {
                $args = explode(" ", strtolower($commands));
                $realCommand = $this->getServer()->getCommandMap()->getCommand($args[0]);
                if ($realCommand !== null) {
                    $args[0] = strtolower($realCommand->getName());
                }
                return implode(" ", $args);
            }, $this->bannedCommands);
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
        return $this->sessions[$player] ??= new Session();
    }

    public function getMessage(string $message): string {
        return TextFormat::colorize($this->config->getNested("messages." . $message, $message));
    }

    public function getCooldown(): int {
        return $this->cooldown;
    }

    public function getQuitKill(): bool {
        return $this->quitKill;
    }

    /** @return string[] */
    public function getBannedCommands(): array {
        return $this->bannedCommands;
    }
}