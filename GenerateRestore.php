<?php


if (isset( $_FILES['RestoreFile']) && $_FILES['RestoreFile']['error'] === UPLOAD_ERR_OK
 || isset($_GET['RestoreFile'])
) 
{
    if (isset($_GET['PlayerPos'])) {

    //$file_content =  isset($_GET['RestoreFile']) ?  fopen($_GET['RestoreFile'], 'r');  $_FILES['RestoreFile'];
     $filename  = isset($_GET['RestoreFile']) ? $_GET['RestoreFile']  :$_FILES['RestoreFile']['tmp_name'];
     //echo   $filename  ; 
     $PlayerPos = $_GET['PlayerPos']; 
     $PlayerName = $_GET['PlayerName'];
     //echo $filename;
    

$temp_file= tmpfile(); 
// Set the appropriate headers to force a download.
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename( $filename ) . '"');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');


$original_content = file_get_contents($filename);
fwrite($temp_file, $original_content);

// Apply the modifications to the temporary file.
// fseek() and fwrite() will overwrite bytes without changing the file size.
$offset1 = 0x590;
fseek($temp_file, $offset1); // Player restore position
fwrite($temp_file, pack('C', $PlayerPos));

$offset2 = 0x594;
fseek($temp_file, $offset2); // Player restore position
fwrite($temp_file, pack('C', $PlayerPos));

$offset3 = 0x508;
fseek($temp_file, $offset3); // Name
fwrite($temp_file, $PlayerName);

// Rewind the file pointer back to the beginning of the temporary file.
rewind($temp_file);

// Send the content of the temporary file to the browser.
fpassthru($temp_file);

// Close the file handle.
fclose($temp_file);

exit;
/*
        $ptr = fopen($filename , 'wb');

         //fwrite($ptr, pack('C', $bytes[$i]));
        $offset1 = 0x590 ;
        fseek($ptr, $offset1);//player restore pos
        fwrite($ptr, pack('C', $PlayerPos));
        $offset1 = 0x594 ;
        fseek($ptr, $offset1);//player restore pos
        fwrite($ptr, pack('C', $PlayerPos));
        $offset1 = 0x508 ;
        fseek($ptr, $offset1);//name 
        fwrite($ptr, $PlayerName); 

        fclose($ptr);
*/
    }
}
 
// 1. Check if the filename parameter exists in the URL
if (isset($_GET['DeleteRestore'])) {

    // 2. Define the directory where files are stored
    $target_dir = "Aoe2Restore/"; 

    // 3. Sanitize the filename to prevent directory traversal attacks
    $filename = basename($_GET['DeleteRestore']);

    // 4. Construct the full, safe file path
    $file_path = $target_dir . $filename;

    // 5. Verify the file exists and is within the correct directory
    if (file_exists($file_path) && strpos(realpath($file_path), realpath($target_dir)) === 0) {
        
        // 6. Attempt to delete the file
        if (unlink($file_path)) {
            echo "File '" . htmlspecialchars($filename) . "' was deleted successfully.";
        } else {
            echo "Error: Could not delete the file.";
        }
    } else {
        echo "Error: File not found or invalid path.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"> 


	<title>Generate Restore To Any Player</title>
</head>
<body>
        <style>
@import url(https://fonts.googleapis.com/css?family=Raleway);
body {
  margin: 0px;
}
nav {
  margin-top: 40px;
  padding: 24px;
  text-align: center;
  font-family: Raleway;
  box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
}
#nav-1 {
  background: #3fa46a;
}
#nav-2 {
  background: #5175C0;
}
#nav-3 {
  background: #EEA200;
}

.link-1 {
  transition: 0.3s ease;
  background: #3fa46a;
  color: #ffffff;
  font-size: 20px;
  text-decoration: none;
  border-top: 4px solid #3fa46a;
  border-bottom: 4px solid #3fa46a;
  padding: 20px 0;
  margin: 0 20px;
}
.link-1:hover {
  border-top: 4px solid #ffffff;
  border-bottom: 4px solid #ffffff;
  padding: 6px 0; 
}

