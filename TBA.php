<?php
require 'functions.php';

function readTBA($url) {
  global $TBAAuthKey;
  $base = "www.thebluealliance.com/api/v3";
  $fullurl = $base . $url;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $fullurl);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-TBA-Auth-Key: '.$TBAAuthKey
    ));

  $result = curl_exec($ch);
  curl_close($ch);

  return json_decode($result,true);
}

function getTeamEvents($team) {
  $teamkey = "frc".$team;
  $url = '/team/'.$teamkey.'/events/'.getDefaultYear().'/simple';
  return readTBA($url);
}

function getTeamEventStatus($team,$eventkey) {
  $teamkey = "frc".$team;
  $url = "/team/".$teamkey."/event/".$eventkey."/status";
  return readTBA($url);
}

function getTeamEventMatches($team,$eventkey) {
  $teamkey = "frc".$team;
  $url = "/team/".$teamkey."/event/".$eventkey."/matches/simple";
  return readTBA($url);
}

function getTeamInfo($team) {
  $teamkey = "frc".$team;
  $url = "/team/".$teamkey."/simple";
  return readTBA($url);
}

function getAccordion($title,$content,$open=false) {
  if(!$open) {
    return '<div class="accordion">
      <button class="accordionbutton">'.$title.'</button>
      <div class="accordioncontent">'.$content.'</div>
      </div>';
  } else {
    return '<div class="accordion">
      <button class="accordionbutton">'.$title.'</button>
      <div class="accordioncontent show">'.$content.'</div>
      </div>';
  }
}

function switchLevelS($level) {
  switch($level) {
    case 'qm':
      return "Qualification Match";
    case 'ef':
      return "ef";
    case 'qf':
      return "Quarterfinal";
    case 'sf':
      return "Semifinal";
    case 'f':
      return "Final";
  }
}
function switchLevel($level){
  switch($level) {
    case 'qm':
      return "Qualification Matches";
    case 'ef':
      return "ef";
    case 'qf':
      return "Quarterfinals";
    case 'sf':
      return "Semifinals";
    case 'f':
      return "Finals";
  }
}

/**
 * @param Array $alliances An array of alliances formatted like so: array[2]=>("red"=>array[3](1,2,3),"blue"=>array[3](4,5,6))
*/
function getAllianceTable($alliances,$target,$winner) {
  $out = "";
  $out .= '<table>';
  if($winner == "red") {
    $out .= '<tr class="redalliance winnerrow">';
  } else {
    $out .= '<tr class="redalliance">';
  }
  foreach($alliances["red"] as $team) {
    if($team == $target) {
      $out .= '<td class="target"><a href="info.php?team='.$team.'">'.$team.'</a></td>';
    } else {
      $out .= '<td><a href="info.php?team='.$team.'">'.$team.'</a></td>';
    }
  }
  $out .= '</tr>';
  if($winner == "blue") {
    $out .= '<tr class="bluealliance winnerrow">';
  } else {
    $out .= '<tr class="bluealliance">';
  }
  foreach($alliances["blue"] as $team) {
    if($team == $target) {
      $out .= '<td class="target"><a href="info.php?team='.$team.'">'.$team.'</a></td>';
    } else {
      $out .= '<td><a href="info.php?team='.$team.'">'.$team.'</a></td>';
    }
  }
  $out .= '</tr>';
  $out .= '</table>';
  # winnerrow is bold, target is underlined
  return $out;
}

function getScoreTable($scores, $targetcolor, $winner) {
  $out = "";
  $out .= '<table>';

  $red = "redalliance";
  $blue = "bluealliance";

  if($winner == "red") {
    $red .= " winnerrow";
  } elseif($winner == "blue") {
    $blue .= " winnerrow";
  }

  if($targetcolor == "red") {
    $red .= " target";
  } elseif($targetcolor == "blue") {
    $blue .= " target";
  }

  $out .= '<tr class="'.$red.'"><td>'.$scores["red"].'</tr></td>';
  $out .= '<tr class="'.$blue.'"><td>'.$scores["blue"].'</tr></td>';

  $out .= '</table>';
  return $out;
}

