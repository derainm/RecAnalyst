<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\ChatMessage;
use RecAnalyst\Model\Tribute;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Civilization;
use RecAnalyst\ResourcePacks\AgeOfEmpires\TechUnit;
use RecAnalyst\Utils;

/**
 * Analyzer for most things in a recorded game file body.
 */
class BodyAnalyzer extends Analyzer
{
    /**
     * Operation ID of in-game commands.
     *
     * @var int
     */
    const OP_COMMAND = 0x01;
    /**
     * Operation ID of sync packets.
     *
     * @var int
     */
    const OP_SYNC = 0x02;
    /**
     * Operation ID of view update packets for the recorded game's owner.
     *
     * @var int
     */
    const OP_VIEWLOCK = 0x03;
    /**
     * Operation ID for chat messages, and the game start message in AoC and newer.
     *
     * @var int
     */
    const OP_CHAT = 0x04;

    /**
     * Resignation command ID.
     *
     * @var int
     */
    const COMMAND_RESIGN = 0x0B;
    /**
     * Research command ID.
     *
     * @var int
     */
    const COMMAND_RESEARCH = 0x65;
    /**
     * Unit training command ID.
     *
     * @var int
     */
    const COMMAND_TRAIN = 0x77;
    /**
     * Unit training command ID (used by AIs).
     *
     * @var int
     */
    const COMMAND_TRAIN_SINGLE = 0x64;
    /**
     * Building command ID.
     *
     * @var int
     */
    const COMMAND_BUILD = 0x66;
    /**
     * Tribute command ID.
     *
     * @var int
     */
    const COMMAND_TRIBUTE = 0x6C;
    /**
     * UserPatch post-game lobby data command ID.
     *
     * @var int
     */
    const COMMAND_POSTGAME = 0xFF;

    /**
     * Feudal age research ID.
     *
     * @var int
     */
    const RESEARCH_FEUDAL = 101;
    /**
     * Castle age research ID.
     *
     * @var int
     */
    const RESEARCH_CASTLE = 102;
    /**
     * Imperial age research ID.
     *
     * @var int
     */
    const RESEARCH_IMPERIAL = 103;

    /**
     * Game version information.
     *
     * @var object
     */
    private $version = null;

    /**
     * Current game time in ms.
     *
     * @var int
     */
    private $currentTime = 0;

    /**
     * Tributes sent during the game.
     *
     * @var \RecAnalyst\Model\Tribute[]
     */
    private $tributes = [];

    /**
     * Chat messages sent during the game.
     *
     * @var \RecAnalyst\Model\ChatMessage[]
     */
    private $chatMessages = [];

    /**
     * Amount of units of each type built during the game.
     *
     * @var array
     */
    private $units = [];

    /**
     * Amount of buildings of each type built during the game.
     *
     * @var array
     */
    private $buildings = [];

    /**
     * Post-game data, such as achievements.
     *
     * @var object|null
     */
    private $postGameData = null;

    /**
     * How often checksums are included in a sync message. Each nth message will contain checksums.
     *
     * @var int
     */
    private $syncChecksumInterval = 500;

    /**
     * Current sync message index. When this reaches $syncChecksumInterval, the message contains checksums,
     * and the counter is reset.
     *
     * @var int
     */
    private $syncIndex = 0;

    /**
     * Whether the actions contain sequence numbers. Enabled when the LC_SEQUENCE flag was used to start the game.
     *
     * @var bool
     */
    private $containsSequenceNumbers = false;

    /**
     * How many chapters the game contains.
     *
     * @var int
     */
    private $numberOfChapters = 0;

