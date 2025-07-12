<?php
session_start(); 
/**
 * This example reads a recorded game file and displays a lot of data about it,
 * in plain PHP. You can use this as a reference for building your own recorded
 * game data overview.
 *
 * It's better to use a template engine of some variety, because they will
 * normally html-escape variables for you. Here, it's done manually using the
 * `htmlentities` function, but that's easy to forget! Twig is a good choice — a
 * Twig-based example is available in the examples/tabbed/ directory. You can
 * use any template engine you want with RecAnalyst, though!
 */

//require  'vendor\autoload.php';
require(__DIR__ . '/vendor/autoload.php');

use RecAnalyst\RecordedGame;
use RecAnalyst\Utils;
use Intervention\Image\ImageManagerStatic;
use RecAnalyst\Analyzers\PostgameDataAnalyzer;
use RecAnalyst\ResourcePacks\AgeOfEmpires\Civilization;

use RecAnalyst\Model\Version;


if(isset($_FILES['record']))
{
   $filename = $_FILES['record']['name'];
   $filename =fopen($_FILES["record"]["tmp_name"], 'r');
}
else
{
    $filename =  '16.mgz';

}
// Define an alias to the htmlentities function so it's easier to type.
if (!function_exists('e')) {
    function e($val)
    {
        return html_entity_decode ($val);
    }
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $host ;//. $uri
}

 
    function getResearchImage($research)
    {
        $path =  getCurrentUrl().'/resources/images/Techs/' . $research->id . '.png';
        echo $path;
        if (is_file($path)) {
            // Turn the image into a data URL.
            return $path;//ImageManagerStatic::make($path)->encode('data-url');
        }
        return '';

    }
    function getResearchImageID($id)
    {
        $path =  getCurrentUrl().'/resources/images/Techs/' .$id . '.png';
        echo $path;
        if (is_file($path)) {
            // Turn the image into a data URL.
            return $path;//ImageManagerStatic::make($path)->encode('data-url');
        }
        return '';

    }
    function getCivImage($player)
    {                       

        $p =   getCurrentUrl().'/resources/images/Civs_Emblems/'. strtolower($player->civName()).'.png';
     
        //echo $p;
        /*
          if (is_file($p)) {
                // Turn the image into a data URL.
                return  $p ;//ImageManagerStatic::make($p)->encode('data-url');
              }
              */
            return $p;
    }
    function getCivImageByName($Name)
    {                       

        $p =   getCurrentUrl().'/resources/images/Civs_Emblems/'. $Name.'.png';
     
       // echo $p;
        /*
          if (is_file($p)) {
                // Turn the image into a data URL.
                return  $p ;//ImageManagerStatic::make($p)->encode('data-url');
              }
              */
            return $p ;
    }
 
    function getUnitImage($id)
    {                       

        $p =   getCurrentUrl().'/resources/images/Buildings_DE/'. $id.'.png';
     
        echo $p;
          if (is_file($p)) { 
                return  $p  ;
              }
            return '';
    } 
    function getAgesImage($Name)
    {                       

        $p =   getCurrentUrl().'/resources/images/Ages/'. $Name.'.png';
     
        echo $p;
          if (is_file($p)) { 
                return  $p  ;
              }
            return '';
    }
    function getResImage($Name)
    {                       

        $p =   getCurrentUrl().'/resources/images/res/'. $Name.'.png';
     
        echo $p;
          if (is_file($p)) { 
                return  $p  ;
              }
            return '';
    }


$rec = new RecordedGame($filename);
 
 //$messages = $rec->runAnalyzer(new BodyAnalyzer)->chatMessages;
 


// In a real app, it's better to save the image using the ->save() method, and
// link to the stored image in your HTML page. For this example, we'll just
// inline the image as a Data URL, because it's easier.
$mapImage = $rec->mapImage()
    ->resize(350, 175)//150)
    ->encode('data-url');
 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RecAnalyst</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
    <link rel="stylesheet" href="index.css">
    <script src="index.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>
