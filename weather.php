<style>
.exWeather-container {
  margin: 40px;
}

.exWeather-container .wxWeekContainer {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
}

.exWeather-container .wxWeekContainer .wxTemp {
  font-weight: 700;
  font-size: 26px;
  color: #27491a;
  margin-top: 4px;
}

.wxCol {
  -webkit-box-flex: 1;
      -ms-flex-positive: 1;
          flex-grow: 1;
  text-align: center;
  margin: 5px;
  border: 1px solid #67bc45;
  background: #b1d8f8;
  padding: 10px;
}

.wxCurrentHeading {
  font-size: 20px;
  text-transform: uppercase;
  font-weight: 700;
  margin: 20px 0;
  color: #319807;
}

.wxCurrentInfoContainer {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
  margin: 40px;
}

.wxCurrentInfoContainer .wxFlexContainer {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
}

.wxCurrentInfoContainer .wxFlexContainer .wxImg {
  background-color: #b1d8f8;
  margin-right: 10px;
  border: 1px solid #67bc45;
}

.wxCurrentInfoContainer .wxFlexContainer .wxImg img {
  height: 150px;
}

.wxCurrentInfoContainer .wxFlexContainer .wxContent .wxDesc {
  font-weight: 700;
}

.wxPageIcon {
  height: 20px;
}

.wxPageIcon:before {
  font-family: 'FontAwesome';
  font-weight: 900;
  content: "\f185";
  color: #67bc45;
  font-size: 60px;
}

h4{
    margin-bottom: 0 !important;
}

audio {width:0px}
img {cursor:pointer}

/*# sourceMappingURL=wx.css.map */
</style>


<div class="weatherContainer">
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// get in the right timezone. 
date_default_timezone_set('America/Chicago');

// ----------------- Current Weather Render ------------------//
$weather = getCurrentWeather();


printf('<div class="wxCurrentInfoContainer">');
printf('<div class="wxCurrentHeading">Weather in %s on %s</div>', $weather['region'], $weather['date']) ;
printf('<div class="wxFlexContainer">');
printf('<div class="wxImg"><img src="%s"/></div>', $weather['sky']['iconPng']);
printf('<div class="wxContent">');
printf('<div class="wxDesc">%s</div>', ucwords( $weather['sky']['description'] ));
printf('<div class="wxTemp">Current Temp: %s&deg;</div>', round($weather['main']->temp, 0));
printf('<div class="wxWind">Wind: %s mph (%s)</div>' , round($weather['wind']->speed, 0), considerWind( round($weather['wind']->speed, 0)));
printf('<div class="wxFeels">Feels Like: %s&deg;</div>', round($weather['main']->feels_like, 0));
printf('<div class="wxHumidity">Humidity: %s&#37;</div>', $weather['main']->humidity);
printf('<div class="sunrise">Sunrise: %s</div>', $weather['sunrise'] );
printf('<div class="sunset">Sunset: %s</div>', $weather['sunset'] );
printf('</div><!--wxContent-->');
printf('</div><!-- wxFlexContainer-->');
printf('</div><!-- wxCurrentInfoContainer -->');
printf('</div> <!-- weather-container -->');

// --------------- Weekly Weather Outlook Render --------------------//
 
print('<div class="exWeather-container">');
print('<div class="wxCurrentHeading">Extended Forecast</div>');
print('<div class="wxWeekContainer">');
 
foreach( getExtendedWeather() as $exWeather ){
 
 $wDescr = considerWind($exWeather['wind']);

    printf('<div class="wxCol">');
    printf('<div class="wxDate">%s</div>', date('l', $exWeather['dt']) );
    printf('<div class="wxImg"><img src="%s"/></div>', $exWeather['icon'] );
    printf('<div class="wxDesc">%s</div>', ucwords($exWeather['sky']) );
    printf('<div class="wxWDesc">%s winds</div>', $wDescr );
    printf('<div class="wxTemp">%s&deg;</div>', $exWeather['temp']);
    printf('</div>');
}
 