function getMatchTable($matchtype, $matches, $team) {
  $out = "";
  $out .= '<table><tr><th>Match</th><th>Time</th><th>Alliances</th><th>Scores</th></tr>';
  $smatches = array();
  foreach($matches as $idx=>$match) {
    $smatches[$match["match_number"]] = $match;
  }
  ksort($smatches);
  foreach($smatches as $matchnum=>$match) {
    $out .= '<tr>';
    # Match name
    $out .= '<td>'.switchLevelS($matchtype).' #'.$match["match_number"].'</td>';

    # Match Time
    if(!is_null($match["actual_time"])) {
      $time = new DateTime('@'.$match["actual_time"]);
    } elseif (!is_null($match["predicted_time"])) {
      $time = new DateTime('@'.$match["predicted_time"]);
    } elseif (!is_null($match["time"])) {
      $time = new DateTime('@'.$match["time"]);
    } else {
      $time = false;
    }
    if($time !== false) {
      $time->setTimezone(new DateTimeZone("America/Los_Angeles"));
      $out .= '<td>'.$time->format("l g:ia").'</td>';
    } else {
      $out .= '<td>&mdash;</td>';
    }

    # Alliance table
    $alliances = array("red"=>array(),"blue"=>array());
    foreach($match["alliances"]["red"]["team_keys"] as $key) {
      $alliances["red"][] = substr($key,3);
    }
    foreach($match["alliances"]["blue"]["team_keys"] as $key) {
      $alliances["blue"][] = substr($key,3);
    }
    $out .= '<td>'.getAllianceTable($alliances,$team,$match["winning_alliance"]).'</td>';

    # Scores
    if(in_array($team, $alliances["red"])) {
      $targetcolor = "red";
    } elseif(in_array($team, $alliances["blue"])) {
      $targetcolor = "blue";
    } else {
      $targetcolor = null;
    }

    $scores = array("red"=>$match["alliances"]["red"]["score"],"blue"=>$match["alliances"]["blue"]["score"]);

    $out .= '<td>'.getScoreTable($scores,$targetcolor,$match["winning_alliance"]).'</td>';

    $out .= '</tr>';
  }
  $out .= '</table>';
  return $out;
}

# Actual data

if(!isset($_GET["team"])) {
  exit("<p>ERROR: No team set!</p>");
}
$team = $_GET["team"];

echo('<p class="label">Team Info</p>');
$info = getTeamInfo($team);
echo("<p>Name: ".$info["nickname"]."</p>");
if($info["country"] == "USA") {
  echo("<p>Location: ".$info["city"].", ".$info["state_prov"]."</p>");
} else {
  echo("<p>Location: ".$info["city"].", ".$info["country"]."</p>");
}



echo('<p class="label">Events</p>');
$events = getTeamEvents($team);
if(empty($events)) {
  echo("<p>".$team." participated in no events in ".getDefaultYear()."</p>");
}
foreach($events as $event) {
  $status = getTeamEventStatus($team,$event["key"]);
  $content = "";
  if(!is_null($status)) {
    if(!is_null($status["playoff"])) {
      $record = $status["playoff"]["record"];
      $content .= '<p class="section"><span class="label">Playoffs:</span> <span class="record">'.$record["wins"].'-'.$record["losses"].'-'.$record["ties"].'</span> '.switchLevel($status["playoff"]["level"]).'</p>';
    }
    $record = $status["qual"]["ranking"]["record"];
    $content .= '<p class="section"><span class="label">Qualification matches:</span> <span class="record">'.$record["wins"].'-'.$record["losses"].'-'.$record["ties"].'</span> '.$status["qual"]["ranking"]["rank"].'/'.$status["qual"]["num_teams"].'</p>';
  }
  $now = new DateTime();
  $start = DateTime::createFromFormat("Y-m-d",$event["start_date"]);
  $end = DateTime::createFromFormat("Y-m-d",$event["end_date"]);
  $endend = clone $end;
  $endend->add(new DateInterval("P1D"));
  if($start <= $now) {
    $content .= '<p class="date"><span class="description">Started: </span>'.$start->format("F j, Y").'</p>';
  } else {
    $content .= '<p class="date"><span class="description">Starts: </span>'.$start->format("F j, Y").'</p>';
  }
  if($endend < $now) {
    $content .= '<p class="date"><span class="description">Ended: </span>'.$end->format("F j, Y").'</p>';
  } else {
    $content .= '<p class="date"><span class="description">Ends: </span>'.$end->format("F j, Y").'</p>';
  }

  $matches = getTeamEventMatches($team, $event["key"]);

  $matchsorted = array("qm"=>array(),"ef"=>array(),"qf"=>array(),"sf"=>array(),"f"=>array());
  foreach($matches as $match) {
    $matchsorted[$match["comp_level"]][] = $match;
  }
  foreach($matchsorted as $matchtype=>$matches) {
    if(!empty($matches)){
      $content .= getAccordion(switchLevel($matchtype),getMatchTable($matchtype,$matches,$team));
    }
  }



  // check is_null($status["playoff"]) to see if made playoffs for the event
  echo(getAccordion($event["name"],$content, ($start <= $now && $endend >= $now)));
}

 ?>