.link-1:hover {
  border-top: 4px solid #ffffff;
  border-bottom: 4px solid #ffffff;
  padding: 6px 0; 
    color:  #ffffff;
  text-decoration: underline; /* Add the underline back on hover */
}
 

/* Style for a link when the mouse is hovering over it */
a:hover {
  color: none;
  text-decoration: underline; /* Add the underline back on hover */
}
.link-2:hover {
  border-right: 2px dotted #ffffff;
  padding-bottom: 24px;
}
.link-3 {
  transition: 0.4s;
  color: #ffffff;
  font-size: 20px;
  text-decoration: none;
  padding: 0 10px;
  margin: 0 10px;
}
.link-3:hover {
  background-color: #ffffff;
  color: #EEA200;
  padding: 24px 10px;
}
.Mylogo
{
 font-size: 35px; 
}
    </style>
    <BR>
<nav id="nav-1">
  <a class="link-1" href="https://aoe2recanalyst.byethost16.com/">Record Analyst</a>
  <a class="link-1" href="https://aoe2recanalyst.byethost16.com/GenerateRestore">Restore Generation</a> 
</nav>
<br>
<br>
    <div class="table-container container Page">
		<form action = "uploadRestoreFile.php" method = "POST" enctype = "multipart/form-data"  id="sub">
			<input type="file" id="RestoreFile" name="RestoreFile"  accept=".mgs, .msx, .msz "> 
			<br>
			<input type="submit" name="submit"> 
		</form>
	</div>
<br>
    
 <script>
     function copy_link(linkToCopy)
     {
          navigator.clipboard.writeText(linkToCopy);
     }

 </script>
 
	<div class="container Page">   
     <table id="rec" class="table" > 
      <thead>
        <tr>
          <th scope="col">Players</th> 
          <th scope="col">Download</th> 
          <th scope="col">Share Link</th> 
        </tr> 
      </thead>
      <tbody>


<?php



class ColorUtils
{
    public static function lightenHexColor(string $hexColor, float $percent): string
    {
        // Remove the '#' if present
        $hexColor = ltrim($hexColor, '#');

        // Ensure it's a valid 6-digit hex color
        if (strlen($hexColor) !== 6) {
            // You might want to throw an exception or return a default color
            return '#000000'; // Return black for invalid input
        }

        // Convert hex to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        // Lighten each RGB component
        $r = round($r + (255 - $r) * $percent);
        $g = round($g + (255 - $g) * $percent);
        $b = round($b + (255 - $b) * $percent);

        // Clamp values to stay within 0-255 range
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        // Convert RGB back to hex
        return sprintf("#%02X%02X%02X", $r, $g, $b);
    }

