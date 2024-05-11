[![](https://poggit.pmmp.io/shield.state/Combat)](https://poggit.pmmp.io/p/Combat)
[![](https://poggit.pmmp.io/shield.api/Combat)](https://poggit.pmmp.io/p/Combat)
[![](https://poggit.pmmp.io/shield.dl.total/Combat)](https://poggit.pmmp.io/p/Combat)

# Combat
Combat is an open source [PocketMine-MP](https://pmmp.io/) plugin adding restrictions to player in combat.

## Features
- Configurable **Cooldown** for combat timer.
- Configurable **kill** players who try to log off while in combat.
- Configurable list of **blocked commands and subcommands** for players who are in combat.

## Known Bugs
- When someone dies while leaving the server, and then joins back, they will be in a weird glitched state where they wont be able to see players until those players for a bit. I cannot do anything about this in the plugin as it is a [pocketmine bug](https://github.com/pmmp/PocketMine-MP/issues/5385).