    /**
     * Run the analysis.
     *
     * @return object
     */
    protected function run()
    {
        $pack = $this->rec->getResourcePack();
        $this->version = $this->get(VersionAnalyzer::class);
        $version = $this->version;

        $players = $this->get(PlayerMetaAnalyzer::class);

        // The player index is used for game actions.
        $playersByIndex = [];
        foreach ($players as $player) {
            $playersByNumber[$player->number] = $player;
            $playersByIndex[$player->index] = $player;
        }

        $this->playersByNumber = $playersByNumber;

        $size = strlen($this->body);

        $this->position = 0;
        if ($version->isMgl) {
            $this->processGameStart();
        }

        while ($this->position < $size - 3) {
            $operationType = $this->readBody('l', 4);

            if ($operationType === self::OP_CHAT) {
                $version->version = $version->version === 5 ? 8 : $version->version;
                // AoC and up store the game start message with the same ID
                // as a CHAT message, but chat messages are prefixed with 0xFFFF_FFFF
                $syncChecksumInterval = $this->readBody('l', 4);
                if ($syncChecksumInterval === -1) {
                    $this->processChatMessage();
                } else {
                    $this->position -= 4;
                    $this->processGameStart();
                }
            } else if ($operationType === self::OP_SYNC) {
                $this->syncIndex++;
                // There are a lot of sync packets, so we get a significant
                // speedup just from doing this inline (and not in a separate
                // method), and by using `unpack` and manual position increments
                // instead of `readBody`.
                $data = unpack('l2', substr($this->body, $this->position, 8));
                $this->currentTime += $data[1]; // $this->readBody('l', 4);
                if ($this->syncIndex === $this->syncChecksumInterval) {
                    // no need to parse checksums
                    $this->position += 28;
                }
            } else if ($operationType === self::OP_VIEWLOCK) {
                $version->version = $version->version === 5 ? 7 : $version->version;
                // just skip for now
                $this->position += 12;
            } else if ($operationType === self::OP_COMMAND) {
                $length = $this->readBody('l', 4);
                $next = $this->position + $length;
                $command = ord($this->body[$this->position]);
                $this->position++;

                switch ($command) {
                    // player resign
                    case self::COMMAND_RESIGN:
                        $playerIndex = ord($this->body[$this->position]);
                        $playerNumber = ord($this->body[$this->position + 1]);
                        $disconnected = ord($this->body[$this->position + 2]);
                        $this->position += 3;
                        $player = $playersByIndex[$playerIndex];
                        if ($player && $player->resignTime === 0) {
                            $player->resignTime = $this->currentTime;
                            if ($this->version->isAoe2Record) {
                                $message = sprintf('%s 投降!', $player->name);
                                $convertToUTF8 = false;
                            } else {
                                $message = mb_convert_encoding(sprintf('%s 投降!', $player->name), 'gbk', 'UTF-8');
                                $convertToUTF8 = true;
                            }
                            $this->chatMessages[] = new ChatMessage($this->currentTime, $message, '', '', $convertToUTF8);
                        } 
                        break;
                    // researches
                    case self::COMMAND_RESEARCH:
                        $this->position += 3;
                        $buildingId = $this->readBody('l', 4);
                        $playerId = $this->readBody('v', 2);
                        $researchId = $this->readBody('v', 2);
                        $player = $playersByIndex[$playerId];
                        if (!$player) {
                            break;
                        }

                        switch ($researchId) {
                            case self::RESEARCH_FEUDAL:
                                $researchDuration = 130000;
                                $player->feudalTime = $this->currentTime + $researchDuration;
                                break;
                            case self::RESEARCH_CASTLE:
                                // persians have faster research time
                                $researchDuration = 160000;
                                if ($player->civId === Civilization::PERSIANS) {
                                    $researchDuration /= 1.10;
                                }
                                $player->castleTime = $this->currentTime + round($researchDuration);
                                break;
                            case self::RESEARCH_IMPERIAL:
                                $researchDuration = 190000;
                                if ($player->civId === Civilization::PERSIANS) {
                                    $researchDuration /= 1.15;
                                }
                                $player->imperialTime = $this->currentTime + round($researchDuration);
                                break;
                        }
                        $player->addResearch($researchId, $this->currentTime);
                        break;
                    // training unit
                    case self::COMMAND_TRAIN:
                        $this->position += 3;
                        $buildingId = $this->readBody('l', 4);
                        $unitType = $this->readBody('v', 2);
                        $amount = $this->readBody('v', 2);



                        if (!isset($this->units[$unitType])) {
                            $this->units[$unitType] = $amount;
                        } else {
                            $this->units[$unitType] += $amount; 
                            $Unit_Id= $this->units[$unitType]; 
                            if($player->numResources> 250)
                            {
                                if (isset(TechUnit::$Tech_Units[$Unit_Id])) {
                                    if( $Unit_Id != 42 && $Unit_Id !=331 //trebuchet
                                    )
                                    {
                                        //echo " unit id :" .$Unit_Id . '<br>';
                                        if(TechUnit::$Tech_Units[ $Unit_Id ]>0)
                                        {
                                            $player->addResearch(TechUnit::$Tech_Units[ $Unit_Id ], $this->currentTime); 
                                        }
                                    }
                                } 
                            } 
                        }
                        break;
                    // AI trains unit
                    case self::COMMAND_TRAIN_SINGLE:
                        $this->position += 9;
                        $unitType = $this->readBody('v', 2);
                        if (!isset($this->units[$unitType])) {
                            $this->units[$unitType] = 1;
                        } else {
                            $this->units[$unitType] += 1; 
                            $Unit_Id= $this->units[$unitType]; 
                            if($player->numResources> 250)
                            {
                                if (isset(TechUnit::$Tech_Units[$Unit_Id])) {
                                    if( $Unit_Id != 42 && $Unit_Id !=331 //trebuchet
                                    )
                                    {
                                        //echo " unit id :" .$Unit_Id . '<br>';
                                        //echo " unit id :" .$Unit_Id . '<br>';
                                        if(TechUnit::$Tech_Units[ $Unit_Id ]>0)
                                        {
                                            $player->addResearch(TechUnit::$Tech_Units[ $Unit_Id ], $this->currentTime); 
                                        }
                                    }
                                } 
                            } 
                         }
                        break;
                    // building
                    case self::COMMAND_BUILD:
                        $this->position += 1;
                        $playerId = $this->readBody('v', 2);
                        $this->position += 8;
                        $buildingType = $this->readBody('v', 2);

                        $buildingType = $pack->normalizeUnit($buildingType);

                        if (!isset($this->buildings[$playerId][$buildingType])) {
                            $this->buildings[$playerId][$buildingType] = 1;
                        } else {
                            $this->buildings[$playerId][$buildingType]++;
                        }
                        break;
                    // tributing
                    case self::COMMAND_TRIBUTE:
                        $playerIdFrom = ord($this->body[$this->position++]);
                        $playerIdTo = ord($this->body[$this->position++]);
                        $resourceId = ord($this->body[$this->position++]);

                        $playerFrom = $playersByIndex[$playerIdFrom];
                        //Bug. See 游戏录象 -  02-七月-2014 21`53`00 from 飞云
                        $playerTo = isset($playersByIndex[$playerIdTo]) ? $playersByIndex[$playerIdTo] : 0;

                        if ($playerFrom && $playerTo) {
                            $amount = $this->readBody('f', 4);
                            $marketFee = $this->readBody('f', 4);

                            $tribute = new Tribute();
                            $tribute->time = $this->currentTime;
                            $tribute->playerFrom = $playerFrom;
                            $tribute->playerTo = $playerTo;
                            $tribute->resourceId = $resourceId;
                            $tribute->amount = floor($amount);
                            $tribute->fee = $marketFee;
                            $this->tributes[] = $tribute;
                        } else {
                            $this->position += 8;
                        }
                        break;
                    // multiplayer postgame data in UP1.4 RC2+
                    case self::COMMAND_POSTGAME:
                        $this->postGameData = $this->read(PostgameDataAnalyzer::class);
                        break;
                    default:
                        break;
                }

                $this->position = $next;
            }
        }

        if (!empty($this->chatMessages)) {
            usort($this->chatMessages, function ($a, $b) {
                return $a->time - $b->time;
            });
        }

        if (!empty($this->buildings)) {
            ksort($this->buildings);
        }

        $analysis = new \StdClass;
        $analysis->duration = $this->currentTime;
        $analysis->tributes = $this->tributes;
        $analysis->chatMessages = Utils::msgToArray($this->chatMessages);
        $analysis->units = $this->units;
        $analysis->buildings = $this->buildings;
        $analysis->postGameData = $this->postGameData;

        return $analysis;
    }

