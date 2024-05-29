<?php

declare(strict_types=1);

namespace Max\Combat\addons\scorehud;

use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Max\Combat\events\CombatCooldownStartEvent;
use Max\Combat\events\CombatCooldownStopEvent;
use Max\Combat\events\CombatCooldownUpdateEvent;
use pocketmine\event\Listener;

final class ScoreHudListener implements Listener {
    public function onTagResolve(TagsResolveEvent $event): void {
        $tag = $event->getTag();
        if ($tag->getName() === "combat.cooldown") {
            $tag->setValue("0");
        }
    }

    /**
     * @priority MONITOR
     */
    public function onCombatStart(CombatCooldownStartEvent $event): void {
        (new PlayerTagUpdateEvent($event->getPlayer(), new ScoreTag("combat.cooldown", (string)($event->getCooldown()/20))))->call();
    }

    /**
     * @priority MONITOR
     */
    public function onCombatUpdate(CombatCooldownUpdateEvent $event): void {
        (new PlayerTagUpdateEvent($event->getPlayer(), new ScoreTag("combat.cooldown", (string)($event->getCooldown()/20))))->call();
    }

    /**
     * @priority MONITOR
     */
    public function onPearlCooldownStop(CombatCooldownStopEvent $event): void {
        (new PlayerTagUpdateEvent($event->getPlayer(), new ScoreTag("combat.cooldown", "0")))->call();
    }
}