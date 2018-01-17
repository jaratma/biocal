<?php
declare(strict_types=1);

class Bioengine {
    protected $isocodes = NULL;

    public function __construct($db=NULL, $iso=NULL) {
        $this->db = $db;
        $this->isocodes = $iso;
    }

    public function displayForm() {
        $html = '<form action="process.php" method="post" autocomplete="off">
    <fieldset>
        <legend>Datos de entrada</legend>
        <label for="calcname">Nombre</label>
        <input type="text" name="calcname" id="calcname" /> <br />
        <label for="surname">Apellidos</label>
        <input type="text" name="surname" id="surname" /> <br />
        <label for="calcdate">Fecha</label>
        <input type="date" name="calcdate" id="calcdate" /> <br />
        <label for="calctime">Hora</label>
        <input type="time" name="calctime" id="calctime" value="now" /> <br />
        <label for="city">Ciudad</label>
        <input type="text" name="city"
              id="search-box" value="" /><br />
        <div id="suggesstion-box"></div>
        <label for="country">País</label>
        <select name="country" id="country">';

        foreach ($this->isocodes as $key => $val) {
            $html .= '<option value="' . $key . '"';
            if ($val == 'España') {
                $html .= ' selected';
            }
            $html .= '>' . $val . '</option>';
        }

        $html .= '</select>
        <input type="hidden" name="action" value="calc" />
        <input type="submit" name="formdata" value="Calcular" />
    </fieldset>
</form>';
    return $html;
    }

    public function processForm() {
        if ( $_POST['action']!= 'calc' ) {
            return "The method processForm was accessed incorrectly";
        }

        //$city = htmlentities($_POST['city'], ENT_QUOTES);
        $city = $_POST['city'];
        $country = htmlentities($_POST['country'], ENT_QUOTES);
        $date = htmlentities($_POST['calcdate'], ENT_QUOTES);
        $time = htmlentities($_POST['calctime'], ENT_QUOTES);

        $query = "SELECT * from cities WHERE Name=:city AND Country=:country";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":city", $city, SQLITE3_TEXT);
            $stmt->bindValue(":country", $country, SQLITE3_TEXT);
            $res= $stmt->execute();
            $fetched = array();
            while($row = $res->fetchArray(SQLITE3_ASSOC)) {
                $fetched['Name'] = $row['Name'];
                $fetched['Latitude'] = $row['Latitude'];
                $fetched['Longitude'] = $row['Longitude'];
                $fetched['Country'] = $row['Country'];
                $fetched['Admin1'] = $row['Admin1'];
                $fetched['Timezone'] = $row['Timezone'];
            }
            $stmt->close();
            } catch ( Exception $e ) {
                return $e->getMessage();
            }

        $gmt =  new DateTimeZone('GMT');
        $tz = new DateTimeZone($fetched['Timezone']);
        $dt = new DateTime($date . " " . $time, $tz);
        $dt->setTimeZone($gmt);
        $usedate = implode(".", array_reverse(explode("-",$dt->format("Y-m-d"))));
        $usetime = $dt->format("H:i");

        $lat = $fetched['Latitude'];
        $lng = $fetched['Longitude'];
        $fields = array();
        $fields['date'] = $usedate;
        $fields['seq'] = "01At";
        $fields['long'] = $lng;
        $fields['lat'] = $lat;
        $fields['ut'] = $usetime;
        $sweres = $this->_foreignData($fields);
        //$swargs = "-edir'./lib/ephe' -b$usedate -fl -p01At -house$lng,$lat,K -ut$usetime -head";
        $seq = $this->_getPosData($sweres);
        $audios = array("I"  => "auragrama-I-rojo.wav",
                        "II"  => "auragrama-II-naranja.wav",
                        "III"  => "auragrama-III-amarillo.wav",
                        "IV"  => "auragrama-IV-verde.wav",
                        "V"  => "auragrama-V-azul.wav",
                        "VI"  => "auragrama-VI-violeta.wav",
                        "VII"  => "auragrama-VII-dorado.wav",
                        "VIII"  => "freq-campo-aurico-VIII.wav");
        $html = "<div class='audio'>";
        $playlist = "<ul id='playlist' style='display:none;'>";
        foreach ($seq as $au) {
          $playlist .= "<li><a href='./audio/" .$audios[$au]. "'></a></li>";
        }
        $playlist .= "</ul>";
        $html .= $playlist;
        $auseq = "<div><p id='labels'>";
        foreach ($seq as $au) {
          $auseq .= "<span class='roman'>" . $au . "</span>&nbsp"; 
        }
        $auseq .= "</p></div>";
        $html .= $auseq;
        $file = $audios['III'];
        $html .= '<audio id="player" controls controlsList="nodownload" preload="metadata" oncontextmenu="return false;"><source src="./audio/' . $file . '" type="audio/wav"/></audio><br/>';
        $html .= "</div>";
        $html .= "<div class='clearfix'><div id='spinner'></div><button id='savebut' onclick='collectaudio()'>Guardar</button>   </div> ";
        $html .= "<script type='text/javascript'>document.getElementById('player').addEventListener('ended',playalong,false);$('#labels').find('span')[0].classList.add('active');</script>";
        return $html;
    }

    private function _foreignData($fields) {
        $url = "https://astro-nex.net/nex/swargs.php";
        $fields_string = "";
        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }
        $fields_string = rtrim($fields_string,'&');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function _getPosData($sweres) {
        //$sweres = shell_exec("./lib/swetest $sweargs");
        $swearr = explode("\n",$sweres);
        $points = array();
        $points['sun'] = $swearr[0]; //7
        $points['earth'] = fmod($swearr[0] + 180.0, 360.0); //5
        $points['moon'] = $swearr[1]; //4
        $points['lil'] = $swearr[2]; //2
        $points['nnode'] = $swearr[3]; //6
        $points['snode'] = fmod($swearr[3] + 180.0, 360.0); //8
        $points['asc'] = $swearr[4]; // 3
        $minuslil = $swearr[2] - 30.0;
        if ($minuslil < 0) {
            $minuslil += 360.0;
        }
        $points['prevlil'] = $minuslil; //1

        asort($points, SORT_NUMERIC);
        foreach (array_keys($points) as $key) {
            if ($key == 'asc') {
                break;
            } else {
                $points[$key] = array_shift($points);
            }
        }
        $order = array('prevlil' => 'I', 'lil' => 'II', 'asc' => 'III', 'moon' => 'IV', 'earth' => 'V',
             'nnode' => 'VI', 'sun' => 'VII', 'snode' => 'VIII');
        $seq = array();
        foreach (array_keys($points) as $key) {
            $seq[] = $order[$key];
        }
        return $seq;
    }
}

?>