    /**
     * Process the game start data.
     */
    private function processGameStart()
    {
        $this->syncChecksumInterval = $this->readBody('l', 4);
        if ($this->version->isMgl) {
            // not sure what difference is vs mgx and up
            $this->position += 28;
            $ver = ord($this->body[$this->position]);
            $this->position += 4;
        } else {
            // These are also stored in the header.
            $isMultiplayer = $this->readBody('l', 4);
            $pov = $this->readBody('l', 4);
            $revealMap = $this->readBody('l', 4);
            $this->containsSequenceNumbers = $this->readBody('l', 4);
            $this->numberOfChapters = $this->readBody('l', 4);
        }
    }

    /**
     * Read a chat message.
     */
    private function processChatMessage()
    {
        $length = $this->readBody('l', 4);
        if ($length <= 0) {
            return;
        }
        $chat = $this->readBodyRaw($length);

        // Chat messages are stored as "@#%dPlayerName: Message", where %d is a
        // digit from 1 to 8 indicating player's index (or colour).
        if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
            $chat = rtrim($chat); // Remove null terminator
            if (substr($chat, 3, 2) == '--' && substr($chat, -2) == '--') {
                // Skip messages like "--Warning: You are under attack... --"
                return;
            }
            if ($this->version->isAoe2Record) {
                $convertToUTF8 = false;
            } else {
                $convertToUTF8 = true;
            }
            $this->chatMessages[] = ChatMessage::create(
                $this->currentTime,
                substr($chat, 3),
                $convertToUTF8
            );
        }
    }
}