    public static function getPlayerColor($id)
    {
    		$data = array( 
		        '0' => '#0000ff',
		        '1' => '#ff0000',
		        '2' => '#00ff00',
		        '3' => '#ffff00',
		        '4' => '#00ffff',
		        '5' => '#ff00ff',
		        '6' => '#434343',
		        '7' => '#ff8201',
		        '8' => '#000000',
		        '9' => '#000000',
		        '10' => '#000000',
		        '11' => '#0000ff',
		        '12' => '#ffff00',
		        '13' => '#ffffff',
		        '14' => '#ff0000', 
				);   
        return ColorUtils::lightenHexColor($data[$id-1],0.474);
    }

 }
 class civ
 {
 	public static function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $host .'/GenerateRestore' ;//. $uri  GenerateRestore.php
}
    public static function getCivImageByName($Name)
    {                       
//getCurrentUrl().
        $p =   'https://aoe2recanalyst.byethost16.com/resources/images/Civs_Emblems/'. $Name.'.png';
     
       // echo $p;
        /*
          if (is_file($p)) {
                // Turn the image into a data URL.
                return  $p ;//ImageManagerStatic::make($p)->encode('data-url');
              }
              */
            return $p ;
    }
    const NONE       = 0; 
    const BRITONS    = 1;
    const FRANKS     = 2;
    const GOTHS      = 3;
    const TEUTONS    = 4;
    const JAPANESE   = 5;
    const CHINESE    = 6;
    const BYZANTINES = 7;
    const PERSIANS   = 8;
    const SARACENS   = 9;
    const TURKS      = 10;
    const VIKINGS    = 11;
    const MONGOLS    = 12;
    const CELTS      = 13;
    const SPANISH    = 14;
    const AZTECS     = 15;
    const MAYANS     = 16;
    const HUNS       = 17;
    const KOREANS    = 18;
    const ITALIANS   = 19;
    const INDIANS    = 20;
    const INCAS      = 21;
    const MAGYARS    = 22;
    const SLAVS      = 23;
    const PORTUGUESE = 24;
    const ETHIOPIANS = 25;
    const MALIANS = 26;
    const BERBERS = 27;
    const KHMER = 28;
    const MALAY = 29;
    const BURMESE = 30;
    const VIETNAMESE = 31;
    const CUMANS = 32;
    const LITHUANIANS = 33;
    const BULGARIANS = 34;
    const TATARS = 35;
    const BURGUNDIANS = 36;
    const SICILIANS = 37;
    const POLES = 38;
    const BOHEMIANS = 39;
    const DRAVIDIANS = 40;
    const BENGALIS = 41;
    const GURJARAS = 42;
    const ROMANS = 43;
    const ARMENIANS = 44;
    const GEORGIANS = 45;
    public static $CIV_NAMES = [
        self::NONE => '',
        self::BRITONS => 'Britons',
        self::FRANKS => 'Franks',
        self::GOTHS => 'Goths',
        self::TEUTONS => 'Teutons',
        self::JAPANESE => 'Japanese',
        self::CHINESE => 'Chinese',
        self::BYZANTINES => 'Byzantines',
        self::PERSIANS => 'Persians',
        self::SARACENS => 'Saracens',
        self::TURKS => 'Turks',
        self::VIKINGS => 'Vikings',
        self::MONGOLS => 'Mongols',
        self::CELTS => 'Celts',
        self::SPANISH => 'Spanish',
        self::AZTECS => 'Aztecs',
        self::MAYANS => 'Mayans',
        self::HUNS => 'Huns',
        self::KOREANS => 'Koreans',
        self::ITALIANS => 'Italians',
        self::INDIANS => 'Indians',
        self::INCAS => 'Incas',
        self::MAGYARS => 'Magyars',
        self::SLAVS => 'Slavs',
        self::PORTUGUESE => 'Portuguese',
        self::ETHIOPIANS => 'Ethiopians',
        self::MALIANS => 'Malians',
        self::BERBERS => 'Berbers',
        self::KHMER => 'Khmer',
        self::MALAY => 'Malay',
        self::BURMESE => 'Burmese',
        self::VIETNAMESE => 'Vietnamese',
        self::CUMANS => 'Cumans',
        self::LITHUANIANS => 'Lithuanians',
        self::BULGARIANS => 'Bulgarians',
        self::TATARS => 'Tatars',
        self::BURGUNDIANS => 'Burgundians',
        self::SICILIANS => 'Sicilians',
        self::POLES => 'Poles',
        self::BOHEMIANS => 'Bohemians',
        self::DRAVIDIANS => 'Dravidians',
        self::BENGALIS => 'Bengalis',
        self::GURJARAS => 'Gurjaras',
        self::ROMANS => 'Romans',
        self::ARMENIANS => 'Armenians',
        self::GEORGIANS => 'Georgians',

    ];
}

	if(isset( $_FILES['RestoreFile']) && $_FILES['RestoreFile']['error'] === UPLOAD_ERR_OK
    || isset($_GET['RestoreFile'])
    ){
	    // Get the temporary file path
   		$tempFilePath =isset($_GET['RestoreFile'])? $_GET['RestoreFile'] : $_FILES['RestoreFile']['tmp_name']; 
 
		if (file_exists($tempFilePath)) {
		    // Open the file in read mode
		    $file_handle = fopen($tempFilePath, 'r');
		    if ($file_handle) {    
		    	$begin = 0x670;
			    for ($i=0; $i < 8; $i++) 
			    {   
                    //echo filesize($tempFilePath);
			        fseek($file_handle, $i*0xA0 + $begin) ; 
	       			$byte = fread($file_handle, 1); 
	       			if( ord($byte) == '0')
	       			{
	       				break;
	       			}
                    
			        fseek($file_handle, $i*0xA0 + $begin)  ; 
		        	$PlayerColor = fread($file_handle, 1); 
		        	fseek($file_handle, ($i+1)*0xA0 + $begin -20) ;
		        	$PlayerCiv = fread($file_handle, 1); 
			        fseek($file_handle, $i*0xA0 + $begin+1) ; 
	       			$Name = fread($file_handle, 0xA0 -28); 
                    $ShareLink = civ::getCurrentUrl().'?PlayerPos='.($i+1).'&PlayerName='.urlencode(trim($Name)).'&RestoreFile='.urlencode( $tempFilePath);
                    echo '<tr> '.
                            '<td >'. 
                                '<p class="outlined-text" style="color:'. ColorUtils::getPlayerColor(ord($PlayerColor)) .'" >' . 
                                "<img class=\"Player-img\" src=\"".civ::getCivImageByName(strtolower(civ::$CIV_NAMES[ord($PlayerCiv)])) ."\"> " . 
                                trim($Name) .
                                '</p>'. //'Player:'.ord($byte). ':'
                                '<span class="small">'.civ::$CIV_NAMES[ord($PlayerCiv)].'</span>  '.
                            '</td>' ;
                    echo '<td  style="color:'. ColorUtils::getPlayerColor(ord($PlayerColor)) .'" >'. 
                             '<CENTER><a class="flat" href="'.civ::getCurrentUrl().'?PlayerPos='.($i+1).'&PlayerName='.trim($Name).'&RestoreFile='. $tempFilePath.'"> <i class="fa-solid fa-file-arrow-down Mylogo"></i></a></CENTER>'.
                            '</td>' ;
                     echo  '<td   style="color:'. ColorUtils::getPlayerColor(ord($PlayerColor)) .'" >'. 
                             '<CENTER><button class="flat"  onclick=copy_link(\''.$ShareLink.'\')><i class="fas fa-share-square Mylogo"></i></button><CENTER>'. 
                            '</td>'.
                         '</tr>';
                         
			    } 
		    } 
		}
			
	}
?>

      </tbody>
    </table>  
</div>



<div class="table-container container Page">   
     <table id="RecordFiles" class="table" > 
      <thead>
        <tr>
          <th scope="col">Uploaded Restore</th> 
          <th scope="col">Delete Restore</th> 
        </tr>
      </thead>
      <tbody>
 
 <?php
 
        $folderPath = 'Aoe2Restore/'; // Replace with the actual path to your folder 
 
        // Get all files and directories in the folder
        $files = glob($folderPath . '*'); 
        foreach ($files as $file) {
            if (is_file($file)) {
            echo '<tr> '.
                    '<td>'.
                        '<a href="'.civ::getCurrentUrl().'?RestoreFile='.$file.'">'.basename($file) .' </a>'.
                    '</td>'.
                    '<td>'. 
                        '<center><a href="'.civ::getCurrentUrl().'?DeleteRestore='.$file.'" onclick="return confirm(\'Are you sure?\');"><i class="fa fa-trash"></i> </a></center>'.
                    '</td>'.
                '</tr>';
            }
        } 
      
 ?>
 
      </tbody>
    </table>  
</div>


</body>
</html>