<body>

    <div class="container Page">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#general" aria-controls="general" role="tab" data-toggle="tab">
                    General
                </a>
            </li>
            <!--
            <li role="presentation">
                <a href="#achievements" aria-controls="achievements" role="tab" data-toggle="tab">
                    Achievements
                </a>
            </li>
            -->
            <li role="presentation">
                <a href="#advancing" aria-controls="advancing" role="tab" data-toggle="tab">
                    Advancing
                </a>
            </li>
            <li role="presentation">
                <a href="#chat" aria-controls="chat" role="tab" data-toggle="tab">
                    Chat
                </a>
            </li>
            <li role="presentation">
                <a href="#researches" aria-controls="researches" role="tab" data-toggle="tab">
                    Researches
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane Category active" id="general">
                <div class="General">
                    <dl class="General-info dl-horizontal">
                        <dt>Version</dt>
                        <dd><?= $rec->version()->name() ?></dd>

                        <dt>Duration</dt>
                        <dd><?= Utils::formatGameTime($rec->body()->duration) ?></dd>

                        <dt>Type</dt>
                        <dd><?= $rec->gameSettings()->gameTypeName() ?></dd>

                        <dt>Map</dt>
                        <dd><?= e($rec->gameSettings()->mapName()) ?></dd>
                       
                        <dt>PoV</dt>
                        <dd><?= $rec->pov() ? e($rec->pov()->name) : 'Unknown' ?></dd>
                       
                    </dl>
                    <div class="General-map">
                        <img src="<?= $mapImage ?>">
                    </div>
                </div>
                <h2>Teams</h2>
                <div class="Teams">
                    <?php foreach ($rec->teams() as $team) { ?>
                        <div class="Team">
                            <strong>Team <?= $team->index() ?></strong>
                            <?php foreach ($team->players() as $player) { ?>
                                <div class="Player">
                                    <img class="Player-img" src="<?= getCivImage($player) ?>">
                                    <strong class="Player-name  outlined-text" style="color: <?= Utils::lightenHexColor($player->color(),0.475) ?>">
                                        <?= e($player->name) ?>
                                    </strong>
                                
                                    <br>
                                    <span class="small"><?= e($player->civName()) ?></span>  
                                    <img>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane Category " id="achievements">
                <div class="Achievements">
                    <strong>Team </strong>
                    <?php if ($rec->achievements()) { ?>
                        <?php foreach ($rec->players() as $player) { ?>
                            <strong><?= e($player->name) ?></strong>
                            <?=  json_encode($player->achievements()) ?>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane Category hide-overflow" id="advancing">
                <div class="Advancing Teams">
                    <?php foreach ($rec->teams() as $team) { ?>
                        <div class="Advancing-team Team">
                            <strong>Team <?= $team->index() ?></strong>
                            <?php foreach ($team->players() as $player) { ?>
                                <div class="Advancing-player Player u-playerColor"
                                    style="background-color: <?= $player->color() ?>">
                                     <img class="Player-img centered-image"   src="<?= getCivImage($player) ?>">
                                    <p class="Player-name  ChatMessage clearfix chat outlined-text"  style="color:<?=Utils::lightenHexColor( $player->color(),0.475)?>" >
                                        <?= e($player->name) ?> <!--<small>(<?= e($player->civName()) ?>)</small>-->
                                    </p>
                                    <ol class="list-unstyled">
                                        <li><img class="NextAge-img" src="<?= getResearchImageID(101) ?>"> Feudal: <?= Utils::formatGameTime($player->feudalTime) ?></li>
                                        <li><img class="NextAge-img" src="<?= getResearchImageID(102) ?>"> Castle: <?= Utils::formatGameTime($player->castleTime) ?></li>
                                        <li><img class="NextAge-img" src="<?= getResearchImageID(103) ?>"> Imperial: <?= Utils::formatGameTime($player->imperialTime) ?></li>
                                    </ol>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
 
            <div role="tabpanel" class="tab-pane Category" id="chat" >
                <div class="Chat">
                     <div class="Chat-pregame">
                        <h3>Pregame</h3>
                        <?php
                            foreach ($rec->header()->pregameChat as $chat) {
                                    //printf("  %s %s\n",  $chat[1],  $chat[2]);
                                    $color = 0;   
                                    foreach($rec->players() as $player)
                                    {
                                        if(strstr($chat[1],$player->name ))//$player->name == $chat[1])
                                        {
                                            $color = $player->color();
                                        }
                                    } 
                                        echo "<div class=\"ChatMessage clearfix chat outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475)."\">";
                                        echo "<span class=\"ChatMessage-sender\" >"; 
                                            echo $chat[1];//str_replace('投降!','',$chat[1]);//$chat->player->name;
                                        echo "</span>:".$chat[2];//$chat->msg; 
                                    echo "</div>";
                            }
                        ?>
                    </div>
                     <div class="Chat-ingame" > 
                        <h3>In-game</h3>
                        <?php    
                            foreach ($rec->body()->chatMessages as $chat) {

                                    //$m =$rec->body()->chatMessages->create($chat[0], $chat[1]); 
                                    $color = 0;   
                                    foreach($rec->players() as $player)
                                    {
                                        if(strstr($chat[1],$player->name ))//$player->name == $chat[1])
                                        {
                                            $color = $player->color();
                                        }
                                    } 
                                    echo "<div class=\"ChatMessage clearfix chat outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475)."\">";//background-
                                        echo "<span class=\"ChatMessage-time\"> ";
                                            echo  $chat[0];//Utils::formatGameTime($chat->time); 
                                        echo " </span>";
                                        echo "<span class=\"ChatMessage-sender\" >"; 
                                            echo $chat[1];//str_replace('投降!','',$chat[1]);//$chat->player->name;
                                        echo "</span>:".$chat[2];//$chat->msg; 
                                    echo "</div>";
                            } 
                        ?> 
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane Category" id="researches">
                <div class="Researches">
                    <?php foreach ($rec->players() as $player) { ?>
                        <div class="Researches-line clearfix ResearchesLine u-playerColor"
                            style="background-color: <?= $player->color() ?>">
                            <div class="ResearchesLine-player ChatMessage clearfix chat outlined-text"  style="color:<?=Utils::lightenHexColor( $player->color(),0.475)?>"> 
                            <img class="Player-Civ-research-img" src="<?= getCivImage($player) ?>"> <?= e(' '.$player->name) ?>
                            </div>
                            <div class="ResearchesLine-researches">
                                <?php foreach ($player->researches() as $research) { ?>
                                    <div class="Research">
                                        
                                        <img class="Research-img" src="<?= getResearchImage($research) ?>">
                                        <div class="Research-time"><?= Utils::formatGameTime($research->time) ?></div>
                                        <!--<div class="Research-name"></div>--><?= e('')/*e($research->name())*/ ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div id="ScoreTable" class="container Page">
        <table class="table" <?php if($rec->version()->version < version::VERSION_USERPATCH15) echo "style=\"visibility: hidden;\"" ;?>> 
          <thead>
            <tr>
              <th scope="col">Overall Score</th>
              <th scope="col">Military Score</th>
              <th scope="col">Economy Score</th>
              <th scope="col">Technology Score</th>
              <th scope="col">Society Score</th>
              <th scope="col">Total Score</th>
            </tr>
          </thead>
          <tbody>
                <?php  
                    if($rec->version()->version >= version::VERSION_USERPATCH15)
                    { 
                        $PostgameDataAnalyzer =$rec->body()->postGameData;
                        $i=0;
                        usort($PostgameDataAnalyzer->players , function($a, $b) {
                            return strcmp($a->team, $b->team);
                        });

                        foreach($PostgameDataAnalyzer->players as $player )
                        { 
                            //$res = $rec->getResourcePack(); 
                            //$color =  $res->getPlayerColor($player->colorId);//idk why no working 
                            $color = 0;   
                            foreach($rec->players() as $p)
                            {
                                if(strstr($player->name,$p->name ))//$player->name == $chat[1])
                                {
                                    $color = $p->color();
                                }
                            }  
                            echo "<tr>";
                            echo "  <td class =\"outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475) ."\">" 
                            ."<img class=\"Player-Civ-Tab-img\" src=\"".getCivImageByName(strtolower(Civilization::$CIV_NAMES[$player->civId])) ."\"> "
                            . $player->name ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($player->militaryStats->score , 0, ',', ' ')    ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->score  , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($player->techStats->score     , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text noMax\">". number_format($player->societyStats->score  , 0, ',', ' ')    ."</td>" ; 
                            $totalScore = 0;
                            $totalScore = $player->militaryStats->score + $player->economyStats->score + $player->techStats->score + $player->societyStats->score;
                            echo "  <td class =\"tab-text\">".number_format($totalScore, 0, ',', ' ')  ."</td>" ; 
                            echo "</tr>";
                            $i++;
                        } 
                    }
                ?>
        
          </tbody>
        </table> 

        <table id="MilitaryTable" class="table" <?php if($rec->version()->version < version::VERSION_USERPATCH15) echo "style=\"visibility: hidden;\"" ;?>> 
          <thead>
            <tr>
              <th scope="col">Military Score</th>
              <th scope="col">Units Killed</th>
              <th scope="col">Units Lost</th>
              <th scope="col">Buildings Razed</th>
              <th scope="col">Buildings Lost</th>
              <th scope="col">Units Converted</th>
            </tr>
          </thead>
          <tbody>
                <?php 

                    if($rec->version()->version >= version::VERSION_USERPATCH15)
                    { 
                        $PostgameDataAnalyzer =$rec->body()->postGameData;
                        foreach($PostgameDataAnalyzer->players as $player )
                        { 
                            $color = 0;   
                            foreach($rec->players() as $p)
                            {
                                if(strstr($player->name,$p->name ))//$player->name == $chat[1])
                                {
                                    $color = $p->color();
                                }
                            } 
                            echo "<tr>";
                            echo "  <td class =\"outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475) ."\">"
                            ."<img class=\"Player-Civ-Tab-img\" src=\"".getCivImageByName(strtolower(Civilization::$CIV_NAMES[$player->civId])) ."\"> "
                            . $player->name ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($player->militaryStats->unitsKilled , 0, ',', ' ')    ."</td>" ;
                            echo "  <td class =\"tab-text minVal\">". number_format($player->militaryStats->unitsLost  , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($player->militaryStats->buildingsRazed     , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text noMax\">". number_format($player->militaryStats->buildingsLost  , 0, ',', ' ')    ."</td>" ;  
                            echo "  <td class =\"tab-text\">". number_format($player->militaryStats->unitsConverted, 0, ',', ' ')  ."</td>" ; 
                            echo "</tr>";
                        }
                    }
                ?>
        
          </tbody>
        </table> 

        <table id="EconomyTable" class="table" <?php if($rec->version()->version < version::VERSION_USERPATCH15) echo "style=\"visibility: hidden;\"" ;?>> 
          <thead>
            <tr>
              <th scope="col">Economy Score</th>
              <th scope="col"><img class =  "img-cent" src="<?= getResImage('food') ?>"></th>
              <th scope="col"><img src="<?= getResImage('wood') ?>"></th>
              <th scope="col"><img src="<?= getResImage('stone') ?>"></th>
              <th scope="col"><img src="<?= getResImage('gold') ?>"></th>
              <th scope="col"><img  class="Tab-img" src="<?= getResImage('trade') ?>"></th> 
              <th scope="col">Tribute Received</th>
              <th scope="col">Tribute Sent</th>
            </tr>
          </thead>
          <tbody>
                <?php 

                    if($rec->version()->version >= version::VERSION_USERPATCH15)
                    { 
                        $PostgameDataAnalyzer =$rec->body()->postGameData;
                        foreach($PostgameDataAnalyzer->players as $player )
                        { 
                            $color = 0;   
                            foreach($rec->players() as $p)
                            {
                                if(strstr($player->name,$p->name ))//$player->name == $chat[1])
                                {
                                    $color = $p->color(); 
                                }
                            } 

                            echo "<tr>";
                            echo "  <td class =\"outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475) ."\">"
                            ."<img class=\"Player-Civ-Tab-img\" src=\"".getCivImageByName(strtolower(Civilization::$CIV_NAMES[$player->civId])) ."\"> "
                            . $player->name ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->foodCollected , 0, ',', ' ')    ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->woodCollected  , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->stoneCollected     , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->goldCollected  , 0, ',', ' ')    ."</td>" ;  
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->tradeProfit  , 0, ',', ' ')    ."</td>" ;   
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->tributeReceived, 0, ',', ' ')  ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->tributeSent, 0, ',', ' ')  ."</td>" ; 
                            echo "</tr>";


                        }
                    }
                ?>
        
          </tbody>
        </table> 

        <table id="TechnologyTable"  class="table" <?php if($rec->version()->version < version::VERSION_USERPATCH15) echo "style=\"visibility: hidden;\"" ;?>> 
          <thead>
            <tr>
              <th scope="col">Technology Score</th>
              <th scope="col"><img class="Tab-img" src="<?= getAgesImage('feudal_age_de') ?>"></th>
              <th scope="col"><img class="Tab-img" src="<?= getAgesImage('castle_age_de') ?>"></th>
              <th scope="col"><img class="Tab-img" src="<?= getAgesImage('imperial_age_de') ?>"></th>
              <th scope="col">Map Explored</th>
              <th scope="col">Research Count</th>
              <th scope="col">Research Percent</th> 
            </tr>
          </thead>
          <tbody>
                <?php 

                    if($rec->version()->version >= version::VERSION_USERPATCH15)
                    { 
                        $PostgameDataAnalyzer =$rec->body()->postGameData;
                        foreach($PostgameDataAnalyzer->players as $player )
                        { 
                            $color = 0;   
                            foreach($rec->players() as $p)
                            {
                                if(strstr($player->name,$p->name ))//$player->name == $chat[1])
                                {
                                    $color = $p->color();
                                }
                            } 
                            echo "<tr>";
                            echo "  <td class =\"outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475) ."\">"
                            ."<img class=\"Player-Civ-Tab-img\" src=\"".getCivImageByName(strtolower(Civilization::$CIV_NAMES[$player->civId])) ."\"> "
                            . $player->name ."</td>" ;
                            echo "  <td class =\"tab-text minVal\">". Utils::formatGameTime($player->techStats->feudalTime,1)   ."</td>" ;
                            echo "  <td class =\"tab-text minVal\">". Utils::formatGameTime($player->techStats->castleTime,1)   ."</td>" ; 
                            echo "  <td class =\"tab-text minVal\">". Utils::formatGameTime($player->techStats->imperialTime,1)   ."</td>" ; 
                            echo "  <td class =\"tab-text\">". $player->techStats->mapExploration  ."%</td>" ;  
                            echo "  <td class =\"tab-text\">". $player->techStats->researchCount   ."</td>" ;   
                            echo "  <td class =\"tab-text noMax\">". $player->techStats->researchPercent ."%</td>" ;  
                            echo "</tr>";

                        }
                    }
                ?>
        
          </tbody>
        </table> 

             <table id="SocietyTable" class="table" <?php if($rec->version()->version < version::VERSION_USERPATCH15) echo "style=\"visibility: hidden;\"" ;?> > 
              <thead>
                <tr>
                  <th scope="col">Society Score</th>
                  <th scope="col"><img class="Tab-img tab-text" src="<?= getUnitImage(276) ?>"></th>
                  <th scope="col"><img class="Tab-img tab-text" src="<?= getUnitImage(82) ?>"></th>
                  <th scope="col"><img class="Tab-img tab-text" src="<?= getUnitImage(285) ?>"></th>
                  <th scope="col">Relic Gold</th>
                  <th scope="col">Villager High</th> 
                </tr>
              </thead>
              <tbody>
                    <?php  
                        if($rec->version()->version >= version::VERSION_USERPATCH15)
                        { 
                            $PostgameDataAnalyzer =$rec->body()->postGameData; 
                            foreach($PostgameDataAnalyzer->players as $player )
                            { 
                                $color = 0;   
                                foreach($rec->players() as $p)
                                {
                                    if(strstr($player->name,$p->name ))//$player->name == $chat[1])
                                    {
                                        $color = $p->color();
                                    }
                                }  
                                echo "<tr>";
                                echo "  <td class =\"outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475) ."\">"
                                ."<img class=\"Player-Civ-Tab-img\" src=\"".getCivImageByName(strtolower(Civilization::$CIV_NAMES[$player->civId])) ."\"> "
                                . $player->name ."</td>" ;
                                echo "  <td class =\"tab-text\">". $player->societyStats->totalWonders   ."</td>" ;
                                echo "  <td class =\"tab-text noMax\">". $player->societyStats->totalCastles   ."</td>" ; 
                                echo "  <td class =\"tab-text\">". $player->societyStats->relicsCaptured ."</td>" ; 
                                echo "  <td class =\"tab-text\">". $player->economyStats->relicGold       ."</td>" ; 
                                echo "  <td class =\"tab-text\">". $player->societyStats->villagerHigh   ."</td>" ;    
                                echo "</tr>";
                            }
                        }
                    ?>
            
              </tbody>
            </table>  
    </div>

<!--
         <table id="EconomyExchangeTable" class="table" <?php if($rec->version()->version < version::VERSION_USERPATCH14) echo "style=\"visibility: hidden;\"" ;?>> 
          <thead>
            <tr>
              <th scope="col">Economy exchange</th>
              <th scope="col">Send Food</th>
              <th scope="col">Send Wood</th>
              <th scope="col">Send Stone</th>
              <th scope="col">Send Gold</th> 
              <th scope="col">Receive Food</th>
              <th scope="col">Receive Wood</th>
              <th scope="col">Receive Stone</th>
              <th scope="col">Receive Gold</th> 
              <th scope="col">Tribute Received</th>
              <th scope="col">Tribute Sent</th>
            </tr>
          </thead>
          <tbody>
        -->
                <?php 
/*
                    if($rec->version()->version >= version::VERSION_USERPATCH14)
                    { 
                        $PostgameDataAnalyzer =$rec->body()->postGameData;
                        foreach($PostgameDataAnalyzer->players as $player )
                        { 
                            $color = 0;   
                            $a_player = 0;   
                            foreach($rec->players() as $p)
                            {
                                if(strstr($player->name,$p->name ))//$player->name == $chat[1])
                                {
                                    $color = $p->color(); 
                                    $a_player = $p;
                                }
                            } 
                            //no work  
                            echo "<tr>";
                            echo "  <td class =\"outlined-text\" style=\"color:". Utils::lightenHexColor($color,0.475) ."\">". $player->name ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[344] , 0, ',', ' ')    ."</td>" ;
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[343] , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[346] , 0, ',', ' ')    ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[345] , 0, ',', ' ')    ."</td>" ;  
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[348] , 0, ',', ' ')    ."</td>" ;   
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[347] , 0, ',', ' ')  ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[350] , 0, ',', ' ')  ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($a_player->Resources[349] , 0, ',', ' ')  ."</td>" ; 

                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->tributeReceived, 0, ',', ' ')  ."</td>" ; 
                            echo "  <td class =\"tab-text\">". number_format($player->economyStats->tributeSent, 0, ',', ' ')  ."</td>" ; 
                            echo "</tr>";


                        }
                    }
                    */
                ?>
<!--        
          </tbody>
        </table> 
-->

        <?php 
/*
            foreach($rec->players() as $player)
            {
                $i = 0;
                foreach($player->Resources as $Resource)
                {
                    echo $player->name . ': ressource'. $i . ':' . $Resource . ' <br>' ;
                    $i++;
                }
            } 
*/
        ?>


    
    <div class="container">






      <form action = "" method = "POST" enctype = "multipart/form-data">
         <input type = "file" name = "record" />
         <input type = "submit"/>
            
 
            
      </form>
    </div>
<a href="mgx format.html"  target="_blank">Mgx format</a><br>
<a href="mgx_english_0.7.xls"  target="_blank">Mgx format xls</a>



</body>
</html>