print('</div></div>');

/**
* Weather Functions
*
*/
function getCurrentWeather(){

      $apiKey   = '9a0cdf9e0f07c98899ac00fd70e699f2';
    //   $url      = "https://api.openweathermap.org/data/2.5/weather?id=4644585&units=imperial&appid=$apiKey";
      $url      = "https://api.openweathermap.org/data/2.5/weather?zip=37188&units=imperial&appid=$apiKey";
      $arRes    = file_get_contents($url);
      $weather  = json_decode($arRes);


      $o                        = array('sky' => null);
      $o['date']                = date('g A l F jS', $weather->dt);
      $o['sky']['description']  = $weather->weather[0]->description;
      $o['sky']['iconPng']      = "https://openweathermap.org/img/wn/".$weather->weather[0]->icon."@2x.png";
      $o['main']                = $weather->main;
      $o['sunrise']             = date('g:i A', $weather->sys->sunrise);
      $o['sunset']              = date('g:i A', $weather->sys->sunset);
      $o['wind']                = $weather->wind;
      $o['region']              = $weather->name;
    
      
      return $o;
    }
    
    function getExtendedWeather(){
    
      $apiKey   = '9a0cdf9e0f07c98899ac00fd70e699f2';
    //   $url      = "https://api.openweathermap.org/data/2.5/forecast?id=4644585&units=imperial&appid=$apiKey";
      $url      = "https://api.openweathermap.org/data/2.5/forecast?zip=37188&units=imperial&appid=$apiKey";
      $arRes    = file_get_contents($url);
      $weather  = json_decode($arRes);
      $today    = date('D');
      $payload  = array();
      $arrOut   = array();

      
      foreach($weather->list as $seg){
    
        // Set timestamp to "Today XX:XX AM/PM" if it's todays seg, else show date and time
        $niceDate = $today == date('D', $seg->dt) ? "Today " . date('g A', $seg->dt) : date('l g A', $seg->dt);
       
        // turn night icons into day icons
        $find  = "/n@/";
        $repl  = "d@";

        $myIcon = preg_replace($find, $repl, $seg->weather[0]->icon."@2x.png");
        array_push($payload, array(
          'date'   => $seg->dt,
          'niceDt'  => $niceDate,
          'temp'   => round($seg->main->temp_max, 0),
          'sky'    => $seg->weather[0]->description,
          'icon'   => "https://openweathermap.org/img/wn/".$seg->weather[0]->icon."@2x.png",
          'wind'   => round($seg->wind->speed),
          'seg'    => $seg
        ));
      }
     
      // We now filter the payload, to get 10 O'clock PM stamp and use that for the day's data
      // =====================================================================================
      foreach($payload as $hour){

        $final = array();
       
        if( (int) date('H', $hour['date']) > 20 && stripos( $hour['niceDt'], "Today") === false ):
            $final['dt']   = $hour['date'];
            $final['niceDt'] = $hour['niceDt'];
            $final['temp']  = $hour['temp'];
            $final['sky']   = $hour['sky'];
            $final['icon']  = $hour['icon'];
            $final['wind']  = $hour['wind'];
            array_push($arrOut, $final);
        endif;
      }
     
      return $arrOut;
    }
    
    // Convert MPH to description for winds
    function considerWind( $speed ){
    
      switch (TRUE){
        case ($speed < 5):
          $desc = "Calm";
        break;
        case( $speed >= 5 && $speed <= 10 ):
          $desc = "Light";
        break;
        case( $speed >10 && $speed <= 20 ):
          $desc = "Breezy";
        break;
        case ( $speed > 20 && $speed <= 25 ):
          $desc = "Gusty";
        break;
        case ( $speed > 25 & $speed <= 30 ):
          $desc = "Strong";
        break;
        case ( $speed > 30 && $speed <= 40 ):
          $desc = "Gale Force";
        break;
        case ( $speed > 40 ):
          $desc = "Dangerous";
        break;
      }
    
      return $desc;
    
    }